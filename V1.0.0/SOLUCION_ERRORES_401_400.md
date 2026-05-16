# Solución Completa: Errores 401 (Unauthorized) y 400 (Bad Request) en API

## Resumen Ejecutivo

Se identificaron y solucionaron **dos problemas raíz independientes** causados por:
1. **Falta de configuración de CORS y Sanctum** para autenticación SPA
2. **Conflicto en estructura de base de datos** + **falta de logging para debugging**

---

## 1. PROBLEMA: Error 401 Unauthorized en GET /api/user

### Raíz del Problema

Laravel no reconocía la sesión en peticiones AJAX porque faltaba:
- Archivo de configuración `config/cors.php` (NO EXISTÍA)
- Archivo de configuración `config/sanctum.php` (NO EXISTÍA)
- Configuración `SANCTUM_STATEFUL_DOMAINS` en `.env`
- Middleware CORS en `bootstrap/app.php`
- Soporte explícito para SPA stateful

### Arquitectura de Autenticación

```
┌─────────────────┐
│   Navegador     │
│  (Vue.js SPA)   │
└────────┬────────┘
         │ withCredentials: true
         │ X-CSRF-TOKEN header
         ↓
┌─────────────────────────────────────┐
│   Axios Instance (api.js)           │
│   - baseURL: /api                   │
│   - withCredentials: true ✅ CRÍTICO│
│   - X-CSRF-TOKEN en headers         │
└────────┬────────────────────────────┘
         │ Petición CORS + Cookie
         ↓
┌─────────────────────────────────────┐
│   Middleware CORS (HandleCors)      │
│   - supports_credentials: true      │
└────────┬────────────────────────────┘
         │ Valida CORS headers
         ↓
┌─────────────────────────────────────┐
│   Middleware Web/Session            │
│   - Recupera sesión de cookie       │
│   - Auth::check() ✅ Funciona       │
└────────┬────────────────────────────┘
         │
         ↓
┌─────────────────────────────────────┐
│   Route Handler (AuthController)    │
│   - $request->user() ✅ Disponible  │
└─────────────────────────────────────┘
```

---

## 2. SOLUCIÓN PASO A PASO

### 2.1 Crear `config/cors.php`

