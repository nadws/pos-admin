<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Models\StockMutation;
use Filament\Facades\Filament;
use App\Models\UnitConversion;

class PurchaseItemObserver
{

    public function created(PurchaseItem $purchaseItem): void
    {
        $product = $purchaseItem->product;

        if ($product) {
            $qtyBeli = $purchaseItem->qty; // Angka 1 dari form
            $multiplier = 1; // Default jika satuan sama dengan satuan dasar

            // CEK KONVERSI: Jika satuan yang dipilih (unit_id) beda dengan satuan dasar produk
            if ($purchaseItem->unit_id && $purchaseItem->unit_id != $product->unit_id) {
                // Cari angka pengalinya di tabel konversi (misal: cari angka 1000 untuk Kg ke Gr)
                $conversion = UnitConversion::where('product_id', $product->id)
                    ->where('from_unit_id', $purchaseItem->unit_id)
                    ->first();

                if ($conversion) {
                    $multiplier = $conversion->multiplier; // Ini yang isinya 1000
                }
            }

            // HITUNG TOTAL: 1 Kg * 1000 = 1000 Gram
            $totalMasuk = $qtyBeli * $multiplier;

            // 1. UPDATE STOK PRODUK (Tambah 1000, bukan 1)
            $stokLama = $product->stock;
            $product->increment('stock', $totalMasuk);

            // 2. CATAT KE KARTU STOK
            StockMutation::create([
                'product_id'      => $product->id,
                'store_id'        => $product->store_id ?? Filament::getTenant()?->id,
                'type'            => 'in',
                'quantity'        => $totalMasuk, // Catat 1000 di riwayat
                'reference_stock' => $stokLama,
                'reference_type'  => 'purchase',
                'reference_id'    => $purchaseItem->purchase_id,
                'description'     => "Pembelian Faktur #" . ($purchaseItem->purchase->invoice_number ?? '-'),
            ]);
        }
    }
    public function updated(PurchaseItem $purchaseItem): void
    {
        //
    }
    public function deleted(PurchaseItem $purchaseItem): void
    {
        //
    }

    public function restored(PurchaseItem $purchaseItem): void
    {
        //
    }

    public function forceDeleted(PurchaseItem $purchaseItem): void
    {
        //
    }
}
