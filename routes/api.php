<?php

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ApiTokenAuthenticate;
use Illuminate\Support\Facades\Route;

// Route Login Admin
Route::post('/login', [ApiController::class, 'login'])->name('api.login');

// Proteksi rute-rute API lainnya menggunakan ApiTokenAuthenticate middleware kustom
Route::middleware([ApiTokenAuthenticate::class])->group(function () {
    
    // CRUD Produk
    Route::get('/produk', [ApiController::class, 'listProduk']);
    Route::get('/produk/{id}', [ApiController::class, 'listProduk']);
    Route::post('/produk', [ApiController::class, 'createProduk']);
    Route::put('/produk/{id}', [ApiController::class, 'updateProduk']);
    Route::delete('/produk/{id}', [ApiController::class, 'deleteProduk']);

    // CRUD Transaksi (Pesanan)
    Route::get('/transaksi', [ApiController::class, 'listTransaksi']);
    Route::get('/transaksi/{id}', [ApiController::class, 'listTransaksi']);
    Route::post('/transaksi', [ApiController::class, 'createTransaksi']);
    Route::put('/transaksi/{id}', [ApiController::class, 'updateTransaksiStatus']);
    Route::delete('/transaksi/{id}', [ApiController::class, 'deleteTransaksi']);

    // CRUD Detail Transaksi
    Route::post('/transaksi_detail', [ApiController::class, 'createTransaksiDetail']);
    Route::delete('/transaksi_detail/{id}', [ApiController::class, 'deleteTransaksiDetail']);

    // Statistik
    Route::get('/stats', [ApiController::class, 'stats']);
});
