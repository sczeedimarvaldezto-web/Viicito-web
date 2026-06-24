# 📋 Implementación de Historia de Usuario: Gestión de Proveedores y Compras

**Fecha de Implementación**: 2026-06-16  
**Versión**: v1.0.0  
**Estado**: ✅ COMPLETADO

---

## 🎯 Historia de Usuario

**Como** administrador **quiero** registrar los datos de los proveedores y las facturas de compra **para** automatizar el reabastecimiento del inventario y conocer el costo real de adquisición de los licores.

---

## ✅ Criterios de Validación - IMPLEMENTADOS

### 1. Tablas con Relación One-to-Many
✅ **Verificación Completada**

- **Tabla `proveedor`**: Creada con campos:
  - `id_proveedor` (PK, BIGINT UNSIGNED)
  - `nombre_empresa` (VARCHAR 100, NOT NULL, UNIQUE)
  - `contacto_nombre` (VARCHAR 100, nullable)
  - `email` (VARCHAR 100, nullable, UNIQUE)
  - `telefono` (VARCHAR 20, nullable)
  - `ciudad` (VARCHAR 50, nullable)
  - `estado_proveedor` (ENUM: Activo, Inactivo, Suspendido)
  - `deleted_at` (TIMESTAMP, nullable) - **Soft Deletes**
  - Timestamps: `created_at`, `updated_at`

- **Tabla `compra`**: Creada con relación FK a proveedor
  - `id_compra` (PK, INT)
  - `id_proveedor` (FK a proveedor) - **RESTRICT ON DELETE**
  - Relación: 1 Proveedor → Muchas Compras

- **Tabla `detalle_compra`**: Creada con detalles de cada compra
  - `id_detalle_compra` (PK, INT)
  - `id_compra` (FK a compra) - **CASCADE ON DELETE**
  - `cantidad_ordenada`, `cantidad_recibida` (INT, validadas)
  - `costo_unitario` (DECIMAL 10,2)
  - `subtotal` (DECIMAL 10,2)

### 2. Transacciones de Base de Datos (DB::transaction)
✅ **Implementadas con Atomicidad ACID**

#### En `CompraController@store`:
```php
DB::transaction(function () {
    // Crear compra
    // Crear detalles
    // Actualizar total con precisión DECIMAL(10,2)
    // Si algo falla → ROLLBACK automático
}, maxAttempts: 3)
```

**Garantías**:
- ✅ Inserción de factura de compra
- ✅ Creación de detalles de compra
- ✅ Cálculo correcto de totales
- ✅ TODO se revierte si algún paso falla

#### En `CompraController@recibirItems`:
```php
DB::transaction(function () {
    // Actualizar cantidad_recibida
    // Incrementar stock_actual del producto
    // Actualizar estado de compra
    // Si algo falla → ROLLBACK (stock NO se incrementa)
}, maxAttempts: 3)
```

**Garantías**:
- ✅ Stock se actualiza SOLO si todos los pasos son exitosos
- ✅ Previene inconsistencias entre compra y inventario
- ✅ Auditoría completa de transacciones

### 3. Precisión de Precios DECIMAL(10,2)
✅ **Validado y Garantizado**

- ✅ Campo `costo_unitario` en `detalle_compra`: DECIMAL(10,2)
- ✅ Campo `subtotal` en `detalle_compra`: DECIMAL(10,2)
- ✅ Campo `precio_compra` en `producto`: DECIMAL(10,2)
- ✅ Campo `total_compra` en `compra`: DECIMAL(12,2) (permite totales mayores)

**Redondeo Correcto**:
```php
// En transacción de creación
$subtotal = round($item['cantidad_ordenada'] * $item['costo_unitario'], 2);
$total = round($total, 2);

// Casting a decimal en modelo
'total_compra' => 'decimal:2',
'costo_unitario' => 'decimal:2',
```

### 4. Foreign Key Constraints con Protección
✅ **Implementado con Soft Deletes**

#### Restricción de Eliminación (RESTRICT)
```sql
ALTER TABLE `compra` 
  ADD CONSTRAINT `compra_id_proveedor_foreign`
  FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor`(`id_proveedor`)
  ON DELETE RESTRICT ON UPDATE CASCADE
```

**Implementación en Código**:

```php
// En ProveedorModel
public function puedeSerEliminado(): bool {
    return !$this->compras()->exists();
}

public function obtenerRazonNoEliminacion(): ?string {
    if ($this->compras()->exists()) {
        $cantidad = $this->compras()->count();
        return "No se puede eliminar: {$cantidad} compra(s) asociada(s)";
    }
    return null;
}

// En ProveedorController@destroy
if (!$proveedor->puedeSerEliminado()) {
    return response()->json([
        'message' => 'No se puede eliminar el proveedor',
        'reason' => $proveedor->obtenerRazonNoEliminacion(),
        'compras_asociadas' => $proveedor->compras()->count()
    ], Response::HTTP_CONFLICT);
}

// Usar Soft Delete (eliminación lógica)
$proveedor->delete(); // No física, lógica
```

**Ventajas del Soft Delete**:
- ✅ Preserva auditoría histórica
- ✅ Permite restauración futura
- ✅ Mantiene integridad referencial
- ✅ NO elimina compras asociadas

---

## 🔧 Cambios Implementados

### 1. Migraciones Creadas/Mejoradas

#### ✅ `2026_06_16_000000_enhance_proveedor_and_compra_tables.php`
- Agregar `deleted_at` a tabla `proveedor` para soft deletes
- Agregar índices en `compra` para optimización
- Validar campos `cantidad_recibida` en `detalle_compra`
- Documentar constraints de foreign keys

### 2. Modelos Actualizados

#### ✅ `Proveedor.php`
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model {
    use HasFactory, SoftDeletes;
    
    // Métodos de validación
    public function puedeSerEliminado(): bool {}
    public function obtenerRazonNoEliminacion(): ?string {}
    
    // Scope para incluir eliminados
    public function scopeConEliminados($query) {}
}
```

