<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // 🔥 PENTING: Biar bisa simpan data pakai create([])
    protected $guarded = [];

    // Relasi: Item ini terhubung ke Order utama
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi: Item ini sebenarnya Produk apa? (Biar bisa ambil nama & gambarnya)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
