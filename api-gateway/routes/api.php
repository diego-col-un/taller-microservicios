<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GatewayController;

// ─────────────────────────────────────────
// Rutas públicas - Autenticación
// ─────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ─────────────────────────────────────────
// Rutas protegidas - Requieren JWT
// ─────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Microservicio Productos (Flask)
    Route::any('/productos/{path?}', [GatewayController::class, 'productos'])
        ->where('path', '.*');

    // Microservicio Ventas (Express)
    Route::any('/ventas/{path?}', [GatewayController::class, 'ventas'])
        ->where('path', '.*');
    
    // Flujo completo de venta
    Route::post('/registro-venta', [GatewayController::class, 'registrarVenta']);
});