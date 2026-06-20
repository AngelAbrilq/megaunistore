-- ============================================================
--  MEGA UNI STORE v3 — SCRIPT 1: VER TODA LA INFORMACIÓN
--  Ejecutar en: MySQL Workbench / phpMyAdmin / HeidiSQL
--  Muestra SELECT * de TODAS las tablas del sistema
-- ============================================================

USE mega_uni_store;   -- ← cambia esto si tu BD tiene otro nombre

-- ============================================================
--  🔐 SEGURIDAD: ROLES Y PERMISOS
-- ============================================================

SELECT '===== ROLES =====' AS '--- TABLA ---';
SELECT * FROM roles ORDER BY nivel ASC;

SELECT '===== PERMISOS =====' AS '--- TABLA ---';
SELECT * FROM permisos ORDER BY modulo, nombre ASC;

SELECT '===== ROLES_PERMISOS (pivote) =====' AS '--- TABLA ---';
SELECT
    rp.id,
    r.nombre  AS rol,
    p.clave   AS permiso,
    p.modulo
FROM roles_permisos rp
JOIN roles    r ON r.id = rp.rol_id
JOIN permisos p ON p.id = rp.permiso_id
ORDER BY r.nivel, p.modulo;

-- ============================================================
--  👥 USUARIOS Y AUTENTICACIÓN
-- ============================================================

SELECT '===== USUARIOS =====' AS '--- TABLA ---';
SELECT id, nombre, apellido, email, activo, created_at FROM usuarios ORDER BY created_at DESC;

SELECT '===== USUARIOS_ROLES (pivote) =====' AS '--- TABLA ---';
SELECT
    ur.id,
    u.email    AS usuario,
    r.nombre   AS rol,
    t.nombre   AS tienda,
    ur.created_at
FROM usuarios_roles ur
JOIN usuarios u ON u.id = ur.usuario_id
JOIN roles    r ON r.id = ur.rol_id
LEFT JOIN tiendas t ON t.id = ur.tienda_id
ORDER BY u.email;

SELECT '===== SESIONES =====' AS '--- TABLA ---';
SELECT * FROM sesiones ORDER BY ultima_actividad DESC LIMIT 50;

SELECT '===== SOLICITUDES_CAMBIO_CONTRASENA =====' AS '--- TABLA ---';
SELECT id, usuario_id, token, usado, expires_at, created_at FROM solicitudes_cambio_contrasena ORDER BY created_at DESC;

SELECT '===== PASSWORD_RESETS =====' AS '--- TABLA ---';
SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 20;

SELECT '===== PASSWORD_RESET_TOKENS =====' AS '--- TABLA ---';
SELECT * FROM password_reset_tokens ORDER BY created_at DESC LIMIT 20;

SELECT '===== PERSONAL_ACCESS_TOKENS =====' AS '--- TABLA ---';
SELECT * FROM personal_access_tokens ORDER BY created_at DESC LIMIT 20;

SELECT '===== USERS (tabla legacy Laravel) =====' AS '--- TABLA ---';
SELECT * FROM users LIMIT 20;

-- ============================================================
--  🏪 TIENDAS Y CONFIGURACIÓN
-- ============================================================

SELECT '===== TIENDAS =====' AS '--- TABLA ---';
SELECT * FROM tiendas ORDER BY created_at DESC;

SELECT '===== TIENDAS_CONFIG =====' AS '--- TABLA ---';
SELECT * FROM tiendas_config ORDER BY tienda_id;

SELECT '===== PLATAFORMA =====' AS '--- TABLA ---';
SELECT * FROM plataforma;

SELECT '===== PLANES =====' AS '--- TABLA ---';
SELECT * FROM planes;

SELECT '===== SUSCRIPCIONES =====' AS '--- TABLA ---';
SELECT * FROM suscripciones ORDER BY created_at DESC;

-- ============================================================
--  📦 CATÁLOGOS BASE
-- ============================================================

