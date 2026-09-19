<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/customers/{email}/orders', [OrderController::class, 'history'])
    ->where('email', '.*'); // allow the dots/@ in an email in the URL segment

Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
