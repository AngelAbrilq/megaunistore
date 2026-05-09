-- ============================================================================
-- FASE 3: POS Completo - Cupones, Devoluciones y Reportes
-- ============================================================================

-- Tabla: cupones
CREATE TABLE IF NOT EXISTS cupones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tienda_id INT UNSIGNED NULL COMMENT 'NULL = cupón global para todas las tiendas',
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    tipo_descuento ENUM('porcentaje', 'fijo') NOT NULL DEFAULT 'porcentaje',
    valor_descuento DECIMAL(10, 2) NOT NULL COMMENT 'Porcentaje o monto fijo',
    descuento_maximo DECIMAL(10, 2) NULL COMMENT 'Descuento máximo aplicable (solo para porcentaje)',
    monto_minimo DECIMAL(10, 2) NULL COMMENT 'Monto mínimo de compra para aplicar el cupón',
    fecha_inicio DATETIME NULL,
    fecha_fin DATETIME NULL,
    usos_maximos INT UNSIGNED NULL COMMENT 'NULL = ilimitado',
    usos_actuales INT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: devoluciones
CREATE TABLE IF NOT EXISTS devoluciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venta_id INT UNSIGNED NOT NULL,
    tienda_id INT UNSIGNED NOT NULL,
    motivo TEXT NOT NULL,
    monto_devuelto DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'completada', 'rechazada') NOT NULL DEFAULT 'completada',
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_venta (venta_id),
    INDEX idx_tienda (tienda_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: devoluciones_detalle
CREATE TABLE IF NOT EXISTS devoluciones_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devolucion_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad DECIMAL(10, 2) NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (devolucion_id) REFERENCES devoluciones(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_devolucion (devolucion_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo cupon_id a la tabla ventas (si no existe)
ALTER TABLE ventas 
ADD COLUMN IF NOT EXISTS cupon_id INT UNSIGNED NULL AFTER caja_id,
ADD FOREIGN KEY IF NOT EXISTS fk_ventas_cupon (cupon_id) REFERENCES cupones(id) ON DELETE SET NULL;

-- Índices adicionales para optimizar reportes
CREATE INDEX IF NOT EXISTS idx_ventas_fecha_estado ON ventas(fecha, estado);
CREATE INDEX IF NOT EXISTS idx_ventas_tienda_fecha ON ventas(tienda_id, fecha);
CREATE INDEX IF NOT EXISTS idx_inventario_tienda_producto ON inventario(tienda_id, producto_id);
CREATE INDEX IF NOT EXISTS idx_movimientos_inventario_fecha ON movimientos_inventario(created_at);
CREATE INDEX IF NOT EXISTS idx_cajas_movimientos_fecha ON cajas_movimientos(created_at);

-- Datos de ejemplo para cupones (opcional)
INSERT INTO cupones (codigo, descripcion, tipo_descuento, valor_descuento, descuento_maximo, monto_minimo, fecha_inicio, fecha_fin, usos_maximos, activo) VALUES
('BIENVENIDA10', 'Cupón de bienvenida - 10% de descuento', 'porcentaje', 10.00, 50.00, 100.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 100, 1),
('VERANO2026', 'Descuento de verano - $20 de descuento', 'fijo', 20.00, NULL, 50.00, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), NULL, 1),
('PRIMERACOMPRA', 'Primera compra - 15% de descuento', 'porcentaje', 15.00, 100.00, 200.00, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY), 50, 1);
