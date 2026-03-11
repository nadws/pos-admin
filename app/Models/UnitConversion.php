<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    protected $fillable = ['product_id', 'from_unit_id', 'multiplier'];

    public function fromUnit()
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }
}
