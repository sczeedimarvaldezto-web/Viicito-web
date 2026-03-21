# 🚀 Viicito - POS System

Sistema de Gestión de Punto de Venta (POS) profesional construido con **Laravel 13** y **Vue 3**.

## 📁 Estructura del Repositorio

```
Viicito-web/
├── V1.0.0/          ← Versión 1.0.0 (actual)
│   ├── app/         ← Controladores, Modelos
│   ├── resources/   ← Componentes Vue, estilos, assets
│   ├── routes/      ← Rutas API y web
│   ├── database/    ← Migraciones, seeders, esquema SQL
│   ├── config/      ← Configuración de la aplicación
│   ├── .env.example ← Variables de entorno (ejemplo)
│   ├── composer.json
│   ├── package.json
│   └── README.md    ← Guía específica de V1.0.0
├── V1.0.1/          ← Próximas versiones (cuando estén disponibles)
│   └── ...
└── README.md        ← Este archivo
```

## 🎯 Características Principales

- ✅ **Dashboard**: Visualización de KPIs y métricas
- ✅ **Gestión de Productos**: CRUD completo de inventario
- ✅ **Gestión de Ventas**: POS integrado
- ✅ **Gestión de Compras**: Órdenes a proveedores
- ✅ **Gestión de Clientes**: Base de datos de clientes
- ✅ **Reportes**: Análisis de ventas, compras e inventario
- ✅ **API REST**: Endpoints JSON completos
- ✅ **Base de Datos**: MySQL con migraciones

## 🚀 Inicio Rápido

Para empezar a usar Viicito V1.0.0:

```bash
# Navegar a la versión actual
cd V1.0.0

# Seguir las instrucciones en V1.0.0/README.md o
# GUÍA_EJECUCIÓN_DASHBOARD.md
```

### Requisitos
- **Laragon** (servidor local)
- **PHP 8.2+**
- **Node.js v16+**
- **MySQL**
- **Composer**

### Pasos Rápidos
```bash
cd V1.0.0

# Backend
composer install
php artisan migrate

# Frontend
npm install
npm run dev

# En otra terminal
php artisan serve
```

## 📋 Cambios Recientes

### **V1.0.0** (20/03/2026)
- ✨ Versión inicial subida a GitHub
- 📦 Sistema POS completo funcional
- 🎨 Dashboard profesional con Vue 3
- 🗄️ Base de datos relacional con 10 tablas
- 🔌 API REST con 30+ endpoints

## 📚 Documentación

Cada versión incluye su propia documentación:

- **[V1.0.0/GUÍA_EJECUCIÓN_DASHBOARD.md](V1.0.0/GUÍA_EJECUCIÓN_DASHBOARD.md)** - Cómo ejecutar el proyecto
- **[V1.0.0/ARQUITECTURA_DETALLADA.md](V1.0.0/ARQUITECTURA_DETALLADA.md)** - Arquitectura técnica
- **[V1.0.0/ESTRUCTURA_PROYECTO.md](V1.0.0/ESTRUCTURA_PROYECTO.md)** - Estructura de carpetas
- **[V1.0.0/database/MER_DOCUMENTATION.md](V1.0.0/database/MER_DOCUMENTATION.md)** - Documentación del modelo de datos

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 13** - Framework PHP
- **MySQL** - Base de datos relacional
- **PHP Artisan** - CLI de Laravel

### Frontend
- **Vue 3** - Framework JavaScript
- **Vite** - Build tool
- **Vue Router** - Enrutamiento
- **CSS + Tailwind** - Estilos

## 📞 Soporte

Para reportar problemas o sugerencias en esta versión específica, revisa la documentación dentro de la carpeta `V1.0.0/`.

## 📅 Roadmap

- [ ] V1.0.1 - Mejoras de UI/UX
- [ ] V1.0.2 - Autenticación y seguridad
- [ ] V1.1.0 - Características avanzadas
- [ ] V2.0.0 - Rediseño completo

---

**¡Viicito está listo para usar! 🎉**

Para más información, abre la carpeta de tu versión deseada y consulta su README.
