-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para mega_uni_store
DROP DATABASE IF EXISTS `mega_uni_store`;
CREATE DATABASE IF NOT EXISTS `mega_uni_store` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `mega_uni_store`;

-- Volcando estructura para procedimiento mega_uni_store.agregar_columna_si_no_existe
DROP PROCEDURE IF EXISTS `agregar_columna_si_no_existe`;
DELIMITER //
CREATE PROCEDURE `agregar_columna_si_no_existe`(
    IN p_tabla   VARCHAR(64),
    IN p_columna VARCHAR(64),
    IN p_alter   TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = p_tabla
          AND COLUMN_NAME  = p_columna
    ) THEN
        SET @sql = p_alter;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('✅ Agregada: ', p_tabla, '.', p_columna) AS resultado;
    ELSE
        SELECT CONCAT('⏭️  Ya existe: ', p_tabla, '.', p_columna) AS resultado;
    END IF;
END//
DELIMITER ;

-- Volcando estructura para tabla mega_uni_store.aportes_seguridad_social
DROP TABLE IF EXISTS `aportes_seguridad_social`;
CREATE TABLE IF NOT EXISTS `aportes_seguridad_social` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomina_id` int NOT NULL,
  `empleado_id` int NOT NULL,
  `salud_empleado` decimal(10,2) DEFAULT NULL,
  `salud_empresa` decimal(10,2) DEFAULT NULL,
  `pension_empleado` decimal(10,2) DEFAULT NULL,
  `pension_empresa` decimal(10,2) DEFAULT NULL,
  `arl` decimal(10,2) DEFAULT NULL,
  `caja_compensacion` decimal(10,2) DEFAULT NULL,
  `icbf` decimal(10,2) DEFAULT NULL,
  `sena` decimal(10,2) DEFAULT NULL,
  `total_empleado` decimal(10,2) DEFAULT NULL,
  `total_empresa` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_aportes_nomina` (`nomina_id`),
  KEY `fk_aportes_empleado` (`empleado_id`),
  CONSTRAINT `fk_aportes_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `fk_aportes_nomina` FOREIGN KEY (`nomina_id`) REFERENCES `nominas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.aportes_seguridad_social: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.areas
DROP TABLE IF EXISTS `areas`;
CREATE TABLE IF NOT EXISTS `areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `responsable_id` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_area_tienda` (`tienda_id`),
  KEY `fk_area_responsable` (`responsable_id`),
  CONSTRAINT `fk_area_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_area_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.areas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.asientos_contables
DROP TABLE IF EXISTS `asientos_contables`;
CREATE TABLE IF NOT EXISTS `asientos_contables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `periodo_id` int NOT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` date NOT NULL,
  `concepto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo_origen` enum('manual','venta','compra','nomina','gasto','ajuste') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'manual',
  `origen_id` int DEFAULT NULL,
  `empleado_id` int DEFAULT NULL,
  `total_debito` decimal(14,2) DEFAULT '0.00',
  `total_credito` decimal(14,2) DEFAULT '0.00',
  `estado` enum('borrador','aprobado','anulado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'borrador',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `periodo_id` (`periodo_id`),
  CONSTRAINT `asientos_contables_ibfk_1` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.asientos_contables: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.asientos_detalle
DROP TABLE IF EXISTS `asientos_detalle`;
CREATE TABLE IF NOT EXISTS `asientos_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asiento_id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `debito` decimal(14,2) DEFAULT '0.00',
  `credito` decimal(14,2) DEFAULT '0.00',
  `centro_costo_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asiento_id` (`asiento_id`),
  KEY `cuenta_id` (`cuenta_id`),
  CONSTRAINT `asientos_detalle_ibfk_1` FOREIGN KEY (`asiento_id`) REFERENCES `asientos_contables` (`id`),
  CONSTRAINT `asientos_detalle_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`),
  CONSTRAINT `chk_partida` CHECK ((((`debito` > 0) and (`credito` = 0)) or ((`credito` > 0) and (`debito` = 0))))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.asientos_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.atributos
