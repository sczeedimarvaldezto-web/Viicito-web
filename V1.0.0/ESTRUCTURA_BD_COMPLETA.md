# 📋 ESTRUCTURA COMPLETA DE BASE DE DATOS - VIICITO v1.0.0
**Última actualización: 2026-06-15**

---

## 📊 TABLA 1: USUARIO

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_usuario** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| nombre_completo | VARCHAR | 100 | Nombre completo del trabajador | NOT NULL |
| email | VARCHAR | 100 | Correo electrónico único | NOT NULL, UNIQUE |
| username | VARCHAR | 50 | Nombre de usuario único | NOT NULL, UNIQUE |
| password_hash | VARCHAR | 255 | Contraseña con hash bcrypt | NOT NULL |
| rol | ENUM | - | Perfil: administrador, vendedor, auditor | NOT NULL, DEFAULT 'vendedor' |
| estado | ENUM | - | Estado: Activo, Inactivo, Bloqueado | NOT NULL, DEFAULT 'Activo' |
| telefono | VARCHAR | 20 | Número telefónico de contacto | NULLABLE |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |
| deleted_at | TIMESTAMP | - | Soft delete (eliminación lógica) | NULLABLE |

**Índices:** email (UNIQUE), username (UNIQUE), estado, rol

---

## 📊 TABLA 2: CATEGORIA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_categoria** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| nombre_categoria | VARCHAR | 50 | Nombre único (Ron, Vodka, etc.) | NOT NULL, UNIQUE |
| descripcion | TEXT | - | Detalle del tipo de productos | NULLABLE |
| estado | ENUM | - | Disponibilidad: Activo, Inactivo | DEFAULT 'Activo' |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Índices:** nombre_categoria, estado

---

## 📊 TABLA 3: MARCA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_marca** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| nombre_marca | VARCHAR | 50 | Nombre de marca (Bacardí, Johnnie Walker) | NOT NULL, UNIQUE |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Índices:** nombre_marca

---

## 📊 TABLA 4: PROVEEDOR

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_proveedor** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| nombre_empresa | VARCHAR | 100 | Razón social o nombre comercial | NOT NULL |
| contacto_nombre | VARCHAR | 100 | Persona de contacto en la empresa | NULLABLE |
| email | VARCHAR | 100 | Correo electrónico de contacto | NULLABLE |
| telefono | VARCHAR | 20 | Número de teléfono o celular | NULLABLE |
| ciudad | VARCHAR | 50 | Ciudad base del proveedor | NULLABLE |
| estado_proveedor | ENUM | - | Activo, Inactivo, Suspendido | DEFAULT 'Activo' |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Índices:** nombre_empresa, estado_proveedor

---

## 📊 TABLA 5: PRODUCTO

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_producto** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| id_categoria | BIGINT UNSIGNED | - | FK a categoria | NOT NULL, FOREIGN KEY |
| id_marca | BIGINT UNSIGNED | - | FK a marca | NULLABLE, FOREIGN KEY |
| codigo_barras | VARCHAR | 50 | Código de barras único | NULLABLE, UNIQUE |
| sku | VARCHAR | 50 | Stock Keeping Unit único | NULLABLE, UNIQUE |
| nombre_producto | VARCHAR | 100 | Nombre comercial del producto | NOT NULL |
| descripcion | TEXT | - | Descripción detallada del producto | NULLABLE |
| precio_compra | DECIMAL | 10,2 | Precio de costo de adquisición | NOT NULL |
| precio_venta | DECIMAL | 10,2 | Precio de venta al público | NOT NULL |
| stock_actual | INT UNSIGNED | - | Cantidad física en inventario | DEFAULT 0 |
| stock_minimo | INT UNSIGNED | - | Nivel crítico para reorden | DEFAULT 5 |
| stock_maximo | INT UNSIGNED | - | Cantidad máxima permitida | DEFAULT 100 |
| volumen_ml | INT | - | Volumen en mililitros (para licores) | NULLABLE |
| grado_alcohol | DECIMAL | 5,2 | Grado alcohólico porcentaje | NULLABLE |
| imagen_url | VARCHAR | 255 | URL de imagen del producto | NULLABLE |
| estado | ENUM | - | Activo, Descontinuado, Suspendido | DEFAULT 'Activo' |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |
| deleted_at | TIMESTAMP | - | Soft delete | NULLABLE |

**Índices:** id_categoria, id_marca, codigo_barras, sku, nombre_producto, estado, stock_actual

---

## 📊 TABLA 6: VENTA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_venta** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| id_usuario | BIGINT UNSIGNED | - | FK a usuario (Vendedor) | NOT NULL, FOREIGN KEY |
| numero_documento | VARCHAR | 50 | Número secuencial de factura | NOT NULL, UNIQUE |
| fecha_hora | DATETIME | - | Fecha y hora de venta | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| subtotal | DECIMAL | 12,2 | Subtotal sin impuestos | NULLABLE |
| impuesto | DECIMAL | 12,2 | Monto de IVA | DEFAULT 0 |
| total_venta | DECIMAL | 12,2 | Total final con impuestos | NOT NULL |
| metodo_pago | ENUM | - | Efectivo, Tarjeta, Cheque, Crédito | NOT NULL |
| estado | ENUM | - | Completada, Cancelada, Pendiente, Devuelta | DEFAULT 'Completada' |
| observacion | TEXT | - | Notas o comentarios de la venta | NULLABLE |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Índices:** id_usuario, numero_documento (UNIQUE), fecha_hora, estado, metodo_pago

---