SELECT '===== CATEGORIAS =====' AS '--- TABLA ---';
SELECT * FROM categorias ORDER BY nombre;

SELECT '===== UNIDADES_MEDIDA =====' AS '--- TABLA ---';
SELECT * FROM unidades_medida ORDER BY nombre;

SELECT '===== IMPUESTOS =====' AS '--- TABLA ---';
SELECT * FROM impuestos ORDER BY porcentaje;

SELECT '===== METODOS_PAGO =====' AS '--- TABLA ---';
SELECT * FROM metodos_pago ORDER BY id;

SELECT '===== ATRIBUTOS =====' AS '--- TABLA ---';
SELECT * FROM atributos ORDER BY nombre;

-- ============================================================
--  🛒 PRODUCTOS E INVENTARIO
-- ============================================================

SELECT '===== PRODUCTOS =====' AS '--- TABLA ---';
SELECT
    p.id, p.codigo, p.nombre, p.precio_venta, p.activo,
    c.nombre AS categoria,
    t.nombre AS tienda,
    p.created_at
FROM productos p
LEFT JOIN categorias c ON c.id = p.categoria_id
LEFT JOIN tiendas    t ON t.id = p.tienda_id
ORDER BY p.created_at DESC;

SELECT '===== PRODUCTOS_IMPUESTOS (pivote) =====' AS '--- TABLA ---';
SELECT pi.*, p.nombre AS producto, i.nombre AS impuesto
FROM productos_impuestos pi
JOIN productos p ON p.id = pi.producto_id
JOIN impuestos i ON i.id = pi.impuesto_id;

SELECT '===== ATRIBUTOS PRODUCTOS (pivote) =====' AS '--- TABLA ---';
SELECT * FROM productos_atributos ORDER BY producto_id;

SELECT '===== TIENDAS_PRODUCTOS (pivote) =====' AS '--- TABLA ---';
SELECT tp.*, t.nombre AS tienda, pr.nombre AS producto
FROM tiendas_productos tp
JOIN tiendas   t  ON t.id  = tp.tienda_id
JOIN productos pr ON pr.id = tp.producto_id;

SELECT '===== PROVEEDORES =====' AS '--- TABLA ---';
SELECT * FROM proveedores ORDER BY nombre;

SELECT '===== PRODUCTOS_PROVEEDORES (pivote) =====' AS '--- TABLA ---';
SELECT pp.*, p.nombre AS producto, pv.nombre AS proveedor
FROM productos_proveedores pp
JOIN productos  p  ON p.id  = pp.producto_id
JOIN proveedores pv ON pv.id = pp.proveedor_id;

SELECT '===== INVENTARIO =====' AS '--- TABLA ---';
SELECT
    i.*,
    p.nombre AS producto,
    t.nombre AS tienda
FROM inventario i
JOIN productos p ON p.id = i.producto_id
LEFT JOIN tiendas t ON t.id = i.tienda_id
ORDER BY i.cantidad ASC;

SELECT '===== MOVIMIENTOS_INVENTARIO =====' AS '--- TABLA ---';
SELECT
    m.*,
    p.nombre AS producto
FROM movimientos_inventario m
JOIN productos p ON p.id = m.producto_id
ORDER BY m.created_at DESC LIMIT 200;

-- ============================================================
--  💰 VENTAS, CAJA Y PAGOS
-- ============================================================

SELECT '===== VENTAS =====' AS '--- TABLA ---';
SELECT
    v.id, v.numero_venta, v.total, v.estado,
    t.nombre  AS tienda,
    u.email   AS vendedor,
    v.created_at
FROM ventas v
LEFT JOIN tiendas  t ON t.id = v.tienda_id
LEFT JOIN usuarios u ON u.id = v.usuario_id
ORDER BY v.created_at DESC LIMIT 200;

SELECT '===== VENTAS_DETALLE =====' AS '--- TABLA ---';
SELECT vd.*, p.nombre AS producto
FROM ventas_detalle vd
JOIN productos p ON p.id = vd.producto_id
ORDER BY vd.venta_id DESC LIMIT 300;

