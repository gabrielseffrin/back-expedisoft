<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Integration\LoadingOrderController;
use App\Http\Controllers\Integration\UserController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/integration/order', [LoadingOrderController::class, 'storeOrder']);
Route::post('/integration/user', [UserController::class, 'storeUser']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/order/{orderId?}', [OrderController::class, 'getOrder']);
});

Route::middleware(['integration.auth', 'throttle:integration'])->group(function () {
    Route::post('/integration/order', [LoadingOrderController::class, 'storeOrder']);
    Route::post('/integration/user', [UserController::class, 'storeUser']);
});
