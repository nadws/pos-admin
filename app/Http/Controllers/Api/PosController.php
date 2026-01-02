<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyRegister;
use App\Models\Store;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosController extends Controller
{
    // 1. Ambil Detail Toko & Menunya
    public function getMenu($slug)
    {
        $store = Store::where('slug', $slug)->first();

        if (!$store) {
            return response()->json(['message' => 'Toko tidak ditemukan'], 404);
        }

        $products = $store->products()
            ->where('is_available', true)
            ->with('category')
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

    // 2. Simpan Order (Checkout)
    public function storeOrder(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => 'nullable|string',
            'payment_method' => 'required|in:cash,qris',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|integer',
            'money_received' => 'nullable|numeric', // Tambahan untuk catat uang diterima
            'change' => 'nullable|numeric'          // Tambahan untuk catat kembalian
        ]);

        try {
            $order = DB::transaction(function () use ($store, $validated) {
                $totalPrice = 0;
                foreach ($validated['items'] as $item) {
                    $totalPrice += $item['price'] * $item['qty'];
                }

                $order = Order::create([
                    'store_id' => $store->id,
                    'invoice_number' => 'INV-' . time() . rand(100, 999),
                    'customer_name' => $validated['customer_name'] ?? 'Pelanggan Umum',
                    'payment_method' => $validated['payment_method'],
                    'total_price' => $totalPrice,
                    'status' => 'pending', // Langsung completed karena POS (bayar di muka)
                    // 'kitchen_status' => 'pending', // Masuk antrian dapur
                    // Simpan info pembayaran jika perlu (opsional, buat kolom baru di migration orders jika mau)
                    // 'money_received' => $validated['money_received'] ?? 0,
                    // 'change' => $validated['change'] ?? 0,
                ]);

                foreach ($validated['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['qty'],
                        'price' => $item['price'],

                        'status' => 'pending',
                    ]);
                }

                return $order;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat!',
                'order' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()], 500);
        }
    }

    // 3. Laporan Dashboard (PERBAIKAN UTAMA DISINI)
    public function getReports(Request $request, $slug)
    {
        try {
            $store = Store::where('slug', $slug)->firstOrFail();

            // --- LOGIKA SHIFT / BUKA TOKO ---
            // Cari shift yang sedang OPEN
            $currentRegister = DailyRegister::where('store_id', $store->id)
                ->where('status', 'open')
                ->latest()
                ->first();

            // Jika toko BUKA, hitung mulai dari jam buka shift. 
            // Jika TUTUP, set waktu ke 'sekarang' (agar hasil pencarian pesanan jadi 0/kosong)
            $startTime = $currentRegister ? $currentRegister->created_at : Carbon::now();

            // A. Revenue Shift Ini (Hanya hitung setelah jam buka toko)
            $currentShiftRevenue = Order::where('store_id', $store->id)
                ->whereIn('status', ['paid', 'ready', 'completed'])
                ->where('created_at', '>=', $startTime)
                ->sum('total_price');

            // B. Total Order Shift Ini
            $currentShiftOrders = Order::where('store_id', $store->id)
                ->where('created_at', '>=', $startTime)
                ->count();

            // C. Order Terbaru (Hanya shift ini)
            $latestOrders = Order::with(['items.product'])
                ->where('store_id', $store->id)
                ->where('created_at', '>=', $startTime)
                ->latest()
                ->take(10)
                ->get();


            // --- DATA GLOBAL / HISTORICAL (Tetap Harian/Mingguan) ---

            // D. Grafik 7 Hari Terakhir
            $salesData = Order::where('store_id', $store->id)
                ->whereIn('status', ['paid', 'ready', 'completed'])
                ->where('created_at', '>=', Carbon::now()->subDays(6))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as total')
                )
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();

            // E. Top Products (Tetap ambil Global/Harian agar data tidak kosong saat baru buka)
            // Atau bisa diubah filter waktunya jika mau Top Product per Shift juga
            $topProducts = OrderItem::whereHas('order', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select('products.name as product_name', DB::raw('SUM(order_items.quantity) as total_qty'))
                ->groupBy('products.name')
                ->orderBy('total_qty', 'DESC')
                ->take(3)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    // Data Grafik
                    'chart_labels' => $salesData->pluck('date')->map(fn($d) => Carbon::parse($d)->format('D')),
                    'chart_values' => $salesData->pluck('total')->map(fn($v) => (int)$v),

                    // Data Dashboard (Shift Ini)
                    'daily_revenue' => (int)$currentShiftRevenue, // Reset jadi 0 kalau baru buka
                    'total_orders_day' => $currentShiftOrders,    // Reset jadi 0 kalau baru buka

                    // Data Lainnya
                    'top_products' => $topProducts,
                    'latest_orders' => $latestOrders,
                    'weekly_revenue' => (int) Order::where('store_id', $store->id)->where('created_at', '>=', Carbon::now()->startOfWeek())->sum('total_price'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Data Karyawan
    public function getEmployees($slug)
    {
        $store = Store::where('slug', $slug)->first();
        if (!$store) return response()->json(['message' => 'Toko tidak ditemukan'], 404);

        $employees = $store->members()
            ->select('users.id', 'users.name', 'users.role')
            ->get();

        return response()->json(['success' => true, 'data' => $employees]);
    }

    // 5. Verifikasi PIN
    public function verifyPin(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'pin' => 'required|string',
        ]);

        $user = User::where('id', $request->user_id)->where('pin', trim($request->pin))->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'PIN salah'], 401);
        }

        $token = $user->createToken('pos_device_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name]
        ]);
    }

    // 6. Cek Status Toko (Untuk Modal Awal)
    public function getStoreStatus($slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        $register = DailyRegister::where('store_id', $store->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json([
            'is_open' => $register ? true : false,
            'data' => $register
        ]);
    }

    // 7. Buka Toko
    public function openStore(Request $request, $slug)
    {
        $request->validate(['start_cash' => 'required|numeric']);
        $store = Store::where('slug', $slug)->firstOrFail();

        // Cek double entry
        $existing = DailyRegister::where('store_id', $store->id)->where('status', 'open')->first();
        if ($existing) return response()->json(['message' => 'Toko sudah buka!'], 400);

        $register = DailyRegister::create([
            'store_id' => $store->id,
            'user_id' => $request->user()->id,
            'start_cash' => $request->start_cash,
            'status' => 'open'
        ]);

        return response()->json(['success' => true, 'data' => $register]);
    }

    // 8. Tutup Toko (Closing)
    public function closeStore(Request $request, $slug)
    {
        $request->validate(['end_cash' => 'required|numeric']);
        $store = Store::where('slug', $slug)->firstOrFail();

        $register = DailyRegister::where('store_id', $store->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$register) return response()->json(['message' => 'Toko sudah tutup'], 400);

        $register->update([
            'end_cash' => $request->end_cash,
            'status' => 'closed',
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Shift berhasil ditutup.']);
    }

    // 9. Laporan Closing (Untuk halaman Closing)
    public function getClosingReport(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        // Cari Shift Aktif
        $register = DailyRegister::where('store_id', $store->id)->where('status', 'open')->latest()->first();

        // Jika tidak ada shift aktif (toko tutup), ambil dari jam 00:00 hari ini
        $startTime = $register ? $register->created_at : Carbon::today();

        $cashTotal = Order::where('store_id', $store->id)
            ->whereIn('status', ['paid', 'ready', 'completed'])
            ->where('payment_method', 'cash')
            ->where('created_at', '>=', $startTime)
            ->sum('total_price');

        $qrisTotal = Order::where('store_id', $store->id)
            ->whereIn('status', ['paid', 'ready', 'completed'])
            ->where('payment_method', 'qris')
            ->where('created_at', '>=', $startTime)
            ->sum('total_price');

        $cancelledCount = Order::where('store_id', $store->id)
            ->where('status', 'cancelled')
            ->where('created_at', '>=', $startTime)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'date' => Carbon::now()->format('d M Y'),
                'cash_total' => (int)$cashTotal,
                'qris_total' => (int)$qrisTotal,
                'grand_total' => (int)($cashTotal + $qrisTotal),
                'total_orders' => Order::where('store_id', $store->id)->where('created_at', '>=', $startTime)->count(),
                'cancelled_orders' => $cancelledCount
            ]
        ]);
    }

    // 10. Fitur Dapur
    public function getKitchenOrders($slug)
    {
        $store = Store::where('slug', $slug)->firstOrFail();
        $orders = Order::where('store_id', $store->id)
            ->whereDate('created_at', Carbon::today()) // Ambil hari ini
            ->where('status', 'completed') // Sudah bayar
            ->where('kitchen_status', '!=', 'ready') // Belum selesai masak
            ->with('items.product')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $orders]);
    }

    // 11. Update Status Dapur
    // 11. Update Status Dapur & Kurangi Stok
    // 11. Update Status Dapur & Potong Stok Produk Langsung
    public function updateKitchenStatus(Request $request, $slug, $id)
    {
        $request->validate([
            'status' => 'required|in:cooking,ready'
        ]);

        $store = Store::where('slug', $slug)->firstOrFail();

        // Cari Ordernya
        $order = Order::with('items.product') // Pastikan load items & product
            ->where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        // LOGIKA UTAMA:
        // Jika status diubah jadi 'ready' DAN sebelumnya belum 'ready'
        if ($request->status === 'ready' && $order->kitchen_status !== 'ready') {

            try {
                DB::transaction(function () use ($order) {

                    // Loop semua item yang dipesan
                    foreach ($order->items as $item) {
                        $product = $item->product;

                        // Cek: Apakah produk ini stoknya harus dihitung?
                        // (Biasanya jasa/ongkir tidak punya stok, tapi barang punya)
                        if ($product) {
                            // RUMUS: Stok Lama - Jumlah Pesanan
                            // Contoh: Stok Aqua 10 - Pesan 2 = Sisa 8
                            $product->decrement('stock', $item->quantity);
                        }
                    }

                    // Ubah status jadi selesai masak
                    $order->update(['kitchen_status' => 'ready']);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan selesai! Stok produk telah dikurangi.'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saat potong stok: ' . $e->getMessage()
                ], 500);
            }
        }

        // Kalau status cuma diubah jadi 'cooking' atau dibatalkan, update status aja tanpa potong stok
        $order->update(['kitchen_status' => $request->status]);

        return response()->json(['success' => true]);
    }
}
