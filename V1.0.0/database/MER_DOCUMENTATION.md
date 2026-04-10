# 📊 Modelo Entidad-Relación (MER) - Viicito

## Diagrama General

```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEMA VIICITO - MER                        │
│                   (Normalización: 3ª Forma Normal)              │
└─────────────────────────────────────────────────────────────────┘

                          ┌──────────────┐
                          │   usuario    │
                          ├──────────────┤
                          │ id_usuario PK│
                          │ nombre       │
                          │ email UNIQUE │
                          │ username     │
                          │ rol ENUM     │
                          │ estado ENUM  │
                          └──────────────┘
                                 ▲
                    ┌────────────┼────────────┐
                    │            │            │
                    │1:N         │1:N         │1:N
                    │            │            │
          ┌─────────┴──────┐  ┌──┴───────┐ ┌──┴───────────┐
          │     venta      │  │  compra  │ │   cliente    │
          ├────────────────┤  ├──────────┤ ├──────────────┤
          │ id_venta PK    │  │id_compra │ │ id_cliente PK│
          │ id_usuario FK  │  │id_usuario│ │ nombre       │
          │ id_cliente FK  │  │id_proveedor FK
          │ fecha_hora     │  │ fecha    │ │ nit_ci UNIQUE│
          │ total_venta    │  │ estado   │ │ saldo_actual │
          │ metodo_pago    │  │ estado   │ │ limite_credito
          │ estado ENUM    │  │          │ │ vendedor_asignado FK
          └────────┬───────┘  └──────────┘ └──────────────┘
                   │                              ▲
                   │1:N                           │
                   │                            1:N
          ┌────────▼──────────────┐              │
          │  detalle_venta       │              │
          ├──────────────────────┤              │
          │id_detalle_venta PK   │              │
          │id_venta FK (CASCADE) │──┐           │
          │id_producto FK        │  │     ┌─────┴──────────┐
          │cantidad              │  │     │   proveedor    │
          │precio_unitario       │  │     ├────────────────┤
          │descuento             │  │     │id_proveedor PK │
          │subtotal              │  │     │nombre_empresa  │
          └──────────────────────┘  │     │contacto        │
                                    │     │estado_proveedor│
                   ┌────────────────┘     └────────────────┘
                   │
                ┌──▼───────────────┐
                │    producto      │
                ├──────────────────┤
                │ id_producto PK   │
                │ id_categoria FK  │
                │ codigo_barras    │
                │ nombre           │
                │ precio_compra    │
                │ precio_venta     │
                │ stock_actual     │
                │ stock_minimo     │
                │ estado ENUM      │
                └──────────────────┘
                        ▲
                        │1:N
                        │
                ┌───────┴──────────┐
                │    categoria     │
                ├──────────────────┤
                │id_categoria PK   │
                │nombre_categoria U│
                │descripcion       │
                │estado ENUM       │
                └──────────────────┘


                            ┌──────────────┐
                            │  auditoria   │
                            ├──────────────┤
                            │id_auditoria  │
                            │id_usuario FK │
                            │tabla         │
                            │tipo_operación│
                            │datos_json    │
                            │fecha_hora    │
                            └──────────────┘
```

## Relaciones Principales

### 1. usuario → venta (1:N)
- Un usuario puede realizar múltiples ventas
- FK: `venta.id_usuario` → `usuario.id_usuario`
- Acción DELETE: **RESTRICT** (no se puede eliminar un vendedor con ventas)
- Acción UPDATE: **CASCADE**

### 2. usuario → compra (1:N)
- Un usuario puede registrar múltiples compras
- FK: `compra.id_usuario` → `usuario.id_usuario`
- Acción DELETE: **RESTRICT**
- Acción UPDATE: **CASCADE**

### 3. cliente → venta (1:N)
- Un cliente realiza múltiples compras
- FK: `venta.id_cliente` → `cliente.id_cliente`
- Acción DELETE: **RESTRICT**
- Acción UPDATE: **CASCADE**

### 4. usuario → cliente (1:N)
- Un vendedor puede tener múltiples clientes asignados
- FK: `cliente.vendedor_asignado` → `usuario.id_usuario`
- Acción DELETE: **SET NULL**
- Acción UPDATE: **CASCADE**

### 5. proveedor → compra (1:N)
- Un proveedor suple múltiples compras
- FK: `compra.id_proveedor` → `proveedor.id_proveedor`
- Acción DELETE: **RESTRICT**
- Acción UPDATE: **CASCADE**