DROP TABLE IF EXISTS `atributos`;
CREATE TABLE IF NOT EXISTS `atributos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.atributos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.audit_log
DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `tienda_id` int DEFAULT NULL,
  `tabla` varchar(80) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT') NOT NULL,
  `registro_id` int DEFAULT NULL,
  `datos_antes` json DEFAULT NULL,
  `datos_despues` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tabla_accion` (`tabla`,`accion`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `fk_audit_tienda` (`tienda_id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_reg` (`tabla`,`registro_id`),
  CONSTRAINT `fk_audit_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.audit_log: ~19 rows (aproximadamente)
REPLACE INTO `audit_log` (`id`, `usuario_id`, `tienda_id`, `tabla`, `accion`, `registro_id`, `datos_antes`, `datos_despues`, `ip_address`, `created_at`) VALUES
	(3, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 10.00}', '{"cantidad": 15.00}', NULL, '2026-04-27 15:36:34'),
	(4, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 15.00}', '{"cantidad": 4.00}', NULL, '2026-04-27 15:38:08'),
	(5, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 4.00}', '{"cantidad": 14.00}', NULL, '2026-04-27 15:45:38'),
	(6, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 14.00}', '{"cantidad": 11.00}', NULL, '2026-04-27 15:46:12'),
	(7, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 11.00}', '{"cantidad": 13.00}', NULL, '2026-04-27 16:15:38'),
	(8, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 13.00}', '{"cantidad": 12.00}', NULL, '2026-04-27 16:32:16'),
	(9, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 12.00}', '{"cantidad": 12.00}', NULL, '2026-04-27 16:32:16'),
	(10, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 12.00}', '{"cantidad": 5.00}', NULL, '2026-04-27 16:39:10'),
	(11, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 5.00}', '{"cantidad": 5.00}', NULL, '2026-04-27 16:39:10'),
	(12, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 5.00}', '{"cantidad": 2.00}', NULL, '2026-04-29 12:50:57'),
	(13, NULL, 1, 'ventas', 'UPDATE', 3, '{"estado": "completada"}', '{"estado": "anulada"}', NULL, '2026-04-29 12:58:46'),
	(14, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 2.00}', '{"cantidad": 5.00}', NULL, '2026-04-29 12:58:46'),
	(15, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 5.00}', '{"cantidad": 4.00}', NULL, '2026-04-29 13:06:11'),
	(16, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 4.00}', '{"cantidad": 5.00}', NULL, '2026-04-29 13:16:30'),
	(17, NULL, 1, 'ventas', 'UPDATE', 4, '{"estado": "completada"}', '{"estado": "anulada"}', NULL, '2026-04-29 13:16:30'),
	(18, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 5.00}', '{"cantidad": 6.00}', NULL, '2026-04-29 13:16:30'),
	(19, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 6.00}', '{"cantidad": 1.00}', NULL, '2026-04-29 17:13:59'),
	(20, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 1.00}', '{"cantidad": 9.00}', NULL, '2026-04-29 17:41:39'),
	(21, NULL, NULL, 'inventario', 'UPDATE', 1, '{"cantidad": 9.00}', '{"cantidad": 5.00}', NULL, '2026-04-29 17:43:13');

-- Volcando estructura para tabla mega_uni_store.cajas
DROP TABLE IF EXISTS `cajas`;
CREATE TABLE IF NOT EXISTS `cajas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_caja_tienda` (`tienda_id`),
  CONSTRAINT `fk_caja_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.cajas: ~1 rows (aproximadamente)
REPLACE INTO `cajas` (`id`, `tienda_id`, `nombre`, `descripcion`, `estado`) VALUES
	(1, 1, 'Caja Principal', 'Caja principal del punto de venta.', 1);

-- Volcando estructura para tabla mega_uni_store.cajas_movimientos
DROP TABLE IF EXISTS `cajas_movimientos`;
CREATE TABLE IF NOT EXISTS `cajas_movimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `caja_id` int NOT NULL,
  `empleado_id` int DEFAULT NULL,
  `tipo` enum('apertura','cierre','ingreso','egreso','arqueo') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `monto_real` decimal(10,2) DEFAULT NULL COMMENT 'Monto físico en arqueo',
  `diferencia` decimal(10,2) DEFAULT NULL COMMENT 'Real - Sistema',
  `descripcion` varchar(200) DEFAULT NULL,
  `venta_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cm_caja` (`caja_id`),
  KEY `fk_cm_emp` (`empleado_id`),
  KEY `fk_cm_venta` (`venta_id`),
  CONSTRAINT `fk_cm_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_emp` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cm_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.cajas_movimientos: ~13 rows (aproximadamente)
REPLACE INTO `cajas_movimientos` (`id`, `caja_id`, `empleado_id`, `tipo`, `monto`, `monto_real`, `diferencia`, `descripcion`, `venta_id`, `created_at`) VALUES
	(1, 1, NULL, 'apertura', 50000.00, NULL, NULL, 'Apertura de turno inicial.', NULL, '2026-04-27 19:23:49'),
	(2, 1, NULL, 'cierre', 50000.00, 50000.00, 0.00, 'Cierre de caja', NULL, '2026-04-27 19:26:06'),
	(3, 1, NULL, 'apertura', 0.00, NULL, NULL, 'Apertura de caja', NULL, '2026-04-27 19:26:54'),
	(4, 1, NULL, 'ingreso', 10000.00, NULL, NULL, 'Ingreso manual de prueba.', NULL, '2026-04-27 19:27:52'),
	(5, 1, NULL, 'cierre', 10000.00, 10000.00, 0.00, 'Cierre de caja', NULL, '2026-04-27 19:28:18'),
	(6, 1, NULL, 'apertura', 0.00, NULL, NULL, 'Apertura de caja', NULL, '2026-04-29 12:43:27'),
	(7, 1, NULL, 'cierre', 0.00, 0.00, 0.00, 'Cierre de caja', NULL, '2026-04-29 12:48:06'),
	(8, 1, NULL, 'apertura', 0.00, NULL, NULL, 'Apertura de caja', NULL, '2026-04-29 12:49:24'),
	(9, 1, NULL, 'ingreso', 12495.00, NULL, NULL, 'Ingreso por venta #3', 3, '2026-04-29 12:50:57'),
	(10, 1, NULL, 'egreso', 12495.00, NULL, NULL, 'Egreso por anulación de venta #3', 3, '2026-04-29 12:58:46'),
	(11, 1, NULL, 'ingreso', 4165.00, NULL, NULL, 'Ingreso por venta #4', 4, '2026-04-29 13:06:11'),
	(12, 1, NULL, 'egreso', 4165.00, NULL, NULL, 'Egreso por anulación de venta #4', 4, '2026-04-29 13:16:30'),
	(13, 1, NULL, 'ingreso', 20825.00, NULL, NULL, 'Ingreso por venta #5', 5, '2026-04-29 17:13:59');

-- Volcando estructura para tabla mega_uni_store.cargos
DROP TABLE IF EXISTS `cargos`;
CREATE TABLE IF NOT EXISTS `cargos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `nivel_jerarquico` int NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_cargo_tienda` (`tienda_id`),
  CONSTRAINT `fk_cargo_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.cargos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `categoria_padre_id` int DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cat_padre` (`categoria_padre_id`),
  KEY `idx_cat_deleted` (`deleted_at`),
  CONSTRAINT `fk_cat_padre` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.categorias: ~3 rows (aproximadamente)
REPLACE INTO `categorias` (`id`, `nombre`, `descripcion`, `categoria_padre_id`, `imagen_url`, `activo`, `deleted_at`) VALUES
	(1, 'Bebidas', 'Productos líquidos para consumo.', NULL, NULL, 1, NULL),
	(2, 'Gaseosas y refresco', 'Bebidas gaseosas, refrescos y similares.', 1, NULL, 1, NULL),
	(3, 'Categoría para eliminar.', 'Categoría para eliminar.', NULL, NULL, 0, '2026-04-27 01:28:07');

-- Volcando estructura para tabla mega_uni_store.centros_costo
DROP TABLE IF EXISTS `centros_costo`;
CREATE TABLE IF NOT EXISTS `centros_costo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `responsable_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `tienda_id` (`tienda_id`),
  CONSTRAINT `centros_costo_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.centros_costo: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.clientes
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `numero_documento` varchar(30) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_cli_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.clientes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.compras
DROP TABLE IF EXISTS `compras`;
CREATE TABLE IF NOT EXISTS `compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `empleado_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','recibida','cancelada') NOT NULL DEFAULT 'pendiente',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_comp_proveedor` (`proveedor_id`),
  KEY `fk_comp_empleado` (`empleado_id`),
  KEY `idx_compra_deleted` (`deleted_at`),
  KEY `fk_comp_updated_by` (`updated_by`),
  KEY `idx_comp_tienda_fecha` (`tienda_id`,`fecha`),
  CONSTRAINT `fk_comp_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_comp_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `fk_comp_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`),
  CONSTRAINT `fk_comp_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.compras: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.compras_detalle
DROP TABLE IF EXISTS `compras_detalle`;
CREATE TABLE IF NOT EXISTS `compras_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `compra_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cd_compra` (`compra_id`),
  KEY `fk_cd_producto` (`producto_id`),
  CONSTRAINT `fk_cd_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cd_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.compras_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.conceptos_nomina
DROP TABLE IF EXISTS `conceptos_nomina`;
CREATE TABLE IF NOT EXISTS `conceptos_nomina` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('devengado','deduccion') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `subtipo` enum('fijo','variable','porcentaje') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'fijo',
  `porcentaje` decimal(6,4) DEFAULT NULL,
  `aplica_base` tinyint(1) DEFAULT '0',
  `obligatorio` tinyint(1) DEFAULT '1',
  `cuenta_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_conceptos_tienda` (`tienda_id`),
  CONSTRAINT `fk_conceptos_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.conceptos_nomina: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.conciliaciones
DROP TABLE IF EXISTS `conciliaciones`;
CREATE TABLE IF NOT EXISTS `conciliaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `fecha` date NOT NULL,
  `saldo_banco` decimal(14,2) NOT NULL,
  `saldo_sistema` decimal(14,2) NOT NULL,
  `diferencia` decimal(14,2) GENERATED ALWAYS AS ((`saldo_banco` - `saldo_sistema`)) STORED,
  `estado` enum('pendiente','conciliado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  `empleado_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tienda_id` (`tienda_id`),
  KEY `cuenta_id` (`cuenta_id`),
  CONSTRAINT `conciliaciones_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`),
  CONSTRAINT `conciliaciones_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.conciliaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.contratos
DROP TABLE IF EXISTS `contratos`;
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `tipo_contrato` enum('indefinido','fijo','obra_labor','aprendizaje','prestacion') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `salario_base` decimal(12,2) NOT NULL,
  `cargo_id` int NOT NULL,
  `jornada` enum('completa','media','flexible') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'completa',
  `estado` enum('activo','terminado','suspendido') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `eps_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `afp_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `arl_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_contratos_empleado` (`empleado_id`),
  KEY `fk_contratos_cargo` (`cargo_id`),
  CONSTRAINT `fk_contratos_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`),
  CONSTRAINT `fk_contratos_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.contratos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.cuentas_contables
DROP TABLE IF EXISTS `cuentas_contables`;
CREATE TABLE IF NOT EXISTS `cuentas_contables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('activo','pasivo','patrimonio','ingreso','egreso','costo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `naturaleza` enum('debito','credito') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cuenta_padre_id` int DEFAULT NULL,
  `nivel` tinyint DEFAULT '1',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tienda_id` (`tienda_id`,`codigo`),
  KEY `cuenta_padre_id` (`cuenta_padre_id`),
  CONSTRAINT `cuentas_contables_ibfk_1` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `cuentas_contables` (`id`),
  CONSTRAINT `cuentas_contables_ibfk_2` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.cuentas_contables: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.cupones
DROP TABLE IF EXISTS `cupones`;
CREATE TABLE IF NOT EXISTS `cupones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` text,
  `tipo_descuento` enum('porcentaje','fijo') NOT NULL DEFAULT 'porcentaje',
  `valor_descuento` decimal(10,2) NOT NULL,
  `descuento_maximo` decimal(10,2) DEFAULT NULL COMMENT 'Tope máximo de descuento (solo tipo porcentaje)',
  `monto_minimo` decimal(10,2) DEFAULT NULL COMMENT 'Compra mínima para aplicar el cupón',
  `usos_maximos` int unsigned DEFAULT NULL,
  `usos_actuales` int NOT NULL DEFAULT '0',
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int unsigned DEFAULT NULL,
  `updated_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cupon_tienda` (`tienda_id`,`codigo`),
  KEY `idx_cupon_deleted` (`deleted_at`),
  KEY `idx_cupon_fechas` (`fecha_inicio`,`fecha_fin`,`activo`),
  CONSTRAINT `fk_cupon_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.cupones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.devoluciones
DROP TABLE IF EXISTS `devoluciones`;
CREATE TABLE IF NOT EXISTS `devoluciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `tienda_id` int unsigned NOT NULL,
  `empleado_id` int DEFAULT NULL,
  `motivo` text NOT NULL,
  `monto_devuelto` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto total devuelto al cliente',
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` enum('pendiente','aprobada','rechazada','completada') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dev_venta` (`venta_id`),
  KEY `fk_dev_empleado` (`empleado_id`),
  KEY `fk_dev_updated_by` (`updated_by`),
  KEY `idx_dev_tienda` (`tienda_id`),
  KEY `idx_dev_deleted` (`deleted_at`),
  CONSTRAINT `fk_dev_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dev_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dev_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.devoluciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.devoluciones_detalle
DROP TABLE IF EXISTS `devoluciones_detalle`;
CREATE TABLE IF NOT EXISTS `devoluciones_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `devolucion_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_dd_devolucion` (`devolucion_id`),
  KEY `fk_dd_producto` (`producto_id`),
  CONSTRAINT `fk_dd_devolucion` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dd_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.devoluciones_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.empleados
DROP TABLE IF EXISTS `empleados`;
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tienda_id` int NOT NULL,
  `codigo_empleado` varchar(20) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_codigo_tienda` (`tienda_id`,`codigo_empleado`),
  KEY `idx_emp_deleted` (`deleted_at`),
  KEY `idx_emp_tienda` (`tienda_id`),
  KEY `idx_emp_usuario` (`usuario_id`),
  CONSTRAINT `fk_emp_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_emp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.empleados: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.empleados_areas
DROP TABLE IF EXISTS `empleados_areas`;
CREATE TABLE IF NOT EXISTS `empleados_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `area_id` int NOT NULL,
  `fecha_asignacion` date DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_area` (`empleado_id`,`area_id`),
  KEY `fk_ea_area` (`area_id`),
  CONSTRAINT `fk_ea_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ea_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.empleados_areas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.empleados_cargos
DROP TABLE IF EXISTS `empleados_cargos`;
CREATE TABLE IF NOT EXISTS `empleados_cargos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `cargo_id` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_ec_empleado` (`empleado_id`),
  KEY `fk_ec_cargo` (`cargo_id`),
  CONSTRAINT `fk_ec_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ec_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.empleados_cargos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.empleados_horarios
DROP TABLE IF EXISTS `empleados_horarios`;
CREATE TABLE IF NOT EXISTS `empleados_horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `horario_id` int NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_eh_empleado` (`empleado_id`),
  KEY `fk_eh_horario` (`horario_id`),
  CONSTRAINT `fk_eh_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_eh_horario` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.empleados_horarios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.envios_reporte
DROP TABLE IF EXISTS `envios_reporte`;
CREATE TABLE IF NOT EXISTS `envios_reporte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exportacion_id` int NOT NULL,
  `canal` enum('email','whatsapp') NOT NULL,
  `destinatario` varchar(200) NOT NULL,
  `estado` enum('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
  `mensaje` text,
  `enviado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_er_exportacion` (`exportacion_id`),
  CONSTRAINT `fk_er_exportacion` FOREIGN KEY (`exportacion_id`) REFERENCES `exportaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.envios_reporte: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.exportaciones
DROP TABLE IF EXISTS `exportaciones`;
CREATE TABLE IF NOT EXISTS `exportaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reporte_id` int NOT NULL,
  `formato` enum('pdf','xlsx','csv') NOT NULL,
  `archivo_url` varchar(255) DEFAULT NULL,
  `tamano_bytes` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_exp_reporte` (`reporte_id`),
  CONSTRAINT `fk_exp_reporte` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.exportaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.failed_jobs: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.gastos
DROP TABLE IF EXISTS `gastos`;
CREATE TABLE IF NOT EXISTS `gastos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `centro_costo_id` int DEFAULT NULL,
  `concepto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `fecha` date NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `comprobante` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` enum('pendiente','pagado','anulado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  `empleado_id` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tienda_id` (`tienda_id`),
  KEY `cuenta_id` (`cuenta_id`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`),
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.gastos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.horarios
DROP TABLE IF EXISTS `horarios`;
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_hor_tienda` (`tienda_id`),
  CONSTRAINT `fk_hor_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.horarios: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.horas_extra
DROP TABLE IF EXISTS `horas_extra`;
CREATE TABLE IF NOT EXISTS `horas_extra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('diurna','nocturna','festiva','nocturna_festiva') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `horas` decimal(4,2) NOT NULL,
  `valor_hora` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `aprobado_por` int DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `fk_horas_extra_empleado` (`empleado_id`),
  CONSTRAINT `fk_horas_extra_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.horas_extra: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.impuestos
DROP TABLE IF EXISTS `impuestos`;
CREATE TABLE IF NOT EXISTS `impuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tipo` varchar(50) NOT NULL DEFAULT 'porcentaje',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.impuestos: ~3 rows (aproximadamente)
REPLACE INTO `impuestos` (`id`, `nombre`, `descripcion`, `porcentaje`, `tipo`, `activo`) VALUES
	(1, 'IVA 19%', 'Impuesto al valor agregado general.', 19.00, 'Ventas', 1),
	(2, 'IVA 5%', 'Tarifa reducida aplicable a productos específicos.', 5.00, 'Ventas', 0),
	(3, 'Impoconsumo 8%', 'Impuesto nacional al consumo.', 8.00, 'Consumo', 0);

-- Volcando estructura para tabla mega_uni_store.inventario
DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_minima` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_maxima` decimal(10,2) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inv_tienda_prod` (`tienda_id`,`producto_id`),
  KEY `fk_inv_producto` (`producto_id`),
  KEY `idx_inv_stock_alerta` (`tienda_id`,`cantidad`,`cantidad_minima`),
  CONSTRAINT `fk_inv_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.inventario: ~1 rows (aproximadamente)
REPLACE INTO `inventario` (`id`, `tienda_id`, `producto_id`, `cantidad`, `cantidad_minima`, `cantidad_maxima`, `ubicacion`, `updated_at`) VALUES
	(1, 1, 1, 5.00, 3.00, 50.00, 'Bodega A - Estante 1', '2026-04-29 17:43:13');

-- Volcando estructura para tabla mega_uni_store.metodos_pago
DROP TABLE IF EXISTS `metodos_pago`;
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.metodos_pago: ~4 rows (aproximadamente)
REPLACE INTO `metodos_pago` (`id`, `nombre`, `descripcion`, `activo`) VALUES
	(1, 'Efectivo', 'Pago en efectivo en punto de venta.', 1),
	(2, 'Transferencia', 'Pago por transferencia bancaria.', 1),
	(3, 'Tarjeta débito', 'Pago con tarjeta débito.', 1),
	(4, 'Tarjeta crédito', 'Pago con tarjeta crédito.', 1);

-- Volcando estructura para tabla mega_uni_store.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.migrations: ~4 rows (aproximadamente)
REPLACE INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- Volcando estructura para tabla mega_uni_store.movimientos_inventario
DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inventario_id` int NOT NULL,
  `tipo` enum('entrada','salida','ajuste') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `empleado_id` int DEFAULT NULL,
  `ref_id` int DEFAULT NULL COMMENT 'ID venta / compra / devolucion',
  `ref_tipo` varchar(50) DEFAULT NULL COMMENT 'ventas | compras | devoluciones',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mi_inventario` (`inventario_id`),
  KEY `idx_mi_fecha` (`created_at`),
  KEY `idx_mi_ref` (`ref_tipo`,`ref_id`),
  KEY `idx_mi_empleado` (`empleado_id`),
  CONSTRAINT `fk_mi_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mi_inventario` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.movimientos_inventario: ~15 rows (aproximadamente)
REPLACE INTO `movimientos_inventario` (`id`, `inventario_id`, `tipo`, `cantidad`, `motivo`, `empleado_id`, `ref_id`, `ref_tipo`, `created_at`) VALUES
	(1, 1, 'entrada', 5.00, 'Compra inicial a proveedor', NULL, NULL, NULL, '2026-04-27 15:36:34'),
	(2, 1, 'ajuste', 4.00, 'Conteo físico', NULL, NULL, NULL, '2026-04-27 15:38:08'),
	(3, 1, 'entrada', 10.00, 'Reposición para prueba de salida', NULL, NULL, NULL, '2026-04-27 15:45:38'),
	(4, 1, 'salida', 3.00, 'prueba de resta', NULL, NULL, NULL, '2026-04-27 15:46:12'),
	(5, 1, 'entrada', 2.00, 'Reposición realizada por bodeguero', NULL, NULL, NULL, '2026-04-27 16:15:38'),
	(6, 1, 'salida', 1.00, 'Venta #1', NULL, 1, 'ventas', '2026-04-27 16:32:16'),
	(7, 1, 'salida', 1.00, 'Venta #1', NULL, 1, 'ventas', '2026-04-27 16:32:16'),
	(8, 1, 'salida', 7.00, 'Venta #2', NULL, 2, 'ventas', '2026-04-27 16:39:10'),
	(9, 1, 'salida', 7.00, 'Venta #2', NULL, 2, 'ventas', '2026-04-27 16:39:10'),
	(10, 1, 'salida', 3.00, 'Venta #3', NULL, 3, 'ventas', '2026-04-29 12:50:57'),
	(11, 1, 'entrada', 3.00, 'Reversión venta anulada #3', NULL, 3, 'ventas_anuladas', '2026-04-29 12:58:46'),
	(12, 1, 'salida', 1.00, 'Venta #4', NULL, 4, 'ventas', '2026-04-29 13:06:11'),
	(13, 1, 'entrada', 1.00, 'Anulación de venta #4', NULL, 4, 'ventas', '2026-04-29 13:16:30'),
	(14, 1, 'entrada', 1.00, 'Reversión venta anulada #4', NULL, 4, 'ventas_anuladas', '2026-04-29 13:16:30'),
	(15, 1, 'salida', 5.00, 'Venta #5', NULL, 5, 'ventas', '2026-04-29 17:13:59');

-- Volcando estructura para tabla mega_uni_store.nominas
DROP TABLE IF EXISTS `nominas`;
CREATE TABLE IF NOT EXISTS `nominas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `tipo` enum('quincenal','mensual','bisemanal') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'mensual',
  `estado` enum('borrador','calculada','aprobada','pagada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'borrador',
  `total_devengado` decimal(14,2) DEFAULT '0.00',
  `total_deducciones` decimal(14,2) DEFAULT '0.00',
  `total_neto` decimal(14,2) DEFAULT '0.00',
  `aprobado_por` int DEFAULT NULL,
  `aprobado_at` timestamp NULL DEFAULT NULL,
  `pagado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_nominas_tienda` (`tienda_id`),
  CONSTRAINT `fk_nominas_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.nominas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.nomina_detalle
DROP TABLE IF EXISTS `nomina_detalle`;
CREATE TABLE IF NOT EXISTS `nomina_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomina_emp_id` int NOT NULL,
  `concepto_id` int NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `tipo` enum('devengado','deduccion') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detalle_nomina_emp` (`nomina_emp_id`),
  KEY `fk_detalle_concepto` (`concepto_id`),
  CONSTRAINT `fk_detalle_concepto` FOREIGN KEY (`concepto_id`) REFERENCES `conceptos_nomina` (`id`),
  CONSTRAINT `fk_detalle_nomina_emp` FOREIGN KEY (`nomina_emp_id`) REFERENCES `nomina_empleado` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.nomina_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.nomina_empleado
DROP TABLE IF EXISTS `nomina_empleado`;
CREATE TABLE IF NOT EXISTS `nomina_empleado` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomina_id` int NOT NULL,
  `empleado_id` int NOT NULL,
  `contrato_id` int NOT NULL,
  `dias_trabajados` decimal(5,2) DEFAULT '30.00',
  `horas_extra` decimal(6,2) DEFAULT '0.00',
  `ausencias` decimal(5,2) DEFAULT '0.00',
  `total_devengado` decimal(12,2) DEFAULT '0.00',
  `total_deducciones` decimal(12,2) DEFAULT '0.00',
  `neto_pagar` decimal(12,2) DEFAULT '0.00',
  `estado` enum('pendiente','pagado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nomina_empleado` (`nomina_id`,`empleado_id`),
  KEY `fk_nomina_emp_empleado` (`empleado_id`),
  KEY `fk_nomina_emp_contrato` (`contrato_id`),
  CONSTRAINT `fk_nomina_emp_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  CONSTRAINT `fk_nomina_emp_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  CONSTRAINT `fk_nomina_emp_nomina` FOREIGN KEY (`nomina_id`) REFERENCES `nominas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.nomina_empleado: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.notificaciones
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'info',
  `leida` tinyint(1) NOT NULL DEFAULT '0',
  `url_accion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_leida` (`usuario_id`,`leida`),
  KEY `fk_notif_tienda` (`tienda_id`),
  KEY `idx_notif_created` (`created_at`),
  CONSTRAINT `fk_notif_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.notificaciones: ~4 rows (aproximadamente)
REPLACE INTO `notificaciones` (`id`, `tienda_id`, `usuario_id`, `titulo`, `mensaje`, `tipo`, `leida`, `url_accion`, `created_at`) VALUES
	(1, 1, 1, 'Stock bajo', '"Coca Cola 400mllll" bajo mínimo. Cantidad: 4.00', 'warning', 0, NULL, '2026-04-27 15:38:08'),
	(2, 1, 1, 'Stock bajo', '"Coca Cola 400mllll" bajo mínimo. Cantidad: 2.00', 'warning', 0, NULL, '2026-04-29 12:50:57'),
	(3, 1, 1, 'Stock bajo', '"Coca Cola 400mllll" bajo mínimo. Cantidad: 4.00', 'warning', 0, NULL, '2026-04-29 13:06:11'),
	(4, 1, 1, 'Stock bajo', '"Coca Cola 400mllll" bajo mínimo. Cantidad: 1.00', 'warning', 0, NULL, '2026-04-29 17:13:59');

-- Volcando estructura para tabla mega_uni_store.pagos
DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `metodo_pago_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `estado` enum('aprobado','rechazado','pendiente') NOT NULL DEFAULT 'aprobado',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pago_metodo` (`metodo_pago_id`),
  KEY `idx_pago_venta` (`venta_id`),
  KEY `idx_pago_estado` (`estado`),
  CONSTRAINT `fk_pago_metodo` FOREIGN KEY (`metodo_pago_id`) REFERENCES `metodos_pago` (`id`),
  CONSTRAINT `fk_pago_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.pagos: ~5 rows (aproximadamente)
REPLACE INTO `pagos` (`id`, `venta_id`, `metodo_pago_id`, `monto`, `referencia`, `estado`, `created_at`) VALUES
	(1, 1, 1, 4165.00, 'Venta prueba 001', 'aprobado', '2026-04-27 16:32:16'),
	(2, 2, 3, 29155.00, 'Venta prueba 002', 'aprobado', '2026-04-27 16:39:10'),
	(3, 3, 1, 12495.00, 'Prueba venta con caja abierta', 'rechazado', '2026-04-29 12:50:57'),
	(4, 4, 1, 4165.00, 'Prueba anulación inventario', 'rechazado', '2026-04-29 13:06:11'),
	(5, 5, 4, 20825.00, 'osman', 'aprobado', '2026-04-29 17:13:59');

-- Volcando estructura para tabla mega_uni_store.password_resets
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.password_resets: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.password_reset_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.periodos_contables
DROP TABLE IF EXISTS `periodos_contables`;
CREATE TABLE IF NOT EXISTS `periodos_contables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('abierto','cerrado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'abierto',
  `cerrado_por` int DEFAULT NULL,
  `cerrado_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tienda_id` (`tienda_id`),
  CONSTRAINT `periodos_contables_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.periodos_contables: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.permisos
DROP TABLE IF EXISTS `permisos`;
CREATE TABLE IF NOT EXISTS `permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `modulo` varchar(80) NOT NULL,
  `accion` varchar(80) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.permisos: ~37 rows (aproximadamente)
REPLACE INTO `permisos` (`id`, `nombre`, `modulo`, `accion`, `descripcion`) VALUES
	(1, 'Ver dashboard', 'dashboard', 'dashboard.view', 'Permite acceder al panel principal del sistema.'),
	(2, 'Ver tiendas', 'tiendas', 'tiendas.view', 'Permite consultar tiendas registradas.'),
	(3, 'Crear tiendas', 'tiendas', 'tiendas.create', 'Permite crear nuevas tiendas.'),
	(4, 'Editar tiendas', 'tiendas', 'tiendas.update', 'Permite actualizar información de tiendas.'),
	(5, 'Cambiar estado de tiendas', 'tiendas', 'tiendas.toggle', 'Permite activar o desactivar tiendas.'),
	(6, 'Eliminar tiendas', 'tiendas', 'tiendas.delete', 'Permite eliminar tiendas de forma lógica.'),
	(7, 'Ver usuarios', 'usuarios', 'usuarios.view', 'Permite consultar usuarios registrados.'),
	(8, 'Crear usuarios', 'usuarios', 'usuarios.create', 'Permite crear usuarios administrativos.'),
	(9, 'Editar usuarios', 'usuarios', 'usuarios.update', 'Permite actualizar información de usuarios.'),
	(10, 'Asignar roles a usuarios', 'usuarios', 'usuarios.roles.assign', 'Permite asignar roles globales o por tienda.'),
	(11, 'Cambiar estado de usuarios', 'usuarios', 'usuarios.toggle', 'Permite activar o desactivar usuarios.'),
	(12, 'Eliminar usuarios', 'usuarios', 'usuarios.delete', 'Permite eliminar usuarios de forma lógica.'),
	(13, 'Ver productos', 'productos', 'productos.view', 'Permite consultar productos.'),
	(14, 'Crear productos', 'productos', 'productos.create', 'Permite registrar productos.'),
	(15, 'Editar productos', 'productos', 'productos.update', 'Permite actualizar productos.'),
	(16, 'Eliminar productos', 'productos', 'productos.delete', 'Permite eliminar productos de forma lógica.'),
	(17, 'Ver inventario', 'inventario', 'inventario.view', 'Permite consultar existencias de inventario.'),
	(18, 'Mover inventario', 'inventario', 'inventario.move', 'Permite registrar entradas, salidas y ajustes de inventario.'),
	(19, 'Ver alertas de stock', 'inventario', 'inventario.alerts', 'Permite consultar alertas de stock mínimo.'),
	(20, 'Ver ventas', 'ventas', 'ventas.view', 'Permite consultar ventas.'),
	(21, 'Crear ventas', 'ventas', 'ventas.create', 'Permite registrar ventas.'),
	(22, 'Anular ventas', 'ventas', 'ventas.cancel', 'Permite anular ventas bajo reglas del sistema.'),
	(23, 'Ver caja', 'caja', 'caja.view', 'Permite consultar caja y movimientos.'),
	(24, 'Gestionar caja', 'caja', 'caja.manage', 'Permite abrir, cerrar y registrar movimientos de caja.'),
	(25, 'Ver reportes', 'reportes', 'reportes.view', 'Permite consultar reportes.'),
	(26, 'Exportar reportes', 'reportes', 'reportes.export', 'Permite exportar reportes en PDF, Excel o CSV.'),
	(27, 'Ver empleados', 'rrhh', 'empleados.view', 'Permite consultar empleados.'),
	(28, 'Gestionar empleados', 'rrhh', 'empleados.manage', 'Permite crear y actualizar empleados.'),
	(29, 'Ver nómina', 'nomina', 'nomina.view', 'Permite consultar nómina.'),
	(30, 'Gestionar nómina', 'nomina', 'nomina.manage', 'Permite procesar y administrar nómina.'),
	(31, 'Ver catálogo cliente', 'catalogo', 'catalogo.view', 'Permite consultar catálogo público o de cliente.'),
	(32, 'Gestionar pedidos propios', 'pedidos', 'pedidos.own.manage', 'Permite al cliente gestionar sus propios pedidos.'),
	(33, 'Gestionar perfil propio', 'perfil', 'perfil.own.manage', 'Permite al usuario gestionar su propio perfil.'),
	(34, 'Calificar productos', 'feedback', 'feedback.create', 'Permite crear reseñas o calificaciones.'),
	(35, 'Ver auditoría', 'auditoria', 'auditoria.view', 'Permite consultar trazabilidad y logs del sistema.'),
	(36, 'Gestionar notificaciones', 'notificaciones', 'notificaciones.manage', 'Permite gestionar alertas y notificaciones internas.'),
	(37, 'Gestionar respaldos', 'sistema', 'backups.manage', 'Permite gestionar respaldos y tareas automáticas.');

-- Volcando estructura para tabla mega_uni_store.personal_access_tokens
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.personal_access_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.planes
DROP TABLE IF EXISTS `planes`;
CREATE TABLE IF NOT EXISTS `planes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text,
  `precio_mes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_tiendas` int NOT NULL DEFAULT '1',
  `max_productos` int NOT NULL DEFAULT '100',
  `max_empleados` int NOT NULL DEFAULT '10',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.planes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.plataforma
DROP TABLE IF EXISTS `plataforma`;
CREATE TABLE IF NOT EXISTS `plataforma` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `config_json` json DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.plataforma: ~1 rows (aproximadamente)
REPLACE INTO `plataforma` (`id`, `nombre`, `logo_url`, `config_json`, `estado`, `created_at`) VALUES
	(1, 'MultiStore Platform', NULL, NULL, 1, '2026-03-16 13:58:23');

-- Volcando estructura para tabla mega_uni_store.prestaciones_sociales
DROP TABLE IF EXISTS `prestaciones_sociales`;
CREATE TABLE IF NOT EXISTS `prestaciones_sociales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `contrato_id` int NOT NULL,
  `tipo` enum('cesantias','intereses_cesantias','prima','vacaciones_dinero') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `base_calculo` decimal(12,2) DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `estado` enum('pendiente','pagado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `fk_prestaciones_empleado` (`empleado_id`),
  KEY `fk_prestaciones_contrato` (`contrato_id`),
  CONSTRAINT `fk_prestaciones_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  CONSTRAINT `fk_prestaciones_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.prestaciones_sociales: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.presupuestos
DROP TABLE IF EXISTS `presupuestos`;
CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `periodo_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` enum('borrador','aprobado','cerrado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'borrador',
  `aprobado_por` int DEFAULT NULL,
  `aprobado_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tienda_id` (`tienda_id`),
  KEY `periodo_id` (`periodo_id`),
  CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`),
  CONSTRAINT `presupuestos_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.presupuestos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.presupuesto_detalle
DROP TABLE IF EXISTS `presupuesto_detalle`;
CREATE TABLE IF NOT EXISTS `presupuesto_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `presupuesto_id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `centro_costo_id` int DEFAULT NULL,
  `monto_proyectado` decimal(14,2) NOT NULL,
  `monto_ejecutado` decimal(14,2) DEFAULT '0.00',
  `mes` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `presupuesto_id` (`presupuesto_id`),
  KEY `cuenta_id` (`cuenta_id`),
  CONSTRAINT `presupuesto_detalle_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`),
  CONSTRAINT `presupuesto_detalle_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.presupuesto_detalle: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.productos
DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `categoria_id` int DEFAULT NULL,
  `unidad_medida_id` int DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  KEY `fk_prod_unidad` (`unidad_medida_id`),
  KEY `idx_prod_deleted` (`deleted_at`),
  KEY `fk_prod_created_by` (`created_by`),
  KEY `fk_prod_updated_by` (`updated_by`),
  KEY `idx_prod_categoria` (`categoria_id`),
  KEY `idx_prod_codigo` (`codigo_barras`),
  CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prod_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prod_unidad` FOREIGN KEY (`unidad_medida_id`) REFERENCES `unidades_medida` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prod_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.productos: ~5 rows (aproximadamente)
REPLACE INTO `productos` (`id`, `nombre`, `descripcion`, `codigo_barras`, `imagen_url`, `categoria_id`, `unidad_medida_id`, `estado`, `deleted_at`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
	(1, 'Coca Cola 400mllll', 'Bebida gaseosa personal.', '7701234567890', NULL, 2, 1, 1, NULL, '2026-04-27 05:22:18', '2026-04-29 17:37:06', 1, 1),
	(2, 'Carne', 'Carne deliciosa', '123456789', NULL, NULL, 2, 1, NULL, '2026-05-08 18:55:21', NULL, 1, 1),
	(3, 'cabeza extragrande de hierro frente en alto', 'mil metros cuadrados', '3636353', NULL, NULL, 4, 1, NULL, '2026-05-09 01:49:10', NULL, 1, 1),
	(4, 'cabeza extragrande de hierro frente en alto', NULL, '36363535', NULL, 1, 2, 1, NULL, '2026-05-13 13:49:14', NULL, 1, 1),
	(5, 'HELGORDO', 'HELADO PARA GORDOS LLENOS DE GRASA COMO BREYNER CAGANZA', NULL, NULL, NULL, NULL, 1, NULL, '2026-05-20 14:45:11', NULL, 1, 1);

-- Volcando estructura para tabla mega_uni_store.productos_atributos
DROP TABLE IF EXISTS `productos_atributos`;
CREATE TABLE IF NOT EXISTS `productos_atributos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `atributo_id` int NOT NULL,
  `valor` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pa_producto` (`producto_id`),
  KEY `fk_pa_atributo` (`atributo_id`),
  CONSTRAINT `fk_pa_atributo` FOREIGN KEY (`atributo_id`) REFERENCES `atributos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pa_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.productos_atributos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.productos_impuestos
DROP TABLE IF EXISTS `productos_impuestos`;
CREATE TABLE IF NOT EXISTS `productos_impuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `impuesto_id` int NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prod_imp` (`producto_id`,`impuesto_id`),
  KEY `fk_pi_impuesto` (`impuesto_id`),
  CONSTRAINT `fk_pi_impuesto` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.productos_impuestos: ~5 rows (aproximadamente)
REPLACE INTO `productos_impuestos` (`id`, `producto_id`, `impuesto_id`, `activo`) VALUES
	(1, 1, 1, 1),
	(4, 2, 1, 1),
	(5, 3, 1, 1),
	(6, 4, 1, 1),
	(7, 5, 1, 1);

-- Volcando estructura para tabla mega_uni_store.productos_proveedores
DROP TABLE IF EXISTS `productos_proveedores`;
CREATE TABLE IF NOT EXISTS `productos_proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `tiempo_entrega_dias` int DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prod_prov` (`producto_id`,`proveedor_id`),
  KEY `fk_pp_proveedor` (`proveedor_id`),
  CONSTRAINT `fk_pp_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pp_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.productos_proveedores: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.proveedores
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `ruc_nit` varchar(30) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `contacto_nombre` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prov_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.proveedores: ~0 rows (aproximadamente)

-- Volcando estructura para procedimiento mega_uni_store.renombrar_columna_si_existe
DROP PROCEDURE IF EXISTS `renombrar_columna_si_existe`;
DELIMITER //
CREATE PROCEDURE `renombrar_columna_si_existe`(
    IN p_tabla      VARCHAR(64),
    IN p_col_vieja  VARCHAR(64),
    IN p_col_nueva  VARCHAR(64),
    IN p_definicion TEXT          -- tipo y atributos de la columna
)
BEGIN
    -- Si ya existe con el nombre nuevo, no hacer nada
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = p_tabla
          AND COLUMN_NAME  = p_col_nueva
    ) THEN
        SELECT CONCAT('⏭️  Ya existe con nombre correcto: ', p_tabla, '.', p_col_nueva) AS resultado;

    -- Si existe con el nombre viejo, renombrar
    ELSEIF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = p_tabla
          AND COLUMN_NAME  = p_col_vieja
    ) THEN
        SET @sql = CONCAT(
            'ALTER TABLE `', p_tabla, '` RENAME COLUMN `',
            p_col_vieja, '` TO `', p_col_nueva, '`'
        );
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('✅ Renombrada: ', p_tabla, '.', p_col_vieja, ' → ', p_col_nueva) AS resultado;

    -- Ninguna de las dos existe: algo raro, reportar
    ELSE
        SELECT CONCAT('⚠️  No existe ninguna: ', p_tabla, '.', p_col_vieja, ' ni .', p_col_nueva) AS resultado;
    END IF;
END//
DELIMITER ;

-- Volcando estructura para tabla mega_uni_store.reportes
DROP TABLE IF EXISTS `reportes`;
CREATE TABLE IF NOT EXISTS `reportes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `tipo` varchar(80) NOT NULL,
  `parametros_json` json DEFAULT NULL,
  `creado_por` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_rep_tienda` (`tienda_id`),
  KEY `fk_rep_usuario` (`creado_por`),
  CONSTRAINT `fk_rep_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rep_usuario` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.reportes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text,
  `nivel` int NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.roles: ~9 rows (aproximadamente)
REPLACE INTO `roles` (`id`, `nombre`, `descripcion`, `nivel`, `activo`) VALUES
	(1, 'Superadministrador', 'Usuario raíz con control total sobre la plataforma, tiendas, usuarios, roles y configuración global.', 1, 1),
	(2, 'Administrador de Tienda', 'Gestiona una tienda específica, su personal, productos, inventario, ventas y reportes.', 2, 1),
	(3, 'Supervisor', 'Supervisa operación, cumplimiento de procesos, transacciones y actividades de la tienda.', 3, 1),
	(4, 'Nómina y RRHH', 'Gestiona personal, novedades laborales, pagos, liquidaciones y reportes de productividad.', 3, 1),
	(5, 'Vendedor', 'Registra ventas, atiende clientes, gestiona carrito, pagos y devoluciones operativas.', 4, 1),
	(6, 'Bodeguero', 'Gestiona inventario, entradas, salidas, ajustes de stock y preparación de pedidos.', 4, 1),
	(7, 'Reportero', 'Consulta, genera y exporta reportes de ventas, inventarios y desempeño operativo.', 4, 1),
	(8, 'Cliente', 'Usuario final que navega, compra, paga, consulta historial y califica productos.', 5, 1),
	(9, 'Sistema', 'Actor lógico para automatizaciones, alertas, respaldos, validaciones y eventos internos.', 1, 1);

-- Volcando estructura para tabla mega_uni_store.roles_permisos
DROP TABLE IF EXISTS `roles_permisos`;
CREATE TABLE IF NOT EXISTS `roles_permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rol_id` int NOT NULL,
  `permiso_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rol_permiso` (`rol_id`,`permiso_id`),
  KEY `fk_rp_permiso` (`permiso_id`),
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.roles_permisos: ~97 rows (aproximadamente)
REPLACE INTO `roles_permisos` (`id`, `rol_id`, `permiso_id`, `created_at`) VALUES
	(1, 1, 1, '2026-04-25 00:29:11'),
	(2, 1, 2, '2026-04-25 00:29:11'),
	(3, 1, 3, '2026-04-25 00:29:11'),
	(4, 1, 4, '2026-04-25 00:29:11'),
	(5, 1, 5, '2026-04-25 00:29:11'),
	(6, 1, 6, '2026-04-25 00:29:11'),
	(7, 1, 7, '2026-04-25 00:29:11'),
	(8, 1, 8, '2026-04-25 00:29:11'),
	(9, 1, 9, '2026-04-25 00:29:11'),
	(10, 1, 10, '2026-04-25 00:29:11'),
	(11, 1, 11, '2026-04-25 00:29:11'),
	(12, 1, 12, '2026-04-25 00:29:11'),
	(13, 1, 13, '2026-04-25 00:29:11'),
	(14, 1, 14, '2026-04-25 00:29:11'),
	(15, 1, 15, '2026-04-25 00:29:11'),
	(16, 1, 16, '2026-04-25 00:29:11'),
	(17, 1, 17, '2026-04-25 00:29:11'),
	(18, 1, 18, '2026-04-25 00:29:11'),
	(19, 1, 19, '2026-04-25 00:29:11'),
	(20, 1, 20, '2026-04-25 00:29:11'),
	(21, 1, 21, '2026-04-25 00:29:11'),
	(22, 1, 22, '2026-04-25 00:29:11'),
	(23, 1, 23, '2026-04-25 00:29:11'),
	(24, 1, 24, '2026-04-25 00:29:11'),
	(25, 1, 25, '2026-04-25 00:29:11'),
	(26, 1, 26, '2026-04-25 00:29:11'),
	(27, 1, 27, '2026-04-25 00:29:11'),
	(28, 1, 28, '2026-04-25 00:29:11'),
	(29, 1, 29, '2026-04-25 00:29:11'),
	(30, 1, 30, '2026-04-25 00:29:11'),
	(31, 1, 31, '2026-04-25 00:29:11'),
	(32, 1, 32, '2026-04-25 00:29:11'),
	(33, 1, 33, '2026-04-25 00:29:11'),
	(34, 1, 34, '2026-04-25 00:29:11'),
	(35, 1, 35, '2026-04-25 00:29:11'),
	(36, 1, 36, '2026-04-25 00:29:11'),
	(37, 1, 37, '2026-04-25 00:29:11'),
	(38, 9, 1, '2026-04-25 00:29:11'),
	(39, 9, 36, '2026-04-25 00:29:11'),
	(40, 9, 37, '2026-04-25 00:29:11'),
	(41, 9, 19, '2026-04-25 00:29:11'),
	(42, 2, 1, '2026-04-25 00:29:11'),
	(43, 2, 13, '2026-04-25 00:29:11'),
	(44, 2, 14, '2026-04-25 00:29:11'),
	(45, 2, 15, '2026-04-25 00:29:11'),
	(46, 2, 16, '2026-04-25 00:29:11'),
	(47, 2, 17, '2026-04-25 00:29:11'),
	(48, 2, 18, '2026-04-25 00:29:11'),
	(49, 2, 19, '2026-04-25 00:29:11'),
	(50, 2, 20, '2026-04-25 00:29:11'),
	(51, 2, 22, '2026-04-25 00:29:11'),
	(52, 2, 23, '2026-04-25 00:29:11'),
	(53, 2, 25, '2026-04-25 00:29:11'),
	(54, 2, 26, '2026-04-25 00:29:11'),
	(55, 2, 27, '2026-04-25 00:29:11'),
	(56, 2, 28, '2026-04-25 00:29:11'),
	(57, 2, 7, '2026-04-25 00:29:11'),
	(58, 3, 1, '2026-04-25 00:29:11'),
	(59, 3, 13, '2026-04-25 00:29:11'),
	(60, 3, 17, '2026-04-25 00:29:11'),
	(61, 3, 19, '2026-04-25 00:29:11'),
	(62, 3, 20, '2026-04-25 00:29:11'),
	(63, 3, 22, '2026-04-25 00:29:11'),
	(64, 3, 23, '2026-04-25 00:29:11'),
	(65, 3, 25, '2026-04-25 00:29:11'),
	(66, 5, 1, '2026-04-25 00:29:11'),
	(67, 5, 13, '2026-04-25 00:29:11'),
	(68, 5, 17, '2026-04-25 00:29:11'),
	(69, 5, 20, '2026-04-25 00:29:11'),
	(70, 5, 21, '2026-04-25 00:29:11'),
	(71, 5, 23, '2026-04-25 00:29:11'),
	(73, 6, 1, '2026-04-25 00:29:11'),
	(74, 6, 13, '2026-04-25 00:29:11'),
	(75, 6, 17, '2026-04-25 00:29:11'),
	(76, 6, 18, '2026-04-25 00:29:11'),
	(77, 6, 19, '2026-04-25 00:29:11'),
	(78, 7, 1, '2026-04-25 00:29:11'),
	(79, 7, 25, '2026-04-25 00:29:11'),
	(80, 7, 26, '2026-04-25 00:29:11'),
	(81, 7, 20, '2026-04-25 00:29:11'),
	(82, 7, 17, '2026-04-25 00:29:11'),
	(83, 4, 1, '2026-04-25 00:29:11'),
	(84, 4, 27, '2026-04-25 00:29:11'),
	(85, 4, 28, '2026-04-25 00:29:11'),
	(86, 4, 29, '2026-04-25 00:29:11'),
	(87, 4, 30, '2026-04-25 00:29:11'),
	(88, 4, 25, '2026-04-25 00:29:11'),
	(89, 8, 1, '2026-04-25 00:29:11'),
	(90, 8, 31, '2026-04-25 00:29:11'),
	(91, 8, 32, '2026-04-25 00:29:11'),
	(92, 8, 33, '2026-04-25 00:29:11'),
	(93, 8, 34, '2026-04-25 00:29:11'),
	(94, 2, 21, '2026-05-06 02:12:33'),
	(95, 2, 24, '2026-05-06 02:12:33'),
	(96, 3, 21, '2026-05-06 02:12:33'),
	(97, 3, 24, '2026-05-06 02:12:33'),
	(98, 5, 24, '2026-05-06 02:12:33');

-- Volcando estructura para tabla mega_uni_store.sesiones
DROP TABLE IF EXISTS `sesiones`;
CREATE TABLE IF NOT EXISTS `sesiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `refresh_token` varchar(255) DEFAULT NULL,
  `dispositivo` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `expires_at` timestamp NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  UNIQUE KEY `refresh_token` (`refresh_token`),
  KEY `idx_token` (`token_hash`),
  KEY `idx_ses_expires` (`expires_at`),
  KEY `idx_ses_usuario` (`usuario_id`),
  CONSTRAINT `fk_ses_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.sesiones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.solicitudes_cambio_contrasena
DROP TABLE IF EXISTS `solicitudes_cambio_contrasena`;
CREATE TABLE IF NOT EXISTS `solicitudes_cambio_contrasena` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int unsigned NOT NULL,
  `nuevo_password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `admin_id` int unsigned DEFAULT NULL,
  `motivo_rechazo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.solicitudes_cambio_contrasena: ~1 rows (aproximadamente)
REPLACE INTO `solicitudes_cambio_contrasena` (`id`, `usuario_id`, `nuevo_password_hash`, `estado`, `admin_id`, `motivo_rechazo`, `created_at`, `updated_at`) VALUES
	(1, 3, '$2y$10$4u5zV5uY/gXOCCyNQVwvG.ixOFxlJ37M.Kjv1gmzotjDlDdXSGIk6', 'aprobada', 1, NULL, '2026-05-25 13:10:46', '2026-05-25 13:14:14');

-- Volcando estructura para procedimiento mega_uni_store.sp_eliminar_producto
DROP PROCEDURE IF EXISTS `sp_eliminar_producto`;
DELIMITER //
CREATE PROCEDURE `sp_eliminar_producto`(IN p_producto_id INT)
BEGIN
  UPDATE productos
  SET deleted_at = NOW(), estado = 0
  WHERE id = p_producto_id AND deleted_at IS NULL;

  INSERT INTO audit_log (tabla, accion, registro_id, created_at)
  VALUES ('productos', 'DELETE', p_producto_id, NOW());
END//
DELIMITER ;

-- Volcando estructura para procedimiento mega_uni_store.sp_eliminar_usuario
DROP PROCEDURE IF EXISTS `sp_eliminar_usuario`;
DELIMITER //
CREATE PROCEDURE `sp_eliminar_usuario`(IN p_id INT)
BEGIN
    UPDATE usuarios SET deleted_at = NOW(), estado = 0 WHERE id = p_id;
    INSERT INTO audit_log (tabla, accion, registro_id, created_at)
    VALUES ('usuarios', 'SOFT_DELETE', p_id, NOW());
END//
DELIMITER ;

-- Volcando estructura para procedimiento mega_uni_store.sp_resumen_caja
DROP PROCEDURE IF EXISTS `sp_resumen_caja`;
DELIMITER //
CREATE PROCEDURE `sp_resumen_caja`(IN p_caja_id INT, IN p_fecha DATE)
BEGIN
  SELECT
    c.nombre                                          AS caja,
    SUM(CASE WHEN cm.tipo = 'apertura' THEN cm.monto ELSE 0 END) AS apertura,
    SUM(CASE WHEN cm.tipo = 'ingreso'  THEN cm.monto ELSE 0 END) AS total_ingresos,
    SUM(CASE WHEN cm.tipo = 'egreso'   THEN cm.monto ELSE 0 END) AS total_egresos,
    SUM(CASE WHEN cm.tipo = 'cierre'   THEN cm.monto_real ELSE 0 END) AS cierre_real,
    COUNT(DISTINCT cm.venta_id)                       AS num_ventas
  FROM cajas c
  JOIN cajas_movimientos cm ON cm.caja_id = c.id
  WHERE c.id = p_caja_id
    AND DATE(cm.created_at) = p_fecha
  GROUP BY c.nombre;
END//
DELIMITER ;

-- Volcando estructura para procedimiento mega_uni_store.sp_ventas_resumen
DROP PROCEDURE IF EXISTS `sp_ventas_resumen`;
DELIMITER //
CREATE PROCEDURE `sp_ventas_resumen`(IN p_tienda INT, IN p_desde DATE, IN p_hasta DATE)
BEGIN
    SELECT DATE(fecha) AS dia, COUNT(*) AS num_ventas,
           COALESCE(SUM(subtotal),0) AS subtotal,
           COALESCE(SUM(descuento),0) AS descuentos,
           COALESCE(SUM(impuesto),0)  AS impuestos,
           COALESCE(SUM(total),0)     AS total_dia
    FROM ventas
    WHERE tienda_id   = p_tienda
      AND DATE(fecha) BETWEEN p_desde AND p_hasta
      AND estado      = 'completada'
      AND deleted_at  IS NULL
    GROUP BY DATE(fecha) ORDER BY dia;
END//
DELIMITER ;

-- Volcando estructura para tabla mega_uni_store.suscripciones
DROP TABLE IF EXISTS `suscripciones`;
CREATE TABLE IF NOT EXISTS `suscripciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plataforma_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activo','vencido','cancelado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sus_plataforma` (`plataforma_id`),
  KEY `fk_sus_plan` (`plan_id`),
  CONSTRAINT `fk_sus_plan` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`),
  CONSTRAINT `fk_sus_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataforma` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.suscripciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.tiendas
DROP TABLE IF EXISTS `tiendas`;
CREATE TABLE IF NOT EXISTS `tiendas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text,
  `logo_url` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `propietario_id` int NOT NULL,
  `plataforma_id` int DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tienda_propietario` (`propietario_id`),
  KEY `fk_tienda_plataforma` (`plataforma_id`),
  KEY `idx_tienda_deleted` (`deleted_at`),
  KEY `fk_tienda_updated_by` (`updated_by`),
  CONSTRAINT `fk_tienda_plataforma` FOREIGN KEY (`plataforma_id`) REFERENCES `plataforma` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tienda_propietario` FOREIGN KEY (`propietario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_tienda_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.tiendas: ~5 rows (aproximadamente)
REPLACE INTO `tiendas` (`id`, `nombre`, `descripcion`, `logo_url`, `direccion`, `telefono`, `email`, `propietario_id`, `plataforma_id`, `estado`, `deleted_at`, `created_at`, `updated_at`, `updated_by`) VALUES
	(1, 'Mega Uni Store Principal', 'Tienda principal del ecosistema Mega_Uni_Store', NULL, 'Carrera 5 # 12-34', '3001234567', 'principal@megaunistore.com', 1, 1, 1, NULL, '2026-04-24 19:29:43', '2026-05-06 16:44:06', 1),
	(2, 'Nueva Tienda', 'Descripción de prueba', NULL, NULL, NULL, NULL, 1, 1, 1, NULL, '2026-04-25 00:21:32', '2026-05-06 16:44:10', 1),
	(3, 'Nueva Tienda', 'Descripción de prueba', NULL, NULL, NULL, NULL, 1, 1, 1, NULL, '2026-04-25 00:21:45', '2026-05-06 16:44:12', 1),
	(4, 'PIRULIN2.0', 'vendemos cabezas grandes para jugar futbol', NULL, 'Huila, Hobo, Carrera 4 #5-35 Barrio San fernado', '3144817006', 'angelnicolasabrilq@gmail.com', 1, 1, 1, NULL, '2026-05-09 01:39:33', NULL, 1),
	(5, 'POPOCHONGO', 'HELADOS Y TUSSI', 'xhttps://p16-comment-sign-sg.tiktokcdn.com/tos-alisg-i-zt8igodiya-sg/8a3e389efc1d46af90d402eb5743c0df~tplv-jj85edgx6n-image-origin.jpeg?dr=8569&refresh_token=761509b8&x-expires=1781884800&x-signature=CMcUSCsCA0%2F%2FrqN%2F%2BAcEuMhlq90%3D&t=67a6c45e&ps=a0', 'CR 45 353-06', '3125631204', NULL, 1, 1, 1, NULL, '2026-05-20 14:42:38', '2026-05-24 15:45:52', 1);

-- Volcando estructura para tabla mega_uni_store.tiendas_clientes
DROP TABLE IF EXISTS `tiendas_clientes`;
CREATE TABLE IF NOT EXISTS `tiendas_clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `puntos_fidelidad` int NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tienda_cliente` (`tienda_id`,`cliente_id`),
  KEY `fk_tcliente_cliente` (`cliente_id`),
  CONSTRAINT `fk_tcliente_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tcliente_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.tiendas_clientes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.tiendas_config
DROP TABLE IF EXISTS `tiendas_config`;
CREATE TABLE IF NOT EXISTS `tiendas_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text,
  `tipo` varchar(50) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tienda_clave` (`tienda_id`,`clave`),
  CONSTRAINT `fk_tc_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.tiendas_config: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.tiendas_productos
DROP TABLE IF EXISTS `tiendas_productos`;
CREATE TABLE IF NOT EXISTS `tiendas_productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tienda_producto` (`tienda_id`,`producto_id`),
  KEY `fk_tp_producto` (`producto_id`),
  CONSTRAINT `fk_tp_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tp_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.tiendas_productos: ~5 rows (aproximadamente)
REPLACE INTO `tiendas_productos` (`id`, `tienda_id`, `producto_id`, `precio_venta`, `precio_compra`, `estado`) VALUES
	(1, 1, 1, 3500.00, 2500.00, 1),
	(4, 1, 2, 15000.00, 20000.00, 1),
	(5, 4, 3, 100000.00, 50000.00, 1),
	(6, 4, 4, 100000.00, 50000.00, 1),
	(7, 5, 5, 3500.00, 4500.00, 1);

-- Volcando estructura para tabla mega_uni_store.turnos
DROP TABLE IF EXISTS `turnos`;
CREATE TABLE IF NOT EXISTS `turnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `horario_id` int NOT NULL,
  `dia_semana` tinyint(1) NOT NULL COMMENT '1=Lun ... 7=Dom',
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_turno_horario` (`horario_id`),
  CONSTRAINT `fk_turno_horario` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.turnos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.unidades_medida
DROP TABLE IF EXISTS `unidades_medida`;
CREATE TABLE IF NOT EXISTS `unidades_medida` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `simbolo` varchar(10) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.unidades_medida: ~4 rows (aproximadamente)
REPLACE INTO `unidades_medida` (`id`, `nombre`, `simbolo`, `tipo`) VALUES
	(1, 'Unidad', 'und', 'Unidad'),
	(2, 'Kilogramo', 'Kg', 'Peso'),
	(3, 'Litro', 'L', 'Volumen'),
	(4, 'Metro', 'm', 'Longitud');

-- Volcando estructura para tabla mega_uni_store.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla mega_uni_store.users: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_usr_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.usuarios: ~5 rows (aproximadamente)
REPLACE INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password_hash`, `telefono`, `avatar_url`, `estado`, `deleted_at`, `created_at`) VALUES
	(1, 'Admin', 'Principal', 'admin@megaunistore.com', '$2y$10$IrQnUXOHrkZEMa3dJOF/Ue84jw/lE/bb6Ruhz1TCZVzaWKxes8mp2', NULL, NULL, 1, NULL, '2026-04-24 18:12:07'),
	(2, 'A\'ngel A\'bril', 'Abril Quimbaya', 'angelnicolasabrilq@gmail.com', '$2y$10$v1G/RNAL5kItzIGmClZHneWP4hIVgALG9IIfhXeV1WjSeWjDaQqSe', '3144817006', NULL, 0, '2026-04-25 00:21:03', '2026-04-25 00:12:13'),
	(3, 'Carlos', 'Vendedor', 'vendedor1@megaunistore.com', '$2y$10$4u5zV5uY/gXOCCyNQVwvG.ixOFxlJ37M.Kjv1gmzotjDlDdXSGIk6', '30000000000', NULL, 1, NULL, '2026-04-25 00:17:32'),
	(6, 'A\'ngel A\'bril', 'Abril Quimbaya', 'angel@gmail.com', '$2y$10$3BSdYQVYa.G/GFojXBYhDuih8ZmWKFXTYwMSqdYDN.dgeTCU6JrYy', '3144817006', NULL, 1, NULL, '2026-04-27 00:21:14'),
	(7, 'Bruno', 'Bodeguero', 'bodeguero1@megaunistore.com', '$2y$10$gajAD2J3in/oi7.iL9MKw.wMCK1z1MCj9xEKCez8bR5Sb5ed2xE1i', '3001112233', NULL, 1, NULL, '2026-04-27 15:50:58');

-- Volcando estructura para tabla mega_uni_store.usuarios_roles
DROP TABLE IF EXISTS `usuarios_roles`;
CREATE TABLE IF NOT EXISTS `usuarios_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `rol_id` int NOT NULL,
  `tienda_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usr_rol_tienda` (`usuario_id`,`rol_id`,`tienda_id`),
  KEY `fk_ur_rol` (`rol_id`),
  KEY `fk_ur_tienda` (`tienda_id`),
  CONSTRAINT `fk_ur_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.usuarios_roles: ~4 rows (aproximadamente)
REPLACE INTO `usuarios_roles` (`id`, `usuario_id`, `rol_id`, `tienda_id`, `created_at`) VALUES
	(1, 1, 1, NULL, '2026-04-24 18:17:35'),
	(2, 3, 5, 1, '2026-04-25 00:17:32'),
	(3, 6, 8, NULL, '2026-04-27 00:21:14'),
	(4, 7, 6, 1, '2026-04-27 15:50:58');

-- Volcando estructura para tabla mega_uni_store.vacaciones
DROP TABLE IF EXISTS `vacaciones`;
CREATE TABLE IF NOT EXISTS `vacaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` int NOT NULL,
  `tipo` enum('vacacion','incapacidad','licencia','calamidad','permiso') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias` int GENERATED ALWAYS AS (((to_days(`fecha_fin`) - to_days(`fecha_inicio`)) + 1)) STORED,
  `motivo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `estado` enum('solicitada','aprobada','rechazada') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'solicitada',
  `aprobado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_vacaciones_empleado` (`empleado_id`),
  CONSTRAINT `fk_vacaciones_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla mega_uni_store.vacaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.ventas
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `cliente_id` int DEFAULT NULL,
  `empleado_id` int DEFAULT NULL,
  `caja_id` int DEFAULT NULL,
  `cupon_id` int unsigned DEFAULT NULL COMMENT 'Cupón aplicado a esta venta',
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `estado` enum('completada','anulada','pendiente') NOT NULL DEFAULT 'completada',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tienda_fecha` (`tienda_id`,`fecha`),
  KEY `fk_venta_caja` (`caja_id`),
  KEY `idx_venta_deleted` (`deleted_at`),
  KEY `fk_venta_created_by` (`created_by`),
  KEY `fk_venta_updated_by` (`updated_by`),
  KEY `idx_venta_tienda_fecha` (`tienda_id`,`fecha`),
  KEY `idx_venta_estado_fecha` (`estado`,`fecha`),
  KEY `idx_venta_cliente` (`cliente_id`),
  KEY `idx_venta_empleado` (`empleado_id`),
  KEY `idx_ventas_fecha_estado` (`fecha`,`estado`),
  KEY `idx_ventas_tienda_fecha` (`tienda_id`,`fecha`),
  CONSTRAINT `fk_venta_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_venta_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_venta_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_venta_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_venta_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`),
  CONSTRAINT `fk_venta_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.ventas: ~5 rows (aproximadamente)
REPLACE INTO `ventas` (`id`, `tienda_id`, `cliente_id`, `empleado_id`, `caja_id`, `cupon_id`, `fecha`, `subtotal`, `descuento`, `impuesto`, `total`, `estado`, `deleted_at`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
	(1, 1, NULL, NULL, NULL, NULL, '2026-04-27 16:32:16', 3500.00, 0.00, 665.00, 4165.00, 'completada', NULL, '2026-04-27 16:32:16', NULL, 3, 3),
	(2, 1, NULL, NULL, NULL, NULL, '2026-04-27 16:39:10', 24500.00, 0.00, 4655.00, 29155.00, 'completada', NULL, '2026-04-27 16:39:10', NULL, 3, 3),
	(3, 1, NULL, NULL, 1, NULL, '2026-04-29 12:50:57', 10500.00, 0.00, 1995.00, 12495.00, 'anulada', NULL, '2026-04-29 12:50:57', '2026-04-29 12:58:46', 1, 1),
	(4, 1, NULL, NULL, 1, NULL, '2026-04-29 13:06:11', 3500.00, 0.00, 665.00, 4165.00, 'anulada', NULL, '2026-04-29 13:06:11', '2026-04-29 13:16:30', 1, 1),
	(5, 1, NULL, NULL, 1, NULL, '2026-04-29 17:13:59', 17500.00, 0.00, 3325.00, 20825.00, 'completada', NULL, '2026-04-29 17:13:59', NULL, 1, 1);

-- Volcando estructura para tabla mega_uni_store.ventas_cupones
DROP TABLE IF EXISTS `ventas_cupones`;
CREATE TABLE IF NOT EXISTS `ventas_cupones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `cupon_id` int NOT NULL,
  `monto_desc` decimal(10,2) NOT NULL DEFAULT '0.00',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venta_cupon` (`venta_id`,`cupon_id`),
  KEY `fk_vc_cupon` (`cupon_id`),
  CONSTRAINT `fk_vc_cupon` FOREIGN KEY (`cupon_id`) REFERENCES `cupones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vc_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.ventas_cupones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mega_uni_store.ventas_detalle
DROP TABLE IF EXISTS `ventas_detalle`;
CREATE TABLE IF NOT EXISTS `ventas_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_vd_venta` (`venta_id`),
  KEY `idx_vd_producto` (`producto_id`),
  CONSTRAINT `fk_vd_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `fk_vd_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla mega_uni_store.ventas_detalle: ~5 rows (aproximadamente)
REPLACE INTO `ventas_detalle` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `descuento`, `subtotal`) VALUES
	(1, 1, 1, 1.00, 3500.00, 0.00, 3500.00),
	(2, 2, 1, 7.00, 3500.00, 0.00, 24500.00),
	(3, 3, 1, 3.00, 3500.00, 0.00, 10500.00),
	(4, 4, 1, 1.00, 3500.00, 0.00, 3500.00),
	(5, 5, 1, 5.00, 3500.00, 0.00, 17500.00);

-- Volcando estructura para vista mega_uni_store.v_clientes_activos
DROP VIEW IF EXISTS `v_clientes_activos`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_clientes_activos` (
	`id` INT(10) NOT NULL,
	`nombre` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`apellido` VARCHAR(100) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`email` VARCHAR(150) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`telefono` VARCHAR(20) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`tipo_documento` VARCHAR(20) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`numero_documento` VARCHAR(30) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`direccion` VARCHAR(255) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`created_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista mega_uni_store.v_empleados_activos
DROP VIEW IF EXISTS `v_empleados_activos`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_empleados_activos` (
	`id` INT(10) NOT NULL,
	`usuario_id` INT(10) NOT NULL,
	`tienda_id` INT(10) NOT NULL,
	`codigo_empleado` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`fecha_ingreso` DATE NOT NULL,
	`salario_base` DECIMAL(10,2) NULL,
	`estado` ENUM('activo','inactivo') NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`deleted_at` TIMESTAMP NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista mega_uni_store.v_productos_activos
DROP VIEW IF EXISTS `v_productos_activos`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_productos_activos` (
	`id` INT(10) NOT NULL,
	`nombre` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`codigo_barras` VARCHAR(50) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`imagen_url` VARCHAR(255) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`categoria_id` INT(10) NULL,
	`unidad_medida_id` INT(10) NULL,
	`estado` TINYINT(1) NOT NULL,
	`deleted_at` TIMESTAMP NULL,
	`created_at` TIMESTAMP NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista mega_uni_store.v_tiendas_activas
DROP VIEW IF EXISTS `v_tiendas_activas`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_tiendas_activas` (
	`id` INT(10) NOT NULL,
	`nombre` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`logo_url` VARCHAR(255) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`direccion` VARCHAR(255) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`telefono` VARCHAR(20) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`email` VARCHAR(150) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`propietario_id` INT(10) NOT NULL,
	`plataforma_id` INT(10) NULL,
	`estado` TINYINT(1) NOT NULL,
	`deleted_at` TIMESTAMP NULL,
	`created_at` TIMESTAMP NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista mega_uni_store.v_usuarios_activos
DROP VIEW IF EXISTS `v_usuarios_activos`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_usuarios_activos` (
	`id` INT(10) NOT NULL,
	`nombre` VARCHAR(80) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`apellido` VARCHAR(80) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`email` VARCHAR(150) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`password_hash` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`telefono` VARCHAR(20) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`avatar_url` VARCHAR(255) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`estado` TINYINT(1) NOT NULL,
	`deleted_at` TIMESTAMP NULL,
	`created_at` TIMESTAMP NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista mega_uni_store.v_ventas_validas
DROP VIEW IF EXISTS `v_ventas_validas`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_ventas_validas` (
	`id` INT(10) NOT NULL,
	`tienda_id` INT(10) NOT NULL,
	`cliente_id` INT(10) NULL,
	`empleado_id` INT(10) NULL,
	`caja_id` INT(10) NULL,
	`fecha` TIMESTAMP NOT NULL,
	`subtotal` DECIMAL(10,2) NOT NULL,
	`descuento` DECIMAL(10,2) NOT NULL,
	`impuesto` DECIMAL(10,2) NOT NULL,
	`total` DECIMAL(10,2) NOT NULL,
	`estado` ENUM('completada','anulada','pendiente') NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`deleted_at` TIMESTAMP NULL,
	`created_at` TIMESTAMP NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para disparador mega_uni_store.trg_audit_inventario_update
DROP TRIGGER IF EXISTS `trg_audit_inventario_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_audit_inventario_update` AFTER UPDATE ON `inventario` FOR EACH ROW BEGIN
  INSERT INTO audit_log
    (tabla, accion, registro_id, datos_antes, datos_despues, created_at)
  VALUES (
    'inventario',
    'UPDATE',
    NEW.id,
    JSON_OBJECT('cantidad', OLD.cantidad),
    JSON_OBJECT('cantidad', NEW.cantidad),
    NOW()
  );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_audit_venta_update
DROP TRIGGER IF EXISTS `trg_audit_venta_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_audit_venta_update` AFTER UPDATE ON `ventas` FOR EACH ROW BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO audit_log (tienda_id, tabla, accion, registro_id, datos_antes, datos_despues, created_at)
        VALUES (NEW.tienda_id, 'ventas', 'UPDATE', NEW.id,
                CONCAT('{"estado":"', OLD.estado, '"}'),
                CONCAT('{"estado":"', NEW.estado, '"}'),
                NOW());
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_compra_cancelada_revertir
DROP TRIGGER IF EXISTS `trg_compra_cancelada_revertir`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_compra_cancelada_revertir` AFTER UPDATE ON `compras` FOR EACH ROW BEGIN
  DECLARE done     INT DEFAULT 0;
  DECLARE v_prod   INT;
  DECLARE v_cant   DECIMAL(10,2);
  DECLARE v_inv_id INT;

  DECLARE cur CURSOR FOR
    SELECT producto_id, cantidad
    FROM compras_detalle WHERE compra_id = NEW.id;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  IF NEW.estado = 'cancelada' AND OLD.estado = 'recibida' THEN
    OPEN cur;
    loop_items: LOOP
      FETCH cur INTO v_prod, v_cant;
      IF done THEN LEAVE loop_items; END IF;

      UPDATE inventario
      SET cantidad = GREATEST(0, cantidad - v_cant)
      WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod;

      SELECT id INTO v_inv_id FROM inventario
      WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod LIMIT 1;

      IF v_inv_id IS NOT NULL THEN
        INSERT INTO movimientos_inventario
          (inventario_id, tipo, cantidad, motivo, ref_id, ref_tipo, created_at)
        VALUES
          (v_inv_id, 'ajuste', v_cant,
           CONCAT('Reversión compra cancelada #', NEW.id),
           NEW.id, 'compras_canceladas', NOW());
      END IF;
    END LOOP;
    CLOSE cur;
  END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_compra_recibida_suma
DROP TRIGGER IF EXISTS `trg_compra_recibida_suma`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_compra_recibida_suma` AFTER UPDATE ON `compras` FOR EACH ROW BEGIN
    DECLARE done   INT DEFAULT 0;
    DECLARE v_prod INT;
    DECLARE v_cant DECIMAL(10,2);
    DECLARE v_inv  INT DEFAULT NULL;
    DECLARE cur CURSOR FOR
        SELECT producto_id, cantidad FROM compras_detalle WHERE compra_id = NEW.id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    IF NEW.estado = 'recibida' AND OLD.estado != 'recibida' THEN
        OPEN cur;
        bucle: LOOP
            FETCH cur INTO v_prod, v_cant;
            IF done = 1 THEN LEAVE bucle; END IF;
            IF EXISTS (SELECT 1 FROM inventario WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod) THEN
                UPDATE inventario SET cantidad = cantidad + v_cant
                 WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod;
            ELSE
                INSERT INTO inventario (tienda_id, producto_id, cantidad, cantidad_minima)
                VALUES (NEW.tienda_id, v_prod, v_cant, 0);
            END IF;
            SELECT id INTO v_inv FROM inventario
             WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod LIMIT 1;
            IF v_inv IS NOT NULL THEN
                INSERT INTO movimientos_inventario
                    (inventario_id, tipo, cantidad, motivo, ref_id, ref_tipo, created_at)
                VALUES (v_inv, 'entrada', v_cant,
                        CONCAT('Compra recibida #', NEW.id), NEW.id, 'compras', NOW());
            END IF;
        END LOOP;
        CLOSE cur;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_devolucion_aprobada_suma
DROP TRIGGER IF EXISTS `trg_devolucion_aprobada_suma`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_devolucion_aprobada_suma` AFTER UPDATE ON `devoluciones` FOR EACH ROW BEGIN
    DECLARE done     INT DEFAULT 0;
    DECLARE v_prod   INT;
    DECLARE v_cant   DECIMAL(10,2);
    DECLARE v_tienda INT DEFAULT NULL;
    DECLARE v_inv    INT DEFAULT NULL;
    DECLARE cur CURSOR FOR
        SELECT producto_id, cantidad FROM devoluciones_detalle WHERE devolucion_id = NEW.id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    IF NEW.estado = 'aprobada' AND OLD.estado != 'aprobada' THEN
        SELECT tienda_id INTO v_tienda FROM ventas WHERE id = NEW.venta_id LIMIT 1;
        IF v_tienda IS NOT NULL THEN
            OPEN cur;
            bucle: LOOP
                FETCH cur INTO v_prod, v_cant;
                IF done = 1 THEN LEAVE bucle; END IF;
                UPDATE inventario SET cantidad = cantidad + v_cant
                 WHERE tienda_id = v_tienda AND producto_id = v_prod;
                SELECT id INTO v_inv FROM inventario
                 WHERE tienda_id = v_tienda AND producto_id = v_prod LIMIT 1;
                IF v_inv IS NOT NULL THEN
                    INSERT INTO movimientos_inventario
                        (inventario_id, tipo, cantidad, motivo, ref_id, ref_tipo, created_at)
                    VALUES (v_inv, 'entrada', v_cant,
                            CONCAT('Devolución aprobada #', NEW.id),
                            NEW.id, 'devoluciones', NOW());
                END IF;
            END LOOP;
            CLOSE cur;
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_stock_alerta_minimo
DROP TRIGGER IF EXISTS `trg_stock_alerta_minimo`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_stock_alerta_minimo` AFTER UPDATE ON `inventario` FOR EACH ROW BEGIN
    DECLARE v_prop   INT DEFAULT NULL;
    DECLARE v_nombre VARCHAR(200) DEFAULT '';
    IF NEW.cantidad < NEW.cantidad_minima AND OLD.cantidad >= OLD.cantidad_minima THEN
        SELECT propietario_id INTO v_prop FROM tiendas WHERE id = NEW.tienda_id LIMIT 1;
        SELECT nombre INTO v_nombre FROM productos WHERE id = NEW.producto_id LIMIT 1;
        INSERT INTO notificaciones (tienda_id, usuario_id, titulo, mensaje, tipo, created_at)
        VALUES (NEW.tienda_id, v_prop, 'Stock bajo',
                CONCAT('"', v_nombre, '" bajo mínimo. Cantidad: ', NEW.cantidad),
                'warning', NOW());
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_usuario_soft_delete
DROP TRIGGER IF EXISTS `trg_usuario_soft_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_usuario_soft_delete` BEFORE DELETE ON `usuarios` FOR EACH ROW BEGIN
  -- Bloquear DELETE físico, forzar soft delete
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'DELETE físico no permitido en usuarios. Use: UPDATE usuarios SET deleted_at = NOW() WHERE id = X';
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_venta_anular_revertir
DROP TRIGGER IF EXISTS `trg_venta_anular_revertir`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_venta_anular_revertir` AFTER UPDATE ON `ventas` FOR EACH ROW BEGIN
    DECLARE done    INT DEFAULT 0;
    DECLARE v_prod  INT;
    DECLARE v_cant  DECIMAL(10,2);
    DECLARE v_inv   INT DEFAULT NULL;
    DECLARE cur CURSOR FOR
        SELECT producto_id, cantidad FROM ventas_detalle WHERE venta_id = NEW.id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    IF NEW.estado = 'anulada' AND OLD.estado != 'anulada' THEN
        OPEN cur;
        bucle: LOOP
            FETCH cur INTO v_prod, v_cant;
            IF done = 1 THEN LEAVE bucle; END IF;
            UPDATE inventario SET cantidad = cantidad + v_cant
             WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod;
            SELECT id INTO v_inv FROM inventario
             WHERE tienda_id = NEW.tienda_id AND producto_id = v_prod LIMIT 1;
            IF v_inv IS NOT NULL THEN
                INSERT INTO movimientos_inventario
                    (inventario_id, tipo, cantidad, motivo, ref_id, ref_tipo, created_at)
                VALUES (v_inv, 'entrada', v_cant,
                        CONCAT('Reversión venta anulada #', NEW.id),
                        NEW.id, 'ventas_anuladas', NOW());
            END IF;
        END LOOP;
        CLOSE cur;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_venta_detalle_descontar
DROP TRIGGER IF EXISTS `trg_venta_detalle_descontar`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_venta_detalle_descontar` AFTER INSERT ON `ventas_detalle` FOR EACH ROW BEGIN
    DECLARE v_tienda INT;
    DECLARE v_inv_id INT DEFAULT NULL;
    SELECT tienda_id INTO v_tienda FROM ventas WHERE id = NEW.venta_id LIMIT 1;
    IF v_tienda IS NOT NULL THEN
        UPDATE inventario SET cantidad = cantidad - NEW.cantidad
         WHERE tienda_id = v_tienda AND producto_id = NEW.producto_id;
        SELECT id INTO v_inv_id FROM inventario
         WHERE tienda_id = v_tienda AND producto_id = NEW.producto_id LIMIT 1;
        IF v_inv_id IS NOT NULL THEN
            INSERT INTO movimientos_inventario
                (inventario_id, tipo, cantidad, motivo, ref_id, ref_tipo, created_at)
            VALUES (v_inv_id, 'salida', NEW.cantidad,
                    CONCAT('Venta #', NEW.venta_id), NEW.venta_id, 'ventas', NOW());
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador mega_uni_store.trg_venta_no_delete
DROP TRIGGER IF EXISTS `trg_venta_no_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_venta_no_delete` BEFORE DELETE ON `ventas` FOR EACH ROW BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'DELETE físico no permitido en ventas. Use: UPDATE ventas SET estado = "anulada", deleted_at = NOW() WHERE id = X';
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para vista mega_uni_store.v_clientes_activos
DROP VIEW IF EXISTS `v_clientes_activos`;
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_clientes_activos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_clientes_activos` AS select `clientes`.`id` AS `id`,`clientes`.`nombre` AS `nombre`,`clientes`.`apellido` AS `apellido`,`clientes`.`email` AS `email`,`clientes`.`telefono` AS `telefono`,`clientes`.`tipo_documento` AS `tipo_documento`,`clientes`.`numero_documento` AS `numero_documento`,`clientes`.`direccion` AS `direccion`,`clientes`.`created_at` AS `created_at`,`clientes`.`deleted_at` AS `deleted_at` from `clientes` where (`clientes`.`deleted_at` is null);

-- Volcando estructura para vista mega_uni_store.v_empleados_activos
DROP VIEW IF EXISTS `v_empleados_activos`;
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_empleados_activos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_empleados_activos` AS select `empleados`.`id` AS `id`,`empleados`.`usuario_id` AS `usuario_id`,`empleados`.`tienda_id` AS `tienda_id`,`empleados`.`codigo_empleado` AS `codigo_empleado`,`empleados`.`fecha_ingreso` AS `fecha_ingreso`,`empleados`.`salario_base` AS `salario_base`,`empleados`.`estado` AS `estado`,`empleados`.`deleted_at` AS `deleted_at` from `empleados` where ((`empleados`.`deleted_at` is null) and (`empleados`.`estado` = 'activo'));

-- Volcando estructura para vista mega_uni_store.v_productos_activos
DROP VIEW IF EXISTS `v_productos_activos`;
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_productos_activos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_productos_activos` AS select `productos`.`id` AS `id`,`productos`.`nombre` AS `nombre`,`productos`.`descripcion` AS `descripcion`,`productos`.`codigo_barras` AS `codigo_barras`,`productos`.`imagen_url` AS `imagen_url`,`productos`.`categoria_id` AS `categoria_id`,`productos`.`unidad_medida_id` AS `unidad_medida_id`,`productos`.`estado` AS `estado`,`productos`.`deleted_at` AS `deleted_at`,`productos`.`created_at` AS `created_at` from `productos` where ((`productos`.`deleted_at` is null) and (`productos`.`estado` = 1));

-- Volcando estructura para vista mega_uni_store.v_tiendas_activas
DROP VIEW IF EXISTS `v_tiendas_activas`;
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_tiendas_activas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_tiendas_activas` AS select `tiendas`.`id` AS `id`,`tiendas`.`nombre` AS `nombre`,`tiendas`.`descripcion` AS `descripcion`,`tiendas`.`logo_url` AS `logo_url`,`tiendas`.`direccion` AS `direccion`,`tiendas`.`telefono` AS `telefono`,`tiendas`.`email` AS `email`,`tiendas`.`propietario_id` AS `propietario_id`,`tiendas`.`plataforma_id` AS `plataforma_id`,`tiendas`.`estado` AS `estado`,`tiendas`.`deleted_at` AS `deleted_at`,`tiendas`.`created_at` AS `created_at` from `tiendas` where ((`tiendas`.`deleted_at` is null) and (`tiendas`.`estado` = 1));

-- Volcando estructura para vista mega_uni_store.v_usuarios_activos
DROP VIEW IF EXISTS `v_usuarios_activos`;
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_usuarios_activos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_usuarios_activos` AS select `usuarios`.`id` AS `id`,`usuarios`.`nombre` AS `nombre`,`usuarios`.`apellido` AS `apellido`,`usuarios`.`email` AS `email`,`usuarios`.`password_hash` AS `password_hash`,`usuarios`.`telefono` AS `telefono`,`usuarios`.`avatar_url` AS `avatar_url`,`usuarios`.`estado` AS `estado`,`usuarios`.`deleted_at` AS `deleted_at`,`usuarios`.`created_at` AS `created_at` from `usuarios` where ((`usuarios`.`deleted_at` is null) and (`usuarios`.`estado` = 1));

-- Volcando estructura para vista mega_uni_store.v_ventas_validas
DROP VIEW IF EXISTS `v_ventas_validas`;
-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_ventas_validas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_ventas_validas` AS select `ventas`.`id` AS `id`,`ventas`.`tienda_id` AS `tienda_id`,`ventas`.`cliente_id` AS `cliente_id`,`ventas`.`empleado_id` AS `empleado_id`,`ventas`.`caja_id` AS `caja_id`,`ventas`.`fecha` AS `fecha`,`ventas`.`subtotal` AS `subtotal`,`ventas`.`descuento` AS `descuento`,`ventas`.`impuesto` AS `impuesto`,`ventas`.`total` AS `total`,`ventas`.`estado` AS `estado`,`ventas`.`deleted_at` AS `deleted_at`,`ventas`.`created_at` AS `created_at` from `ventas` where ((`ventas`.`deleted_at` is null) and (`ventas`.`estado` <> 'anulada'));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
