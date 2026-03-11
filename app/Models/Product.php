<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = [];

    // Produk ini milik Toko mana?
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Produk ini masuk kategori apa?
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function recipes()
    {
        return $this->hasMany(RecipeItem::class, 'product_id');
    }
    public function unitConversions()
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
