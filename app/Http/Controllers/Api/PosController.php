<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
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
        $store = \App\Models\Store::where('slug', $slug)->firstOrFail();

        // 1. Ambil Penjualan 7 Hari Terakhir untuk Grafik
        $salesData = \App\Models\Order::where('store_id', $store->id)
            ->where('status', 'paid') // Hanya yang sudah dibayar
            ->where('created_at', '>=', Carbon::now()->subDays(6))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 2. Ambil Menu Terlaris (Top 3)
        $topProducts = \App\Models\OrderItem::whereHas('order', function ($q) use ($store) {
            $q->where('store_id', $store->id)->where('status', 'paid');
        })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_name')
            ->orderBy('total_qty', 'DESC')
            ->take(3)
            ->get();

        // 3. Ringkasan Statistik
        $totalRevenue = \App\Models\Order::where('store_id', $store->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->sum('total_price');

        return response()->json([
            'status' => 'success',
            'chart_data' => [
                'labels' => $salesData->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('D'); // Format nama hari (Sen, Sel, dsb)
                }),
                'values' => $salesData->pluck('total')
            ],
            'top_products' => $topProducts,
            'weekly_revenue' => $totalRevenue,
            'total_orders' => \App\Models\Order::where('store_id', $store->id)->count()
        ]);
    }
}
