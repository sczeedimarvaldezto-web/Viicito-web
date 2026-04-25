# 🏗️ Arquitectura Backend + Frontend - Viicito

## Diagrama de Arquitectura

```
┌──────────────────────────────────────────────────────────────────────────┐
│                                                                          │
│                    🌐 NAVEGADOR DEL USUARIO                             │
│                  (Chrome, Firefox, Edge, Safari)                        │
│                                                                          │
└─────────────────────────────────────────┬──────────────────────────────┘
                                          │
                                          │ HTTP/HTTPS
                                          │
                      ┌───────────────────┴────────────────────┐
                      │                                        │
        ┌─────────────▼──────────────┐         ┌──────────────▼──────────┐
        │                            │         │                         │
        │   🎨 FRONTEND              │         │   🔧 BACKEND            │
        │   (Vue.js 3 SPA)          │         │   (Laravel API)         │
        │                            │         │                         │
        │ resources/js/             │         │ app/                    │
        ├────────────────────────────┤         ├─────────────────────────┤
        │                            │         │                         │
        │ ✓ App.vue                 │         │ ✓ Controllers/Api       │
        │   - Layout principal       │         │   - ProductoController  │
        │   - Navbar + Sidebar       │         │   - VentaController     │
        │                            │         │   - CompraController    │
        │ ✓ pages/                  │         │   - ClienteController   │
        │   - Dashboard.vue         │         │   - CategoriaController │
        │   - Productos.vue         │         │   - DashboardController │
        │   - Ventas.vue            │         │                         │
        │   - NuevaVenta.vue        │         │ ✓ Models (Eloquent)    │
        │   - Compras.vue           │         │   - Usuario             │
        │   - NuevaCompra.vue       │         │   - Producto            │
        │   - Clientes.vue          │         │   - Categoria           │
        │   - Categorias.vue        │         │   - Cliente             │
        │   - Reportes.vue          │         │   - Proveedor           │
        │                            │         │   - Venta               │
        │ ✓ services/               │         │   - DetalleVenta        │
        │   - api.js (Axios)        │         │   - Compra              │
        │                            │         │   - DetalleCompra       │
        │ ✓ router.js               │         │   - Auditoria           │
        │   (Vue Router)             │         │                         │
        │                            │         │ ✓ routes/api.php       │
        │ ✓ Bootstrap 5 + CSS       │         │   - 20 endpoints REST   │
        │                            │         │                         │
        └─────────────┬──────────────┘         └────────┬────────────────┘
                      │                                 │
                      │ JSON (GET/POST/PUT/DELETE)     │
                      │                                 │
                      │  routes/api.php                 │ Eloquent ORM
                      │  - /api/productos               │ Queries
                      │  - /api/ventas                  │ Validation
                      │  - /api/compras                 │ Business Logic
                      │  - /api/clientes                │
                      │  - /api/categorias              │
                      │  - /api/dashboard               │
                      │                                 │
                      └────────────────┬────────────────┘
                                      │
                                      │ SQL
                                      │
                      ┌───────────────▼────────────────┐
                      │                                │
                      │   💾 BASE DE DATOS             │
                      │   (MySQL 9.4+)                │
                      │                                │
                      ├────────────────────────────────┤
                      │                                │
                      │ ✓ usuario                      │
                      │ ✓ producto                     │
                      │ ✓ categoria                    │
                      │ ✓ cliente                      │
                      │ ✓ proveedor                    │
                      │ ✓ venta                        │
                      │ ✓ detalle_venta                │
                      │ ✓ compra                       │
                      │ ✓ detalle_compra               │
                      │ ✓ auditoria                    │
                      │                                │
                      │ 3FN Normalizado                │
                      │ 11 Foreign Keys                │
                      │ 11 Índices Optimizados         │
                      │ 3 Vistas SQL                   │
                      │                                │
                      └────────────────────────────────┘
```

---

