# 📁 Estructura del Proyecto Viicito

## Overview
```
Sistema_Viicito_web_V1.0.1/
│
├── 🔧 BACKEND (PHP/Laravel)
│   └── app/
│       ├── Http/Controllers/Api/          ← APIs REST
│       │   ├── ProductoController.php     (CRUD productos)
│       │   ├── VentaController.php        (Ventas + transacciones)
│       │   ├── CompraController.php       (Compras + recepción)
│       │   ├── ClienteController.php      (Clientes)
│       │   ├── CategoriaController.php    (Categorías)
│       │   ├── DashboardController.php    (Analytics)
│       │   └── Controller.php             (Base controller)
│       │
│       └── Models/                        ← ORM Eloquent
│           ├── Usuario.php                (Usuarios + RBAC)
│           ├── Producto.php               (Inventario)
│           ├── Categoria.php              (Categorías)
│           ├── Cliente.php                (Clientes)
│           ├── Proveedor.php              (Proveedores)
│           ├── Venta.php                  (Ventas)
│           ├── DetalleVenta.php           (Líneas de venta)
│           ├── Compra.php                 (Compras)
│           ├── DetalleCompra.php          (Líneas de compra)
│           └── Auditoria.php              (Auditoría)
│
├── 🎨 FRONTEND (Vue.js 3)
│   └── resources/js/
│       ├── pages/                         ← Componentes de páginas
│       │   ├── Dashboard.vue              (Panel principal)
│       │   ├── Productos.vue              (Listado productos)
│       │   ├── Ventas.vue                 (Listado ventas)
│       │   ├── NuevaVenta.vue             (POS - Nueva venta)
│       │   ├── Compras.vue                (Listado compras)
│       │   ├── NuevaCompra.vue            (Nueva compra)
│       │   ├── Clientes.vue               (Gestión clientes)
│       │   ├── Categorias.vue             (Gestión categorías)
│       │   └── Reportes.vue               (Reportes)
│       │
│       ├── services/                      ← Capas de servicio
│       │   └── api.js                     (Cliente HTTP con Axios)
│       │
│       ├── App.vue                        (Layout principal)
│       ├── router.js                      (Vue Router config)
│       └── app.js                         (Inicializador Vue)
│
└── 📊 BASE DE DATOS
    └── database/
        ├── schema.sql                     (Schema 3FN normalizado)
        ├── MER_DOCUMENTATION.md           (Diagrama + análisis)
        └── migrations/
            └── *_create_viicito_tables.php (Laravel migrations)

```

---

## 🔗 Conexión Backend ↔ Frontend

### Flujo de Datos:

```
FRONTEND (Vue.js)
    ↓
    └─→ services/api.js (Axios HTTP)
            ↓
            └─→ BACKEND API (Laravel)
                    ↓
                    ├─→ Controllers/Api/*.php
                    │       ↓
                    │   Models/
                    │       ↓
                    │   MySQL Database
                    │
                    └─→ JSON Response
                            ↓
                    Frontend renderiza datos
```

---

## 📝 API Endpoints Disponibles

### Dashboard
```
GET    /api/dashboard/resumen              → Métricas generales
GET    /api/dashboard/vendedores           → Rendimiento vendedores
GET    /api/dashboard/tendencia-ventas     → Tendencia de ventas
GET    /api/dashboard/alertas-stock        → Alertas de stock bajo
```

### Productos
```
GET    /api/productos                      → Listar (paginado)
POST   /api/productos                      → Crear
PUT    /api/productos/{id}                 → Actualizar
DELETE /api/productos/{id}                 → Eliminar
GET    /api/productos/stock/bajo           → Stock bajo
```

### Ventas
```
GET    /api/ventas                         → Listar (filtros)
POST   /api/ventas                         → Crear venta
PUT    /api/ventas/{id}                    → Actualizar estado
DELETE /api/ventas/{id}                    → Cancelar (solo pendientes)
```

### Compras
```
GET    /api/compras                        → Listar
POST   /api/compras                        → Crear
PUT    /api/compras/{id}                   → Actualizar
POST   /api/compras/{id}/recibir           → Recibir items
```

### Clientes
```
GET    /api/clientes                       → Listar
POST   /api/clientes                       → Crear
PUT    /api/clientes/{id}                  → Actualizar
GET    /api/clientes/{id}/historial        → Historial de compras
```

### Categorías
```
GET    /api/categorias                     → Listar
POST   /api/categorias                     → Crear
PUT    /api/categorias/{id}                → Actualizar
GET    /api/categorias/{id}/productos     → Productos de categoría
DELETE /api/categorias/{id}                → Eliminar
```

---

## 🎯 Tecnologías por Capa

| Capa | Tecnología | Ubicación |
|------|-----------|-----------|
| **Backend API** | Laravel 13, PHP 8.3, Eloquent ORM | `app/` |
| **Base de Datos** | MySQL 9.4+, 3FN Normalizado | `database/schema.sql` |
| **Frontend** | Vue.js 3, Bootstrap 5, Axios | `resources/js/` |
| **Rutas API** | REST con JSON | `routes/api.php` |
| **Enrutamiento SPA** | Vue Router | `resources/js/router.js` |

---

## 🚀 Cómo Ejecutar

### 1. Backend
```bash
# Configurar base de datos
php artisan migrate                    # Ejecutar migraciones

# Iniciar servidor
php artisan serve                      # o Laragon (automático)
```

### 2. Frontend
```bash
# Instalar dependencias
npm install

# Desarrollo (con HMR)
npm run dev                            # Acceder: http://localhost:5173

# Producción
npm run build                          # Genera build optimizado
```

---

## 📊 Modelos Eloquent y Relaciones

```
Usuario
├─ hasMany Venta
├─ hasMany Compra
└─ hasMany Cliente (como vendedor)

Categoria
└─ hasMany Producto

Producto
├─ belongsTo Categoria
├─ hasMany DetalleVenta
└─ hasMany DetalleCompra

Cliente
├─ belongsTo Usuario (vendedor)
└─ hasMany Venta

Proveedor
└─ hasMany Compra

Venta
├─ belongsTo Usuario
├─ belongsTo Cliente
└─ hasMany DetalleVenta (CASCADE)

DetalleVenta
├─ belongsTo Venta
└─ belongsTo Producto

Compra
├─ belongsTo Usuario
├─ belongsTo Proveedor
└─ hasMany DetalleCompra (CASCADE)

DetalleCompra
├─ belongsTo Compra
└─ belongsTo Producto

Auditoria
└─ Logs de cambios con datos anteriores/nuevos
```

---

## ✅ Checklist de Implementación

- [x] Backend API completamente funcional
- [x] 10 Modelos Eloquent con relaciones
- [x] 7 Controllers REST
- [x] 20+ Endpoints REST
- [x] Frontend con 9 componentes Vue
- [x] Router Vue configurado
- [x] Base de datos normalizada 3FN
- [x] Validaciones en backend
- [x] Transacciones para operaciones críticas
- [x] CSRF token en requests
- [x] Manejo de errores 401/404/500

