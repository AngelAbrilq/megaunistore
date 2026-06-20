-- =====================================================================
-- SCRIPT 1: VER TODA LA INFORMACIÓN DE LA BASE DE DATOS
-- Mega Uni Store v3
-- Uso: ejecutar en MySQL Workbench, phpMyAdmin o HeidiSQL
-- Cada sección tiene un encabezado para identificar la tabla
-- =====================================================================


-- ╔══════════════════════════════════════╗
-- ║        SISTEMA: ROLES Y PERMISOS     ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ ROLES ═══════════' AS info;
SELECT * FROM roles ORDER BY nivel;

SELECT '═══════════ PERMISOS ═══════════' AS info;
SELECT * FROM permisos ORDER BY modulo, accion;

SELECT '═══════════ ROLES → PERMISOS ═══════════' AS info;
SELECT rp.id, r.nombre AS rol, p.modulo, p.accion, p.nombre AS permiso
FROM roles_permisos rp
JOIN roles r ON r.id = rp.rol_id
JOIN permisos p ON p.id = rp.permiso_id
ORDER BY r.nombre, p.modulo;


-- ╔══════════════════════════════════════╗
-- ║          USUARIOS Y SESIONES         ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ USUARIOS ═══════════' AS info;
SELECT id, nombre, apellido, email, estado, telefono, avatar_url, deleted_at, created_at FROM usuarios ORDER BY id;

SELECT '═══════════ USUARIOS → ROLES ═══════════' AS info;
SELECT ur.id, u.nombre, u.email, r.nombre AS rol, t.nombre AS tienda
FROM usuarios_roles ur
JOIN usuarios u ON u.id = ur.usuario_id
JOIN roles r ON r.id = ur.rol_id
LEFT JOIN tiendas t ON t.id = ur.tienda_id
ORDER BY u.nombre;

SELECT '═══════════ SESIONES ACTIVAS ═══════════' AS info;
SELECT * FROM sesiones ORDER BY created_at DESC LIMIT 50;

SELECT '═══════════ SOLICITUDES CAMBIO CONTRASEÑA ═══════════' AS info;
SELECT * FROM solicitudes_cambio_contrasena ORDER BY created_at DESC;


-- ╔══════════════════════════════════════╗
-- ║          TIENDAS Y PLATAFORMA        ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ TIENDAS ═══════════' AS info;
SELECT * FROM tiendas ORDER BY id;

SELECT '═══════════ CONFIGURACIÓN POR TIENDA ═══════════' AS info;
SELECT * FROM tiendas_config ORDER BY tienda_id;

SELECT '═══════════ PLATAFORMA (CONFIG GLOBAL) ═══════════' AS info;
SELECT * FROM plataforma;

SELECT '═══════════ PLANES ═══════════' AS info;
SELECT * FROM planes ORDER BY id;

SELECT '═══════════ SUSCRIPCIONES ═══════════' AS info;
SELECT * FROM suscripciones ORDER BY created_at DESC;


-- ╔══════════════════════════════════════╗
-- ║     CATÁLOGOS Y TABLAS MAESTRAS      ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ CATEGORÍAS ═══════════' AS info;
SELECT * FROM categorias ORDER BY id;

SELECT '═══════════ UNIDADES DE MEDIDA ═══════════' AS info;
SELECT * FROM unidades_medida ORDER BY id;

SELECT '═══════════ IMPUESTOS ═══════════' AS info;
SELECT * FROM impuestos ORDER BY id;

SELECT '═══════════ MÉTODOS DE PAGO ═══════════' AS info;
SELECT * FROM metodos_pago ORDER BY id;

SELECT '═══════════ PROVEEDORES ═══════════' AS info;
SELECT * FROM proveedores ORDER BY id;

SELECT '═══════════ ATRIBUTOS (Variantes) ═══════════' AS info;
SELECT * FROM atributos ORDER BY id;


-- ╔══════════════════════════════════════╗
-- ║          PRODUCTOS E INVENTARIO      ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ PRODUCTOS ═══════════' AS info;
SELECT * FROM productos ORDER BY id;

SELECT '═══════════ PRODUCTOS → IMPUESTOS ═══════════' AS info;
SELECT * FROM productos_impuestos ORDER BY producto_id;

SELECT '═══════════ PRODUCTOS → PROVEEDORES ═══════════' AS info;
SELECT * FROM productos_proveedores ORDER BY producto_id;

SELECT '═══════════ PRODUCTOS → ATRIBUTOS ═══════════' AS info;
SELECT * FROM productos_atributos ORDER BY producto_id;

SELECT '═══════════ TIENDAS → PRODUCTOS ═══════════' AS info;
SELECT * FROM tiendas_productos ORDER BY tienda_id;

SELECT '═══════════ INVENTARIO ═══════════' AS info;
SELECT * FROM inventario ORDER BY id;

