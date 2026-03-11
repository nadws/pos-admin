<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    protected $fillable = [
        'product_id',
        'ingredient_id',
        'quantity',
        'unit_id',
    ];

    // Relasi balik ke Produk yang dijual (misal: Risol)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relasi ke bahan bakunya (misal: Ayam suir/Tepung yang juga ada di tabel products)
    public function ingredient()
    {
        return $this->belongsTo(Product::class, 'ingredient_id');
    }
}
