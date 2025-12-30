<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    // 🔥 PENTING: Biar bisa simpan data pakai create([])
    protected $guarded = [];

    // Relasi: Satu Order punya banyak Item (Barang)
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi: Order ini milik Toko mana?
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
