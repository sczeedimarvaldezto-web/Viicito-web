<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Middleware\EnsureUserRole;

/**
 * API Routes - Viicito Sistema de Licorería
 * 
 * Version: v1
 * Middleware: Sanctum para SPA con autenticación basada en sesiones
 */

// Middleware para SPA stateful - permite cookies de sesión en peticiones CORS
Route::middleware(['web', \Illuminate\Session\Middleware\StartSession::class])->group(function () {
    // ============================================
    // AUTENTICACIÓN
    // ============================================
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth');

    // ============================================
    // DASHBOARD + REPORTES + GESTIÓN DE SHOP
    // ============================================
    Route::middleware(['auth', EnsureUserRole::class.':owner'])->group(function () {
        Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
        Route::get('/dashboard/vendedores', [DashboardController::class, 'vendedores']);
        Route::get('/dashboard/tendencia-ventas', [DashboardController::class, 'tendenciaVentas']);
        Route::get('/dashboard/alertas-stock', [DashboardController::class, 'alertasStock']);
        Route::get('/dashboard/empleados', [DashboardController::class, 'empleados']);

        Route::apiResource('categorias', CategoriaController::class);
        Route::get('/categorias/{categoria}/productos', [CategoriaController::class, 'productos']);

        Route::apiResource('compras', CompraController::class);
        Route::post('/compras/{compra}/recibir', [CompraController::class, 'recibirItems']);

        Route::apiResource('proveedores', ProveedorController::class);
        Route::get('/configuracion', [ConfiguracionController::class, 'obtener']);
        Route::post('/configuracion', [ConfiguracionController::class, 'guardar']);
    });

    // ============================================
    // PRODUCTOS Y VENTAS (OWNER + EMPLOYEE)
    // ============================================
    Route::middleware(['auth', EnsureUserRole::class.':owner,employee'])->group(function () {
        Route::apiResource('productos', ProductoController::class);
        Route::get('/productos/stock/bajo', [ProductoController::class, 'stockBajo']);

        Route::apiResource('ventas', VentaController::class);
        Route::get('/ventas/reporte/diario', [VentaController::class, 'reporteDiario']);
    });
});

