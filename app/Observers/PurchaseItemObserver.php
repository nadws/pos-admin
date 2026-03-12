<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Models\StockMutation;

class PurchaseItemObserver
{

    public function created(PurchaseItem $purchaseItem): void
    {
        $product = $purchaseItem->product;

        if ($product) {
            $qty = $purchaseItem->qty;
            $multiplier = 1;
            $totalAdd = $qty * $multiplier;
            $oldStock = $product->stock;
            $product->increment('stock', $totalAdd);
            StockMutation::create([
                'product_id' => $product->id,
                'store_id' => $product->store_id,
                'type' => 'in',
                'quantity' => $totalAdd,
                'reference_stock' => $oldStock,
                'reference_type' => 'purchase',
                'reference_id' => $purchaseItem->purchase_id,
                'description' => "Pembelian dari Supplier: " . ($purchaseItem->purchase->supplier_name ?? '-'),
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
