# 📋 ESTRUCTURA ACTUAL DE BASE DE DATOS - VIICITO v1.0.0

## ✅ TABLA 1: USUARIO

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_usuario** | BIGINT UNSIGNED | - | Identificador único con incremento automático | PK, AUTO_INCREMENT |
| nombre_completo | VARCHAR | 100 | Nombre completo del trabajador | NOT NULL |
| email | VARCHAR | 100 | Correo electrónico único para inicio de sesión | NOT NULL, UNIQUE |
| username | VARCHAR | 50 | Nombre de usuario único en el sistema | NOT NULL, UNIQUE |
| password_hash | VARCHAR | 255 | Clave de acceso cifrada con algoritmo bcrypt | NOT NULL |
| rol | ENUM | - | Perfil de acceso: administrador, vendedor, auditor | NOT NULL, DEFAULT 'vendedor' |
| estado | ENUM | - | Estado de la cuenta: Activo, Inactivo, Bloqueado | NOT NULL, DEFAULT 'Activo' |
| telefono | VARCHAR | 20 | Número telefónico de contacto | NULLABLE |
| created_at | TIMESTAMP | - | Fecha y hora de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |
| **deleted_at** | TIMESTAMP | - | Soft delete (eliminación lógica) | NULLABLE |

---

## ✅ TABLA 2: CATEGORIA

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_categoria** | BIGINT UNSIGNED | - | Identificador único de la categoría (PK) | PK, AUTO_INCREMENT |
| nombre_categoria | VARCHAR | 50 | Nombre único (Ron, Vodka, Whisky, etc.) | NOT NULL, UNIQUE |
| descripcion | TEXT | - | Detalle sobre el tipo de productos | NULLABLE |
| estado | ENUM | - | Disponibilidad: Activo, Inactivo | DEFAULT 'Activo' |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

---

## ✅ TABLA 3: MARCA (NUEVA)

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_marca** | BIGINT UNSIGNED | - | Identificador único de la marca (PK) | PK, AUTO_INCREMENT |
| nombre_marca | VARCHAR | 50 | Nombre de marca (Bacardí, Johnnie Walker, etc.) | NOT NULL, UNIQUE |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

---

## ✅ TABLA 4: PROVEEDOR

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_proveedor** | BIGINT UNSIGNED | - | Identificador único del proveedor (PK) | PK, AUTO_INCREMENT |
| nombre_empresa | VARCHAR | 100 | Razón social o nombre comercial | NOT NULL |
| contacto_nombre | VARCHAR | 100 | Persona de contacto en la empresa | NULLABLE |
| email | VARCHAR | 100 | Correo electrónico de contacto | NULLABLE |
| telefono | VARCHAR | 20 | Número de teléfono o celular | NULLABLE |
| ciudad | VARCHAR | 50 | Ciudad base del proveedor | NULLABLE |
| estado_proveedor | ENUM | - | Activo, Inactivo, Suspendido | DEFAULT 'Activo' |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

---

## ✅ TABLA 5: PRODUCTO

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_producto** | BIGINT UNSIGNED | - | Identificador único (PK, AUTO_INCREMENT) | PK, AUTO_INCREMENT |
| id_categoria | BIGINT UNSIGNED | - | FK a categoria.id_categoria (NOT NULL) | NOT NULL, FOREIGN KEY |
| **id_marca** | BIGINT UNSIGNED | - | FK a marca.id_marca | NULLABLE, FOREIGN KEY |
| codigo_barras | VARCHAR | 50 | Código de barras único (NULLABLE, UNIQUE) | NULLABLE, UNIQUE |
| **sku** | VARCHAR | 50 | Stock Keeping Unit único | NULLABLE, UNIQUE |
| nombre_producto | VARCHAR | 100 | Nombre comercial del producto (NOT NULL) | NOT NULL |
| **descripcion** | TEXT | - | Descripción detallada del producto | NULLABLE |
| precio_compra | DECIMAL | 10,2 | Precio de costo de adquisición (NOT NULL) | NOT NULL |
| precio_venta | DECIMAL | 10,2 | Precio de venta al público (NOT NULL) | NOT NULL |
| stock_actual | INT UNSIGNED | - | Cantidad física en inventario (DEFAULT: 0) | DEFAULT 0 |
| stock_minimo | INT UNSIGNED | - | Nivel crítico para reorden (DEFAULT: 5) | DEFAULT 5 |
| **stock_maximo** | INT UNSIGNED | - | Cantidad máxima permitida en inventario | DEFAULT 100 |
| **volumen_ml** | INT | - | Volumen en mililitros (para licores) | NULLABLE |
| **grado_alcohol** | DECIMAL | 5,2 | Grado alcohólico porcentaje | NULLABLE |
| **imagen_url** | VARCHAR | 255 | URL de imagen del producto | NULLABLE |
| **estado** | ENUM | - | Activo, Descontinuado, Suspendido | DEFAULT 'Activo' |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |
| **deleted_at** | TIMESTAMP | - | Soft delete (eliminación lógica) | NULLABLE |