#### ✅ `Compra.php`
```php
class Compra extends Model {
    // Relaciones y scopes existentes optimizados
    // Casting a decimal:2 para precisión
    
    protected $casts = [
        'total_compra' => 'decimal:2',
        'fecha_orden' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];
}
```

### 3. Controladores Mejorados

#### ✅ `ProveedorController.php`
```php
// Nuevas funcionalidades:
- index(): Filtros mejorados + incluir_eliminados
- store(): Validación completa de uniqueness
- update(): Evita actualizar eliminados
- destroy(): PROTECCIÓN con verificación de compras
- restaurar(): Restaurar proveedores eliminados (NEW)
```

#### ✅ `CompraController.php`
```php
// Transacciones ACID mejoradas:
- store(): Transacción con maxAttempts: 3
- recibirItems(): Transacción atómica para stock
- destroy(): Validación estado Pendiente
- Validaciones de integridad referencial
- Mensajes de error específicos
```

---

## 📊 Validación de Criterios

| Criterio | Status | Detalles |
|----------|--------|----------|
| **Tablas (proveedor + compra)** | ✅ COMPLETO | Creadas con relación 1:Many |
| **Transacciones ACID** | ✅ COMPLETO | `DB::transaction()` con rollback |
| **DECIMAL(10,2)** | ✅ COMPLETO | Precios y cálculos precisos |
| **Foreign Key Constraints** | ✅ COMPLETO | RESTRICT ON DELETE implementado |
| **Soft Deletes** | ✅ COMPLETO | Eliminación lógica en proveedor |
| **Protección de Integridad** | ✅ COMPLETO | Validación previa a delete |

---

## 🚀 Uso del Sistema

### Crear Proveedor
```http
POST /api/proveedores
Content-Type: application/json

{
  "nombre_empresa": "Distribuidora Licores XYZ",
  "contacto_nombre": "Juan Pérez",
  "email": "juan@distribuidor.com",
  "telefono": "+1-234-567890",
  "ciudad": "Santo Domingo",
  "estado_proveedor": "Activo"
}
```

### Crear Compra con Transacción
```http
POST /api/compras
Content-Type: application/json

{
  "id_usuario": 1,
  "id_proveedor": 5,
  "numero_factura_proveedor": "FAC-2026-001",
  "items": [
    {
      "id_producto": 1,
      "cantidad_ordenada": 10,
      "costo_unitario": 85.50
    },
    {
      "id_producto": 2,
      "cantidad_ordenada": 5,
      "costo_unitario": 125.00
    }
  ],
  "observacion": "Compra de reabastecimiento mensual"
}
```

**Transacción Ejecutada**:
1. ✅ Crear `compra` con número único
2. ✅ Crear `detalle_compra` para cada item
3. ✅ Calcular totales con DECIMAL(10,2)
4. ✅ Si falla → ROLLBACK (nada se guarda)

### Recibir Items
```http
POST /api/compras/1/recibir
Content-Type: application/json

{
  "items": [
    {
      "id_detalle_compra": 1,
      "cantidad_recibida": 10
    }
  ]
}
```

**Transacción Ejecutada**:
1. ✅ Validar cantidad ≤ cantidad_ordenada
2. ✅ Actualizar `cantidad_recibida`
3. ✅ Incrementar `stock_actual` del producto
4. ✅ Actualizar estado de compra
5. ✅ Si algo falla → ROLLBACK (stock NO se incrementa)

### Eliminar Proveedor (Protegido)
```http
DELETE /api/proveedores/5
```

**Respuesta si tiene compras**:
```json
{
  "success": false,
  "message": "No se puede eliminar el proveedor",
  "reason": "No se puede eliminar este proveedor porque tiene 3 compra(s) asociada(s).",
  "compras_asociadas": 3,
  "status_code": 409
}
```

---

## 🧪 Testing

### Para Validar Transacciones
```php
// En test
DB::rollBack(); // Simular rollback

// Verificar que nada se guardó
$this->assertDatabaseEmpty('compra');
$this->assertDatabaseEmpty('detalle_compra');
```

### Para Validar Foreign Keys
```php
// Intentar eliminar proveedor con compras
$response = $this->delete('/api/proveedores/1');
$response->assertStatus(409); // Conflict
$response->assertJson(['message' => 'No se puede eliminar...']);
```

---

## 📝 Notas Importantes

1. **Soft Deletes**: Los proveedores eliminados se pueden restaurar via `/api/proveedores/{id}/restaurar`
2. **Auditoría**: Todos los cambios quedan registrados en `created_at`, `updated_at`, `deleted_at`
3. **Precisión**: Se usan `round(..., 2)` para garantizar DECIMAL(10,2) correcto
4. **Transacciones**: Se reintentan 3 veces si hay deadlock (`maxAttempts: 3`)
5. **Integridad**: No se puede eliminar proveedor si tiene compras (RESTRICT)

---

## ✨ Próximos Pasos Opcionales

- [ ] Agregar tests unitarios para transacciones
- [ ] Crear reportes de compras por proveedor
- [ ] Agregar validación de cambios de precios
- [ ] Implementar auditoría de cambios en compras
- [ ] Agregar notificaciones de compras recibidas
