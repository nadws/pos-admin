<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_stock',
        'actual_stock',
        'difference',
    ];

    /**
     * Logic Otomatis Update Stok Produk
     */
    protected static function booted()
    {
        static::created(function ($item) {
            // Cek apakah statusnya published, baru update stok
            if ($item->stockOpname->status === 'published') {
                $item->product->update([
                    'stock' => $item->actual_stock
                ]);
            }
        });

        static::updated(function ($item) {
            // Jika saat edit status berubah jadi published
            if ($item->stockOpname->status === 'published') {
                $item->product->update([
                    'stock' => $item->actual_stock
                ]);
            }
        });
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