SELECT '===== PAGOS =====' AS '--- TABLA ---';
SELECT pg.*, mp.nombre AS metodo
FROM pagos pg
LEFT JOIN metodos_pago mp ON mp.id = pg.metodo_pago_id
ORDER BY pg.created_at DESC LIMIT 200;

SELECT '===== CUPONES =====' AS '--- TABLA ---';
SELECT * FROM cupones ORDER BY created_at DESC;

SELECT '===== VENTAS_CUPONES (pivote) =====' AS '--- TABLA ---';
SELECT vc.*, c.codigo AS cupon
FROM ventas_cupones vc
JOIN cupones c ON c.id = vc.cupon_id
ORDER BY vc.venta_id DESC LIMIT 100;

SELECT '===== CAJAS =====' AS '--- TABLA ---';
SELECT cj.*, t.nombre AS tienda, u.email AS usuario
FROM cajas cj
LEFT JOIN tiendas  t ON t.id = cj.tienda_id
LEFT JOIN usuarios u ON u.id = cj.usuario_id
ORDER BY cj.created_at DESC;

SELECT '===== CAJAS_MOVIMIENTOS =====' AS '--- TABLA ---';
SELECT * FROM cajas_movimientos ORDER BY created_at DESC LIMIT 200;

SELECT '===== DEVOLUCIONES =====' AS '--- TABLA ---';
SELECT * FROM devoluciones ORDER BY created_at DESC LIMIT 100;

SELECT '===== DEVOLUCIONES_DETALLE =====' AS '--- TABLA ---';
SELECT * FROM devoluciones_detalle ORDER BY devolucion_id DESC LIMIT 200;

-- ============================================================
--  👤 CLIENTES
-- ============================================================

SELECT '===== CLIENTES =====' AS '--- TABLA ---';
SELECT * FROM clientes ORDER BY created_at DESC;

SELECT '===== TIENDAS_CLIENTES (pivote) =====' AS '--- TABLA ---';
SELECT tc.*, t.nombre AS tienda, c.nombre AS cliente
FROM tiendas_clientes tc
JOIN tiendas  t ON t.id = tc.tienda_id
JOIN clientes c ON c.id = tc.cliente_id;

-- ============================================================
--  👨‍💼 RRHH, EMPLEADOS Y NÓMINA
-- ============================================================

SELECT '===== AREAS =====' AS '--- TABLA ---';
SELECT * FROM areas ORDER BY nombre;

SELECT '===== CARGOS =====' AS '--- TABLA ---';
SELECT * FROM cargos ORDER BY nombre;

SELECT '===== HORARIOS =====' AS '--- TABLA ---';
SELECT * FROM horarios;

SELECT '===== TURNOS =====' AS '--- TABLA ---';
SELECT * FROM turnos ORDER BY created_at DESC;

SELECT '===== EMPLEADOS =====' AS '--- TABLA ---';
SELECT e.*, t.nombre AS tienda, u.email AS usuario
FROM empleados e
LEFT JOIN tiendas  t ON t.id = e.tienda_id
LEFT JOIN usuarios u ON u.id = e.usuario_id
ORDER BY e.created_at DESC;

SELECT '===== EMPLEADOS_AREAS (pivote) =====' AS '--- TABLA ---';
SELECT * FROM empleados_areas;

SELECT '===== EMPLEADOS_CARGOS (pivote) =====' AS '--- TABLA ---';
SELECT * FROM empleados_cargos;

SELECT '===== EMPLEADOS_HORARIOS (pivote) =====' AS '--- TABLA ---';
SELECT * FROM empleados_horarios;

SELECT '===== CONTRATOS =====' AS '--- TABLA ---';
SELECT * FROM contratos ORDER BY created_at DESC;

SELECT '===== CONCEPTOS_NOMINA =====' AS '--- TABLA ---';
SELECT * FROM conceptos_nomina ORDER BY nombre;

