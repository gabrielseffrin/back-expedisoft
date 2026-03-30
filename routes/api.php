<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DockController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Integration\DockController as IntegrationDockController;
use App\Http\Controllers\Integration\LoadingOrderController;
use App\Http\Controllers\Integration\UserController as IntegrationUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/health', HealthController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/operators', [UserController::class, 'getOperators']);

    Route::get('/order/{orderId?}', [OrderController::class, 'getOrder']);
    Route::post('/order/schedule-order', [OrderController::class, 'scheduleOrder']);

    route::get('/docks', [DockController::class, 'getAllDocks']);
});

Route::middleware(['integration.auth', 'throttle:integration'])->group(function () {
    Route::post('/integration/order', [LoadingOrderController::class, 'storeOrder']);
    Route::post('/integration/user', [IntegrationUserController::class, 'storeUser']);
    Route::post('/integration/dock', [IntegrationDockController::class, 'storeDock']);
});
