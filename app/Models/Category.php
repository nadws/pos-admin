<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $guarded = [];
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Kategori punya banyak produk
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