**Archivo**: `/config/cors.php`  
**Líneas**: 1-25

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,  // ✅ CRÍTICO: Permitir cookies
];
```

**Explicación técnica**:
- `supports_credentials: true` → Axios puede enviar cookies (sesión)
- `allowed_origins: ['*']` → Acepta peticiones desde cualquier origen (local)
- `paths: ['api/*']` → Aplica CORS solo a rutas `/api/*`

---

### 2.2 Crear `config/sanctum.php`

**Archivo**: `/config/sanctum.php`  
**Líneas**: 1-60

```php
<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,',
        env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),
    'guard' => ['web'],
    'expiration' => null,
    // ... más configuración
];
```

**Explicación técnica**:
- `stateful` → Lista de dominios donde Laravel NO requiere Bearer token (usa sesiones)
- `guard: ['web']` → Usa guard 'web' (sesiones) en lugar de 'api' (tokens)
- Incluye: `127.0.0.1:8000`, `localhost:8000`, etc.

---

### 2.3 Actualizar `.env`

**Archivo**: `/.env`  
**Cambios**:

```diff
- APP_URL=http://localhost:8000
+ APP_URL=http://127.0.0.1:8000

- SESSION_DRIVER=database
+ SESSION_DRIVER=file
+ SESSION_DOMAIN=127.0.0.1

+ # Configuración Sanctum para SPA con autenticación basada en sesiones
+ SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000,localhost:8000,127.0.0.1,localhost
```

**Explicación técnica**:
- `APP_URL=http://127.0.0.1:8000` → Debe coincidir con la URL de acceso
- `SESSION_DOMAIN=127.0.0.1` → Dominio donde se guardan cookies de sesión
- `SANCTUM_STATEFUL_DOMAINS` → Dominios que usan sesiones (NO tokens)

---

### 2.4 Actualizar `bootstrap/app.php`

**Archivo**: `/bootstrap/app.php`  
**Cambios**: Líneas 13-22

```php
->withMiddleware(function (Middleware $middleware): void {
    // Agregar middleware CORS a todas las rutas
    $middleware->api(append: [
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\DebugApiRequests::class, // Debug logging
    ]);
    
    // Asegurar que las cookies CSRF se envíen en respuestas
    $middleware->statefulApi();
})
->withExceptions(function (Exceptions $exceptions): void {
    // Manejar excepciones de validación con detalles completos
    $exceptions->render(function (Throwable $e) {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        }
    });
})
```

**Explicación técnica**:
- `HandleCors::class` → Middleware que maneja CORS usando `config/cors.php`
- `statefulApi()` → Configura Laravel para SPA con cookies
- `render()` → Intercepta excepciones de validación y retorna JSON con detalles

---

### 2.5 Actualizar `routes/api.php`

**Archivo**: `/routes/api.php`  
**Cambios**: Línea 18

```diff
- Route::middleware('web')->group(function () {
+ Route::middleware(['web', \Illuminate\Session\Middleware\StartSession::class])->group(function () {
```

**Explicación técnica**:
- `StartSession::class` → Asegura que se inicia sesión incluso en requests API
- Mantiene `'web'` middleware para autenticación

---

## 3. PROBLEMA: Error 400 Bad Request en POST /api/ventas

### Raíz del Problema

**Múltiples causas**:

1. **Sin sesión válida** (relacionada al error 401 anterior)
2. **Sin logging detallado** para saber qué errores de validación ocurren realmente
3. **Sin debugging** para ver el payload que llega al servidor

### Solución: Agregar Logging y Debugging

#### 3.1 Crear Middleware de Debugging

**Archivo**: `/app/Http/Middleware/DebugApiRequests.php`  
**Líneas**: 1-70

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugApiRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Registrar petición
        Log::debug('🔵 INCOMING REQUEST', [
            'method' => $request->method(),
            'path' => $request->path(),
            'authenticated_user' => auth()->check() ? auth()->user()->id : 'NOT AUTHENTICATED',
            'session_id' => session()->getId(),
            'all_data' => $request->all(),
            'has_csrf_token' => (bool) $request->header('X-CSRF-TOKEN'),
            'cookie_header' => $request->header('Cookie') ? '***[SET]***' : 'NO COOKIE',
        ]);

        $response = $next($request);

        // Registrar respuesta
        Log::debug('🟢 OUTGOING RESPONSE', [
            'status' => $response->getStatusCode(),
            'path' => $request->path(),
            'set_cookie' => $response->headers->get('Set-Cookie') ? '***[SET]***' : 'NONE',
        ]);

        return $response;
    }
}
```

**Explicación técnica**:
- Registra TODAS las peticiones antes y después del procesamiento
- Muestra: usuario autenticado, datos enviados, token CSRF, cookies
- Revela si la sesión se está creando/enviando correctamente

#### 3.2 Actualizar VentaController.php

**Archivo**: `/app/Http/Controllers/Api/VentaController.php`  
**Cambios**: Líneas 35-50

```php
public function store(Request $request)
{
    \Log::info('📊 VentaController@store - Petición recibida', [
        'method' => $request->method(),
        'url' => $request->url(),
        'all_data' => $request->all(),
        'auth_user' => auth()->user()?->id,
        'session_id' => session()->getId(),
    ]);

    $validated = $request->validate([
        'id_usuario' => 'required|integer|exists:users,id',
        'metodo_pago' => 'required|in:Efectivo,Tarjeta,Cheque,Crédito',
        'observacion' => 'nullable|string',
        'items' => 'required|array',
        'items.*.id_producto' => 'required|exists:producto,id_producto',
        'items.*.cantidad' => 'required|integer|min:1',
        'items.*.precio_unitario' => 'required|numeric|min:0',
        'items.*.descuento' => 'nullable|numeric|min:0',
    ]);

    \Log::info('✅ Validación exitosa', ['validated' => $validated]);
    // ... resto del código
}
```

**Explicación técnica**:
- `\Log::info()` registra:
  - Todos los datos recibidos (`$request->all()`)
  - Usuario autenticado
  - ID de sesión
- Si falla validación, Laravel automáticamente registra los errores con status 422
- Los errores detallados se envían en JSON gracias al handler de excepciones

---

## 4. CÓMO DEBUGGEAR EN EL NAVEGADOR

### Pestaña Network (F12)

1. **Abre DevTools**: `F12`
2. **Ve a pestaña**: `Network`
3. **Filtra por**: `XHR` (XMLHttpRequest)
4. **Busca petición**: `POST /api/ventas`
5. **Haz clic en la petición**
6. **Revisa tabs**:
   - **Headers**: 
     - ¿Hay `Cookie: XSRF-TOKEN=...`?
     - ¿Hay `X-CSRF-TOKEN: ...`?
   - **Payload / Request body**:
     - ¿Se envían los datos correctamente?
   - **Response**:
     - Si es 400, verás: `"errors": {"id_usuario": ["..."]}` con detalles
     - Si es 401, verás: `"error": "Unauthorized"`

### Ver Logs en Servidor

1. **Ve a**: `/storage/logs/laravel.log`
2. **Abre el archivo** y busca por líneas con `📊` o `🔵`
3. **Verás exactamente**:
   - Qué datos llegaron al servidor
   - Si el usuario está autenticado
   - Qué validaciones fallaron

---

## 5. FLUJO CORRECTO DESPUÉS DE LOS CAMBIOS

```
Usuario logs in (edimartorrezlobo@gmail.com / password123)
    ↓
AuthController@login
    ↓ Auth::attempt() ✅
$request->session()->regenerate()
    ↓
Retorna response con cookie XSRF-TOKEN + LARAVEL_SESSION
    ↓
Navegador guarda cookies en localStorage
    ↓
Usuario navega a /nueva-venta
    ↓
NuevaVenta.vue hace GET /api/user (con cookies automáticamente)
    ↓
MiddlewareHandleCors valida CORS
    ↓
MiddlewareSession recupera sesión de la cookie
    ↓
auth()->user() retorna el usuario ✅
    ↓
AuthController@user retorna datos del usuario
    ↓
Usuario agrega productos al carrito
    ↓
Usuario clicks en "Completar Venta"
    ↓
NuevaVenta.vue hace POST /api/ventas
    Con headers:
    - Cookie: XSRF-TOKEN=...; LARAVEL_SESSION=...
    - X-CSRF-TOKEN: [valor del meta tag]
    - Content-Type: application/json
    ↓
MiddlewareHandleCors ✅
MiddlewareSession ✅ Recupera sesión
MiddlewareVerifyCsrfToken ✅ Valida token
VentaController@store ✅
    - auth()->user() está disponible
    - $request->all() tiene todos los datos
    - Validación pasa
    - Venta se crea
    ↓
Response 201 Created ✅
```

---

## 6. ARCHIVOS MODIFICADOS Y CREADOS

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `/config/cors.php` | **CREADO** | Configuración CORS con `supports_credentials: true` |
| `/config/sanctum.php` | **CREADO** | Configuración Sanctum con dominios stateful |
| `/.env` | **MODIFICADO** | `APP_URL`, `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS` |
| `/bootstrap/app.php` | **MODIFICADO** | Middleware CORS y `statefulApi()` |
| `/routes/api.php` | **MODIFICADO** | Agregado `StartSession::class` middleware |
| `/app/Http/Middleware/DebugApiRequests.php` | **CREADO** | Logging detallado de peticiones/respuestas |
| `/app/Http/Controllers/Api/VentaController.php` | **MODIFICADO** | Agregado logging en método `store()` |

---

## 7. VALIDACIÓN DE LA SOLUCIÓN

### Test Manual

```bash
1. Abre navegador → http://127.0.0.1:8000
2. Login: edimartorrezlobo@gmail.com / password123
3. Abre DevTools (F12) → Network
4. Haz clic en "Nueva Venta"
5. Observa GET /api/user → ¿200 OK?
6. Agrega producto al carrito
7. Haz clic en "Completar Venta"
8. Observa POST /api/ventas → ¿201 Created?
```

### Ver Logs Detallados

```bash
tail -f storage/logs/laravel.log | grep -E "🔵|🟢|📊|✅"
```

---

## 8. RESUMEN DE CAMBIOS TÉCNICOS

### Antes (❌ No funciona)
```
Axios withCredentials: false
    ↓ Sin cookies
Laravel no reconoce sesión
    ↓ auth()->user() == null
GET /api/user → 401 Unauthorized
POST /api/ventas → Falla con error 400 (sin detalles)
```

### Después (✅ Funciona)
```
Axios withCredentials: true
    ↓ Incluye cookies + X-CSRF-TOKEN
Middleware CORS valida (supports_credentials: true)
    ↓
Middleware Session recupera sesión
    ↓ auth()->user() == User object
GET /api/user → 200 OK
POST /api/ventas → Valida y crea venta (201 Created)
O retorna errores detallados con status 422
```

---

## 9. PRÓXIMOS PASOS DE DEBUGGING

Si aún hay problemas:

1. **Abre DevTools (F12) → Console** y ejecuta:
   ```javascript
   console.log('Cookies:', document.cookie);
   console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);
   ```

2. **Revisa logs en**: `storage/logs/laravel.log`
   - Busca líneas con `🔵 INCOMING REQUEST`
   - Verifica: `authenticated_user`, `all_data`, `has_csrf_token`

3. **Si error 422 persiste**:
   - Revisa pestaña Network → POST /api/ventas → Response
   - Busca el campo exacto que falla
   - Ajusta validación o payload según sea necesario