SELECT '═══════════ MOVIMIENTOS DE INVENTARIO ═══════════' AS info;
SELECT * FROM movimientos_inventario ORDER BY created_at DESC LIMIT 200;


-- ╔══════════════════════════════════════╗
-- ║            VENTAS Y CAJA             ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ CAJAS ═══════════' AS info;
SELECT * FROM cajas ORDER BY id;

SELECT '═══════════ MOVIMIENTOS DE CAJA ═══════════' AS info;
SELECT * FROM cajas_movimientos ORDER BY created_at DESC LIMIT 200;

SELECT '═══════════ VENTAS ═══════════' AS info;
SELECT * FROM ventas ORDER BY id;

SELECT '═══════════ DETALLE DE VENTAS ═══════════' AS info;
SELECT * FROM ventas_detalle ORDER BY venta_id;

SELECT '═══════════ PAGOS ═══════════' AS info;
SELECT * FROM pagos ORDER BY created_at DESC;

SELECT '═══════════ CUPONES ═══════════' AS info;
SELECT * FROM cupones ORDER BY id;

SELECT '═══════════ VENTAS → CUPONES ═══════════' AS info;
SELECT * FROM ventas_cupones ORDER BY venta_id;

SELECT '═══════════ DEVOLUCIONES ═══════════' AS info;
SELECT * FROM devoluciones ORDER BY id;

SELECT '═══════════ DETALLE DE DEVOLUCIONES ═══════════' AS info;
SELECT * FROM devoluciones_detalle ORDER BY devolucion_id;

SELECT '═══════════ COMPRAS (a proveedores) ═══════════' AS info;
SELECT * FROM compras ORDER BY id;

SELECT '═══════════ DETALLE DE COMPRAS ═══════════' AS info;
SELECT * FROM compras_detalle ORDER BY compra_id;


-- ╔══════════════════════════════════════╗
-- ║          CLIENTES Y PORTAL           ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ CLIENTES ═══════════' AS info;
SELECT * FROM clientes ORDER BY id;

SELECT '═══════════ TIENDAS → CLIENTES ═══════════' AS info;
SELECT * FROM tiendas_clientes ORDER BY tienda_id;


-- ╔══════════════════════════════════════╗
-- ║       EMPLEADOS Y ORGANIZACIÓN       ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ EMPLEADOS ═══════════' AS info;
SELECT * FROM empleados ORDER BY id;

SELECT '═══════════ CARGOS ═══════════' AS info;
SELECT * FROM cargos ORDER BY id;

SELECT '═══════════ ÁREAS ═══════════' AS info;
SELECT * FROM areas ORDER BY id;

SELECT '═══════════ CENTROS DE COSTO ═══════════' AS info;
SELECT * FROM centros_costo ORDER BY id;

SELECT '═══════════ EMPLEADOS → ÁREAS ═══════════' AS info;
SELECT * FROM empleados_areas ORDER BY empleado_id;

SELECT '═══════════ EMPLEADOS → CARGOS ═══════════' AS info;
SELECT * FROM empleados_cargos ORDER BY empleado_id;

SELECT '═══════════ CONTRATOS ═══════════' AS info;
SELECT * FROM contratos ORDER BY id;

SELECT '═══════════ HORARIOS ═══════════' AS info;
SELECT * FROM horarios ORDER BY id;

SELECT '═══════════ EMPLEADOS → HORARIOS ═══════════' AS info;
SELECT * FROM empleados_horarios ORDER BY empleado_id;

SELECT '═══════════ TURNOS ═══════════' AS info;
SELECT * FROM turnos ORDER BY id;


-- ╔══════════════════════════════════════╗
-- ║          NÓMINA Y RRHH               ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ CONCEPTOS DE NÓMINA ═══════════' AS info;
SELECT * FROM conceptos_nomina ORDER BY id;

SELECT '═══════════ NÓMINAS ═══════════' AS info;
SELECT * FROM nominas ORDER BY id;

SELECT '═══════════ NÓMINA → EMPLEADO ═══════════' AS info;
SELECT * FROM nomina_empleado ORDER BY nomina_id;

SELECT '═══════════ DETALLE DE NÓMINA ═══════════' AS info;
SELECT * FROM nomina_detalle ORDER BY id;

SELECT '═══════════ HORAS EXTRA ═══════════' AS info;
SELECT * FROM horas_extra ORDER BY id;

SELECT '═══════════ VACACIONES ═══════════' AS info;
SELECT * FROM vacaciones ORDER BY id;

SELECT '═══════════ PRESTACIONES SOCIALES ═══════════' AS info;
SELECT * FROM prestaciones_sociales ORDER BY id;

SELECT '═══════════ APORTES SEGURIDAD SOCIAL ═══════════' AS info;
SELECT * FROM aportes_seguridad_social ORDER BY id;


-- ╔══════════════════════════════════════╗
-- ║       CONTABILIDAD Y FINANZAS        ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ PERIODOS CONTABLES ═══════════' AS info;
SELECT * FROM periodos_contables ORDER BY id;