## Flujo de una Transacción: Nueva Venta

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                     │
│  USUARIO EN NAVEGADOR (Chrome/Firefox)                             │
│                                                                     │
│  1. Hace clic en "Nueva Venta" → router.js redirige a NuevaVenta   │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  │
                    ┌─────────────▼───────────────┐
                    │                             │
                    │  NuevaVenta.vue Cargado    │
                    │  (Frontend)                 │
                    │                             │
                    │  1. Busca productos         │
                    │  2. Selecciona cliente      │
                    │  3. Elige método pago       │
                    │  4. Agrega items al carrito │
                    │  5. Calcula total + IVA     │
                    │                             │
                    └─────────────┬───────────────┘
                                  │
                                  │ POST /api/ventas
                                  │ (JSON: cliente, items, pago)
                                  │
                    ┌─────────────▼───────────────┐
                    │                             │
                    │  Backend: VentaController   │
                    │  (api.php → POST handler)   │
                    │                             │
                    │  1. Valida datos            │
                    │  2. Verifica stock          │
                    │  3. Genera # documento      │
                    │  4. Calcula totales         │
                    │  5. Inicia transacción      │
                    │                             │
                    └─────────────┬───────────────┘
                                  │
                                  │ Eloquent ORM
                                  │
                    ┌─────────────▼───────────────┐
                    │                             │
                    │  Modelos Eloquent           │
                    │  - Venta::create()          │
                    │  - DetalleVenta::create()   │
                    │  - Producto::decreaseStock()│
                    │  - Auditoria::register()    │
                    │                             │
                    └─────────────┬───────────────┘
                                  │
                                  │ SQL INSERT/UPDATE
                                  │
                    ┌─────────────▼───────────────┐
                    │                             │
                    │  MySQL Database             │
                    │  🔒 Transacción Atómica     │
                    │                             │
                    │  INSERT venta               │
                    │  INSERT detalle_venta x N   │
                    │  UPDATE producto (stock)    │
                    │  INSERT auditoria           │
                    │  COMMIT                     │
                    │                             │
                    └─────────────┬───────────────┘
                                  │
                                  │ JSON Response
                                  │ {"success": true, "numero_documento": "VTA-00000001"}
                                  │
                    ┌─────────────▼───────────────┐
                    │                             │
                    │  Frontend: NuevaVenta.vue   │
                    │  - Muestra confirmación     │
                    │  - Redirige a /ventas       │
                    │  - Recarga listado          │
                    │                             │
                    └─────────────┬───────────────┘
                                  │
                                  │
                    ┌─────────────▼───────────────┐
                    │                             │
                    │  ✅ VENTA COMPLETADA       │
                    │  Aparece en lista de ventas │
                    │                             │
                    └─────────────────────────────┘
```

---

## Carpetas Clave Explicadas

### 📁 Backend: `app/`

```
app/
├── Http/Controllers/Api/
│   ├── ProductoController.php      Gestiona productos
│   │   ├── index()    → GET /api/productos
│   │   ├── store()    → POST /api/productos
│   │   ├── update()   → PUT /api/productos/{id}
│   │   └── destroy()  → DELETE /api/productos/{id}
│   │
│   ├── VentaController.php         Gestiona ventas
│   │   ├── index()    → GET /api/ventas (con filtros)
│   │   ├── store()    → POST /api/ventas (crear con transacción)
│   │   ├── update()   → PUT /api/ventas/{id} (cambiar estado)
│   │   └── destroy()  → DELETE /api/ventas/{id} (solo pendientes)
│   │
│   └── ... (otros controllers)
│
└── Models/
    ├── Producto.php               Tabla: producto
    │   ├── categoria()            Relación belongsTo
    │   ├── detalleVentas()        Relación hasMany
    │   ├── scopeStockBajo()       Custom scope
    │   └── getEstadoStockAttribute() Calculated field
    │
    ├── Venta.php                  Tabla: venta
    │   ├── usuario()              Relación belongsTo
    │   ├── cliente()              Relación belongsTo
    │   ├── detalleVentas()        Relación hasMany (CASCADE)
    │   └── scopeCompletadas()     Custom scope
    │
    └── ... (otros modelos)
