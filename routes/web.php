<?php

use App\Models\StockOpname;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/stock-opname/{record}/print', function (StockOpname $record) {
    return view('print-opname', ['opname' => $record]);
})->name('stock-opname.print');
