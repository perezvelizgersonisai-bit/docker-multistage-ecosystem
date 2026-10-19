<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — E-commerce
|--------------------------------------------------------------------------
*/

// -----------------------------------------------------------------
// Autenticación
// -----------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// -----------------------------------------------------------------
// Catálogo de productos: lectura pública, escritura protegida
// -----------------------------------------------------------------
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id');

    Route::middleware('jwt.auth')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update'])->whereNumber('id');
        Route::patch('/{id}', [ProductController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
    });
});

// -----------------------------------------------------------------
// Órdenes de compra y pago con Stripe — todo protegido
// -----------------------------------------------------------------
Route::middleware('jwt.auth')->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show'])->whereNumber('id');
    Route::post('/{id}/pay', [OrderController::class, 'pay'])->whereNumber('id');
});