```

### 🎨 Frontend: `resources/js/`

```
resources/js/
├── App.vue                         Componente raíz
│   ├── Navbar superior
│   ├── Sidebar izquierda
│   └── router-view (contenido dinámico)
│
├── pages/                          Componentes de página (rutas)
│   ├── Dashboard.vue               Métricas + gráficos
│   │   ├── Métodos:
│   │   │   └── cargarDatos()       Llama a /api/dashboard/resumen
│   │   └── Rendered as: GET /
│   │
│   ├── Productos.vue               Listado + búsqueda + filtros
│   │   ├── Métodos:
│   │   │   ├── cargarProductos()   Llama a /api/productos
│   │   │   └── buscar()            Con debounce
│   │   └── Rendered as: GET /productos
│   │
│   ├── NuevaVenta.vue              POS interactivo
│   │   ├── Data:
│   │   │   ├── carrito[]           Items a vender
│   │   │   └── cliente             Cliente seleccionado
│   │   ├── Métodos:
│   │   │   ├── buscarProductos()   Llama a /api/productos
│   │   │   ├── completarVenta()    POST /api/ventas
│   │   └── Rendered as: GET /nueva-venta
│   │
│   ├── Ventas.vue                  Historial de ventas
│   │   ├── Métodos:
│   │   │   └── cargarVentas()      Llama a /api/ventas
│   │   └── Rendered as: GET /ventas
│   │
│   └── ... (otros componentes)
│
├── services/
│   └── api.js                      Cliente HTTP centralizado
│       ├── axios instance
│       ├── Base URL: /api
│       ├── CSRF token interceptor
│       └── Error handler (401 → login)
│
├── router.js                       Configuración de rutas
│   ├── /               → Dashboard
│   ├── /productos      → Productos
│   ├── /ventas         → Ventas
│   ├── /nueva-venta    → NuevaVenta
│   ├── /compras        → Compras
│   ├── /nueva-compra   → NuevaCompra
│   ├── /clientes       → Clientes
│   ├── /categorias     → Categorias
│   └── /reportes       → Reportes
│
└── app.js                          Punto de entrada
    ├── createApp(App)
    ├── app.use(router)
    └── app.mount('#app')
```

---

## Ejemplo: Cómo Funciona una Búsqueda

### Frontend (Productos.vue)
```javascript
// Usuario escribe "Ron" en búsqueda
buscar() {
  clearTimeout(this.timeoutBusqueda);
  this.timeoutBusqueda = setTimeout(async () => {
    const response = await api.get('/productos', {
      params: {
        nombre: "Ron",
        per_page: 20
      }
    });
    this.productos = response.data.data;
  }, 300); // Debounce 300ms
}
```

### Backend (ProductoController.php)
```php
// GET /api/productos?nombre=Ron&per_page=20
public function index(Request $request)
{
    $query = Producto::query();
    
    if ($request->nombre) {
        $query->where('nombre_producto', 'like', '%' . $request->nombre . '%');
    }
    
    return response()->json([
        'data' => $query->paginate(20)
    ]);
}
```

### Database (MySQL)
```sql
-- Query ejecutada en MySQL
SELECT * FROM producto 
WHERE nombre_producto LIKE '%Ron%'
LIMIT 20;
```

---

## Tecnologías por Sección

| Sección | Tecnología | Función |
|---------|-----------|---------|
| **Frontend** | Vue.js 3 | Interfaz interactiva |
| **HTTP Client** | Axios | Comunicación con API |
| **Enrutador** | Vue Router | Navegación SPA |
| **CSS** | Bootstrap 5 | Estilos responsivos |
| **API** | Laravel REST | Endpoints HTTP |
| **ORM** | Eloquent | Mapeo a tablas |
| **Validación** | Rules Laravel | Validar datos |
| **Transacciones** | MySQL | Operaciones atómicas |
| **Base de Datos** | MySQL 9.4+ | Persistencia 3FN |

