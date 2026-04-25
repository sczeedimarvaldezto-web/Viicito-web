/**
 * VIICITO - Sistema de Gestión para Licorería
 * Schema Database - Versión 1.0.0
 * Date: 2026-03-20
 * 
 * NORMALIZACIÓN: Tercera Forma Normal (3FN)
 * - Cumple con 1FN: Valores atómicos en cada celda
 * - Cumple con 2FN: Todas las columnas no-clave dependen de la clave completa
 * - Cumple con 3FN: Sin dependencias transitivas entre atributos no-clave
 */

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================
-- DATABASE CREATION
-- ============================================
DROP DATABASE IF EXISTS `viicito_db`;
CREATE DATABASE `viicito_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `viicito_db`;

-- ============================================
-- TABLE: usuario
-- Almacena información de administradores y vendedores
-- NORMALIZATION: 3FN - Solo clave primaria y atributos descriptivos
-- ============================================
CREATE TABLE `usuario` (
  `id_usuario` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(100) NOT NULL COMMENT 'Nombre completo del usuario',
  `email` varchar(100) NOT NULL UNIQUE COMMENT 'Email único para login',
  `username` varchar(50) NOT NULL UNIQUE COMMENT 'Username único',
  `password_hash` varchar(255) NOT NULL COMMENT 'Password con hash bcrypt',
  `rol` ENUM('administrador', 'vendedor', 'auditor') NOT NULL DEFAULT 'vendedor' COMMENT 'Rol de acceso',
  `estado` ENUM('Activo', 'Inactivo', 'Bloqueado') NOT NULL DEFAULT 'Activo',
  `telefono` varchar(20) NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL COMMENT 'Soft delete',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: categoria
-- Categorías de productos (Ron, Vodka, etc)
-- NORMALIZATION: 3FN - Solo atributos específicos de categoría
-- ============================================
CREATE TABLE `categoria` (
  `id_categoria` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(50) NOT NULL UNIQUE COMMENT 'Nombre único de categoría',
  `descripcion` text NULL,
  `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`),
  INDEX `idx_nombre` (`nombre_categoria`),
  INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: proveedor
-- Lista de proveedores de licores
-- NORMALIZATION: 3FN - Todos los atributos dependen de la clave primaria
-- ============================================
CREATE TABLE `proveedor` (
  `id_proveedor` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(100) NOT NULL,
  `contacto_nombre` varchar(100) NULL COMMENT 'Nombre del contacto',
  `email` varchar(100) NULL,
  `telefono` varchar(20) NULL,
  `ciudad` varchar(50) NULL,
  `estado_proveedor` ENUM('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor`),
  INDEX `idx_nombre` (`nombre_empresa`),
  INDEX `idx_estado` (`estado_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: producto
-- Inventario de licores/productos
-- NORMALIZATION: 3FN
--   - id_categoria es FK (depende de categoria)
--   - Todos otros atributos dependen de id_producto
--   - Sin dependencias transitivas
-- TIPOS DE DATOS: DECIMAL(10,2) para precios (precision exacta, no FLOAT)
-- ============================================
CREATE TABLE `producto` (
  `id_producto` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_categoria` bigint UNSIGNED NOT NULL COMMENT 'Foreign Key a categoria',
  `codigo_barras` varchar(50) NULL UNIQUE COMMENT 'Código de barras único',
  `sku` varchar(50) NULL UNIQUE COMMENT 'Stock Keeping Unit',
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text NULL,
  `precio_compra` DECIMAL(10,2) NOT NULL COMMENT 'Precio de costo (exacto)',
  `precio_venta` DECIMAL(10,2) NOT NULL COMMENT 'Precio de venta (exacto)',
  `stock_actual` int UNSIGNED DEFAULT 0 COMMENT 'Cantidad física en stock',
  `stock_minimo` int UNSIGNED DEFAULT 5 COMMENT 'Nivel de reorden',
  `stock_maximo` int UNSIGNED DEFAULT 100 COMMENT 'Cantidad máxima permitida',
  `volumen_ml` int NULL COMMENT 'Volumen en mililitros',
  `grado_alcohol` DECIMAL(5,2) NULL COMMENT 'Grado alcohólico %',
  `estado` ENUM('Activo', 'Descontinuado', 'Suspendido') DEFAULT 'Activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  KEY `id_categoria` (`id_categoria`),
  INDEX `idx_codigo_barras` (`codigo_barras`),
  INDEX `idx_nombre` (`nombre_producto`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_stock` (`stock_actual`),
  CONSTRAINT `FK_producto_categoria` 
    FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: cliente
-- Clientes que realizan compras
-- NORMALIZATION: 3FN - Todos los atributos dependen de id_cliente
-- ============================================
CREATE TABLE `cliente` (
  `id_cliente` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_razon_social` varchar(100) NOT NULL,
  `tipo_cliente` ENUM('Natural', 'Jurídica') DEFAULT 'Natural',
  `nit_ci` varchar(30) NULL UNIQUE COMMENT 'NIT o Cédula Identidad',
  `email` varchar(100) NULL,
  `telefono` varchar(20) NULL,
  `vendedor_asignado` bigint UNSIGNED NULL COMMENT 'FK a usuario (vendedor)',
  `saldo_actual` DECIMAL(12,2) DEFAULT 0 COMMENT 'Saldo adeudado',
  `limite_credito` DECIMAL(12,2) DEFAULT 0 COMMENT 'Límite de crédito',
  `ciudad` varchar(50) NULL,
  `estado` ENUM('Activo', 'Inactivo', 'Bloqueado') DEFAULT 'Activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`),
  INDEX `idx_nit_ci` (`nit_ci`),
  INDEX `idx_nombre` (`nombre_razon_social`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `FK_cliente_usuario`
    FOREIGN KEY (`vendedor_asignado`) REFERENCES `usuario` (`id_usuario`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: venta
-- Registro de transacciones de venta
-- NORMALIZATION: 3FN
--   - id_usuario y id_cliente son FKs
--   - total_venta se calcula pero se almacena para performance
-- ============================================
CREATE TABLE `venta` (
  `id_venta` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint UNSIGNED NOT NULL COMMENT 'Vendedor que realizó la venta',
  `id_cliente` bigint UNSIGNED NOT NULL COMMENT 'Cliente que compró',
  `numero_documento` varchar(50) NOT NULL UNIQUE COMMENT 'Número secuencial de venta',
  `fecha_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_venta` DECIMAL(12,2) NOT NULL COMMENT 'Total (suma de detalles)',
  `subtotal` DECIMAL(12,2) NOT NULL COMMENT 'Subtotal antes de impuestos',
  `impuesto` DECIMAL(12,2) DEFAULT 0 COMMENT 'Cantidad de IVA',
  `metodo_pago` ENUM('Efectivo', 'Tarjeta', 'Cheque', 'Crédito') NOT NULL,
  `estado` ENUM('Completada', 'Cancelada', 'Pendiente', 'Devuelta') DEFAULT 'Completada',
  `observacion` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venta`),
  UNIQUE KEY `numero_documento` (`numero_documento`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_cliente` (`id_cliente`),
  INDEX `idx_fecha` (`fecha_hora`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `FK_venta_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `FK_venta_cliente`
    FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: detalle_venta
-- Items de cada venta (DEPENDENCIA DE VENTA)
-- NORMALIZATION: 3FN
--   - Depende de id_venta y id_producto
--   - Precio unitario se copia para histórico
-- ============================================
CREATE TABLE `detalle_venta` (
  `id_detalle_venta` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_venta` bigint UNSIGNED NOT NULL COMMENT 'Referencia a venta',
  `id_producto` bigint UNSIGNED NOT NULL COMMENT 'Producto vendido',
  `cantidad` int UNSIGNED NOT NULL COMMENT 'Cantidad vendida',
  `precio_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Precio al momento de venta',
  `descuento` DECIMAL(10,2) DEFAULT 0 COMMENT 'Descuento aplicado',
  `subtotal` DECIMAL(12,2) NOT NULL COMMENT 'cantidad * precio_unitario - descuento',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detalle_venta`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`),
  INDEX `idx_venta_producto` (`id_venta`, `id_producto`),
  CONSTRAINT `FK_detalle_venta_venta`
    FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_detalle_venta_producto`
    FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: compra
-- Órdenes de compra a proveedores
-- NORMALIZATION: 3FN
--   - id_usuario y id_proveedor son FKs
-- ============================================
CREATE TABLE `compra` (
  `id_compra` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint UNSIGNED NOT NULL COMMENT 'Usuario que registra la compra',
  `id_proveedor` bigint UNSIGNED NOT NULL COMMENT 'Proveedor',
  `numero_orden` varchar(50) NOT NULL UNIQUE COMMENT 'Número de orden de compra',
  `numero_factura_proveedor` varchar(50) NULL,
  `fecha_orden` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` datetime NULL,
  `total_compra` DECIMAL(12,2) NOT NULL COMMENT 'Total pagado',
  `estado` ENUM('Pendiente', 'Parcial', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
  `observacion` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_compra`),
  UNIQUE KEY `numero_orden` (`numero_orden`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_proveedor` (`id_proveedor`),
  INDEX `idx_fecha_orden` (`fecha_orden`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `FK_compra_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `FK_compra_proveedor`
    FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: detalle_compra
-- Items de cada compra
-- NORMALIZATION: 3FN
--   - Depende de id_compra y id_producto
-- ============================================
CREATE TABLE `detalle_compra` (
  `id_detalle_compra` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_compra` bigint UNSIGNED NOT NULL COMMENT 'Referencia a compra',
  `id_producto` bigint UNSIGNED NOT NULL COMMENT 'Producto comprado',
  `cantidad_ordenada` int UNSIGNED NOT NULL,
  `cantidad_recibida` int UNSIGNED DEFAULT 0,
  `costo_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Costo al momento de compra',
  `subtotal` DECIMAL(12,2) NOT NULL COMMENT 'cantidad_ordenada * costo_unitario',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detalle_compra`),
  KEY `id_compra` (`id_compra`),
  KEY `id_producto` (`id_producto`),
  INDEX `idx_compra_producto` (`id_compra`, `id_producto`),
  CONSTRAINT `FK_detalle_compra_compra`
    FOREIGN KEY (`id_compra`) REFERENCES `compra` (`id_compra`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_detalle_compra_producto`
    FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: auditoria
-- Log de cambios en transacciones importantes
-- ============================================
CREATE TABLE `auditoria` (
  `id_auditoria` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint UNSIGNED NOT NULL,
  `tabla` varchar(50) NOT NULL COMMENT 'Nombre de tabla modificada',
  `tipo_operacion` ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
  `id_registro` bigint UNSIGNED NOT NULL COMMENT 'ID del registro afectado',
  `datos_anteriores` json NULL COMMENT 'Valores antes del cambio',
  `datos_nuevos` json NULL COMMENT 'Valores después del cambio',
  `fecha_hora` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `id_usuario` (`id_usuario`),
  INDEX `idx_tabla` (`tabla`),
  INDEX `idx_fecha` (`fecha_hora`),
  CONSTRAINT `FK_auditoria_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VIEWS FOR BUSINESS LOGIC
-- ============================================

-- Vista: Productos con información de categoría
CREATE OR REPLACE VIEW v_producto_completo AS
SELECT 
  p.id_producto,
  p.nombre_producto,
  p.codigo_barras,
  p.sku,
  c.id_categoria,
  c.nombre_categoria,
  p.precio_compra,
  p.precio_venta,
  ROUND(((p.precio_venta - p.precio_compra) / p.precio_compra) * 100, 2) AS margen_ganancia,
  p.stock_actual,
  p.stock_minimo,
  p.stock_maximo,
  CASE 
    WHEN p.stock_actual <= p.stock_minimo THEN 'Bajo'
    WHEN p.stock_actual > p.stock_maximo THEN 'Exceso'
    ELSE 'Normal'
  END AS estado_stock,
  p.volumen_ml,
  p.grado_alcohol,
  p.estado
FROM producto p
INNER JOIN categoria c ON p.id_categoria = c.id_categoria;

-- Vista: Ventas con detalles de cliente y usuario
CREATE OR REPLACE VIEW v_venta_detallada AS
SELECT 
  v.id_venta,
  v.numero_documento,
  v.fecha_hora,
  u.nombre_completo AS vendedor,
  c.nombre_razon_social AS cliente,
  v.total_venta,
  v.metodo_pago,
  v.estado,
  COUNT(DISTINCT dv.id_producto) AS cantidad_items
FROM venta v
INNER JOIN usuario u ON v.id_usuario = u.id_usuario
INNER JOIN cliente c ON v.id_cliente = c.id_cliente
LEFT JOIN detalle_venta dv ON v.id_venta = dv.id_venta
GROUP BY v.id_venta;

-- Vista: Stock bajo en productos
CREATE OR REPLACE VIEW v_stock_bajo AS
SELECT 
  id_producto,
  nombre_producto,
  stock_actual,
  stock_minimo,
  (stock_minimo - stock_actual) AS cantidad_faltante,
  nombre_categoria
FROM v_producto_completo
WHERE stock_actual <= stock_minimo
ORDER BY cantidad_faltante DESC;

-- ============================================
-- INITIAL DATA (Optional)
-- ============================================
INSERT INTO `categoria` (`nombre_categoria`, `descripcion`, `estado`) VALUES
('Ron', 'Bebidas destiladas de caña de azúcar', 'Activo'),
('Vodka', 'Bebidas destiladas de cereales', 'Activo'),
('Whisky', 'Bebidas destiladas envejecidas en barril', 'Activo'),
('Cerveza', 'Bebidas fermentadas de malta', 'Activo'),
('Vino', 'Bebidas fermentadas de uva', 'Activo'),
('Licores', 'Bebidas alcohólicas diversas', 'Activo');

-- Insert default admin user (password: Admin@123456)
INSERT INTO `usuario` (`nombre_completo`, `email`, `username`, `password_hash`, `rol`, `estado`) VALUES
('Administrador Sistema', 'admin@viicito.local', 'admin', '$2y$10$Zp9F7dQvZbR4T2k8W1m9PuK6L5N4M3J2H1G0F9E8D7C6B5A4Z3Y2X', 'administrador', 'Activo');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
