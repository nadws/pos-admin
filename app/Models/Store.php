<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'subscription_until',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_until' => 'datetime',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    // Relasi: Toko ini punya apa aja?
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function isSubscriptionActive(): bool
    {
        // Cek apakah tombol lunas aktif DAN tanggal berakhirnya masih di masa depan
        return $this->is_active && $this->subscription_until && $this->subscription_until->isFuture();
    }
}