### 6. categoria → producto (1:N)
- Una categoría contiene múltiples productos
- FK: `producto.id_categoria` → `categoria.id_categoria`
- Acción DELETE: **RESTRICT** (no eliminar categorías con productos)
- Acción UPDATE: **CASCADE**

### 7. venta → detalle_venta (1:N)
- Una venta contiene múltiples items
- FK: `detalle_venta.id_venta` → `venta.id_venta`
- Acción DELETE: **CASCADE** (eliminar detalles al eliminar venta)
- Acción UPDATE: **CASCADE**

### 8. producto → detalle_venta (1:N)
- Un producto aparece en múltiples ventas
- FK: `detalle_venta.id_producto` → `producto.id_producto`
- Acción DELETE: **RESTRICT**
- Acción UPDATE: **CASCADE**

### 9. compra → detalle_compra (1:N)
- Una compra contiene múltiples items
- FK: `detalle_compra.id_compra` → `compra.id_compra`
- Acción DELETE: **CASCADE**
- Acción UPDATE: **CASCADE**

### 10. producto → detalle_compra (1:N)
- Un producto aparece en múltiples compras
- FK: `detalle_compra.id_producto` → `producto.id_producto`
- Acción DELETE: **RESTRICT**
- Acción UPDATE: **CASCADE**

### 11. usuario → auditoria (1:N)
- Un usuario realiza múltiples registros de auditoría
- FK: `auditoria.id_usuario` → `usuario.id_usuario`
- Acción DELETE: **RESTRICT**
- Acción UPDATE: **CASCADE**

---

## Análisis de Normalización (3FN)

### ✅ Primera Forma Normal (1FN)
- ✓ Todos los atributos contienen valores atómicos (no divisibles)
- ✓ No hay grupos repetidos
- ✓ Cada columna tiene un único dominio de valores

### ✅ Segunda Forma Normal (2FN)
- ✓ Satisface 1FN
- ✓ Todos los atributos no-clave dependen completamente de la clave primaria
- ✓ No hay dependencias parciales
- ✓ En `detalle_venta`: ambos `id_venta` e `id_producto` son necesarios para identifi car el registro

### ✅ Tercera Forma Normal (3FN)
- ✓ Satisface 2FN
- ✓ No hay dependencias transitivas
- ✓ Ejemplo: En `producto`, `nombre_categoria` está en tabla `categoria`, no en `producto`
- ✓ En `venta`, se almacena `total_venta` para performance, pero es derivable

---

## Tipos de Datos Optimizados

| Atributo | Tipo | Razón |
|----------|------|-------|
| `precio_compra` | `DECIMAL(10,2)` | Precisión exacta (no FLOAT) |
| `precio_venta` | `DECIMAL(10,2)` | Precisión exacta |
| `stock_actual` | `INT UNSIGNED` | Siempre positivo |
| `id_*` | `BIGINT UNSIGNED` | Escalabilidad a millones de registros |
| `rol`, `estado` | `ENUM` | Eficiente en espacio, valores predefinidos |
| `fecha_hora` | `DATETIME` | Precisión hasta segundos |
| `email`, `username` | `VARCHAR(100, 50)` con UNIQUE | Índice implícito |

---

## Índices Para Performance

```sql
-- Búsquedas frecuentes
INDEX idx_codigo_barras (codigo_barras)
INDEX idx_nombre_producto (nombre_producto)
INDEX idx_estado (estado)
INDEX idx_stock (stock_actual)

-- Filtros por rango de tiempo
INDEX idx_fecha (fecha_hora)

-- Queries combinadas
INDEX idx_compra_producto (id_compra, id_producto)
INDEX idx_venta_producto (id_venta, id_producto)
```

---

## Vistas Implementadas

### v_producto_completo
Junta información de producto + categoría con cálculos:
- Margen de ganancia
- Estado del stock
- Información completa sin necesidad de JOINs

### v_venta_detallada
Información de venta con:
- Nombre del vendedor
- Nombre del cliente
- Cantidad de items vendidos

### v_stock_bajo
Productos con stock bajo que requieren reorden

---

## Conclusión

Este MER está **100% normalizado hasta 3FN** y optimizado para:
- ✅ Integridad referencial
- ✅ Performance en búsquedas
- ✅ Escalabilidad
- ✅ Auditoría de cambios
- ✅ Lógica de negocio clara
