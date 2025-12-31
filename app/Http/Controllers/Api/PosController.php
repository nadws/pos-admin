<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;

class PosController extends Controller
{
    // 1. Ambil Detail Toko & Menunya
    public function getMenu($slug)
    {
        // Cari toko berdasarkan slug
        $store = Store::where('slug', $slug)->first();

        // Kalau toko gak ketemu, return error 404
        if (!$store) {
            return response()->json(['message' => 'Toko tidak ditemukan'], 404);
        }

        // Ambil semua produk yang "Available" saja
        // Kita load juga relasi 'category' biar tahu ini makanan/minuman
        $products = $store->products()
            ->where('is_available', true)
            ->with('category') // Eager load kategori
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'store' => [
                'name' => $store->name,
                'address' => $store->address,
                'logo' => $store->logo,
            ],
            'products' => $products
        ]);
    }

    public function storeOrder(Request $request, $slug)
    {
        // Cari Toko
        $store = Store::where('slug', $slug)->firstOrFail();

        // Validasi data yang dikirim Next.js
        $validated = $request->validate([
            'customer_name' => 'nullable|string',
            'payment_method' => 'required|in:cash,qris',
            'items' => 'required|array|min:1', // Harus ada barang
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|integer', // Harga dikirim dari front biar aman (atau ambil DB)
        ]);

        // Gunakan Transaction biar kalau error, data gak masuk setengah-setengah
        try {
            $order = DB::transaction(function () use ($store, $validated) {

                // Hitung Total Harga Server Side (Lebih aman)
                $totalPrice = 0;
                foreach ($validated['items'] as $item) {
                    $totalPrice += $item['price'] * $item['qty'];
                }

                // 1. Buat Data Order Utama
                $order = Order::create([
                    'store_id' => $store->id,
                    'invoice_number' => 'INV-' . time(), // Contoh INV-17098822
                    'customer_name' => $validated['customer_name'] ?? 'Pelanggan Umum',
                    'payment_method' => $validated['payment_method'],
                    'total_price' => $totalPrice,
                    'status' => 'pending', // Status awal: Menunggu dimasak
                ]);

                // 2. Simpan Detail Barang (Looping)
                foreach ($validated['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                    ]);

                    // Opsional: Kurangi Stok Produk di sini jika mau
                }

                return $order;
            });

            // TODO: Di sini nanti kita pasang kode REVERB (Realtime)

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat!',
                'order_id' => $order->invoice_number
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()], 500);
        }
    }

    public function markAsReady($id)
    {
        // Cari order berdasarkan ID
        $order = Order::findOrFail($id);

        // Ubah statusnya jadi 'ready'
        $order->update([
            'status' => 'ready'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan selesai dimasak!'
        ]);
    }

    public function getKitchenOrders($slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        $orders = Order::where('store_id', $store->id)
            ->where('status', 'pending') // Cuma ambil yang belum dimasak
            ->with('items.product')      // Sertakan data barang & nama produk
            ->orderBy('created_at', 'asc') // Urutkan dari yang pesan duluan
            ->get();

        return response()->json([
            'status' => 'success',
            'orders' => $orders
        ]);
    }

    // 5. Update Status PER ITEM
    public function markItemReady($itemId)
    {
        // 1. Cari Itemnya
        $item = OrderItem::findOrFail($itemId);

        // 2. Ubah status item jadi 'done'
        $item->update(['status' => 'done']);

        // 3. Cek Induknya (Order Utama)
        // Apakah masih ada teman-temannya yang statusnya 'pending'?
        $pendingItems = OrderItem::where('order_id', $item->order_id)
            ->where('status', 'pending')
            ->count();

        // 4. Jika pendingItems == 0, berarti SEMUA sudah dimasak
        if ($pendingItems == 0) {
            $order = Order::find($item->order_id);
            $order->update(['status' => 'ready']); // Order dianggap selesai total

            return response()->json([
                'status' => 'success',
                'message' => 'Item selesai. Order juga selesai sepenuhnya!',
                'order_completed' => true // Flag buat frontend
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item ditandai selesai.',
            'order_completed' => false
        ]);
    }

    public function getReports(Request $request, $slug)
    {
        // Gunakan try-catch agar jika ada error database, pesan errornya jelas
        try {
            $store = Store::where('slug', $slug)->firstOrFail();

            // 1. Ambil Penjualan 7 Hari Terakhir (Hanya status 'paid' atau 'ready')
            // Sesuaikan 'status' dengan apa yang kamu gunakan (misal: 'ready' jika sudah bayar)
            $salesData = Order::where('store_id', $store->id)
                ->whereIn('status', ['paid', 'ready'])
                ->where('created_at', '>=', Carbon::now()->subDays(6))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as total')
                )
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();

            // 2. Ambil Menu Terlaris (Top 3)
            // KUNCI PERBAIKAN: Join ke tabel products karena product_name tidak ada di order_items
            $topProducts = OrderItem::whereHas('order', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })
                ->join('products', 'order_items.product_id', '=', 'products.id') // Join ke produk
                ->select('products.name as product_name', DB::raw('SUM(order_items.quantity) as total_qty'))
                ->groupBy('products.name')
                ->orderBy('total_qty', 'DESC')
                ->take(3)
                ->get();

            // 3. Ringkasan Statistik
            $totalRevenue = Order::where('store_id', $store->id)
                ->whereIn('status', ['paid', 'ready'])
                ->where('created_at', '>=', Carbon::now()->startOfWeek())
                ->sum('total_price');
            $totalRevenueday = Order::where('store_id', $store->id)
                ->whereIn('status', ['paid', 'ready'])
                ->where('created_at', '>=', Carbon::now()->startOfDay())
                ->sum('total_price');

            $latestOrders = Order::with(['items.product']) // TAMBAHKAN WITH INI
                ->where('store_id', $store->id)
                ->whereDate('created_at', Carbon::today())
                ->latest()
                ->get();

            // Pastikan format return match dengan yang diminta frontend (data.chart_labels, dll)
            return response()->json([
                'status' => 'success',
                'data' => [
                    'chart_labels' => $salesData->pluck('date')->map(function ($date) {
                        return Carbon::parse($date)->format('D');
                    }),
                    'chart_values' => $salesData->pluck('total')->map(fn($v) => (int)$v),
                    'top_products' => $topProducts,
                    'weekly_revenue' => (int)$totalRevenue,

                    'daily_revenue' => (int)$totalRevenueday,
                    'total_orders' => Order::where('store_id', $store->id)->count(),
                    'total_orders_day' => Order::where('store_id', $store->id)->whereDate('created_at', Carbon::today())->count(),
                    'new_customers' => 0, // Opsional jika belum ada sistem user
                    'latest_orders' => $latestOrders
                ]
            ]);
        } catch (\Exception $e) {
            // Jika error, kirim pesan error aslinya agar gampang debug
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelOrder(Request $request, $slug, $id)
    {
        try {
            $store = Store::where('slug', $slug)->firstOrFail();
            $order = Order::where('store_id', $store->id)->where('id', $id)->firstOrFail();

            // Pastikan pesanan belum dibatalkan sebelumnya
            if ($order->status === 'cancelled') {
                return response()->json(['status' => 'error', 'message' => 'Pesanan sudah dibatalkan'], 400);
            }

            // Update status jadi cancelled
            $order->update(['status' => 'cancelled']);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getClosingReport(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();
        $today = \Carbon\Carbon::today();

        // Hitung total Cash
        $cashTotal = Order::where('store_id', $store->id)
            ->where('status', 'ready')
            ->where('payment_method', 'cash')
            ->whereDate('created_at', $today)
            ->sum('total_price');

        // Hitung total QRIS
        $qrisTotal = Order::where('store_id', $store->id)
            ->where('status', 'ready')
            ->where('payment_method', 'qris')
            ->whereDate('created_at', $today)
            ->sum('total_price');

        // Hitung pesanan yang dibatalkan (untuk audit)
        $cancelledCount = Order::where('store_id', $store->id)
            ->where('status', 'cancelled')
            ->whereDate('created_at', $today)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'date' => $today->format('d M Y'),
                'cash_total' => (int)$cashTotal,
                'qris_total' => (int)$qrisTotal,
                'grand_total' => (int)($cashTotal + $qrisTotal),
                'total_orders' => Order::where('store_id', $store->id)->where('status', 'ready')->whereDate('created_at', $today)->count(),
                'cancelled_orders' => $cancelledCount
            ]
        ]);
    }

    public function getEmployees($slug)
    {
        $store = \App\Models\Store::where('slug', $slug)->first();

        if (!$store) {
            return response()->json(['message' => 'Toko tidak ditemukan'], 404);
        }

        // Ambil user yang merupakan anggota dari toko tersebut
        $employees = $store->members()
            ->select('users.id', 'users.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function verifyPin(Request $request)
    {
        // Gunakan validasi yang lebih fleksibel untuk debugging
        $request->validate([
            'user_id' => 'required',
            'pin' => 'required|string',
        ]);

        // Trim PIN untuk menghindari spasi tak kasat mata
        $userPin = trim($request->pin);

        $user = User::where('id', $request->user_id)
            ->where('pin', $userPin)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'PIN salah',
                // Hapus baris debug ini jika sudah produksi:
                'debug_received' => $userPin
            ], 401);
        }

        $token = $user->createToken('pos_device_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name]
        ]);
    }
}