SELECT '===== NOMINAS =====' AS '--- TABLA ---';
SELECT * FROM nominas ORDER BY created_at DESC;

SELECT '===== NOMINA_EMPLEADO (pivote) =====' AS '--- TABLA ---';
SELECT * FROM nomina_empleado ORDER BY nomina_id;

SELECT '===== NOMINA_DETALLE =====' AS '--- TABLA ---';
SELECT * FROM nomina_detalle ORDER BY nomina_id DESC LIMIT 200;

SELECT '===== HORAS_EXTRA =====' AS '--- TABLA ---';
SELECT * FROM horas_extra ORDER BY fecha DESC LIMIT 100;

SELECT '===== VACACIONES =====' AS '--- TABLA ---';
SELECT * FROM vacaciones ORDER BY created_at DESC;

SELECT '===== APORTES_SEGURIDAD_SOCIAL =====' AS '--- TABLA ---';
SELECT * FROM aportes_seguridad_social ORDER BY created_at DESC;

SELECT '===== PRESTACIONES_SOCIALES =====' AS '--- TABLA ---';
SELECT * FROM prestaciones_sociales ORDER BY created_at DESC;

-- ============================================================
--  📊 COMPRAS, GASTOS Y CONTABILIDAD
-- ============================================================

SELECT '===== COMPRAS =====' AS '--- TABLA ---';
SELECT * FROM compras ORDER BY created_at DESC;

SELECT '===== COMPRAS_DETALLE =====' AS '--- TABLA ---';
SELECT * FROM compras_detalle ORDER BY compra_id DESC LIMIT 200;

SELECT '===== GASTOS =====' AS '--- TABLA ---';
SELECT * FROM gastos ORDER BY created_at DESC;

SELECT '===== PRESUPUESTOS =====' AS '--- TABLA ---';
SELECT * FROM presupuestos ORDER BY created_at DESC;

SELECT '===== PRESUPUESTO_DETALLE =====' AS '--- TABLA ---';
SELECT * FROM presupuesto_detalle ORDER BY presupuesto_id;

SELECT '===== CUENTAS_CONTABLES =====' AS '--- TABLA ---';
SELECT * FROM cuentas_contables ORDER BY codigo;

SELECT '===== CENTROS_COSTO =====' AS '--- TABLA ---';
SELECT * FROM centros_costo ORDER BY nombre;

SELECT '===== PERIODOS_CONTABLES =====' AS '--- TABLA ---';
SELECT * FROM periodos_contables ORDER BY fecha_inicio DESC;

SELECT '===== ASIENTOS_CONTABLES =====' AS '--- TABLA ---';
SELECT * FROM asientos_contables ORDER BY created_at DESC LIMIT 100;

SELECT '===== ASIENTOS_DETALLE =====' AS '--- TABLA ---';
SELECT * FROM asientos_detalle ORDER BY asiento_id DESC LIMIT 200;

SELECT '===== CONCILIACIONES =====' AS '--- TABLA ---';
SELECT * FROM conciliaciones ORDER BY created_at DESC;

-- ============================================================
--  📋 REPORTES, EXPORTACIONES Y NOTIFICACIONES
-- ============================================================

SELECT '===== REPORTES =====' AS '--- TABLA ---';
SELECT r.*, t.nombre AS tienda, u.email AS creador
FROM reportes r
LEFT JOIN tiendas  t ON t.id = r.tienda_id
LEFT JOIN usuarios u ON u.id = r.creado_por
ORDER BY r.created_at DESC LIMIT 100;

SELECT '===== EXPORTACIONES =====' AS '--- TABLA ---';
SELECT e.*, r.nombre AS reporte
FROM exportaciones e
LEFT JOIN reportes r ON r.id = e.reporte_id
ORDER BY e.created_at DESC LIMIT 100;

SELECT '===== ENVIOS_REPORTE =====' AS '--- TABLA ---';
SELECT * FROM envios_reporte ORDER BY created_at DESC LIMIT 50;

