<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\InventarioController;
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

        Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
        Route::get('/categorias/{categoria}/productos', [CategoriaController::class, 'productos']);

        Route::apiResource('marcas', MarcaController::class)->except(['index', 'show']);
        Route::get('/marcas/{marca}/productos', [MarcaController::class, 'productos']);

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
        Route::get('/categorias', [CategoriaController::class, 'index']);
        Route::get('/categorias/{categoria}', [CategoriaController::class, 'show']);
        Route::get('/marcas', [MarcaController::class, 'index']);
        Route::get('/marcas/{marca}', [MarcaController::class, 'show']);

        Route::get('/inventario', [InventarioController::class, 'index']);
        Route::apiResource('productos', ProductoController::class);
        Route::get('/productos/stock/bajo', [ProductoController::class, 'stockBajo']);

        Route::get('/ventas/comprobante', [VentaController::class, 'comprobante']);
        Route::get('/ventas/{venta}/comprobante', [VentaController::class, 'comprobante']);
        Route::get('/ventas/reporte/diario', [VentaController::class, 'reporteDiario']);
        Route::apiResource('ventas', VentaController::class);
    });
});

