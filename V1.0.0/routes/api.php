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
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\NoCache;

// ENDPOINT DE DEBUG (TEMPORAL)
Route::get('/debug/ventas-count', function () {
    $count = \App\Models\Venta::count();
    $ventas = \App\Models\Venta::limit(10)->get(['id_venta', 'numero_documento', 'fecha_hora', 'total_venta', 'metodo_pago']);
    return response()->json([
        'total_ventas' => $count,
        'ultimas_10' => $ventas,
    ]);
});

// ============================================
// MIDDLEWARE GLOBAL DE SESIÓN SPA
// ============================================
Route::middleware(['web', \Illuminate\Session\Middleware\StartSession::class, NoCache::class])->group(function () {
    
    // 1. AUTENTICACIÓN (Público)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth');

    // ============================================
    // 2. EXCLUSIVO PROPIETARIO (Solo Owner)
    // ============================================
    Route::middleware(['auth', EnsureUserRole::class.':owner'])->group(function () {
        
        // Dashboard y Gestión
        Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
        Route::get('/dashboard/vendedores', [DashboardController::class, 'vendedores']);
        Route::get('/dashboard/tendencia-ventas', [DashboardController::class, 'tendenciaVentas']);
        Route::get('/dashboard/empleados', [DashboardController::class, 'empleados']);
        Route::post('/dashboard/cierre-caja', [DashboardController::class, 'cerrarCaja']);
        Route::post('/dashboard/reiniciar', [DashboardController::class, 'reiniciarVentas']);
        Route::get('/dashboard/historial-cierres', [DashboardController::class, 'historialCierres']);
        
        // Usuarios y Reportes
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::put('/usuarios/{usuario}', [UserController::class, 'update']);
        Route::get('/reportes/exportar', [VentaController::class, 'exportar']);
        Route::get('/ventas/reporte/diario', [VentaController::class, 'reporteDiario']);
        Route::get('/export/ventas', [ExportController::class, 'exportarVentas']);
        Route::get('/export/inventario', [ExportController::class, 'exportarInventario']);

        // Operaciones destructivas y compras
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy']);
        Route::apiResource('compras', CompraController::class);
        Route::post('/compras/{compra}/recibir', [CompraController::class, 'recibirItems']);

        // Gestión de datos maestros (Escritura)
        Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
        Route::get('/categorias/{categoria}/productos', [CategoriaController::class, 'productos']);
        Route::apiResource('marcas', MarcaController::class)->except(['index', 'show']);
        Route::get('/marcas/{marca}/productos', [MarcaController::class, 'productos']);
        
        // Configuraciones y Proveedores
        Route::apiResource('proveedores', ProveedorController::class);
        Route::get('/configuracion', [ConfiguracionController::class, 'obtener']);
        Route::post('/configuracion', [ConfiguracionController::class, 'guardar']);
    });

    // ============================================
    // 3. COMPARTIDO (Owner + Employee)
    // ============================================
    Route::middleware(['auth', EnsureUserRole::class.':owner,employee'])->group(function () {
        
        // 👉 Dashboard Alertas Stock (Agregado aquí para que el empleado vea los productos bajos)
        Route::get('/dashboard/alertas-stock', [DashboardController::class, 'alertasStock']);

        // 👉 Categorías y Marcas (Solo Lectura)
        Route::get('/categorias', [CategoriaController::class, 'index']);
        Route::get('/categorias/{categoria}', [CategoriaController::class, 'show']);
        Route::get('/marcas', [MarcaController::class, 'index']);
        Route::get('/marcas/{marca}', [MarcaController::class, 'show']);

        // 👉 Inventario y Búsquedas Especiales de Productos
        Route::get('/inventario', [InventarioController::class, 'index']);
        
        // IMPORTANTE: Las rutas específicas (buscar/codigo y stock/bajo) siempre deben ir ANTES del apiResource
        Route::get('/productos/buscar/codigo/{codigo}', [ProductoController::class, 'buscarPorCodigo']);
        Route::get('/productos/stock/bajo', [ProductoController::class, 'stockBajo']);
        
        // 👉 CRUD de Productos y Ventas
        Route::apiResource('productos', ProductoController::class)->except(['destroy']);
        Route::get('/ventas/comprobante', [VentaController::class, 'comprobante']);
        Route::get('/ventas/{venta}/comprobante', [VentaController::class, 'comprobante']);
        Route::apiResource('ventas', VentaController::class);
    });
});