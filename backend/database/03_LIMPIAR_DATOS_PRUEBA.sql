-- =====================================================================
-- SCRIPT 3: LIMPIAR DATOS DE PRUEBA — INICIO LIMPIO REAL
-- Mega Uni Store v3
--
-- ✅ PRESERVA:
--     roles, permisos, roles_permisos
--     usuarios       → solo id=1 (Admin Principal - admin@megaunistore.com)
--     usuarios_roles → solo la asignación del Admin Principal
--     metodos_pago   → Efectivo, Transferencia, Tarjeta débito/crédito
--     unidades_medida → Unidad, Kg, Litro, Metro
--     impuestos       → IVA 19%, IVA 5%, Impoconsumo 8%
--
-- 🗑️ ELIMINA: todos los datos de negocio, tiendas, productos,
--             ventas, empleados, clientes, categorías de prueba, etc.
--
-- 🔄 RESETEA: AUTO_INCREMENT a 1 en tablas truncadas
--             AUTO_INCREMENT a 2 en usuarios (id=1 preservado)
--
-- ▶️  CÓMO EJECUTAR (IMPORTANTE):
--     MySQL Workbench : Ctrl+Shift+Enter → "Run Script" (NO solo F5)
--     HeidiSQL        : Ctrl+Shift+F9
--     phpMyAdmin      : ver nota en PASO 3 sobre DELIMITER
-- =====================================================================


-- ─────────────────────────────────────────────────────────────────────
-- PASO 1: Desactivar claves foráneas
-- ─────────────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 0;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 2: AUDITORÍA Y SISTEMA (limpiar logs)
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE audit_log;
TRUNCATE TABLE notificaciones;
TRUNCATE TABLE exportaciones;
TRUNCATE TABLE reportes;
TRUNCATE TABLE envios_reporte;
TRUNCATE TABLE sesiones;
TRUNCATE TABLE solicitudes_cambio_contrasena;
TRUNCATE TABLE failed_jobs;
TRUNCATE TABLE personal_access_tokens;
TRUNCATE TABLE password_reset_tokens;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE users;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 3: USUARIOS — conservar solo el Super Admin (id = 1)
--
-- ⚠️  La tabla usuarios tiene un trigger BEFORE DELETE que bloquea
--     los borrados físicos con Error 1644 (trg_usuario_soft_delete).
--
--     FIX: drop temporal del trigger → DELETE → ALTER → recrear trigger.
--
-- ▶️  MySQL Workbench: usa Ctrl+Shift+Enter "Run Script" (NO F5)
--     phpMyAdmin: si da error con DELIMITER, cambia el delimitador a ;;
--     en la opción "Delimitador" de la pestaña SQL antes de ejecutar.
-- ─────────────────────────────────────────────────────────────────────

-- 3a. Borrar asignaciones de roles de usuarios de prueba (no hay trigger aquí)
DELETE FROM usuarios_roles WHERE usuario_id != 1;

-- 3b. Quitar temporalmente el trigger que bloquea DELETE físico
DROP TRIGGER IF EXISTS trg_usuario_soft_delete;

-- 3c. Borrar usuarios de prueba (ahora sin el trigger bloqueando)
DELETE FROM usuarios WHERE id != 1;

-- 3d. Resetear AUTO_INCREMENT al siguiente disponible tras id=1
ALTER TABLE usuarios       AUTO_INCREMENT = 2;
ALTER TABLE usuarios_roles AUTO_INCREMENT = 2;

-- 3e. Recrear el trigger de protección
DELIMITER ;;
CREATE TRIGGER `trg_usuario_soft_delete`
BEFORE DELETE ON `usuarios`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'DELETE físico no permitido en usuarios. Use: UPDATE usuarios SET deleted_at = NOW() WHERE id = X';
END;;
DELIMITER ;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 4: TIENDAS Y CONFIGURACIÓN
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE tiendas_config;
TRUNCATE TABLE tiendas_clientes;
TRUNCATE TABLE tiendas_productos;
TRUNCATE TABLE tiendas;
TRUNCATE TABLE plataforma;
TRUNCATE TABLE planes;
TRUNCATE TABLE suscripciones;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 5: PRODUCTOS, INVENTARIO Y CATÁLOGOS DE PRUEBA
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE movimientos_inventario;
TRUNCATE TABLE inventario;
TRUNCATE TABLE productos_atributos;
TRUNCATE TABLE productos_impuestos;
TRUNCATE TABLE productos_proveedores;
TRUNCATE TABLE productos;
TRUNCATE TABLE atributos;
TRUNCATE TABLE proveedores;