---

## ✅ TABLA 6: VENTA

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_venta** | BIGINT UNSIGNED | - | Identificador único (PK, AUTO_INCREMENT) | PK, AUTO_INCREMENT |
| id_usuario | BIGINT UNSIGNED | - | FK a usuario.id_usuario (Vendedor) | NOT NULL, FOREIGN KEY |
| numero_documento | VARCHAR | 50 | Número secuencial factura (NOT NULL, UNIQUE) | NOT NULL, UNIQUE |
| fecha_hora | DATETIME | - | Fecha y hora de venta | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| **subtotal** | DECIMAL | 12,2 | Subtotal sin impuestos | NULLABLE |
| **impuesto** | DECIMAL | 12,2 | Monto de IVA/impuesto | DEFAULT 0 |
| total_venta | DECIMAL | 12,2 | Total final con impuestos (NOT NULL) | NOT NULL |
| metodo_pago | ENUM | - | Efectivo, Tarjeta, Cheque, Crédito | NOT NULL |
| estado | ENUM | - | Completada, Cancelada, Pendiente, Devuelta | DEFAULT 'Completada' |
| **observacion** | TEXT | - | Notas o comentarios de la venta | NULLABLE |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

---

## ✅ TABLA 7: DETALLE_VENTA

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_detalle_venta** | BIGINT UNSIGNED | - | Identificador único del detalle (PK) | PK, AUTO_INCREMENT |
| id_venta | BIGINT UNSIGNED | - | Relación con la cabecera de venta (FK) | NOT NULL, FOREIGN KEY (CASCADE) |
| id_producto | BIGINT UNSIGNED | - | Relación con el producto vendido (FK) | NOT NULL, FOREIGN KEY |
| cantidad | INT UNSIGNED | - | Unidades vendidas en este ítem | NOT NULL |
| precio_unitario | DECIMAL | 10,2 | Precio aplicado al momento de la venta | NOT NULL |
| descuento | DECIMAL | 10,2 | Rebaja aplicada individualmente | DEFAULT 0 |
| subtotal | DECIMAL | 12,2 | (Cantidad * Precio) - Descuento | NOT NULL |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |

---

## ✅ TABLA 8: COMPRA

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_compra** | BIGINT UNSIGNED | - | Identificador único de la compra (PK) | PK, AUTO_INCREMENT |
| id_usuario | BIGINT UNSIGNED | - | Usuario que registra la entrada (FK) | NOT NULL, FOREIGN KEY |
| id_proveedor | BIGINT UNSIGNED | - | Empresa que provee los productos (FK) | NOT NULL, FOREIGN KEY |
| numero_orden | VARCHAR | 50 | Código único de la orden de compra | NOT NULL, UNIQUE |
| **numero_factura_proveedor** | VARCHAR | 50 | Número de factura del proveedor | NULLABLE |
| **fecha_orden** | DATETIME | - | Fecha de la orden de compra | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| **fecha_entrega** | DATETIME | - | Fecha de entrega recibida | NULLABLE |
| total_compra | DECIMAL | 12,2 | Monto total pagado al proveedor | NOT NULL |
| estado | ENUM | - | Pendiente, Parcial, Completada, Cancelada | DEFAULT 'Pendiente' |
| **observacion** | TEXT | - | Notas de la compra | NULLABLE |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

---

