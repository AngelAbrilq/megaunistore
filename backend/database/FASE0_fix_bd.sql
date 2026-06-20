-- ============================================================================
-- FASE 0 v4 — Reparación de BD  |  Mega_Uni_Store v3
-- ============================================================================
-- Compatible con MySQL 8.0 puro (sin ADD COLUMN IF NOT EXISTS ni procedimientos)
-- Cada operación verifica information_schema antes de ejecutar
-- ============================================================================

USE mega_uni_store;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- FIX #1 — Tabla `cupones`
-- ============================================================================

-- Renombrar uso_maximo → usos_maximos
SET @sql = (SELECT IF(COUNT(*) > 0,
    'ALTER TABLE `cupones` RENAME COLUMN `uso_maximo` TO `usos_maximos`',
    'SELECT "cupones: uso_maximo ya no existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='uso_maximo');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar descuento_maximo
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `cupones` ADD COLUMN `descuento_maximo` DECIMAL(10,2) NULL',
    'SELECT "cupones: descuento_maximo ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='descuento_maximo');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar monto_minimo
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `cupones` ADD COLUMN `monto_minimo` DECIMAL(10,2) NULL',
    'SELECT "cupones: monto_minimo ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='monto_minimo');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar updated_at
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `cupones` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'SELECT "cupones: updated_at ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='updated_at');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar created_by
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `cupones` ADD COLUMN `created_by` INT UNSIGNED NULL',
    'SELECT "cupones: created_by ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='created_by');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar updated_by
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `cupones` ADD COLUMN `updated_by` INT UNSIGNED NULL',
    'SELECT "cupones: updated_by ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='updated_by');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar deleted_at
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `cupones` ADD COLUMN `deleted_at` DATETIME NULL',
    'SELECT "cupones: deleted_at ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='deleted_at');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Pasar fecha_inicio a DATETIME si sigue siendo DATE
SET @sql = (SELECT IF(DATA_TYPE = 'date',
    'ALTER TABLE `cupones` MODIFY COLUMN `fecha_inicio` DATETIME NULL',
    'SELECT "cupones: fecha_inicio OK" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='fecha_inicio');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Pasar fecha_fin a DATETIME si sigue siendo DATE
SET @sql = (SELECT IF(DATA_TYPE = 'date',
    'ALTER TABLE `cupones` MODIFY COLUMN `fecha_fin` DATETIME NULL',
    'SELECT "cupones: fecha_fin OK" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones' AND COLUMN_NAME='fecha_fin');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- ============================================================================
-- FIX #2 — Tabla `devoluciones`
-- ============================================================================

-- Agregar tienda_id (nullable)
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD COLUMN `tienda_id` INT UNSIGNED NULL',
    'SELECT "devoluciones: tienda_id ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='tienda_id');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar monto_devuelto
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD COLUMN `monto_devuelto` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
    'SELECT "devoluciones: monto_devuelto ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='monto_devuelto');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar created_at
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'SELECT "devoluciones: created_at ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='created_at');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar created_by
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD COLUMN `created_by` INT UNSIGNED NULL',
    'SELECT "devoluciones: created_by ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='created_by');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar deleted_at
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD COLUMN `deleted_at` TIMESTAMP NULL',
    'SELECT "devoluciones: deleted_at ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='deleted_at');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Corregir ENUM: agregar valor 'completada'
SET @sql = (SELECT IF(COLUMN_TYPE NOT LIKE '%completada%',
    "ALTER TABLE `devoluciones` MODIFY COLUMN `estado` ENUM('pendiente','aprobada','rechazada','completada') NOT NULL DEFAULT 'pendiente'",
    'SELECT "devoluciones: ENUM ya tiene completada" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='estado');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Poblar tienda_id desde ventas
UPDATE `devoluciones` d
    INNER JOIN `ventas` v ON v.id = d.venta_id
    SET d.tienda_id = v.tienda_id
    WHERE d.tienda_id IS NULL;

-- Hacer tienda_id NOT NULL (solo si ya no hay NULLs)
SET @null_count = (SELECT COUNT(*) FROM `devoluciones` WHERE tienda_id IS NULL);
SET @sql = IF(@null_count = 0,
    'ALTER TABLE `devoluciones` MODIFY COLUMN `tienda_id` INT UNSIGNED NOT NULL',
    'SELECT "devoluciones: aun hay filas sin tienda_id" AS info');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- ============================================================================
-- FIX #3 — Tabla `devoluciones_detalle`
-- Renombrar `monto` → `subtotal`
-- ============================================================================

SET @tiene_monto = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones_detalle' AND COLUMN_NAME='monto');
SET @tiene_subtotal = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones_detalle' AND COLUMN_NAME='subtotal');

SET @sql = IF(@tiene_monto > 0 AND @tiene_subtotal = 0,
    'ALTER TABLE `devoluciones_detalle` RENAME COLUMN `monto` TO `subtotal`',
    'SELECT "devoluciones_detalle: subtotal ya existe o monto ya no existe" AS info');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- Agregar created_at a devoluciones_detalle
SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones_detalle` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'SELECT "devoluciones_detalle: created_at ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones_detalle' AND COLUMN_NAME='created_at');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- ============================================================================
-- FIX #4 — Tabla `ventas`: agregar cupon_id
-- ============================================================================

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `ventas` ADD COLUMN `cupon_id` INT UNSIGNED NULL',
    'SELECT "ventas: cupon_id ya existe" AS info')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='ventas' AND COLUMN_NAME='cupon_id');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- ============================================================================
-- FIX #5 — Índices de rendimiento
-- ============================================================================

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD INDEX `idx_dev_tienda` (`tienda_id`)',
    'SELECT "idx_dev_tienda ya existe" AS info')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND INDEX_NAME='idx_dev_tienda');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `devoluciones` ADD INDEX `idx_dev_deleted` (`deleted_at`)',
    'SELECT "idx_dev_deleted ya existe" AS info')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND INDEX_NAME='idx_dev_deleted');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `ventas` ADD INDEX `idx_ventas_fecha_estado` (`fecha`, `estado`)',
    'SELECT "idx_ventas_fecha_estado ya existe" AS info')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='ventas' AND INDEX_NAME='idx_ventas_fecha_estado');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `ventas` ADD INDEX `idx_ventas_tienda_fecha` (`tienda_id`, `fecha`)',
    'SELECT "idx_ventas_tienda_fecha ya existe" AS info')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='ventas' AND INDEX_NAME='idx_ventas_tienda_fecha');
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VERIFICACION FINAL — todas deben mostrar OK
-- ============================================================================
SELECT columna, IF(cnt > 0, '✅ OK', '❌ FALTA') AS resultado FROM (
    SELECT 'cupones.usos_maximos'          AS columna, COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones'             AND COLUMN_NAME='usos_maximos'    UNION ALL
    SELECT 'cupones.descuento_maximo',      COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones'             AND COLUMN_NAME='descuento_maximo' UNION ALL
    SELECT 'cupones.monto_minimo',          COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones'             AND COLUMN_NAME='monto_minimo'     UNION ALL
    SELECT 'cupones.created_by',            COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='cupones'             AND COLUMN_NAME='created_by'       UNION ALL
    SELECT 'devoluciones.tienda_id',        COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones'        AND COLUMN_NAME='tienda_id'        UNION ALL
    SELECT 'devoluciones.monto_devuelto',   COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones'        AND COLUMN_NAME='monto_devuelto'   UNION ALL
    SELECT 'devoluciones.created_by',       COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones'        AND COLUMN_NAME='created_by'       UNION ALL
    SELECT 'devoluciones.deleted_at',       COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones'        AND COLUMN_NAME='deleted_at'       UNION ALL
    SELECT 'devoluciones_detalle.subtotal', COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones_detalle' AND COLUMN_NAME='subtotal'        UNION ALL
    SELECT 'ventas.cupon_id',               COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='ventas'               AND COLUMN_NAME='cupon_id'         UNION ALL
    SELECT 'devoluciones.estado=completada', IF(COLUMN_TYPE LIKE '%completada%',1,0) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='mega_uni_store' AND TABLE_NAME='devoluciones' AND COLUMN_NAME='estado'
) t;