-- Categorías: las existentes son de prueba → limpiar todas
-- (cuando entres al sistema creas las categorías reales de tu negocio)
TRUNCATE TABLE categorias;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 6: VENTAS, PAGOS Y CAJA
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE ventas_cupones;
TRUNCATE TABLE ventas_detalle;
TRUNCATE TABLE pagos;
TRUNCATE TABLE ventas;
TRUNCATE TABLE cajas_movimientos;
TRUNCATE TABLE cajas;
TRUNCATE TABLE cupones;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 7: DEVOLUCIONES Y COMPRAS
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE devoluciones_detalle;
TRUNCATE TABLE devoluciones;
TRUNCATE TABLE compras_detalle;
TRUNCATE TABLE compras;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 8: CLIENTES
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE clientes;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 9: EMPLEADOS Y ORGANIZACIÓN
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE nomina_detalle;
TRUNCATE TABLE nomina_empleado;
TRUNCATE TABLE nominas;
TRUNCATE TABLE aportes_seguridad_social;
TRUNCATE TABLE prestaciones_sociales;
TRUNCATE TABLE horas_extra;
TRUNCATE TABLE vacaciones;
TRUNCATE TABLE conceptos_nomina;
TRUNCATE TABLE contratos;
TRUNCATE TABLE empleados_horarios;
TRUNCATE TABLE empleados_cargos;
TRUNCATE TABLE empleados_areas;
TRUNCATE TABLE empleados;
TRUNCATE TABLE turnos;
TRUNCATE TABLE horarios;
TRUNCATE TABLE cargos;
TRUNCATE TABLE areas;
TRUNCATE TABLE centros_costo;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 10: CONTABILIDAD
-- ─────────────────────────────────────────────────────────────────────
TRUNCATE TABLE asientos_detalle;
TRUNCATE TABLE asientos_contables;
TRUNCATE TABLE conciliaciones;
TRUNCATE TABLE presupuesto_detalle;
TRUNCATE TABLE presupuestos;
TRUNCATE TABLE gastos;
TRUNCATE TABLE periodos_contables;
TRUNCATE TABLE cuentas_contables;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 11: Reactivar claves foráneas
-- ─────────────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;


-- ─────────────────────────────────────────────────────────────────────
-- VERIFICACIÓN FINAL — confirmar que todo quedó correcto
-- ─────────────────────────────────────────────────────────────────────

SELECT '════════════════════════════════════' AS '';
SELECT '  VERIFICACIÓN FINAL               ' AS '';
SELECT '════════════════════════════════════' AS '';

SELECT '✅ Roles preservados (deben ser 9):' AS estado;
SELECT id, nombre, nivel FROM roles ORDER BY nivel;

SELECT '✅ Permisos preservados (deben ser 37):' AS estado;
SELECT COUNT(*) AS total_permisos FROM permisos;

SELECT '✅ Super Admin preservado:' AS estado;
SELECT id, nombre, apellido, email, estado FROM usuarios;

SELECT '✅ Rol del Super Admin:' AS estado;
SELECT ur.id, u.nombre, r.nombre AS rol
FROM usuarios_roles ur
JOIN usuarios u ON u.id = ur.usuario_id
JOIN roles r ON r.id = ur.rol_id;

SELECT '✅ Métodos de pago (deben ser 4):' AS estado;
SELECT id, nombre, activo FROM metodos_pago;

SELECT '✅ Unidades de medida (deben ser 4):' AS estado;
SELECT * FROM unidades_medida;

SELECT '✅ Impuestos (deben ser 3):' AS estado;
SELECT id, nombre, porcentaje, activo FROM impuestos;

SELECT '✅ Trigger recreado correctamente:' AS estado;
SELECT TRIGGER_NAME, EVENT_MANIPULATION, ACTION_TIMING, EVENT_OBJECT_TABLE
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
  AND TRIGGER_NAME = 'trg_usuario_soft_delete';

SELECT '🔄 Conteo de tablas limpiadas (deben ser 0):' AS estado;
SELECT 'tiendas'    AS tabla, COUNT(*) AS filas FROM tiendas    UNION ALL
SELECT 'productos',          COUNT(*)           FROM productos  UNION ALL
SELECT 'inventario',         COUNT(*)           FROM inventario UNION ALL
SELECT 'ventas',             COUNT(*)           FROM ventas     UNION ALL
SELECT 'empleados',          COUNT(*)           FROM empleados  UNION ALL
SELECT 'clientes',           COUNT(*)           FROM clientes   UNION ALL
SELECT 'categorias',         COUNT(*)           FROM categorias UNION ALL
SELECT 'cajas',              COUNT(*)           FROM cajas      UNION ALL
SELECT 'audit_log',          COUNT(*)           FROM audit_log;

SELECT '════════════════════════════════════' AS '';
SELECT '  SISTEMA LISTO — DATOS REALES 🚀  ' AS '';
SELECT '  Accede con: admin@megaunistore.com' AS '';
SELECT '════════════════════════════════════' AS '';

-- ─────────────────────────────────────────────────────────────────────
-- PRÓXIMOS PASOS tras ejecutar este script:
--
-- 1. Entra al sistema: http://localhost/mega_uni_store_v3/backend/public/
-- 2. Login: admin@megaunistore.com (contraseña original del backup)
-- 3. Crea la primera Tienda real
-- 4. Crea las Categorías de tus productos reales
-- 5. Crea usuarios con sus roles (Vendedor, Bodeguero, etc.)
-- 6. Carga productos e inventario
-- 7. ¡A vender!
-- ─────────────────────────────────────────────────────────────────────