SELECT '═══════════ CUENTAS CONTABLES ═══════════' AS info;
SELECT * FROM cuentas_contables ORDER BY id;

SELECT '═══════════ ASIENTOS CONTABLES ═══════════' AS info;
SELECT * FROM asientos_contables ORDER BY id;

SELECT '═══════════ DETALLE DE ASIENTOS ═══════════' AS info;
SELECT * FROM asientos_detalle ORDER BY asiento_id;

SELECT '═══════════ CONCILIACIONES ═══════════' AS info;
SELECT * FROM conciliaciones ORDER BY id;

SELECT '═══════════ GASTOS ═══════════' AS info;
SELECT * FROM gastos ORDER BY id;

SELECT '═══════════ PRESUPUESTOS ═══════════' AS info;
SELECT * FROM presupuestos ORDER BY id;

SELECT '═══════════ DETALLE DE PRESUPUESTOS ═══════════' AS info;
SELECT * FROM presupuesto_detalle ORDER BY presupuesto_id;


-- ╔══════════════════════════════════════╗
-- ║    REPORTES, EXPORTACIONES Y ALERTAS ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ REPORTES ═══════════' AS info;
SELECT * FROM reportes ORDER BY created_at DESC LIMIT 100;

SELECT '═══════════ EXPORTACIONES ═══════════' AS info;
SELECT * FROM exportaciones ORDER BY created_at DESC LIMIT 100;

SELECT '═══════════ ENVÍOS DE REPORTE ═══════════' AS info;
SELECT * FROM envios_reporte ORDER BY created_at DESC LIMIT 100;

SELECT '═══════════ NOTIFICACIONES ═══════════' AS info;
SELECT * FROM notificaciones ORDER BY created_at DESC LIMIT 200;


-- ╔══════════════════════════════════════╗
-- ║            AUDITORÍA                 ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ AUDIT LOG ═══════════' AS info;
SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 200;


-- ╔══════════════════════════════════════╗
-- ║     TABLAS DE INFRAESTRUCTURA        ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ MIGRACIONES (Laravel) ═══════════' AS info;
SELECT * FROM migrations ORDER BY id;

SELECT '═══════════ JOBS FALLIDOS ═══════════' AS info;
SELECT * FROM failed_jobs ORDER BY failed_at DESC;

SELECT '═══════════ TOKENS DE ACCESO PERSONAL ═══════════' AS info;
SELECT * FROM personal_access_tokens ORDER BY id;

SELECT '═══════════ PASSWORD RESETS ═══════════' AS info;
SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 20;

SELECT '═══════════ TABLA users (Laravel legacy) ═══════════' AS info;
SELECT * FROM users ORDER BY id;


-- ╔══════════════════════════════════════╗
-- ║     RESUMEN: CONTEO POR TABLA        ║
-- ╚══════════════════════════════════════╝
SELECT '═══════════ RESUMEN: FILAS POR TABLA ═══════════' AS info;
SELECT 'roles' AS tabla, COUNT(*) AS total FROM roles UNION ALL
SELECT 'permisos', COUNT(*) FROM permisos UNION ALL
SELECT 'roles_permisos', COUNT(*) FROM roles_permisos UNION ALL
SELECT 'usuarios', COUNT(*) FROM usuarios UNION ALL
SELECT 'usuarios_roles', COUNT(*) FROM usuarios_roles UNION ALL
SELECT 'tiendas', COUNT(*) FROM tiendas UNION ALL
SELECT 'clientes', COUNT(*) FROM clientes UNION ALL
SELECT 'empleados', COUNT(*) FROM empleados UNION ALL
SELECT 'productos', COUNT(*) FROM productos UNION ALL
SELECT 'inventario', COUNT(*) FROM inventario UNION ALL
SELECT 'ventas', COUNT(*) FROM ventas UNION ALL
SELECT 'ventas_detalle', COUNT(*) FROM ventas_detalle UNION ALL
SELECT 'pagos', COUNT(*) FROM pagos UNION ALL
SELECT 'cajas', COUNT(*) FROM cajas UNION ALL
SELECT 'cupones', COUNT(*) FROM cupones UNION ALL
SELECT 'devoluciones', COUNT(*) FROM devoluciones UNION ALL
SELECT 'proveedores', COUNT(*) FROM proveedores UNION ALL
SELECT 'categorias', COUNT(*) FROM categorias UNION ALL
SELECT 'contratos', COUNT(*) FROM contratos UNION ALL
SELECT 'nominas', COUNT(*) FROM nominas UNION ALL
SELECT 'notificaciones', COUNT(*) FROM notificaciones UNION ALL
SELECT 'audit_log', COUNT(*) FROM audit_log UNION ALL
SELECT 'exportaciones', COUNT(*) FROM exportaciones UNION ALL
SELECT 'reportes', COUNT(*) FROM reportes
ORDER BY total DESC;
