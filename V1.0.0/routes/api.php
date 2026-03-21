<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\DashboardController;

/**
 * API Routes - Viicito Sistema de Licorería
 * 
 * Prefix: /api
 * Version: v1
 */

Route::prefix('api')->group(function () {
    // ============================================
    // DASHBOARD
    // ============================================
    Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
    Route::get('/dashboard/vendedores', [DashboardController::class, 'vendedores']);
    Route::get('/dashboard/tendencia-ventas', [DashboardController::class, 'tendenciaVentas']);
    Route::get('/dashboard/alertas-stock', [DashboardController::class, 'alertasStock']);

    // ============================================
    // PRODUCTOS
    // ============================================
    Route::apiResource('productos', ProductoController::class);
    Route::get('/productos/stock/bajo', [ProductoController::class, 'stockBajo']);

    // ============================================
    // CATEGORÍAS
    // ============================================
    Route::apiResource('categorias', CategoriaController::class);
    Route::get('/categorias/{categoria}/productos', [CategoriaController::class, 'productos']);

    // ============================================
    // CLIENTES
    // ============================================
    Route::apiResource('clientes', ClienteController::class);
    Route::get('/clientes/{cliente}/historial', [ClienteController::class, 'historial']);

    // ============================================
    // VENTAS
    // ============================================
    Route::apiResource('ventas', VentaController::class);
    Route::get('/ventas/reporte/diario', [VentaController::class, 'reporteDiario']);

    // ============================================
    // COMPRAS
    // ============================================
    Route::apiResource('compras', CompraController::class);
    Route::post('/compras/{compra}/recibir', [CompraController::class, 'recibirItems']);
});
