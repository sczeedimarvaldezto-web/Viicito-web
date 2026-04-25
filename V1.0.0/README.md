# Viicito - Sistema de Gestión para Licorería v1.0.1

Sistema web integrado para la gestión completa de una licorería, incluyendo punto de venta (POS), inventario, auditoría de transacciones y gestión de cuentas corrientes.

<p align="center">
  <strong>Stack Tecnológico:</strong> Laravel 13 • PHP 8.3 • MySQL 9.4+ • Vue.js/Bootstrap 5
</p>

---

## 📋 Requisitos del Sistema

### Versiones Requeridas

| Componente | Versión | Observaciones |
|---|---|---|
| **PHP** | `8.3` o superior | Incluye ext-mysql, ext-json, ext-curl |
| **MySQL** | `9.4.0` o superior | Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci` |
| **PHP-FPM** | `8.3` | Por defecto en Laragon |
| **Composer** | `2.6+` | Gestor de dependencias de PHP |
| **Node.js** | `18.13+` | Para compilar assets con Vite |
| **npm** | `9.0+` | Gestor de paquetes JavaScript |

---

## 🚀 Instalación y Configuración Local

### Paso 1: Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/Sistema_Viicito_web.git
cd Sistema_Viicito_web_V1.0.1
git checkout develop  # Rama de desarrollo
```

### Paso 2: Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### Paso 3: Crear Base de Datos

**Opción A: Usando PHPMyAdmin (Recomendado para Laragon)**

1. Abrir PHPMyAdmin desde Laragon (http://localhost/phpmyadmin)
2. Ir a **Import** o similary
3. Seleccionar archivo: `database/viicito_db.sql`
4. Click en **Import**

**Opción B: Usando Línea de Comandos**

```bash
# Asegúrate de que MySQL está corriendo
# Si tienes credenciales específicas, ajusta el comando:
mysql -h localhost -u root [-p password] < database/viicito_db.sql

# Para Laragon (si requiere socket):
mysql --socket=/tmp/mysql.sock -u root < database/viicito_db.sql
```

**Opción C: Usando HeidiSQL (Incluido en Laragon)**

1. Abrir HeidiSQL desde Laragon
2. File → Run SQL file
3. Seleccionar `database/viicito_db.sql`

**Nota sobre Credenciales**: Si Laragon pide contraseña, contacta al administrador de sistemas o revisa la configuración de MySQL en Laragon.

### Paso 4: Actualizar .env con Credenciales

Editar `.env` con tu configuración local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=viicito_db
DB_USERNAME=root
DB_PASSWORD=tu_password_aqui
```

### Paso 5: Instalar Dependencias

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### Paso 6: Ejecutar Migraciones

```bash
php artisan migrate --seed
```

Esto ejecutará:
- Migraciones del framework Laravel
- Seeders para datos iniciales
- Importará la estructura de `viicito_db`

### Paso 7: Compilar Assets

```bash
# Desarrollo (con watch mode)
npm run dev

# Producción (optimizado)
npm run build
```

---

## ✅ Verificar Conexión a Base de Datos

Una vez importado el SQL, valida la conexión:

```bash
# Método 1: Usar Laravel Tinker
php artisan tinker
>>> DB::connection('mysql')->getPdo()
=> PDOConnection object (proves connection works!)
>>> exit

# Método 2: Ejecutar test
php artisan test --filter=DatabaseTest

# Método 3: Ver tables
php artisan tinker
>>> DB::select('SHOW TABLES FROM viicito_db')
=> Array with all tables
```

### Troubleshooting Conexión

**Error: "Access denied for user 'root'@'localhost'"**
- Verifica credenciales en `.env` match tu configuración local
- En Laragon: abre PHPMyAdmin para confirmar credenciales
- Prueba conectar con HeidiSQL primero

**Error: "No such file or directory"**
- Asegurate de estar en el directorio correcto: `cd c:\laragon\www\Sistema_Viicito_web_V1.0.1`
- Verifica que `database/viicito_db.sql` existe

---

```bash
# Con Laragon: Simplemente start
# Manual:
php artisan serve
```

Acceder a: `http://localhost:8000` o `http://Sistema_Viicito_web_V1.0.1.test`

---

## 📁 Estructura del Proyecto

```
Sistema_Viicito_web_V1.0.1/
├── app/                    # Código de aplicación
│   ├── Http/Controllers/   # Controladores
│   ├── Models/             # Modelos Eloquent
│   └── Providers/          # Service providers
├── bootstrap/              # Bootstrapping
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones de BD
│   ├── factories/          # Model factories
│   └── seeders/            # Database seeders
├── public/                 # Assets públicos
├── resources/
│   ├── css/                # Estilos Vite
│   ├── js/                 # JavaScript/Vue
│   └── views/              # Vistas Blade
├── routes/                 # Definición de rutas
├── storage/                # Logs, cache, sesiones
├── tests/                  # Tests unitarios y feature
├── .env.example            # Template de variables
├── .gitignore              # Archivos ignorados por Git
├── composer.json           # Dependencias PHP
├── package.json            # Dependencias Node.js
└── vite.config.js          # Configuración Vite
```

---

## 🗄️ Estructura Base de Datos

Tablas principales:

```
usuario          → Staff y administradores del sistema
cliente          → Clientes con cuentas corrientes
categoria        → Categorías de productos
producto         → Inventario de licores
proveedor        → Proveedores de stock
venta            → Transacciones de venta
detalle_venta    → Items de cada venta
compra           → Órdenes de compra
detalle_compra   → Items de cada compra
```

---

## 🔒 Control de Versiones (Git)

### Estrategia de Ramas

- **`main`** → Rama de producción (releases estables)
- **`develop`** → Rama de integración (features en desarrollo)
- **`feature/*`** → Ramas feature (ej: `feature/pos-system`)
- **`bugfix/*`** → Correcciones (ej: `bugfix/inventory-count`)

### Flujo de Trabajo

```bash
# Crear feature
git checkout -b feature/nueva-funcionalidad develop

# Después de terminar
git commit -m "feat: descripción de cambios"
git push origin feature/nueva-funcionalidad
# → Crear Pull Request en GitHub
```

---

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Con coverage
php artisan test --coverage

# Tests específicos
php artisan test tests/Feature/PosTest.php
```

---

## 🚨 Troubleshooting

### Error de conexión a BD
```bash
# Verificar que MySQL está corriendo
# En Laragon: Start All Services

# Validar credenciales en .env
php artisan tinker
DB::connection('mysql')->getPdo();  # Debe retornar PDO instance
```

### Permisos de storage
```bash
chmod -R 775 storage bootstrap/cache
```

### Cachés y vistas
```bash
php artisan cache:clear
php artisan view:clear
php artisan clear-compiled
```

---

## 📝 Licencia

Este proyecto es propietario de Viicito. Todos los derechos reservados.

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
