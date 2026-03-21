# 🚀 Guía de Ejecución - Dashboard Viicito

## Descripción General
Este documento explica cómo ejecutar el **Sistema de Gestión Viicito** y acceder al dashboard profesional.

---

## 📋 Requisitos Previos

Asegúrate de tener instalado y configurado:

1. **Laragon** (servidor local con PHP, MySQL, Apache)
2. **Node.js** (v16 o superior)
3. **npm** (gestor de paquetes, incluido con Node.js)
4. **Composer** (gestor de dependencias PHP)

---

## 🎯 Pasos para Ejecutar el Proyecto

### **Paso 1: Navega a la carpeta del proyecto**

```powershell
cd c:\laragon\www\Sistema_Viicito_web_V1.0.1
```

### **Paso 2: Inicia los servidores**

El proyecto requiere **3 servicios en paralelo**:

#### **2a) Inicia Laragon (si no está activo)**
- Abre **Laragon** desde el menú de inicio
- Haz clic en el botón **START ALL** (inicio de todos los servicios)
- Verifica que **Apache** y **MySQL** estén en verde ✅

#### **2b) Terminal 1: Inicia el servidor Laravel (API)**

```powershell
php artisan serve
```

**Resultado esperado:**
```
Server running on [http://127.0.0.1:8000]
```

**Puerto:** `8000` → Esta es la API backend

#### **2c) Terminal 2: Inicia el servidor Vite (Frontend)**

```powershell
npm run dev
```

**Resultado esperado:**
```
VITE v8.0.1 ready in 364 ms
➜  Local:   http://localhost:5175/
```

**Puerto:** `5175` → Este es el dashboard frontend

---

## 🌐 Acceder al Dashboard

Una vez que ambos servidores estén corriendo, abre tu navegador:

### **URL del Dashboard:**
```
http://localhost:5175/
```

### **Qué deberías ver:**

✅ **Interfaz Viicito limpia y profesional**
- Sidebar izquierda con menú de navegación
- Header superior con título y opciones
- Dashboard con tarjetas de métricas (gradientes)
- Tablas de productos y alertas de stock

✅ **Sin branding de Laravel**
- ❌ No hay logo de Laravel
- ❌ No hay publicidad o código sobrante
- ✅ Solo la interfaz de Viicito

---

## 📱 Módulos Disponibles en el Menú

| Módulo | Descripción |
|--------|-------------|
| 📊 **Dashboard** | Vista general con KPIs y métricas |
| 📦 **Productos** | Gestión de inventario |
| 💰 **Ventas** | Historial de transacciones |
| 🛒 **Nueva Venta** | Interfaz POS para crear ventas |
| 📥 **Compras** | Órdenes de compra a proveedores |
| ➕ **Nueva Compra** | Crear nueva orden de compra |
| 👥 **Clientes** | Gestión de clientes |
| 📂 **Categorías** | Gestión de categorías de productos |
| 📈 **Reportes** | Análisis y reportes (Ventas, Compras, Inventario, Clientes) |

---

## 🔧 Solución de Problemas

### ❌ El puerto 5175 está ocupado

Si ves `Port 5175 is in use...`, Vite automáticamente intentará usar 5174, 5175, etc.

**Alternativa:** Especifica un puerto diferente
```powershell
npm run dev -- --port 5176
```

### ❌ Error de conexión con MySQL

**Verifica:** 
- Abre Laragon y haz clic en **Database** → **MySQL**
- Usuario: `root`
- Contraseña: `edimar12345`
- Base de datos: `viicito_db`

Si hay error, reinicia MySQL desde Laragon.

### ❌ Dependencies no instaladas

```powershell
npm install
composer install
```

### ❌ Migraciones no ejecutadas

```powershell
php artisan migrate
```

---

## 📁 Estructura del Proyecto

```
Sistema_Viicito_web_V1.0.1/
├── app/
│   ├── Http/Controllers/Api/    ← Controladores API (Backend)
│   └── Models/                  ← Modelos Eloquent
├── resources/
│   ├── js/
│   │   ├── app.js              ← Entry point Vue
│   │   ├── App.vue             ← Layout principal
│   │   ├── router.js            ← Rutas Vue Router
│   │   ├── services/api.js      ← Cliente HTTP
│   │   └── pages/               ← Componentes (Dashboard, Productos, etc.)
│   ├── css/app.css             ← Estilos globales
│   └── views/welcome.blade.php ← HTML root del proyecto
├── routes/
│   ├── api.php                 ← Rutas API REST
│   └── web.php                 ← Rutas web
├── database/
│   ├── migrations/             ← Migraciones de tabla
│   ├── schema.sql              ← Script SQL completo
│   └── seeders/                ← Datos iniciales (opcional)
├── vite.config.js              ← Configuración frontend
├── package.json                ← Dependencias npm
└── composer.json               ← Dependencias PHP
```

---

## 🔌 Conexión Backend ↔ Frontend

El frontend (Vue) se comunica con el backend (Laravel) mediante:

- **Base URL API:** `http://localhost:8000/api/`
- **Método:** HTTP REST (GET, POST, PUT, DELETE)
- **Autenticación:** Token CSRF automático en metas

**Ejemplo de llamada:**
```javascript
GET http://localhost:8000/api/dashboard/resumen
→ Devuelve métricas del dashboard
```

---

## ✅ Checklist de Verificación

Una vez ejecutado, verifica:

- [x] Laragon con Apache + MySQL corriendo
- [x] Terminal 1: `php artisan serve` en puerto 8000
- [x] Terminal 2: `npm run dev` en puerto 5175
- [x] Navegador: `http://localhost:5175/` cargando sin errores
- [x] Dashboard visible con: Sidebar, KPIs, Tablas
- [x] Sin logo de Laravel visible
- [x] Navegación entre módulos funcionando
- [x] Consola del navegador (F12) sin errores rojo

---

## 📚 Próximos Pasos

Una vez el dashboard esté ejecutando correctamente:

1. **Historias de Usuario:** Continuar con implementación de features específicas
2. **Seed Data:** Cargar datos de prueba en la BD
3. **Autenticación:** Implementar login/register
4. **Testing:** Crear pruebas unitarias e integración
5. **Producción:** Build final y deployment

---

## 📞 Soporte

Si tienes problemas:

1. Verifica que todos 3 servicios estén corriendo ✅
2. Revisa la consola del navegador (F12) para errores
3. Revisa los logs de terminal (Vite y Laravel)
4. Asegúrate que la BD `viicito_db` existe y tiene tablas

---

**¡El proyecto está listo para usarse! 🎉**
