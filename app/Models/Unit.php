<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'store_id',

    ];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
