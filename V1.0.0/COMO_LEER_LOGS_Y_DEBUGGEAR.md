# Instrucciones para Leer Logs y Debuggear

## 1. VERIFICACIÓN RÁPIDA EN EL NAVEGADOR

```
1. Abre: http://127.0.0.1:8000
2. Login: edimartorrezlobo@gmail.com / password123
3. Presiona F12 para abrir DevTools
4. Ve a pestaña "Network"
5. Filtra por "XHR" (XMLHttpRequest)
6. Navega a "Nueva Venta"
7. Observa las peticiones:
   - GET /api/user → ¿Status 200 OK?
   - GET /api/productos → ¿Status 200 OK?
8. Intenta crear una venta y observa:
   - POST /api/ventas → ¿Status 201 Created o error?
```

## 2. LEER LOGS EN VISUAL STUDIO CODE

### Opción A: Desde el Editor (RECOMENDADO)

```
1. En VS Code, presiona: Ctrl+P
2. Escribe: storage/logs/laravel.log
3. Presiona Enter
4. El archivo se abre en el editor
5. Presiona Ctrl+End para ir al final del archivo
6. Verás los logs más recientes
7. Busca líneas que contengan:
   - "ERROR" - errores críticos
   - "🔵 INCOMING REQUEST" - peticiones recibidas
   - "🟢 RESPONSE" - respuestas enviadas
```

### Opción B: Desde Terminal (Para monitoreo en vivo)

```bash
# En una terminal nueva, en el directorio v1.0.0:

# Para Windows PowerShell:
Get-Content storage/logs/laravel.log -Wait -Tail 50

# O usa tail si tienes Git Bash instalado:
tail -f storage/logs/laravel.log
```

## 3. INTERPRETAR LOGS DE ERROR

Si ves un error como:
```
[2026-05-10 12:34:56] local.ERROR: SQLSTATE[HY000] [1045] Access denied for user 'root'
```

Significa:
- **SQLSTATE[HY000]**: Error de conexión a base de datos
- **[1045]**: Código MySQL para "credenciales inválidas"
- **using password: NO**: Laravel NO está usando contraseña (revisar `.env`)

## 4. LOGS IMPORTANTES A BUSCAR

Después de hacer login, deberías ver:
```
[2026-05-10 HH:MM:SS] local.DEBUG: 🔵 INCOMING REQUEST {
  "method": "GET",
  "path": "user",
  "url": "http://127.0.0.1:8000/api/user"
}

[2026-05-10 HH:MM:SS] local.DEBUG: 🟢 RESPONSE {
  "status": 200,
  "path": "user"
}
```

Si ves **status 401**, significa que la sesión NO se recuperó.

## 5. DEBUGGING DE VALIDACIÓN (Error 422)

Si intenta crear venta y recibe **error 422**:

En DevTools → Network → POST /api/ventas → Response, verás algo como:

```json
{
  "message": "Errores de validación",
  "errors": {
    "id_usuario": ["The selected id usuario is invalid."],
    "metodo_pago": ["The metodo pago field is required."]
  }
}
```

Esto te dice EXACTAMENTE qué campo falla.

## 6. CÓMO COMPARTIR LOGS CONMIGO

Si ocurre un error:

1. Abre `storage/logs/laravel.log`
2. Ve al **FINAL** del archivo (Ctrl+End)
3. Copia las últimas **20-30 líneas** que contengan ERROR o mensajes relevantes
4. Pégalos en tu próximo mensaje con este formato:

```
---LOGS START---
[líneas del log aquí]
---LOGS END---
```

## 7. ORDEN DE OPERACIONES PARA DEBUGGEAR

```
1. Limpia cache: php artisan config:clear && php artisan cache:clear
2. Reinicia servidor
3. Abre navegador → http://127.0.0.1:8000
4. Abre DevTools (F12)
5. Ve a pestaña Network
6. Intenta login
7. Observa peticiones en Network
8. Si error, copia los logs
```

## 8. ARCHIVOS CRÍTICOS PARA REVISAR

Si persisten errores, estos archivos son críticos:

- `/.env` - Variables de entorno (especialmente DB_PASSWORD)
- `/config/cors.php` - Configuración CORS
- `/config/sanctum.php` - Configuración de autenticación
- `/bootstrap/app.php` - Middleware y configuración
- `/routes/api.php` - Rutas API
- `/storage/logs/laravel.log` - Logs de errores
