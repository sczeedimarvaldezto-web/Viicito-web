# 🚀 Dashboard - Quick Start Guide

## Inicio Rápido del Dashboard

### 1️⃣ Compilar el Proyecto

```bash
cd V1.0.0
npm run build
# o para desarrollo:
npm run dev
```

### 2️⃣ Iniciar el Servidor

```bash
php artisan serve
```

El servidor estará en `http://localhost:8000`

### 3️⃣ Acceder al Dashboard

1. Abre tu navegador en `http://localhost:8000`
2. Serás redirigido a `/login`
3. Ingresa tu email y contraseña
4. ✅ Automáticamente te llevará al Dashboard en `/`

---

## 📍 Ubicaciones de Archivos Principales

```
Viicito-web-master1.1/
└── V1.0.0/
    ├── resources/
    │   ├── css/
    │   │   ├── app.css (importa obsidian.css)
    │   │   └── obsidian.css (NEW) ⭐
    │   └── js/
    │       ├── app.js
    │       ├── App.vue (modificado)
    │       ├── router.js (modificado)
    │       ├── layouts/
    │       │   └── AppLayout.vue (NEW) ⭐
    │       └── pages/
    │           ├── Dashboard.vue (modificado)
    │           └── Login.vue (modificado)
    └── app/
        └── Http/
            └── Controllers/
                └── Api/
                    └── DashboardController.php ✅
```

---

## 🎨 Colores del Sistema (Obsidian Sommelier)

```css
--color-primary-container: #ffbf00;    /* Botones principales */
--color-primary: #ffe2ab;              /* Textos claros */
--color-secondary: #e9c176;            /* Textos secundarios */
--color-on-surface: #e5e2e1;           /* Texto principal */
--color-surface-container-low: #1c1b1b; /* Cards */
--color-background: #131313;            /* Fondo oscuro */
```

---

## 🔐 Credenciales de Prueba

Para probar el sistema, puedes crear un usuario en:
1. `http://localhost:8000/register`
2. O usar credenciales existentes si las tienes

**Nota:** El primer usuario registrado será asignado como Owner automáticamente.

---

## 📊 KPIs del Dashboard

| KPI | API Field | Descripción |
|-----|-----------|-------------|
| Ventas del Día | `ventas.total_ventas` | Total de ventas en el período |
| Ingresos del Mes | `ventas.total_ventas` | Ingresos acumulados |
| Productos | `inventario.productos_activos` | Total de productos en el sistema |
| Alertas | `inventario.productos_bajo_stock` | Productos bajo stock mínimo |

---

## 🔄 Flujo de Autenticación

```
1. Usuario accede a /
    ↓
2. Router verifica si está autenticado (localStorage.getItem('user'))
    ↓
3. Si NO está autenticado → Redirige a /login
    ↓
4. Usuario ingresa credenciales
    ↓
5. POST /api/login (con CSRF token)
    ↓
6. Servidor valida y devuelve datos de usuario
    ↓
7. Frontend guarda usuario en localStorage
    ↓
8. Redirige automáticamente a / (Dashboard)
    ↓
9. App.vue muestra AppLayout + Dashboard
```

---

## 🛡️ Protección de Rutas

```javascript
// Rutas públicas (sin autenticación)
/login
/register

// Rutas protegidas (requieren autenticación)
/                 // Dashboard (Owner)
/productos        // Inventario
/nueva-venta      // Ventas
/reportes         // Reportes (Owner only)
/categorias       // Configuración (Owner only)
/compras          // Compras (Owner only)
/empleados        // Empleados (Owner only)
```

**Regla:** Si intentas acceder a una ruta protegida sin estar autenticado, serás redirigido a `/login`.

---

## 📱 Responsive Design

### Desktop (>1024px)
- Sidebar visible a la izquierda
- Contenido ocupa el espacio restante
- Grid de 4 columnas para KPIs

### Tablet (768px-1024px)
- Sidebar visible pero compacto
- Grid de 2 columnas para KPIs
- Top bar con menú hamburguesa

### Móvil (<768px)
- Sidebar colapsible (menú hamburguesa)
- Grid de 1 columna para KPIs
- Touch-friendly buttons

**Prueba:** Redimensiona tu navegador para ver los cambios.

---

## 🔄 Actualizar Datos del Dashboard

### Opción 1: Botón "Actualizar"
Click en el botón "Actualizar" en la esquina superior derecha.

### Opción 2: Recarga la Página
`F5` o `Ctrl+R`

### Opción 3: Automático al Montar
Los datos se cargan automáticamente al abrir el Dashboard.

---

## 📈 Endpoint Principal

```bash
GET /api/dashboard/resumen

Query Parameters:
  - fecha_inicio: YYYY-MM-DD (default: primer día del mes)
  - fecha_final: YYYY-MM-DD (default: hoy)

Response:
{
  "periodo": {
    "fecha_inicio": "2026-01-01",
    "fecha_final": "2026-05-16"
  },
  "ventas": {
    "cantidad_transacciones": 45,
    "total_ventas": 12500.50,
    "promedio_venta": 277.79,
    "efectivo": 5000.00,
    "tarjeta": 4500.00,
    "credito": 3000.50
  },
  "inventario": {
    "productos_activos": 156,
    "productos_bajo_stock": 8,
    "valor_inventario": 45800.00
  },
  "top_productos": [
    {
      "id": 1,
      "nombre": "Whisky Premium",
      "cantidad_vendida": 23,
      "precio_venta": 450.00
    }
  ]
}
```

---

## 🐛 Troubleshooting

### Problema: "No se cargan los datos del dashboard"

**Solución:**
1. Abre DevTools (F12)
2. Ir a Network
3. Recarga la página
4. Verifica que `GET /api/dashboard/resumen` devuelve 200
5. Si hay error 401, significa que no estás autenticado

### Problema: "El layout no se ve correctamente"

**Solución:**
1. Limpia el caché: `Ctrl+Shift+Delete`
2. Recarga con `Ctrl+Shift+R` (recarga hard)
3. Verifica que los estilos CSS se cargan:
   - DevTools → Sources → Check `obsidian.css`

### Problema: "El sidebar no aparece en móvil"

**Solución:**
1. Click en el botón ☰ (menú hamburguesa)
2. O redimensiona la ventana a menos de 1024px

---

## 📝 Notas Importantes

- ✅ El Dashboard se actualiza dinámicamente
- ✅ Los KPIs se calculan en tiempo real desde la BD
- ✅ El sistema es seguro con protección CSRF
- ✅ Completamente responsive
- ✅ Compatible con todos los navegadores modernos

---

## 🎯 Próximas Mejoras (Futuro)

- Gráficos interactivos (Chart.js)
- Notificaciones en tiempo real
- Exportar a PDF
- Filtros avanzados
- Auditoría de acciones
- Multi-idioma

---

## 📞 Contacto y Soporte

Si encuentras problemas:
1. Revisa el archivo `CRITERIOS_VALIDACION_DASHBOARD.md`
2. Consulta la documentación en `DASHBOARD_IMPLEMENTATION_SUMMARY.md`
3. Verifica los logs en `storage/logs/laravel.log`

---

**¡Disfruta tu nuevo Dashboard! 🎉**

---

*Última actualización: 16 de Mayo de 2026*
