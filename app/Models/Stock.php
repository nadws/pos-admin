<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['product_id', 'qty', 'note', 'store_id',];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
