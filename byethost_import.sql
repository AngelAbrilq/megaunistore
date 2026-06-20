-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: mega_uni_store
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `mega_uni_store`
--



--
-- Table structure for table `aportes_seguridad_social`
--

DROP TABLE IF EXISTS `aportes_seguridad_social`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aportes_seguridad_social` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aportes_seguridad_social`
--

LOCK TABLES `aportes_seguridad_social` WRITE;
/*!40000 ALTER TABLE `aportes_seguridad_social` DISABLE KEYS */;
/*!40000 ALTER TABLE `aportes_seguridad_social` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asientos_contables`
--

DROP TABLE IF EXISTS `asientos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos_contables` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asientos_contables`
--

LOCK TABLES `asientos_contables` WRITE;
/*!40000 ALTER TABLE `asientos_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `asientos_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asientos_detalle`
--

DROP TABLE IF EXISTS `asientos_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos_detalle` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asientos_detalle`
--

LOCK TABLES `asientos_detalle` WRITE;
/*!40000 ALTER TABLE `asientos_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `asientos_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atributos`
--

DROP TABLE IF EXISTS `atributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atributos`
--

LOCK TABLES `atributos` WRITE;
/*!40000 ALTER TABLE `atributos` DISABLE KEYS */;
/*!40000 ALTER TABLE `atributos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (3,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 10.00}','{\"cantidad\": 15.00}',NULL,'2026-04-27 15:36:34'),(4,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 15.00}','{\"cantidad\": 4.00}',NULL,'2026-04-27 15:38:08'),(5,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 4.00}','{\"cantidad\": 14.00}',NULL,'2026-04-27 15:45:38'),(6,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 14.00}','{\"cantidad\": 11.00}',NULL,'2026-04-27 15:46:12'),(7,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 11.00}','{\"cantidad\": 13.00}',NULL,'2026-04-27 16:15:38'),(8,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 13.00}','{\"cantidad\": 12.00}',NULL,'2026-04-27 16:32:16'),(9,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 12.00}','{\"cantidad\": 12.00}',NULL,'2026-04-27 16:32:16'),(10,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 12.00}','{\"cantidad\": 5.00}',NULL,'2026-04-27 16:39:10'),(11,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 5.00}','{\"cantidad\": 5.00}',NULL,'2026-04-27 16:39:10'),(12,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 5.00}','{\"cantidad\": 2.00}',NULL,'2026-04-29 12:50:57'),(13,NULL,1,'ventas','UPDATE',3,'{\"estado\": \"completada\"}','{\"estado\": \"anulada\"}',NULL,'2026-04-29 12:58:46'),(14,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 2.00}','{\"cantidad\": 5.00}',NULL,'2026-04-29 12:58:46'),(15,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 5.00}','{\"cantidad\": 4.00}',NULL,'2026-04-29 13:06:11'),(16,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 4.00}','{\"cantidad\": 5.00}',NULL,'2026-04-29 13:16:30'),(17,NULL,1,'ventas','UPDATE',4,'{\"estado\": \"completada\"}','{\"estado\": \"anulada\"}',NULL,'2026-04-29 13:16:30'),(18,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 5.00}','{\"cantidad\": 6.00}',NULL,'2026-04-29 13:16:30'),(19,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 6.00}','{\"cantidad\": 1.00}',NULL,'2026-04-29 17:13:59'),(20,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 1.00}','{\"cantidad\": 9.00}',NULL,'2026-04-29 17:41:39'),(21,NULL,NULL,'inventario','UPDATE',1,'{\"cantidad\": 9.00}','{\"cantidad\": 5.00}',NULL,'2026-04-29 17:43:13');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cajas`
--

DROP TABLE IF EXISTS `cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_caja_tienda` (`tienda_id`),
  CONSTRAINT `fk_caja_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cajas`
--

LOCK TABLES `cajas` WRITE;
/*!40000 ALTER TABLE `cajas` DISABLE KEYS */;
INSERT INTO `cajas` VALUES (1,1,'Caja Principal','Caja principal del punto de venta.',1);
/*!40000 ALTER TABLE `cajas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cajas_movimientos`
--

DROP TABLE IF EXISTS `cajas_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas_movimientos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cajas_movimientos`
--

LOCK TABLES `cajas_movimientos` WRITE;
/*!40000 ALTER TABLE `cajas_movimientos` DISABLE KEYS */;
INSERT INTO `cajas_movimientos` VALUES (1,1,NULL,'apertura',50000.00,NULL,NULL,'Apertura de turno inicial.',NULL,'2026-04-27 19:23:49'),(2,1,NULL,'cierre',50000.00,50000.00,0.00,'Cierre de caja',NULL,'2026-04-27 19:26:06'),(3,1,NULL,'apertura',0.00,NULL,NULL,'Apertura de caja',NULL,'2026-04-27 19:26:54'),(4,1,NULL,'ingreso',10000.00,NULL,NULL,'Ingreso manual de prueba.',NULL,'2026-04-27 19:27:52'),(5,1,NULL,'cierre',10000.00,10000.00,0.00,'Cierre de caja',NULL,'2026-04-27 19:28:18'),(6,1,NULL,'apertura',0.00,NULL,NULL,'Apertura de caja',NULL,'2026-04-29 12:43:27'),(7,1,NULL,'cierre',0.00,0.00,0.00,'Cierre de caja',NULL,'2026-04-29 12:48:06'),(8,1,NULL,'apertura',0.00,NULL,NULL,'Apertura de caja',NULL,'2026-04-29 12:49:24'),(9,1,NULL,'ingreso',12495.00,NULL,NULL,'Ingreso por venta #3',3,'2026-04-29 12:50:57'),(10,1,NULL,'egreso',12495.00,NULL,NULL,'Egreso por anulación de venta #3',3,'2026-04-29 12:58:46'),(11,1,NULL,'ingreso',4165.00,NULL,NULL,'Ingreso por venta #4',4,'2026-04-29 13:06:11'),(12,1,NULL,'egreso',4165.00,NULL,NULL,'Egreso por anulación de venta #4',4,'2026-04-29 13:16:30'),(13,1,NULL,'ingreso',20825.00,NULL,NULL,'Ingreso por venta #5',5,'2026-04-29 17:13:59');
/*!40000 ALTER TABLE `cajas_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos`
--

LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Bebidas','Productos líquidos para consumo.',NULL,NULL,1,NULL),(2,'Gaseosas y refresco','Bebidas gaseosas, refrescos y similares.',1,NULL,1,NULL),(3,'Categoría para eliminar.','Categoría para eliminar.',NULL,NULL,0,'2026-04-27 01:28:07');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `centros_costo`
--

DROP TABLE IF EXISTS `centros_costo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `centros_costo` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `centros_costo`
--

LOCK TABLES `centros_costo` WRITE;
/*!40000 ALTER TABLE `centros_costo` DISABLE KEYS */;
/*!40000 ALTER TABLE `centros_costo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `compras_detalle`
--

DROP TABLE IF EXISTS `compras_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_detalle` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras_detalle`
--

LOCK TABLES `compras_detalle` WRITE;
/*!40000 ALTER TABLE `compras_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `compras_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conceptos_nomina`
--

DROP TABLE IF EXISTS `conceptos_nomina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conceptos_nomina` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conceptos_nomina`
--

LOCK TABLES `conceptos_nomina` WRITE;
/*!40000 ALTER TABLE `conceptos_nomina` DISABLE KEYS */;
/*!40000 ALTER TABLE `conceptos_nomina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conciliaciones`
--

DROP TABLE IF EXISTS `conciliaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conciliaciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conciliaciones`
--

LOCK TABLES `conciliaciones` WRITE;
/*!40000 ALTER TABLE `conciliaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `conciliaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contratos`
--

DROP TABLE IF EXISTS `contratos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contratos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contratos`
--

LOCK TABLES `contratos` WRITE;
/*!40000 ALTER TABLE `contratos` DISABLE KEYS */;
/*!40000 ALTER TABLE `contratos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_contables`
--

DROP TABLE IF EXISTS `cuentas_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_contables` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_contables`
--

LOCK TABLES `cuentas_contables` WRITE;
/*!40000 ALTER TABLE `cuentas_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cupones`
--

DROP TABLE IF EXISTS `cupones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cupones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cupones`
--

LOCK TABLES `cupones` WRITE;
/*!40000 ALTER TABLE `cupones` DISABLE KEYS */;
/*!40000 ALTER TABLE `cupones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devoluciones`
--

DROP TABLE IF EXISTS `devoluciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devoluciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devoluciones`
--

LOCK TABLES `devoluciones` WRITE;
/*!40000 ALTER TABLE `devoluciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `devoluciones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `devoluciones_detalle`
--

DROP TABLE IF EXISTS `devoluciones_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devoluciones_detalle` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devoluciones_detalle`
--

LOCK TABLES `devoluciones_detalle` WRITE;
/*!40000 ALTER TABLE `devoluciones_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `devoluciones_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados`
--

DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados_areas`
--

DROP TABLE IF EXISTS `empleados_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados_areas` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados_areas`
--

LOCK TABLES `empleados_areas` WRITE;
/*!40000 ALTER TABLE `empleados_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleados_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados_cargos`
--

DROP TABLE IF EXISTS `empleados_cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados_cargos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados_cargos`
--

LOCK TABLES `empleados_cargos` WRITE;
/*!40000 ALTER TABLE `empleados_cargos` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleados_cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados_horarios`
--

DROP TABLE IF EXISTS `empleados_horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados_horarios` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados_horarios`
--

LOCK TABLES `empleados_horarios` WRITE;
/*!40000 ALTER TABLE `empleados_horarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleados_horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `envios_reporte`
--

DROP TABLE IF EXISTS `envios_reporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `envios_reporte` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `envios_reporte`
--

LOCK TABLES `envios_reporte` WRITE;
/*!40000 ALTER TABLE `envios_reporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `envios_reporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exportaciones`
--

DROP TABLE IF EXISTS `exportaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exportaciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exportaciones`
--

LOCK TABLES `exportaciones` WRITE;
/*!40000 ALTER TABLE `exportaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `exportaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gastos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
/*!40000 ALTER TABLE `gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horarios`
--

DROP TABLE IF EXISTS `horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_hor_tienda` (`tienda_id`),
  CONSTRAINT `fk_hor_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios`
--

LOCK TABLES `horarios` WRITE;
/*!40000 ALTER TABLE `horarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horas_extra`
--

DROP TABLE IF EXISTS `horas_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horas_extra` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horas_extra`
--

LOCK TABLES `horas_extra` WRITE;
/*!40000 ALTER TABLE `horas_extra` DISABLE KEYS */;
/*!40000 ALTER TABLE `horas_extra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `impuestos`
--

DROP TABLE IF EXISTS `impuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `impuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tipo` varchar(50) NOT NULL DEFAULT 'porcentaje',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `impuestos`
--

LOCK TABLES `impuestos` WRITE;
/*!40000 ALTER TABLE `impuestos` DISABLE KEYS */;
INSERT INTO `impuestos` VALUES (1,'IVA 19%','Impuesto al valor agregado general.',19.00,'Ventas',1),(2,'IVA 5%','Tarifa reducida aplicable a productos específicos.',5.00,'Ventas',0),(3,'Impoconsumo 8%','Impuesto nacional al consumo.',8.00,'Consumo',0);
/*!40000 ALTER TABLE `impuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
INSERT INTO `inventario` VALUES (1,1,1,5.00,3.00,50.00,'Bodega A - Estante 1','2026-04-29 17:43:13');
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `metodos_pago`
--

DROP TABLE IF EXISTS `metodos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metodos_pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metodos_pago`
--

LOCK TABLES `metodos_pago` WRITE;
/*!40000 ALTER TABLE `metodos_pago` DISABLE KEYS */;
INSERT INTO `metodos_pago` VALUES (1,'Efectivo','Pago en efectivo en punto de venta.',1),(2,'Transferencia','Pago por transferencia bancaria.',1),(3,'Tarjeta débito','Pago con tarjeta débito.',1),(4,'Tarjeta crédito','Pago con tarjeta crédito.',1);
/*!40000 ALTER TABLE `metodos_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_inventario` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (1,1,'entrada',5.00,'Compra inicial a proveedor',NULL,NULL,NULL,'2026-04-27 15:36:34'),(2,1,'ajuste',4.00,'Conteo físico',NULL,NULL,NULL,'2026-04-27 15:38:08'),(3,1,'entrada',10.00,'Reposición para prueba de salida',NULL,NULL,NULL,'2026-04-27 15:45:38'),(4,1,'salida',3.00,'prueba de resta',NULL,NULL,NULL,'2026-04-27 15:46:12'),(5,1,'entrada',2.00,'Reposición realizada por bodeguero',NULL,NULL,NULL,'2026-04-27 16:15:38'),(6,1,'salida',1.00,'Venta #1',NULL,1,'ventas','2026-04-27 16:32:16'),(7,1,'salida',1.00,'Venta #1',NULL,1,'ventas','2026-04-27 16:32:16'),(8,1,'salida',7.00,'Venta #2',NULL,2,'ventas','2026-04-27 16:39:10'),(9,1,'salida',7.00,'Venta #2',NULL,2,'ventas','2026-04-27 16:39:10'),(10,1,'salida',3.00,'Venta #3',NULL,3,'ventas','2026-04-29 12:50:57'),(11,1,'entrada',3.00,'Reversión venta anulada #3',NULL,3,'ventas_anuladas','2026-04-29 12:58:46'),(12,1,'salida',1.00,'Venta #4',NULL,4,'ventas','2026-04-29 13:06:11'),(13,1,'entrada',1.00,'Anulación de venta #4',NULL,4,'ventas','2026-04-29 13:16:30'),(14,1,'entrada',1.00,'Reversión venta anulada #4',NULL,4,'ventas_anuladas','2026-04-29 13:16:30'),(15,1,'salida',5.00,'Venta #5',NULL,5,'ventas','2026-04-29 17:13:59');
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nomina_detalle`
--

DROP TABLE IF EXISTS `nomina_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomina_detalle` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nomina_detalle`
--

LOCK TABLES `nomina_detalle` WRITE;
/*!40000 ALTER TABLE `nomina_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `nomina_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nomina_empleado`
--

DROP TABLE IF EXISTS `nomina_empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomina_empleado` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nomina_empleado`
--

LOCK TABLES `nomina_empleado` WRITE;
/*!40000 ALTER TABLE `nomina_empleado` DISABLE KEYS */;
/*!40000 ALTER TABLE `nomina_empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nominas`
--

DROP TABLE IF EXISTS `nominas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nominas` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nominas`
--

LOCK TABLES `nominas` WRITE;
/*!40000 ALTER TABLE `nominas` DISABLE KEYS */;
/*!40000 ALTER TABLE `nominas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,1,1,'Stock bajo','\"Coca Cola 400mllll\" bajo mínimo. Cantidad: 4.00','warning',0,NULL,'2026-04-27 15:38:08'),(2,1,1,'Stock bajo','\"Coca Cola 400mllll\" bajo mínimo. Cantidad: 2.00','warning',0,NULL,'2026-04-29 12:50:57'),(3,1,1,'Stock bajo','\"Coca Cola 400mllll\" bajo mínimo. Cantidad: 4.00','warning',0,NULL,'2026-04-29 13:06:11'),(4,1,1,'Stock bajo','\"Coca Cola 400mllll\" bajo mínimo. Cantidad: 1.00','warning',0,NULL,'2026-04-29 17:13:59');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (1,1,1,4165.00,'Venta prueba 001','aprobado','2026-04-27 16:32:16'),(2,2,3,29155.00,'Venta prueba 002','aprobado','2026-04-27 16:39:10'),(3,3,1,12495.00,'Prueba venta con caja abierta','rechazado','2026-04-29 12:50:57'),(4,4,1,4165.00,'Prueba anulación inventario','rechazado','2026-04-29 13:06:11'),(5,5,4,20825.00,'osman','aprobado','2026-04-29 17:13:59');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_contables`
--

DROP TABLE IF EXISTS `periodos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_contables` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos_contables`
--

LOCK TABLES `periodos_contables` WRITE;
/*!40000 ALTER TABLE `periodos_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `periodos_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `modulo` varchar(80) NOT NULL,
  `accion` varchar(80) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'Ver dashboard','dashboard','dashboard.view','Permite acceder al panel principal del sistema.'),(2,'Ver tiendas','tiendas','tiendas.view','Permite consultar tiendas registradas.'),(3,'Crear tiendas','tiendas','tiendas.create','Permite crear nuevas tiendas.'),(4,'Editar tiendas','tiendas','tiendas.update','Permite actualizar información de tiendas.'),(5,'Cambiar estado de tiendas','tiendas','tiendas.toggle','Permite activar o desactivar tiendas.'),(6,'Eliminar tiendas','tiendas','tiendas.delete','Permite eliminar tiendas de forma lógica.'),(7,'Ver usuarios','usuarios','usuarios.view','Permite consultar usuarios registrados.'),(8,'Crear usuarios','usuarios','usuarios.create','Permite crear usuarios administrativos.'),(9,'Editar usuarios','usuarios','usuarios.update','Permite actualizar información de usuarios.'),(10,'Asignar roles a usuarios','usuarios','usuarios.roles.assign','Permite asignar roles globales o por tienda.'),(11,'Cambiar estado de usuarios','usuarios','usuarios.toggle','Permite activar o desactivar usuarios.'),(12,'Eliminar usuarios','usuarios','usuarios.delete','Permite eliminar usuarios de forma lógica.'),(13,'Ver productos','productos','productos.view','Permite consultar productos.'),(14,'Crear productos','productos','productos.create','Permite registrar productos.'),(15,'Editar productos','productos','productos.update','Permite actualizar productos.'),(16,'Eliminar productos','productos','productos.delete','Permite eliminar productos de forma lógica.'),(17,'Ver inventario','inventario','inventario.view','Permite consultar existencias de inventario.'),(18,'Mover inventario','inventario','inventario.move','Permite registrar entradas, salidas y ajustes de inventario.'),(19,'Ver alertas de stock','inventario','inventario.alerts','Permite consultar alertas de stock mínimo.'),(20,'Ver ventas','ventas','ventas.view','Permite consultar ventas.'),(21,'Crear ventas','ventas','ventas.create','Permite registrar ventas.'),(22,'Anular ventas','ventas','ventas.cancel','Permite anular ventas bajo reglas del sistema.'),(23,'Ver caja','caja','caja.view','Permite consultar caja y movimientos.'),(24,'Gestionar caja','caja','caja.manage','Permite abrir, cerrar y registrar movimientos de caja.'),(25,'Ver reportes','reportes','reportes.view','Permite consultar reportes.'),(26,'Exportar reportes','reportes','reportes.export','Permite exportar reportes en PDF, Excel o CSV.'),(27,'Ver empleados','rrhh','empleados.view','Permite consultar empleados.'),(28,'Gestionar empleados','rrhh','empleados.manage','Permite crear y actualizar empleados.'),(29,'Ver nómina','nomina','nomina.view','Permite consultar nómina.'),(30,'Gestionar nómina','nomina','nomina.manage','Permite procesar y administrar nómina.'),(31,'Ver catálogo cliente','catalogo','catalogo.view','Permite consultar catálogo público o de cliente.'),(32,'Gestionar pedidos propios','pedidos','pedidos.own.manage','Permite al cliente gestionar sus propios pedidos.'),(33,'Gestionar perfil propio','perfil','perfil.own.manage','Permite al usuario gestionar su propio perfil.'),(34,'Calificar productos','feedback','feedback.create','Permite crear reseñas o calificaciones.'),(35,'Ver auditoría','auditoria','auditoria.view','Permite consultar trazabilidad y logs del sistema.'),(36,'Gestionar notificaciones','notificaciones','notificaciones.manage','Permite gestionar alertas y notificaciones internas.'),(37,'Gestionar respaldos','sistema','backups.manage','Permite gestionar respaldos y tareas automáticas.');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planes`
--

DROP TABLE IF EXISTS `planes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planes` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planes`
--

LOCK TABLES `planes` WRITE;
/*!40000 ALTER TABLE `planes` DISABLE KEYS */;
/*!40000 ALTER TABLE `planes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plataforma`
--

DROP TABLE IF EXISTS `plataforma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plataforma` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `config_json` json DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plataforma`
--

LOCK TABLES `plataforma` WRITE;
/*!40000 ALTER TABLE `plataforma` DISABLE KEYS */;
INSERT INTO `plataforma` VALUES (1,'MultiStore Platform',NULL,NULL,1,'2026-03-16 13:58:23');
/*!40000 ALTER TABLE `plataforma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestaciones_sociales`
--

DROP TABLE IF EXISTS `prestaciones_sociales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prestaciones_sociales` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestaciones_sociales`
--

LOCK TABLES `prestaciones_sociales` WRITE;
/*!40000 ALTER TABLE `prestaciones_sociales` DISABLE KEYS */;
/*!40000 ALTER TABLE `prestaciones_sociales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuesto_detalle`
--

DROP TABLE IF EXISTS `presupuesto_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presupuesto_detalle` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuesto_detalle`
--

LOCK TABLES `presupuesto_detalle` WRITE;
/*!40000 ALTER TABLE `presupuesto_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `presupuesto_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presupuestos`
--

DROP TABLE IF EXISTS `presupuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presupuestos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presupuestos`
--

LOCK TABLES `presupuestos` WRITE;
/*!40000 ALTER TABLE `presupuestos` DISABLE KEYS */;
/*!40000 ALTER TABLE `presupuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Coca Cola 400mllll','Bebida gaseosa personal.','7701234567890',NULL,2,1,1,NULL,'2026-04-27 05:22:18','2026-04-29 17:37:06',1,1),(2,'Carne','Carne deliciosa','123456789',NULL,NULL,2,1,NULL,'2026-05-08 18:55:21',NULL,1,1),(3,'cabeza extragrande de hierro frente en alto','mil metros cuadrados','3636353',NULL,NULL,4,1,NULL,'2026-05-09 01:49:10',NULL,1,1),(4,'cabeza extragrande de hierro frente en alto',NULL,'36363535',NULL,1,2,1,NULL,'2026-05-13 13:49:14',NULL,1,1),(5,'HELGORDO','HELADO PARA GORDOS LLENOS DE GRASA COMO BREYNER CAGANZA',NULL,NULL,NULL,NULL,1,NULL,'2026-05-20 14:45:11',NULL,1,1);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_atributos`
--

DROP TABLE IF EXISTS `productos_atributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_atributos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_atributos`
--

LOCK TABLES `productos_atributos` WRITE;
/*!40000 ALTER TABLE `productos_atributos` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos_atributos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_impuestos`
--

DROP TABLE IF EXISTS `productos_impuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_impuestos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_impuestos`
--

LOCK TABLES `productos_impuestos` WRITE;
/*!40000 ALTER TABLE `productos_impuestos` DISABLE KEYS */;
INSERT INTO `productos_impuestos` VALUES (1,1,1,1),(4,2,1,1),(5,3,1,1),(6,4,1,1),(7,5,1,1);
/*!40000 ALTER TABLE `productos_impuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_proveedores`
--

DROP TABLE IF EXISTS `productos_proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_proveedores` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_proveedores`
--

LOCK TABLES `productos_proveedores` WRITE;
/*!40000 ALTER TABLE `productos_proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos_proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes`
--

DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes`
--

LOCK TABLES `reportes` WRITE;
/*!40000 ALTER TABLE `reportes` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text,
  `nivel` int NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Superadministrador','Usuario raíz con control total sobre la plataforma, tiendas, usuarios, roles y configuración global.',1,1),(2,'Administrador de Tienda','Gestiona una tienda específica, su personal, productos, inventario, ventas y reportes.',2,1),(3,'Supervisor','Supervisa operación, cumplimiento de procesos, transacciones y actividades de la tienda.',3,1),(4,'Nómina y RRHH','Gestiona personal, novedades laborales, pagos, liquidaciones y reportes de productividad.',3,1),(5,'Vendedor','Registra ventas, atiende clientes, gestiona carrito, pagos y devoluciones operativas.',4,1),(6,'Bodeguero','Gestiona inventario, entradas, salidas, ajustes de stock y preparación de pedidos.',4,1),(7,'Reportero','Consulta, genera y exporta reportes de ventas, inventarios y desempeño operativo.',4,1),(8,'Cliente','Usuario final que navega, compra, paga, consulta historial y califica productos.',5,1),(9,'Sistema','Actor lógico para automatizaciones, alertas, respaldos, validaciones y eventos internos.',1,1);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_permisos`
--

DROP TABLE IF EXISTS `roles_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_permisos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permisos`
--

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
INSERT INTO `roles_permisos` VALUES (1,1,1,'2026-04-25 00:29:11'),(2,1,2,'2026-04-25 00:29:11'),(3,1,3,'2026-04-25 00:29:11'),(4,1,4,'2026-04-25 00:29:11'),(5,1,5,'2026-04-25 00:29:11'),(6,1,6,'2026-04-25 00:29:11'),(7,1,7,'2026-04-25 00:29:11'),(8,1,8,'2026-04-25 00:29:11'),(9,1,9,'2026-04-25 00:29:11'),(10,1,10,'2026-04-25 00:29:11'),(11,1,11,'2026-04-25 00:29:11'),(12,1,12,'2026-04-25 00:29:11'),(13,1,13,'2026-04-25 00:29:11'),(14,1,14,'2026-04-25 00:29:11'),(15,1,15,'2026-04-25 00:29:11'),(16,1,16,'2026-04-25 00:29:11'),(17,1,17,'2026-04-25 00:29:11'),(18,1,18,'2026-04-25 00:29:11'),(19,1,19,'2026-04-25 00:29:11'),(20,1,20,'2026-04-25 00:29:11'),(21,1,21,'2026-04-25 00:29:11'),(22,1,22,'2026-04-25 00:29:11'),(23,1,23,'2026-04-25 00:29:11'),(24,1,24,'2026-04-25 00:29:11'),(25,1,25,'2026-04-25 00:29:11'),(26,1,26,'2026-04-25 00:29:11'),(27,1,27,'2026-04-25 00:29:11'),(28,1,28,'2026-04-25 00:29:11'),(29,1,29,'2026-04-25 00:29:11'),(30,1,30,'2026-04-25 00:29:11'),(31,1,31,'2026-04-25 00:29:11'),(32,1,32,'2026-04-25 00:29:11'),(33,1,33,'2026-04-25 00:29:11'),(34,1,34,'2026-04-25 00:29:11'),(35,1,35,'2026-04-25 00:29:11'),(36,1,36,'2026-04-25 00:29:11'),(37,1,37,'2026-04-25 00:29:11'),(38,9,1,'2026-04-25 00:29:11'),(39,9,36,'2026-04-25 00:29:11'),(40,9,37,'2026-04-25 00:29:11'),(41,9,19,'2026-04-25 00:29:11'),(42,2,1,'2026-04-25 00:29:11'),(43,2,13,'2026-04-25 00:29:11'),(44,2,14,'2026-04-25 00:29:11'),(45,2,15,'2026-04-25 00:29:11'),(46,2,16,'2026-04-25 00:29:11'),(47,2,17,'2026-04-25 00:29:11'),(48,2,18,'2026-04-25 00:29:11'),(49,2,19,'2026-04-25 00:29:11'),(50,2,20,'2026-04-25 00:29:11'),(51,2,22,'2026-04-25 00:29:11'),(52,2,23,'2026-04-25 00:29:11'),(53,2,25,'2026-04-25 00:29:11'),(54,2,26,'2026-04-25 00:29:11'),(55,2,27,'2026-04-25 00:29:11'),(56,2,28,'2026-04-25 00:29:11'),(57,2,7,'2026-04-25 00:29:11'),(58,3,1,'2026-04-25 00:29:11'),(59,3,13,'2026-04-25 00:29:11'),(60,3,17,'2026-04-25 00:29:11'),(61,3,19,'2026-04-25 00:29:11'),(62,3,20,'2026-04-25 00:29:11'),(63,3,22,'2026-04-25 00:29:11'),(64,3,23,'2026-04-25 00:29:11'),(65,3,25,'2026-04-25 00:29:11'),(66,5,1,'2026-04-25 00:29:11'),(67,5,13,'2026-04-25 00:29:11'),(68,5,17,'2026-04-25 00:29:11'),(69,5,20,'2026-04-25 00:29:11'),(70,5,21,'2026-04-25 00:29:11'),(71,5,23,'2026-04-25 00:29:11'),(73,6,1,'2026-04-25 00:29:11'),(74,6,13,'2026-04-25 00:29:11'),(75,6,17,'2026-04-25 00:29:11'),(76,6,18,'2026-04-25 00:29:11'),(77,6,19,'2026-04-25 00:29:11'),(78,7,1,'2026-04-25 00:29:11'),(79,7,25,'2026-04-25 00:29:11'),(80,7,26,'2026-04-25 00:29:11'),(81,7,20,'2026-04-25 00:29:11'),(82,7,17,'2026-04-25 00:29:11'),(83,4,1,'2026-04-25 00:29:11'),(84,4,27,'2026-04-25 00:29:11'),(85,4,28,'2026-04-25 00:29:11'),(86,4,29,'2026-04-25 00:29:11'),(87,4,30,'2026-04-25 00:29:11'),(88,4,25,'2026-04-25 00:29:11'),(89,8,1,'2026-04-25 00:29:11'),(90,8,31,'2026-04-25 00:29:11'),(91,8,32,'2026-04-25 00:29:11'),(92,8,33,'2026-04-25 00:29:11'),(93,8,34,'2026-04-25 00:29:11'),(94,2,21,'2026-05-06 02:12:33'),(95,2,24,'2026-05-06 02:12:33'),(96,3,21,'2026-05-06 02:12:33'),(97,3,24,'2026-05-06 02:12:33'),(98,5,24,'2026-05-06 02:12:33');
/*!40000 ALTER TABLE `roles_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones`
--

DROP TABLE IF EXISTS `sesiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones`
--

LOCK TABLES `sesiones` WRITE;
/*!40000 ALTER TABLE `sesiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_cambio_contrasena`
--

DROP TABLE IF EXISTS `solicitudes_cambio_contrasena`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_cambio_contrasena` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_cambio_contrasena`
--

LOCK TABLES `solicitudes_cambio_contrasena` WRITE;
/*!40000 ALTER TABLE `solicitudes_cambio_contrasena` DISABLE KEYS */;
INSERT INTO `solicitudes_cambio_contrasena` VALUES (1,3,'$2y$10$4u5zV5uY/gXOCCyNQVwvG.ixOFxlJ37M.Kjv1gmzotjDlDdXSGIk6','aprobada',1,NULL,'2026-05-25 13:10:46','2026-05-25 13:14:14');
/*!40000 ALTER TABLE `solicitudes_cambio_contrasena` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suscripciones`
--

DROP TABLE IF EXISTS `suscripciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suscripciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suscripciones`
--

LOCK TABLES `suscripciones` WRITE;
/*!40000 ALTER TABLE `suscripciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `suscripciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiendas`
--

DROP TABLE IF EXISTS `tiendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiendas` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiendas`
--

LOCK TABLES `tiendas` WRITE;
/*!40000 ALTER TABLE `tiendas` DISABLE KEYS */;
INSERT INTO `tiendas` VALUES (1,'Mega Uni Store Principal','Tienda principal del ecosistema Mega_Uni_Store',NULL,'Carrera 5 # 12-34','3001234567','principal@megaunistore.com',1,1,1,NULL,'2026-04-24 19:29:43','2026-05-06 16:44:06',1),(2,'Nueva Tienda','Descripción de prueba',NULL,NULL,NULL,NULL,1,1,1,NULL,'2026-04-25 00:21:32','2026-05-06 16:44:10',1),(3,'Nueva Tienda','Descripción de prueba',NULL,NULL,NULL,NULL,1,1,1,NULL,'2026-04-25 00:21:45','2026-05-06 16:44:12',1),(4,'PIRULIN2.0','vendemos cabezas grandes para jugar futbol',NULL,'Huila, Hobo, Carrera 4 #5-35 Barrio San fernado','3144817006','angelnicolasabrilq@gmail.com',1,1,1,NULL,'2026-05-09 01:39:33',NULL,1),(5,'POPOCHONGO','HELADOS Y TUSSI','xhttps://p16-comment-sign-sg.tiktokcdn.com/tos-alisg-i-zt8igodiya-sg/8a3e389efc1d46af90d402eb5743c0df~tplv-jj85edgx6n-image-origin.jpeg?dr=8569&refresh_token=761509b8&x-expires=1781884800&x-signature=CMcUSCsCA0%2F%2FrqN%2F%2BAcEuMhlq90%3D&t=67a6c45e&ps=a0','CR 45 353-06','3125631204',NULL,1,1,1,NULL,'2026-05-20 14:42:38','2026-05-24 15:45:52',1);
/*!40000 ALTER TABLE `tiendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiendas_clientes`
--

DROP TABLE IF EXISTS `tiendas_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiendas_clientes` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiendas_clientes`
--

LOCK TABLES `tiendas_clientes` WRITE;
/*!40000 ALTER TABLE `tiendas_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tiendas_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiendas_config`
--

DROP TABLE IF EXISTS `tiendas_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiendas_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tienda_id` int NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text,
  `tipo` varchar(50) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tienda_clave` (`tienda_id`,`clave`),
  CONSTRAINT `fk_tc_tienda` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiendas_config`
--

LOCK TABLES `tiendas_config` WRITE;
/*!40000 ALTER TABLE `tiendas_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `tiendas_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiendas_productos`
--

DROP TABLE IF EXISTS `tiendas_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiendas_productos` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiendas_productos`
--

LOCK TABLES `tiendas_productos` WRITE;
/*!40000 ALTER TABLE `tiendas_productos` DISABLE KEYS */;
INSERT INTO `tiendas_productos` VALUES (1,1,1,3500.00,2500.00,1),(4,1,2,15000.00,20000.00,1),(5,4,3,100000.00,50000.00,1),(6,4,4,100000.00,50000.00,1),(7,5,5,3500.00,4500.00,1);
/*!40000 ALTER TABLE `tiendas_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turnos`
--

DROP TABLE IF EXISTS `turnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `horario_id` int NOT NULL,
  `dia_semana` tinyint(1) NOT NULL COMMENT '1=Lun ... 7=Dom',
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_turno_horario` (`horario_id`),
  CONSTRAINT `fk_turno_horario` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turnos`
--

LOCK TABLES `turnos` WRITE;
/*!40000 ALTER TABLE `turnos` DISABLE KEYS */;
/*!40000 ALTER TABLE `turnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades_medida`
--

DROP TABLE IF EXISTS `unidades_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades_medida` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `simbolo` varchar(10) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades_medida`
--

LOCK TABLES `unidades_medida` WRITE;
/*!40000 ALTER TABLE `unidades_medida` DISABLE KEYS */;
INSERT INTO `unidades_medida` VALUES (1,'Unidad','und','Unidad'),(2,'Kilogramo','Kg','Peso'),(3,'Litro','L','Volumen'),(4,'Metro','m','Longitud');
/*!40000 ALTER TABLE `unidades_medida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Admin','Principal','admin@megaunistore.com','$2y$10$IrQnUXOHrkZEMa3dJOF/Ue84jw/lE/bb6Ruhz1TCZVzaWKxes8mp2',NULL,NULL,1,NULL,'2026-04-24 18:12:07'),(2,'A\'ngel A\'bril','Abril Quimbaya','angelnicolasabrilq@gmail.com','$2y$10$v1G/RNAL5kItzIGmClZHneWP4hIVgALG9IIfhXeV1WjSeWjDaQqSe','3144817006',NULL,0,'2026-04-25 00:21:03','2026-04-25 00:12:13'),(3,'Carlos','Vendedor','vendedor1@megaunistore.com','$2y$10$4u5zV5uY/gXOCCyNQVwvG.ixOFxlJ37M.Kjv1gmzotjDlDdXSGIk6','30000000000',NULL,1,NULL,'2026-04-25 00:17:32'),(6,'A\'ngel A\'bril','Abril Quimbaya','angel@gmail.com','$2y$10$3BSdYQVYa.G/GFojXBYhDuih8ZmWKFXTYwMSqdYDN.dgeTCU6JrYy','3144817006',NULL,1,NULL,'2026-04-27 00:21:14'),(7,'Bruno','Bodeguero','bodeguero1@megaunistore.com','$2y$10$gajAD2J3in/oi7.iL9MKw.wMCK1z1MCj9xEKCez8bR5Sb5ed2xE1i','3001112233',NULL,1,NULL,'2026-04-27 15:50:58');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `usuarios_roles`
--

DROP TABLE IF EXISTS `usuarios_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios_roles` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_roles`
--

LOCK TABLES `usuarios_roles` WRITE;
/*!40000 ALTER TABLE `usuarios_roles` DISABLE KEYS */;
INSERT INTO `usuarios_roles` VALUES (1,1,1,NULL,'2026-04-24 18:17:35'),(2,3,5,1,'2026-04-25 00:17:32'),(3,6,8,NULL,'2026-04-27 00:21:14'),(4,7,6,1,'2026-04-27 15:50:58');
/*!40000 ALTER TABLE `usuarios_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_clientes_activos`
--

DROP TABLE IF EXISTS `v_clientes_activos`;
/*!50001 DROP VIEW IF EXISTS `v_clientes_activos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_clientes_activos` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `apellido`,
 1 AS `email`,
 1 AS `telefono`,
 1 AS `tipo_documento`,
 1 AS `numero_documento`,
 1 AS `direccion`,
 1 AS `created_at`,
 1 AS `deleted_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_empleados_activos`
--

DROP TABLE IF EXISTS `v_empleados_activos`;
/*!50001 DROP VIEW IF EXISTS `v_empleados_activos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_empleados_activos` AS SELECT 
 1 AS `id`,
 1 AS `usuario_id`,
 1 AS `tienda_id`,
 1 AS `codigo_empleado`,
 1 AS `fecha_ingreso`,
 1 AS `salario_base`,
 1 AS `estado`,
 1 AS `deleted_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_productos_activos`
--

DROP TABLE IF EXISTS `v_productos_activos`;
/*!50001 DROP VIEW IF EXISTS `v_productos_activos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_productos_activos` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `descripcion`,
 1 AS `codigo_barras`,
 1 AS `imagen_url`,
 1 AS `categoria_id`,
 1 AS `unidad_medida_id`,
 1 AS `estado`,
 1 AS `deleted_at`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_tiendas_activas`
--

DROP TABLE IF EXISTS `v_tiendas_activas`;
/*!50001 DROP VIEW IF EXISTS `v_tiendas_activas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_tiendas_activas` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `descripcion`,
 1 AS `logo_url`,
 1 AS `direccion`,
 1 AS `telefono`,
 1 AS `email`,
 1 AS `propietario_id`,
 1 AS `plataforma_id`,
 1 AS `estado`,
 1 AS `deleted_at`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_usuarios_activos`
--

DROP TABLE IF EXISTS `v_usuarios_activos`;
/*!50001 DROP VIEW IF EXISTS `v_usuarios_activos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_usuarios_activos` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `apellido`,
 1 AS `email`,
 1 AS `password_hash`,
 1 AS `telefono`,
 1 AS `avatar_url`,
 1 AS `estado`,
 1 AS `deleted_at`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_ventas_validas`
--

DROP TABLE IF EXISTS `v_ventas_validas`;
/*!50001 DROP VIEW IF EXISTS `v_ventas_validas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_ventas_validas` AS SELECT 
 1 AS `id`,
 1 AS `tienda_id`,
 1 AS `cliente_id`,
 1 AS `empleado_id`,
 1 AS `caja_id`,
 1 AS `fecha`,
 1 AS `subtotal`,
 1 AS `descuento`,
 1 AS `impuesto`,
 1 AS `total`,
 1 AS `estado`,
 1 AS `deleted_at`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vacaciones`
--

DROP TABLE IF EXISTS `vacaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vacaciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vacaciones`
--

LOCK TABLES `vacaciones` WRITE;
/*!40000 ALTER TABLE `vacaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `vacaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,1,NULL,NULL,NULL,NULL,'2026-04-27 16:32:16',3500.00,0.00,665.00,4165.00,'completada',NULL,'2026-04-27 16:32:16',NULL,3,3),(2,1,NULL,NULL,NULL,NULL,'2026-04-27 16:39:10',24500.00,0.00,4655.00,29155.00,'completada',NULL,'2026-04-27 16:39:10',NULL,3,3),(3,1,NULL,NULL,1,NULL,'2026-04-29 12:50:57',10500.00,0.00,1995.00,12495.00,'anulada',NULL,'2026-04-29 12:50:57','2026-04-29 12:58:46',1,1),(4,1,NULL,NULL,1,NULL,'2026-04-29 13:06:11',3500.00,0.00,665.00,4165.00,'anulada',NULL,'2026-04-29 13:06:11','2026-04-29 13:16:30',1,1),(5,1,NULL,NULL,1,NULL,'2026-04-29 17:13:59',17500.00,0.00,3325.00,20825.00,'completada',NULL,'2026-04-29 17:13:59',NULL,1,1);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ventas_cupones`
--

DROP TABLE IF EXISTS `ventas_cupones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas_cupones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas_cupones`
--

LOCK TABLES `ventas_cupones` WRITE;
/*!40000 ALTER TABLE `ventas_cupones` DISABLE KEYS */;
/*!40000 ALTER TABLE `ventas_cupones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas_detalle`
--

DROP TABLE IF EXISTS `ventas_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas_detalle` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas_detalle`
--

LOCK TABLES `ventas_detalle` WRITE;
/*!40000 ALTER TABLE `ventas_detalle` DISABLE KEYS */;
INSERT INTO `ventas_detalle` VALUES (1,1,1,1.00,3500.00,0.00,3500.00),(2,2,1,7.00,3500.00,0.00,24500.00),(3,3,1,3.00,3500.00,0.00,10500.00),(4,4,1,1.00,3500.00,0.00,3500.00),(5,5,1,5.00,3500.00,0.00,17500.00);
/*!40000 ALTER TABLE `ventas_detalle` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Current Database: `mega_uni_store`
--


--
-- Final view structure for view `v_clientes_activos`
--

/*!50001 DROP VIEW IF EXISTS `v_clientes_activos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_clientes_activos` AS select `clientes`.`id` AS `id`,`clientes`.`nombre` AS `nombre`,`clientes`.`apellido` AS `apellido`,`clientes`.`email` AS `email`,`clientes`.`telefono` AS `telefono`,`clientes`.`tipo_documento` AS `tipo_documento`,`clientes`.`numero_documento` AS `numero_documento`,`clientes`.`direccion` AS `direccion`,`clientes`.`created_at` AS `created_at`,`clientes`.`deleted_at` AS `deleted_at` from `clientes` where (`clientes`.`deleted_at` is null) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_empleados_activos`
--

/*!50001 DROP VIEW IF EXISTS `v_empleados_activos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_empleados_activos` AS select `empleados`.`id` AS `id`,`empleados`.`usuario_id` AS `usuario_id`,`empleados`.`tienda_id` AS `tienda_id`,`empleados`.`codigo_empleado` AS `codigo_empleado`,`empleados`.`fecha_ingreso` AS `fecha_ingreso`,`empleados`.`salario_base` AS `salario_base`,`empleados`.`estado` AS `estado`,`empleados`.`deleted_at` AS `deleted_at` from `empleados` where ((`empleados`.`deleted_at` is null) and (`empleados`.`estado` = 'activo')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_productos_activos`
--

/*!50001 DROP VIEW IF EXISTS `v_productos_activos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_productos_activos` AS select `productos`.`id` AS `id`,`productos`.`nombre` AS `nombre`,`productos`.`descripcion` AS `descripcion`,`productos`.`codigo_barras` AS `codigo_barras`,`productos`.`imagen_url` AS `imagen_url`,`productos`.`categoria_id` AS `categoria_id`,`productos`.`unidad_medida_id` AS `unidad_medida_id`,`productos`.`estado` AS `estado`,`productos`.`deleted_at` AS `deleted_at`,`productos`.`created_at` AS `created_at` from `productos` where ((`productos`.`deleted_at` is null) and (`productos`.`estado` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_tiendas_activas`
--

/*!50001 DROP VIEW IF EXISTS `v_tiendas_activas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_tiendas_activas` AS select `tiendas`.`id` AS `id`,`tiendas`.`nombre` AS `nombre`,`tiendas`.`descripcion` AS `descripcion`,`tiendas`.`logo_url` AS `logo_url`,`tiendas`.`direccion` AS `direccion`,`tiendas`.`telefono` AS `telefono`,`tiendas`.`email` AS `email`,`tiendas`.`propietario_id` AS `propietario_id`,`tiendas`.`plataforma_id` AS `plataforma_id`,`tiendas`.`estado` AS `estado`,`tiendas`.`deleted_at` AS `deleted_at`,`tiendas`.`created_at` AS `created_at` from `tiendas` where ((`tiendas`.`deleted_at` is null) and (`tiendas`.`estado` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_usuarios_activos`
--

/*!50001 DROP VIEW IF EXISTS `v_usuarios_activos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_usuarios_activos` AS select `usuarios`.`id` AS `id`,`usuarios`.`nombre` AS `nombre`,`usuarios`.`apellido` AS `apellido`,`usuarios`.`email` AS `email`,`usuarios`.`password_hash` AS `password_hash`,`usuarios`.`telefono` AS `telefono`,`usuarios`.`avatar_url` AS `avatar_url`,`usuarios`.`estado` AS `estado`,`usuarios`.`deleted_at` AS `deleted_at`,`usuarios`.`created_at` AS `created_at` from `usuarios` where ((`usuarios`.`deleted_at` is null) and (`usuarios`.`estado` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ventas_validas`
--

/*!50001 DROP VIEW IF EXISTS `v_ventas_validas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_ventas_validas` AS select `ventas`.`id` AS `id`,`ventas`.`tienda_id` AS `tienda_id`,`ventas`.`cliente_id` AS `cliente_id`,`ventas`.`empleado_id` AS `empleado_id`,`ventas`.`caja_id` AS `caja_id`,`ventas`.`fecha` AS `fecha`,`ventas`.`subtotal` AS `subtotal`,`ventas`.`descuento` AS `descuento`,`ventas`.`impuesto` AS `impuesto`,`ventas`.`total` AS `total`,`ventas`.`estado` AS `estado`,`ventas`.`deleted_at` AS `deleted_at`,`ventas`.`created_at` AS `created_at` from `ventas` where ((`ventas`.`deleted_at` is null) and (`ventas`.`estado` <> 'anulada')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-27  8:06:10
