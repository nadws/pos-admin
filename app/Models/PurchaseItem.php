<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {

        static::created(function ($item) {
            if ($item->product) {
                $item->product->increment('stock', $item->qty);
            }
        });

        static::updating(function ($item) {
            if ($item->product && $item->isDirty('qty')) {
                $oldQty = $item->getOriginal('qty');
                $newQty = $item->qty;

                $diff = $newQty - $oldQty;

                $item->product->increment('stock', $diff);
            }
        });

        static::deleted(function ($item) {
            if ($item->product) {
                $item->product->decrement('stock', $item->qty);
            }
        });
    }
}
