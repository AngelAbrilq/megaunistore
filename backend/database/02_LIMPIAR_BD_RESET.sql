-- =====================================================================
-- SCRIPT 2: LIMPIAR TODA LA BASE DE DATOS — DATOS FRESCOS DESDE CERO
-- Mega Uni Store v3
--
-- ✅ PRESERVA:  roles, permisos, roles_permisos
-- 🗑️ BORRA:     todo lo demás (usuarios, tiendas, ventas, etc.)
-- 🔄 RESETEA:   AUTO_INCREMENT a 1 en TODAS las tablas limpiadas
--
-- TRUNCATE es superior a DELETE para esto porque:
--   1. Borra los datos
--   2. Resetea el AUTO_INCREMENT a 1 automáticamente
--   3. Es más rápido (no registra fila por fila en el log)
--
-- ⚠️  ADVERTENCIA: Esta operación es IRREVERSIBLE.
--     Haz un backup antes si tienes datos que quieras conservar.
--     Para hacer backup rápido desde phpMyAdmin: Exportar → SQL → Ejecutar
-- =====================================================================

-- ─────────────────────────────────────────────────────────────────────
-- PASO 1: Desactivar verificación de claves foráneas
-- (necesario para poder truncar tablas con dependencias)
-- ─────────────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 0;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 2: LIMPIAR tablas de negocio y datos de usuario
-- ─────────────────────────────────────────────────────────────────────

-- Auditoría
TRUNCATE TABLE audit_log;

-- Notificaciones
TRUNCATE TABLE notificaciones;

-- Reportes y exportaciones
TRUNCATE TABLE exportaciones;
TRUNCATE TABLE reportes;
TRUNCATE TABLE envios_reporte;

-- Contabilidad
TRUNCATE TABLE asientos_detalle;
TRUNCATE TABLE asientos_contables;
TRUNCATE TABLE conciliaciones;
TRUNCATE TABLE presupuesto_detalle;
TRUNCATE TABLE presupuestos;
TRUNCATE TABLE gastos;
TRUNCATE TABLE periodos_contables;
TRUNCATE TABLE cuentas_contables;

-- Nómina y RRHH
TRUNCATE TABLE nomina_detalle;
TRUNCATE TABLE nomina_empleado;
TRUNCATE TABLE nominas;
TRUNCATE TABLE aportes_seguridad_social;
TRUNCATE TABLE prestaciones_sociales;
TRUNCATE TABLE horas_extra;
TRUNCATE TABLE vacaciones;
TRUNCATE TABLE conceptos_nomina;

-- Contratos y organización
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

-- Clientes
TRUNCATE TABLE tiendas_clientes;
TRUNCATE TABLE clientes;

-- Ventas, pagos y caja
TRUNCATE TABLE ventas_cupones;
TRUNCATE TABLE ventas_detalle;
TRUNCATE TABLE pagos;
TRUNCATE TABLE ventas;
TRUNCATE TABLE cajas_movimientos;
TRUNCATE TABLE cajas;
TRUNCATE TABLE cupones;

-- Devoluciones
TRUNCATE TABLE devoluciones_detalle;
TRUNCATE TABLE devoluciones;

-- Compras a proveedores
TRUNCATE TABLE compras_detalle;
TRUNCATE TABLE compras;

-- Inventario y productos
TRUNCATE TABLE movimientos_inventario;
TRUNCATE TABLE inventario;
TRUNCATE TABLE tiendas_productos;
TRUNCATE TABLE productos_atributos;
TRUNCATE TABLE productos_impuestos;
TRUNCATE TABLE productos_proveedores;
TRUNCATE TABLE productos;
TRUNCATE TABLE atributos;
TRUNCATE TABLE proveedores;

-- Catálogos (si quieres empezar desde cero también en estos, descomenta:)
-- TRUNCATE TABLE categorias;
-- TRUNCATE TABLE unidades_medida;
-- TRUNCATE TABLE impuestos;
-- TRUNCATE TABLE metodos_pago;

-- Tiendas y config
TRUNCATE TABLE tiendas_config;
TRUNCATE TABLE tiendas;

-- Suscripciones y planes
TRUNCATE TABLE suscripciones;
TRUNCATE TABLE planes;
TRUNCATE TABLE plataforma;

-- Usuarios y sesiones
TRUNCATE TABLE solicitudes_cambio_contrasena;
TRUNCATE TABLE sesiones;
TRUNCATE TABLE usuarios_roles;
TRUNCATE TABLE usuarios;

-- Infraestructura Laravel/legacy
TRUNCATE TABLE failed_jobs;
TRUNCATE TABLE personal_access_tokens;
TRUNCATE TABLE password_reset_tokens;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE users;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 3: Reactivar verificación de claves foráneas
-- ─────────────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 4: Verificar que los roles siguen intactos
-- ─────────────────────────────────────────────────────────────────────
SELECT '✅ VERIFICACIÓN: Roles preservados' AS estado;
SELECT id, nombre, nivel FROM roles ORDER BY nivel;

SELECT '✅ VERIFICACIÓN: Permisos preservados' AS estado;
SELECT COUNT(*) AS total_permisos FROM permisos;

SELECT '✅ VERIFICACIÓN: Roles-Permisos preservados' AS estado;
SELECT COUNT(*) AS total_asignaciones FROM roles_permisos;


-- ─────────────────────────────────────────────────────────────────────
-- PASO 5: Confirmar que los contadores están en 1
-- ─────────────────────────────────────────────────────────────────────
SELECT '🔄 AUTO_INCREMENT verificación' AS estado;
SELECT TABLE_NAME, AUTO_INCREMENT
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME NOT IN ('roles', 'permisos', 'roles_permisos', 'migrations')
  AND AUTO_INCREMENT IS NOT NULL
ORDER BY TABLE_NAME;
-- Todas deben mostrar AUTO_INCREMENT = 1


-- ─────────────────────────────────────────────────────────────────────
-- LISTO ✅
-- Ahora puedes:
-- 1. Entrar al sistema con la ruta de setup para crear el usuario admin
--    → http://tu-dominio/index.php?route=setup&key=TU_CLAVE
-- 2. Crear la primera tienda
-- 3. Crear usuarios y asignarles roles
-- 4. Cargar productos, inventario, etc.
-- ─────────────────────────────────────────────────────────────────────