SELECT '===== NOTIFICACIONES =====' AS '--- TABLA ---';
SELECT n.*, u.email AS usuario, t.nombre AS tienda
FROM notificaciones n
LEFT JOIN usuarios u ON u.id = n.usuario_id
LEFT JOIN tiendas  t ON t.id = n.tienda_id
ORDER BY n.created_at DESC LIMIT 200;

-- ============================================================
--  🔎 AUDITORÍA
-- ============================================================

SELECT '===== AUDIT_LOG =====' AS '--- TABLA ---';
SELECT
    al.*,
    u.email AS usuario
FROM audit_log al
LEFT JOIN usuarios u ON u.id = al.usuario_id
ORDER BY al.created_at DESC LIMIT 300;

-- ============================================================
--  🛠️ INFRAESTRUCTURA
-- ============================================================

SELECT '===== MIGRATIONS =====' AS '--- TABLA ---';
SELECT * FROM migrations ORDER BY id;

SELECT '===== FAILED_JOBS =====' AS '--- TABLA ---';
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 20;

-- ============================================================
--  📊 RESUMEN TOTAL DE REGISTROS POR TABLA
-- ============================================================

SELECT '===== CONTEO TOTAL POR TABLA =====' AS '--- RESUMEN FINAL ---';
SELECT 'aportes_seguridad_social' AS tabla, COUNT(*) AS registros FROM aportes_seguridad_social UNION ALL
SELECT 'areas',                  COUNT(*) FROM areas UNION ALL
SELECT 'asientos_contables',     COUNT(*) FROM asientos_contables UNION ALL
SELECT 'asientos_detalle',       COUNT(*) FROM asientos_detalle UNION ALL
SELECT 'atributos',              COUNT(*) FROM atributos UNION ALL
SELECT 'audit_log',              COUNT(*) FROM audit_log UNION ALL
SELECT 'cajas',                  COUNT(*) FROM cajas UNION ALL
SELECT 'cajas_movimientos',      COUNT(*) FROM cajas_movimientos UNION ALL
SELECT 'cargos',                 COUNT(*) FROM cargos UNION ALL
SELECT 'categorias',             COUNT(*) FROM categorias UNION ALL
SELECT 'centros_costo',          COUNT(*) FROM centros_costo UNION ALL
SELECT 'clientes',               COUNT(*) FROM clientes UNION ALL
SELECT 'compras',                COUNT(*) FROM compras UNION ALL
SELECT 'compras_detalle',        COUNT(*) FROM compras_detalle UNION ALL
SELECT 'conceptos_nomina',       COUNT(*) FROM conceptos_nomina UNION ALL
SELECT 'conciliaciones',         COUNT(*) FROM conciliaciones UNION ALL
SELECT 'contratos',              COUNT(*) FROM contratos UNION ALL
SELECT 'cuentas_contables',      COUNT(*) FROM cuentas_contables UNION ALL
SELECT 'cupones',                COUNT(*) FROM cupones UNION ALL
SELECT 'devoluciones',           COUNT(*) FROM devoluciones UNION ALL
SELECT 'devoluciones_detalle',   COUNT(*) FROM devoluciones_detalle UNION ALL
SELECT 'empleados',              COUNT(*) FROM empleados UNION ALL
SELECT 'empleados_areas',        COUNT(*) FROM empleados_areas UNION ALL
SELECT 'empleados_cargos',       COUNT(*) FROM empleados_cargos UNION ALL
SELECT 'empleados_horarios',     COUNT(*) FROM empleados_horarios UNION ALL
SELECT 'envios_reporte',         COUNT(*) FROM envios_reporte UNION ALL
SELECT 'exportaciones',          COUNT(*) FROM exportaciones UNION ALL
SELECT 'failed_jobs',            COUNT(*) FROM failed_jobs UNION ALL
SELECT 'gastos',                 COUNT(*) FROM gastos UNION ALL
SELECT 'horarios',               COUNT(*) FROM horarios UNION ALL
SELECT 'horas_extra',            COUNT(*) FROM horas_extra UNION ALL
SELECT 'impuestos',              COUNT(*) FROM impuestos UNION ALL
SELECT 'inventario',             COUNT(*) FROM inventario UNION ALL
SELECT 'metodos_pago',           COUNT(*) FROM metodos_pago UNION ALL
SELECT 'migrations',             COUNT(*) FROM migrations UNION ALL
SELECT 'movimientos_inventario', COUNT(*) FROM movimientos_inventario UNION ALL
SELECT 'nomina_detalle',         COUNT(*) FROM nomina_detalle UNION ALL
SELECT 'nomina_empleado',        COUNT(*) FROM nomina_empleado UNION ALL
SELECT 'nominas',                COUNT(*) FROM nominas UNION ALL
SELECT 'notificaciones',         COUNT(*) FROM notificaciones UNION ALL
SELECT 'pagos',                  COUNT(*) FROM pagos UNION ALL
SELECT 'password_reset_tokens',  COUNT(*) FROM password_reset_tokens UNION ALL
SELECT 'password_resets',        COUNT(*) FROM password_resets UNION ALL
SELECT 'periodos_contables',     COUNT(*) FROM periodos_contables UNION ALL
SELECT 'permisos',               COUNT(*) FROM permisos UNION ALL
SELECT 'personal_access_tokens', COUNT(*) FROM personal_access_tokens UNION ALL
SELECT 'planes',                 COUNT(*) FROM planes UNION ALL
SELECT 'plataforma',             COUNT(*) FROM plataforma UNION ALL
SELECT 'prestaciones_sociales',  COUNT(*) FROM prestaciones_sociales UNION ALL
SELECT 'presupuesto_detalle',    COUNT(*) FROM presupuesto_detalle UNION ALL
SELECT 'presupuestos',           COUNT(*) FROM presupuestos UNION ALL
SELECT 'productos',              COUNT(*) FROM productos UNION ALL
SELECT 'productos_atributos',    COUNT(*) FROM productos_atributos UNION ALL
SELECT 'productos_impuestos',    COUNT(*) FROM productos_impuestos UNION ALL
SELECT 'productos_proveedores',  COUNT(*) FROM productos_proveedores UNION ALL
SELECT 'proveedores',            COUNT(*) FROM proveedores UNION ALL
SELECT 'reportes',               COUNT(*) FROM reportes UNION ALL
SELECT 'roles',                  COUNT(*) FROM roles UNION ALL
SELECT 'roles_permisos',         COUNT(*) FROM roles_permisos UNION ALL
SELECT 'sesiones',               COUNT(*) FROM sesiones UNION ALL
SELECT 'solicitudes_cambio_contrasena', COUNT(*) FROM solicitudes_cambio_contrasena UNION ALL
SELECT 'suscripciones',          COUNT(*) FROM suscripciones UNION ALL
SELECT 'tiendas',                COUNT(*) FROM tiendas UNION ALL
SELECT 'tiendas_clientes',       COUNT(*) FROM tiendas_clientes UNION ALL
SELECT 'tiendas_config',         COUNT(*) FROM tiendas_config UNION ALL
SELECT 'tiendas_productos',      COUNT(*) FROM tiendas_productos UNION ALL
SELECT 'turnos',                 COUNT(*) FROM turnos UNION ALL
SELECT 'unidades_medida',        COUNT(*) FROM unidades_medida UNION ALL
SELECT 'users',                  COUNT(*) FROM users UNION ALL
SELECT 'usuarios',               COUNT(*) FROM usuarios UNION ALL
SELECT 'usuarios_roles',         COUNT(*) FROM usuarios_roles UNION ALL
SELECT 'vacaciones',             COUNT(*) FROM vacaciones UNION ALL
SELECT 'ventas',                 COUNT(*) FROM ventas UNION ALL
SELECT 'ventas_cupones',         COUNT(*) FROM ventas_cupones UNION ALL
SELECT 'ventas_detalle',         COUNT(*) FROM ventas_detalle
ORDER BY registros DESC;