## ✅ TABLA 9: DETALLE_COMPRA

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_detalle_compra** | BIGINT UNSIGNED | - | Identificador del ítem de compra (PK) | PK, AUTO_INCREMENT |
| id_compra | BIGINT UNSIGNED | - | Relación con la cabecera de compra (FK) | NOT NULL, FOREIGN KEY (CASCADE) |
| id_producto | BIGINT UNSIGNED | - | Producto recibido (FK) | NOT NULL, FOREIGN KEY |
| cantidad_ordenada | INT UNSIGNED | - | Unidades solicitadas al proveedor | NOT NULL |
| **cantidad_recibida** | INT UNSIGNED | - | Unidades realmente recibidas | DEFAULT 0 |
| costo_unitario | DECIMAL | 10,2 | Costo unitario al momento de la compra | NOT NULL |
| **subtotal** | DECIMAL | 12,2 | cantidad_ordenada × costo_unitario | NOT NULL |
| created_at | TIMESTAMP | - | Fecha de creación del registro | DEFAULT CURRENT_TIMESTAMP |
| **updated_at** | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

---

## ✅ TABLA 10: AUDITORIA

| Campo | Tipo Dato | Longitud | Descripción | Constraints |
|-------|-----------|----------|-------------|-------------|
| **id_auditoria** | BIGINT UNSIGNED | - | Identificador único del registro (PK) | PK, AUTO_INCREMENT |
| id_usuario | BIGINT UNSIGNED | - | Usuario que realizó la acción (FK) | NOT NULL, FOREIGN KEY |
| tabla | VARCHAR | 50 | Nombre de la tabla que fue modificada | NOT NULL |
| tipo_operacion | ENUM | - | Tipo de cambio: INSERT, UPDATE, DELETE | NOT NULL |
| id_registro | BIGINT UNSIGNED | - | ID del registro afectado en la tabla origen | NOT NULL |
| datos_anteriores | JSON | - | Valores previos al cambio | NULLABLE |
| datos_nuevos | JSON | - | Valores posteriores al cambio | NULLABLE |
| fecha_hora | TIMESTAMP | - | Momento exacto de la modificación | DEFAULT CURRENT_TIMESTAMP |

---

## 📊 CAMPOS AGREGADOS (Resumen)

Los campos marcados en **NEGRITA** en las tablas anteriores son los que se agregaron a la documentación original para reflejar lo que realmente existe en el proyecto.

### Por Tabla:

**USUARIO:** `updated_at`, `deleted_at`  
**CATEGORIA:** `updated_at`  
**MARCA:** Tabla completa (nueva)  
**PROVEEDOR:** `updated_at`  
**PRODUCTO:** `id_marca`, `sku`, `descripcion`, `stock_maximo`, `volumen_ml`, `grado_alcohol`, `imagen_url`, `estado`, `updated_at`, `deleted_at`  
**VENTA:** `subtotal`, `impuesto`, `observacion`, `updated_at`  
**COMPRA:** `numero_factura_proveedor`, `fecha_orden`, `fecha_entrega`, `observacion`, `updated_at`  
**DETALLE_COMPRA:** `cantidad_recibida`, `subtotal`, `updated_at`  

---

## 🔄 RELACIONES PRINCIPALES

- **usuario → venta** (1:N) - Un usuario vende múltiples productos
- **usuario → compra** (1:N) - Un usuario registra múltiples compras
- **usuario → auditoria** (1:N) - Un usuario realiza múltiples auditorías
- **categoria → producto** (1:N) - Una categoría contiene múltiples productos
- **marca → producto** (1:N) - Una marca tiene múltiples productos
- **proveedor → compra** (1:N) - Un proveedor suple múltiples compras
- **venta → detalle_venta** (1:N) - Una venta contiene múltiples items
- **compra → detalle_compra** (1:N) - Una compra contiene múltiples items
- **producto → detalle_venta** (1:N) - Un producto se vende múltiples veces
- **producto → detalle_compra** (1:N) - Un producto se compra múltiples veces

---

## 📐 NORMALIZACIÓN

✅ **Primera Forma Normal (1FN):** Todos los atributos son atómicos  
✅ **Segunda Forma Normal (2FN):** Sin dependencias parciales de clave primaria  
✅ **Tercera Forma Normal (3FN):** Sin dependencias transitivas entre atributos no-clave  

**Estado:** CUMPLE COMPLETAMENTE 3FN