## 📊 TABLA 7: DETALLE_VENTA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_detalle_venta** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| id_venta | BIGINT UNSIGNED | - | FK a venta | NOT NULL, FOREIGN KEY (CASCADE) |
| id_producto | BIGINT UNSIGNED | - | FK a producto vendido | NOT NULL, FOREIGN KEY |
| cantidad | INT UNSIGNED | - | Unidades vendidas | NOT NULL |
| precio_unitario | DECIMAL | 10,2 | Precio al momento de venta | NOT NULL |
| descuento | DECIMAL | 10,2 | Descuento aplicado al item | DEFAULT 0 |
| subtotal | DECIMAL | 12,2 | (cantidad × precio) - descuento | NOT NULL |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |

**Índices:** id_venta, id_producto, (id_venta, id_producto)

---

## 📊 TABLA 8: COMPRA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_compra** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| id_usuario | BIGINT UNSIGNED | - | FK a usuario que registra | NOT NULL, FOREIGN KEY |
| id_proveedor | BIGINT UNSIGNED | - | FK a proveedor | NOT NULL, FOREIGN KEY |
| numero_orden | VARCHAR | 50 | Número único de orden de compra | NOT NULL, UNIQUE |
| numero_factura_proveedor | VARCHAR | 50 | Número de factura del proveedor | NULLABLE |
| fecha_orden | DATETIME | - | Fecha de la orden | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| fecha_entrega | DATETIME | - | Fecha de entrega | NULLABLE |
| total_compra | DECIMAL | 12,2 | Total pagado al proveedor | NOT NULL |
| estado | ENUM | - | Pendiente, Parcial, Completada, Cancelada | DEFAULT 'Pendiente' |
| observacion | TEXT | - | Notas de la compra | NULLABLE |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Índices:** id_usuario, id_proveedor, numero_orden (UNIQUE), fecha_orden, estado

---

## 📊 TABLA 9: DETALLE_COMPRA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_detalle_compra** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| id_compra | BIGINT UNSIGNED | - | FK a compra | NOT NULL, FOREIGN KEY (CASCADE) |
| id_producto | BIGINT UNSIGNED | - | FK a producto | NOT NULL, FOREIGN KEY |
| cantidad_ordenada | INT UNSIGNED | - | Unidades solicitadas al proveedor | NOT NULL |
| cantidad_recibida | INT UNSIGNED | - | Unidades realmente recibidas | DEFAULT 0 |
| costo_unitario | DECIMAL | 10,2 | Costo al momento de compra | NOT NULL |
| subtotal | DECIMAL | 12,2 | cantidad_ordenada × costo_unitario | NOT NULL |
| created_at | TIMESTAMP | - | Fecha de creación | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | - | Fecha de última actualización | DEFAULT CURRENT_TIMESTAMP ON UPDATE |

**Índices:** id_compra, id_producto, (id_compra, id_producto)

---

## 📊 TABLA 10: AUDITORIA

| Campo | Tipo | Longitud | Descripción | Constraints |
|-------|------|----------|-------------|-------------|
| **id_auditoria** | BIGINT UNSIGNED | - | Identificador único | PK, AUTO_INCREMENT |
| id_usuario | BIGINT UNSIGNED | - | FK a usuario que realizó acción | NOT NULL, FOREIGN KEY |
| tabla | VARCHAR | 50 | Nombre de tabla modificada | NOT NULL |
| tipo_operacion | ENUM | - | INSERT, UPDATE, DELETE | NOT NULL |
| id_registro | BIGINT UNSIGNED | - | ID del registro afectado | NOT NULL |
| datos_anteriores | JSON | - | Valores previos al cambio | NULLABLE |
| datos_nuevos | JSON | - | Valores posteriores al cambio | NULLABLE |
| fecha_hora | TIMESTAMP | - | Momento exacto de modificación | DEFAULT CURRENT_TIMESTAMP |

**Índices:** id_usuario, tabla, fecha_hora

---

## 📊 VISTAS SQL IMPLEMENTADAS

### v_producto_completo
Información completa de productos con cálculos:
- `id_producto`, `nombre_producto`, `codigo_barras`, `sku`
- `id_categoria`, `nombre_categoria`
- `precio_compra`, `precio_venta`, **margen_ganancia** (calculado)
- `stock_actual`, `stock_minimo`, `stock_maximo`, **estado_stock** (calculado)
- `volumen_ml`, `grado_alcohol`, `estado`

### v_venta_detallada
Resumen de ventas con información relacionada:
- `id_venta`, `numero_documento`, `fecha_hora`
- `vendedor` (nombre del usuario)
- `total_venta`, `metodo_pago`, `estado`
- `cantidad_items` (conteo de productos)

### v_stock_bajo
Productos con stock crítico:
- `id_producto`, `nombre_producto`
- `stock_actual`, `stock_minimo`, `cantidad_faltante`
- `nombre_categoria`

---

## ✅ RESUMEN DE CAMBIOS APLICADOS (2026-06-15)

| Tabla | Campo Agregado | Tipo | Razón |
|-------|---|---|---|
| PRODUCTO | descripcion | TEXT | Descripción detallada |
| PRODUCTO | volumen_ml | INT | Para licores |
| PRODUCTO | stock_maximo | INT | Control de máximos |
| VENTA | subtotal | DECIMAL | Desglose de precios |
| VENTA | impuesto | DECIMAL | Cálculo de IVA |
| VENTA | observacion | TEXT | Notas adicionales |
| DETALLE_COMPRA | cantidad_recibida | INT | Control de recepciones |

---

## 📐 NORMALIZACIÓN (3FN)

✅ **1ª Forma Normal (1FN):** Todos los atributos son atómicos  
✅ **2ª Forma Normal (2FN):** Sin dependencias parciales de clave primaria  
✅ **3ª Forma Normal (3FN):** Sin dependencias transitivas entre atributos no-clave  

**Estado:** CUMPLE COMPLETAMENTE 3FN
