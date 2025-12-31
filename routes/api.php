<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pos/{slug}/menu', [PosController::class, 'getMenu']);
    // Simpan Pesanan
    Route::post('/pos/{slug}/checkout', [PosController::class, 'storeOrder']);
    Route::post('/orders/{id}/ready', [PosController::class, 'markAsReady']);
    Route::get('/pos/{slug}/kitchen', [PosController::class, 'getKitchenOrders']);
    // Ganti route markReady yg lama, atau tambah baru khusus item
    Route::post('/order-items/{id}/ready', [PosController::class, 'markItemReady']);
    Route::get('/pos/{slug}/reports', [PosController::class, 'getReports']);
    Route::post('/pos/{slug}/orders/{id}/cancel', [PosController::class, 'cancelOrder']);
    Route::get('/pos/{slug}/closing', [PosController::class, 'getClosingReport']);
    Route::get('/pos/{slug}/employees', [PosController::class, 'getEmployees']);
});

// Route Logout (Harus punya Token)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// routes/api.php
Route::middleware('auth:sanctum')->get('/my-stores', function (Request $request) {
    return response()->json([
        'status' => 'success',
        // Ambil semua toko milik user yang login
        'stores' => $request->user()->stores
    ]);
});
