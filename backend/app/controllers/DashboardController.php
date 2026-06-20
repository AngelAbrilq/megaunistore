<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * DashboardController — Fase 4
 *
 * Centraliza todas las queries de KPIs y datos para gráficas por rol.
 * Cada método público devuelve un array con:
 *   'kpis'      => [ ['icon', 'label', 'value', 'sub', 'color'], ... ]
 *   'chartData' => [ 'ventas7dias' => [...], 'topProductos' => [...], ... ]
 */
final class DashboardController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // SUPERADMIN — visión global de toda la plataforma
    // =========================================================================

    public function superadmin(): array
    {
        // --- KPIs globales ---
        $tiendasActivas = (int) $this->scalar("
            SELECT COUNT(*) FROM tiendas WHERE estado = 1 AND deleted_at IS NULL
        ");

        $usuariosActivos = (int) $this->scalar("
            SELECT COUNT(*) FROM usuarios WHERE estado = 1 AND deleted_at IS NULL
        ");

        $ventasMes = (float) $this->scalar("
            SELECT COALESCE(SUM(total), 0)
            FROM ventas
            WHERE estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        ");

        $ventasMesCount = (int) $this->scalar("
            SELECT COUNT(*)
            FROM ventas
            WHERE estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        ");

        $devolucionesPendientes = (int) $this->scalar("
            SELECT COUNT(*) FROM devoluciones WHERE estado = 'pendiente' AND deleted_at IS NULL
        ");

        $kpis = [
            [
                'icon'  => '🏪',
                'label' => 'Tiendas activas',
                'value' => number_format($tiendasActivas),
                'sub'   => 'en la plataforma',
                'color' => '#2563eb',
            ],
            [
                'icon'  => '👤',
                'label' => 'Usuarios activos',
                'value' => number_format($usuariosActivos),
                'sub'   => 'cuentas habilitadas',
                'color' => '#7c3aed',
            ],
            [
                'icon'  => '💰',
                'label' => 'Ventas este mes',
                'value' => '$' . number_format($ventasMes, 2),
                'sub'   => number_format($ventasMesCount) . ' transacciones',
                'color' => '#16a34a',
            ],
            [
                'icon'  => '🔄',
                'label' => 'Devoluciones pendientes',
                'value' => number_format($devolucionesPendientes),
                'sub'   => 'requieren atención',
                'color' => $devolucionesPendientes > 0 ? '#dc2626' : '#6b7280',
            ],
        ];

        // --- Chart 1: Ventas globales últimos 7 días ---
        $ventas7 = $this->ventasPorDia(null, 7);

        // --- Chart 2: Ventas por tienda (mes actual) ---
        $stmt = $this->db->query("
            SELECT t.nombre AS tienda, COALESCE(SUM(v.total), 0) AS total
            FROM tiendas t
            LEFT JOIN ventas v
                ON v.tienda_id = t.id
               AND v.estado = 'completada'
               AND v.deleted_at IS NULL
               AND v.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
               AND v.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
            WHERE t.estado = 1 AND t.deleted_at IS NULL
            GROUP BY t.id, t.nombre
            ORDER BY total DESC
            LIMIT 10
        ");
        $ventasPorTienda = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'kpis' => $kpis,
            'chartData' => [
                'ventas7dias'    => $ventas7,
                'ventasPorTienda' => $ventasPorTienda,
            ],
        ];
    }

    // =========================================================================
    // ADMIN DE TIENDA — operación de su tienda
    // =========================================================================

    public function adminTienda(int $tiendaId): array
    {
        // --- KPIs ---
        $ventasHoy = (int) $this->scalar("
            SELECT COUNT(*)
            FROM ventas
            WHERE tienda_id = :tid AND estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= CURDATE() AND fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ", [':tid' => $tiendaId]);

        $ingresosHoy = (float) $this->scalar("
            SELECT COALESCE(SUM(total), 0)
            FROM ventas
            WHERE tienda_id = :tid AND estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= CURDATE() AND fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ", [':tid' => $tiendaId]);

        $ventasMes = (float) $this->scalar("
            SELECT COALESCE(SUM(total), 0)
            FROM ventas
            WHERE tienda_id = :tid AND estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        ", [':tid' => $tiendaId]);

        $bajosStock = (int) $this->scalar("
            SELECT COUNT(*)
            FROM inventario i
            WHERE i.tienda_id = :tid AND i.cantidad <= i.cantidad_minima
        ", [':tid' => $tiendaId]);

        $kpis = [
            [
                'icon'  => '🛒',
                'label' => 'Ventas hoy',
                'value' => number_format($ventasHoy),
                'sub'   => 'transacciones completadas',
                'color' => '#2563eb',
            ],
            [
                'icon'  => '💵',
                'label' => 'Ingresos hoy',
                'value' => '$' . number_format($ingresosHoy, 2),
                'sub'   => 'ventas del día',
                'color' => '#16a34a',
            ],
            [
                'icon'  => '📅',
                'label' => 'Ventas del mes',
                'value' => '$' . number_format($ventasMes, 2),
                'sub'   => date('F Y'),
                'color' => '#7c3aed',
            ],
            [
                'icon'  => '⚠️',
                'label' => 'Alertas de stock',
                'value' => number_format($bajosStock),
                'sub'   => 'productos bajo mínimo',
                'color' => $bajosStock > 0 ? '#dc2626' : '#16a34a',
            ],
        ];

        // --- Chart 1: Ventas últimos 7 días (tienda) ---
        $ventas7 = $this->ventasPorDia($tiendaId, 7);

        // --- Chart 2: Top 5 productos más vendidos (mes) ---
        $stmt = $this->db->prepare("
            SELECT p.nombre, COALESCE(SUM(vd.cantidad), 0) AS unidades
            FROM ventas_detalle vd
            JOIN ventas v ON v.id = vd.venta_id
            JOIN productos p ON p.id = vd.producto_id
            WHERE v.tienda_id = :tid
              AND v.estado = 'completada'
              AND v.deleted_at IS NULL
              AND v.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND v.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
            GROUP BY vd.producto_id, p.nombre
            ORDER BY unidades DESC
            LIMIT 5
        ");
        $stmt->execute([':tid' => $tiendaId]);
        $topProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'kpis' => $kpis,
            'chartData' => [
                'ventas7dias'  => $ventas7,
                'topProductos' => $topProductos,
            ],
        ];
    }

    // =========================================================================
    // VENDEDOR — sus ventas personales del día/mes
    // =========================================================================

    public function vendedor(int $tiendaId, int $usuarioId): array
    {
        // ventas.empleado_id → empleados.id → empleados.usuario_id
        // No existe columna vendedor_id en ventas; se hace JOIN con empleados.
        $ventasHoy = (int) $this->scalar("
            SELECT COUNT(*)
            FROM ventas v
            JOIN empleados e ON e.id = v.empleado_id AND e.usuario_id = :uid
            WHERE v.tienda_id = :tid
              AND v.estado = 'completada' AND v.deleted_at IS NULL
              AND v.fecha >= CURDATE() AND v.fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ", [':tid' => $tiendaId, ':uid' => $usuarioId]);

        $ingresosHoy = (float) $this->scalar("
            SELECT COALESCE(SUM(v.total), 0)
            FROM ventas v
            JOIN empleados e ON e.id = v.empleado_id AND e.usuario_id = :uid
            WHERE v.tienda_id = :tid
              AND v.estado = 'completada' AND v.deleted_at IS NULL
              AND v.fecha >= CURDATE() AND v.fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ", [':tid' => $tiendaId, ':uid' => $usuarioId]);

        $ventasMes = (int) $this->scalar("
            SELECT COUNT(*)
            FROM ventas v
            JOIN empleados e ON e.id = v.empleado_id AND e.usuario_id = :uid
            WHERE v.tienda_id = :tid
              AND v.estado = 'completada' AND v.deleted_at IS NULL
              AND v.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND v.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        ", [':tid' => $tiendaId, ':uid' => $usuarioId]);

        $ingresosMes = (float) $this->scalar("
            SELECT COALESCE(SUM(v.total), 0)
            FROM ventas v
            JOIN empleados e ON e.id = v.empleado_id AND e.usuario_id = :uid
            WHERE v.tienda_id = :tid
              AND v.estado = 'completada' AND v.deleted_at IS NULL
              AND v.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND v.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        ", [':tid' => $tiendaId, ':uid' => $usuarioId]);

        // Caja abierta actualmente en la tienda
        $cajaAbierta = (int) $this->scalar("
            SELECT COUNT(*)
            FROM cajas c
            WHERE c.tienda_id = :tid
              AND EXISTS (
                  SELECT 1 FROM cajas_movimientos cm
                  WHERE cm.caja_id = c.id AND cm.tipo = 'apertura'
                    AND cm.created_at >= CURDATE()
                    AND cm.created_at <  DATE_ADD(CURDATE(), INTERVAL 1 DAY)
              )
              AND NOT EXISTS (
                  SELECT 1 FROM cajas_movimientos cm2
                  WHERE cm2.caja_id = c.id AND cm2.tipo = 'cierre'
                    AND cm2.created_at >= CURDATE()
                    AND cm2.created_at <  DATE_ADD(CURDATE(), INTERVAL 1 DAY)
              )
        ", [':tid' => $tiendaId]);

        $kpis = [
            [
                'icon'  => '🛒',
                'label' => 'Mis ventas hoy',
                'value' => number_format($ventasHoy),
                'sub'   => '$' . number_format($ingresosHoy, 2) . ' generados',
                'color' => '#2563eb',
            ],
            [
                'icon'  => '📅',
                'label' => 'Ventas del mes',
                'value' => number_format($ventasMes),
                'sub'   => '$' . number_format($ingresosMes, 2) . ' este mes',
                'color' => '#7c3aed',
            ],
            [
                'icon'  => $cajaAbierta > 0 ? '🟢' : '🔴',
                'label' => 'Caja',
                'value' => $cajaAbierta > 0 ? 'Abierta' : 'Cerrada',
                'sub'   => $cajaAbierta > 0 ? 'sesión activa hoy' : 'sin apertura hoy',
                'color' => $cajaAbierta > 0 ? '#16a34a' : '#dc2626',
            ],
        ];

        return [
            'kpis'      => $kpis,
            'chartData' => [],
        ];
    }

    // =========================================================================
    // SUPERVISOR — control operativo de la tienda
    // =========================================================================

    public function supervisor(int $tiendaId): array
    {
        $ventasHoy = (int) $this->scalar("
            SELECT COUNT(*) FROM ventas
            WHERE tienda_id = :tid AND estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= CURDATE() AND fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ", [':tid' => $tiendaId]);

        $ingresosHoy = (float) $this->scalar("
            SELECT COALESCE(SUM(total), 0) FROM ventas
            WHERE tienda_id = :tid AND estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= CURDATE() AND fecha < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ", [':tid' => $tiendaId]);

        $devolucionesPendientes = (int) $this->scalar("
            SELECT COUNT(*) FROM devoluciones
            WHERE tienda_id = :tid AND estado = 'pendiente' AND deleted_at IS NULL
        ", [':tid' => $tiendaId]);

        $alertasStock = (int) $this->scalar("
            SELECT COUNT(*) FROM inventario
            WHERE tienda_id = :tid AND cantidad <= cantidad_minima
        ", [':tid' => $tiendaId]);

        $kpis = [
            [
                'icon'  => '💰',
                'label' => 'Ventas hoy',
                'value' => number_format($ventasHoy),
                'sub'   => '$' . number_format($ingresosHoy, 2),
                'color' => '#16a34a',
            ],
            [
                'icon'  => '🔄',
                'label' => 'Devoluciones pendientes',
                'value' => number_format($devolucionesPendientes),
                'sub'   => 'requieren revisión',
                'color' => $devolucionesPendientes > 0 ? '#dc2626' : '#6b7280',
            ],
            [
                'icon'  => '⚠️',
                'label' => 'Alertas de stock',
                'value' => number_format($alertasStock),
                'sub'   => 'productos bajo mínimo',
                'color' => $alertasStock > 0 ? '#d97706' : '#6b7280',
            ],
        ];

        $ventas7 = $this->ventasPorDia($tiendaId, 7);

        return [
            'kpis'      => $kpis,
            'chartData' => ['ventas7dias' => $ventas7],
        ];
    }

    // =========================================================================
    // BODEGUERO — estado del inventario
    // =========================================================================

    public function bodeguero(int $tiendaId): array
    {
        $totalProductos = (int) $this->scalar("
            SELECT COUNT(DISTINCT producto_id) FROM inventario WHERE tienda_id = :tid
        ", [':tid' => $tiendaId]);

        $bajosStock = (int) $this->scalar("
            SELECT COUNT(*) FROM inventario WHERE tienda_id = :tid AND cantidad <= cantidad_minima
        ", [':tid' => $tiendaId]);

        $sinStock = (int) $this->scalar("
            SELECT COUNT(*) FROM inventario WHERE tienda_id = :tid AND cantidad = 0
        ", [':tid' => $tiendaId]);

        $kpis = [
            [
                'icon'  => '📦',
                'label' => 'Productos en inventario',
                'value' => number_format($totalProductos),
                'sub'   => 'con registro activo',
                'color' => '#2563eb',
            ],
            [
                'icon'  => '⚠️',
                'label' => 'Bajo mínimo',
                'value' => number_format($bajosStock),
                'sub'   => 'requieren reposición',
                'color' => $bajosStock > 0 ? '#d97706' : '#16a34a',
            ],
            [
                'icon'  => '🚫',
                'label' => 'Sin stock',
                'value' => number_format($sinStock),
                'sub'   => 'agotados',
                'color' => $sinStock > 0 ? '#dc2626' : '#16a34a',
            ],
        ];

        // Top 5 productos más bajos en stock
        $stmt = $this->db->prepare("
            SELECT p.nombre, i.cantidad, i.cantidad_minima
            FROM inventario i
            JOIN productos p ON p.id = i.producto_id
            WHERE i.tienda_id = :tid AND i.cantidad <= i.cantidad_minima
            ORDER BY i.cantidad ASC
            LIMIT 5
        ");
        $stmt->execute([':tid' => $tiendaId]);
        $criticosStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'kpis'      => $kpis,
            'chartData' => ['criticosStock' => $criticosStock],
        ];
    }

    // =========================================================================
    // Helper: ventas agrupadas por día (últimos N días)
    // =========================================================================

    private function ventasPorDia(?int $tiendaId, int $dias): array
    {
        $sql = "
            SELECT
                DATE(fecha)              AS fecha,
                COUNT(*)                 AS cantidad,
                COALESCE(SUM(total), 0)  AS total
            FROM ventas
            WHERE estado = 'completada'
              AND deleted_at IS NULL
              AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
        ";

        $params = [':dias' => $dias - 1];

        if ($tiendaId !== null) {
            $sql .= ' AND tienda_id = :tid';
            $params[':tid'] = $tiendaId;
        }

        $sql .= ' GROUP BY DATE(fecha) ORDER BY fecha ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rellenar días sin ventas con 0
        $resultado = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $resultado[$fecha] = ['fecha' => $fecha, 'cantidad' => 0, 'total' => 0.0];
        }
        foreach ($rows as $row) {
            $resultado[$row['fecha']] = [
                'fecha'    => $row['fecha'],
                'cantidad' => (int)   $row['cantidad'],
                'total'    => (float) $row['total'],
            ];
        }

        return array_values($resultado);
    }

    // =========================================================================
    // Helper: ejecuta un scalar (primera columna, primera fila)
    // =========================================================================

    private function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : 0;
    }
}
