<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'store_id',
        'invoice_number',
        'supplier_name',
        'total_price',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }


    protected static function booted()
    {
        static::deleting(function ($purchase) {

            foreach ($purchase->items as $item) {
                $product = $item->product;

                if ($product) {
                    // Kurangi stok saat invoice dihapus
                    $product->decrement('stock', $item->qty);
                }
            }

            // optional: hapus items manual kalau belum cascade
            $purchase->items()->delete();
        });
    }
}
