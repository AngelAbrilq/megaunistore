<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Reporte
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // Reportes de ventas
    // =========================================================================

    public function ventasPorPeriodo(string $fechaInicio, string $fechaFin, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                DATE(v.fecha) AS fecha,
                COUNT(v.id) AS total_ventas,
                SUM(v.subtotal) AS subtotal,
                SUM(v.descuento) AS descuento,
                SUM(v.impuesto) AS impuesto,
                SUM(v.total) AS total
            FROM ventas v
            WHERE v.deleted_at IS NULL
              AND v.estado = 'completada'
              AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
        ";

        if ($tiendaId !== null) {
            $sql .= " AND v.tienda_id = :tienda_id";
        }

        $sql .= " GROUP BY DATE(v.fecha) ORDER BY fecha DESC";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ];

        if ($tiendaId !== null) {
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function ventasPorTienda(string $fechaInicio, string $fechaFin): array
    {
        $sql = "
            SELECT
                t.id,
                t.nombre AS tienda,
                COUNT(v.id) AS total_ventas,
                SUM(v.total) AS total_ingresos
            FROM tiendas t
            LEFT JOIN ventas v ON v.tienda_id = t.id
                AND v.deleted_at IS NULL
                AND v.estado = 'completada'
                AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
            WHERE t.deleted_at IS NULL
            GROUP BY t.id, t.nombre
            ORDER BY total_ingresos DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ]);

        return $stmt->fetchAll();
    }

    public function productosMasVendidos(string $fechaInicio, string $fechaFin, ?int $tiendaId = null, int $limite = 10): array
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.codigo_barras,
                SUM(vd.cantidad) AS cantidad_vendida,
                SUM(vd.subtotal) AS total_ventas,
                COUNT(DISTINCT v.id) AS numero_ventas
            FROM ventas_detalle vd
            INNER JOIN ventas v ON v.id = vd.venta_id
            INNER JOIN productos p ON p.id = vd.producto_id
            WHERE v.deleted_at IS NULL
              AND v.estado = 'completada'
              AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
        ";

        if ($tiendaId !== null) {
            $sql .= " AND v.tienda_id = :tienda_id";
        }

        $sql .= "
            GROUP BY p.id, p.nombre, p.codigo_barras
            ORDER BY cantidad_vendida DESC
            LIMIT :limite
        ";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
            ':limite' => $limite,
        ];

        if ($tiendaId !== null) {
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function ventasPorMetodoPago(string $fechaInicio, string $fechaFin, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                mp.nombre AS metodo_pago,
                COUNT(DISTINCT p.venta_id) AS total_ventas,
                SUM(p.monto) AS total_monto
            FROM pagos p
            INNER JOIN metodos_pago mp ON mp.id = p.metodo_pago_id
            INNER JOIN ventas v ON v.id = p.venta_id
            WHERE v.deleted_at IS NULL
              AND v.estado = 'completada'
              AND p.estado = 'aprobado'
              AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
        ";

        if ($tiendaId !== null) {
            $sql .= " AND v.tienda_id = :tienda_id";
        }

        $sql .= " GROUP BY mp.id, mp.nombre ORDER BY total_monto DESC";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ];

        if ($tiendaId !== null) {
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // =========================================================================
    // Reportes de inventario
    // =========================================================================

    public function inventarioPorTienda(?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                t.nombre AS tienda,
                p.nombre AS producto,
                p.codigo_barras,
                i.cantidad AS stock_actual,
                i.cantidad_minima AS stock_minimo,
                i.cantidad_maxima AS stock_maximo,
                um.simbolo AS unidad,
                CASE
                    WHEN i.cantidad <= i.cantidad_minima THEN 'Bajo'
                    WHEN i.cantidad_maxima IS NOT NULL AND i.cantidad >= i.cantidad_maxima THEN 'Alto'
                    ELSE 'Normal'
                END AS estado_stock
            FROM inventario i
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            LEFT JOIN unidades_medida um ON um.id = p.unidad_medida_id
            WHERE t.deleted_at IS NULL
              AND p.deleted_at IS NULL
        ";

        if ($tiendaId !== null) {
            $sql .= " AND i.tienda_id = :tienda_id";
        }

        $sql .= " ORDER BY t.nombre, p.nombre";

        $stmt = $this->db->prepare($sql);

        if ($tiendaId !== null) {
            $stmt->execute([':tienda_id' => $tiendaId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    public function productosStockBajo(?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                t.nombre AS tienda,
                p.nombre AS producto,
                p.codigo_barras,
                i.cantidad AS stock_actual,
                i.cantidad_minima AS stock_minimo,
                um.simbolo AS unidad
            FROM inventario i
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            LEFT JOIN unidades_medida um ON um.id = p.unidad_medida_id
            WHERE t.deleted_at IS NULL
              AND p.deleted_at IS NULL
              AND i.cantidad <= i.cantidad_minima
        ";

        if ($tiendaId !== null) {
            $sql .= " AND i.tienda_id = :tienda_id";
        }

        $sql .= " ORDER BY i.cantidad ASC";

        $stmt = $this->db->prepare($sql);

        if ($tiendaId !== null) {
            $stmt->execute([':tienda_id' => $tiendaId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    public function movimientosInventario(string $fechaInicio, string $fechaFin, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                DATE(mi.created_at) AS fecha,
                t.nombre AS tienda,
                p.nombre AS producto,
                mi.tipo,
                mi.cantidad,
                mi.motivo,
                mi.ref_tipo,
                mi.ref_id
            FROM movimientos_inventario mi
            INNER JOIN inventario i ON i.id = mi.inventario_id
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            WHERE DATE(mi.created_at) BETWEEN :fecha_inicio AND :fecha_fin
        ";

        if ($tiendaId !== null) {
            $sql .= " AND i.tienda_id = :tienda_id";
        }

        $sql .= " ORDER BY mi.created_at DESC";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ];

        if ($tiendaId !== null) {
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // =========================================================================
    // Reportes de caja
    // =========================================================================

    public function movimientosCaja(string $fechaInicio, string $fechaFin, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                DATE(cm.created_at) AS fecha,
                t.nombre AS tienda,
                c.nombre AS caja,
                cm.tipo,
                cm.monto,
                cm.descripcion,
                cm.venta_id
            FROM cajas_movimientos cm
            INNER JOIN cajas c ON c.id = cm.caja_id
            INNER JOIN tiendas t ON t.id = c.tienda_id
            WHERE DATE(cm.created_at) BETWEEN :fecha_inicio AND :fecha_fin
        ";

        if ($tiendaId !== null) {
            $sql .= " AND c.tienda_id = :tienda_id";
        }

        $sql .= " ORDER BY cm.created_at DESC";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ];

        if ($tiendaId !== null) {
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // =========================================================================
    // Dashboard / Resumen
    // =========================================================================

    public function resumenGeneral(?int $tiendaId = null): array
    {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');

        // Ventas del día
        $ventasHoy = $this->obtenerTotalVentas($hoy, $hoy, $tiendaId);

        // Ventas del mes
        $ventasMes = $this->obtenerTotalVentas($inicioMes, $hoy, $tiendaId);

        // Productos con stock bajo
        $productosStockBajo = count($this->productosStockBajo($tiendaId));

        // Total de productos
        $totalProductos = $this->contarProductos($tiendaId);

        return [
            'ventas_hoy' => $ventasHoy,
            'ventas_mes' => $ventasMes,
            'productos_stock_bajo' => $productosStockBajo,
            'total_productos' => $totalProductos,
        ];
    }

    private function obtenerTotalVentas(string $fechaInicio, string $fechaFin, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                COUNT(v.id) AS total_ventas,
                COALESCE(SUM(v.total), 0) AS total_ingresos
            FROM ventas v
            WHERE v.deleted_at IS NULL
              AND v.estado = 'completada'
              AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
        ";

        if ($tiendaId !== null) {
            $sql .= " AND v.tienda_id = :tienda_id";
        }

        $stmt = $this->db->prepare($sql);

        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
        ];

        if ($tiendaId !== null) {
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt->execute($params);

        return $stmt->fetch() ?: ['total_ventas' => 0, 'total_ingresos' => 0];
    }

    private function contarProductos(?int $tiendaId = null): int
    {
        if ($tiendaId === null) {
            $sql = "SELECT COUNT(*) AS total FROM productos WHERE deleted_at IS NULL";
            $stmt = $this->db->query($sql);
        } else {
            $sql = "
                SELECT COUNT(DISTINCT i.producto_id) AS total
                FROM inventario i
                INNER JOIN productos p ON p.id = i.producto_id
                WHERE i.tienda_id = :tienda_id
                  AND p.deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':tienda_id' => $tiendaId]);
        }

        $resultado = $stmt->fetch();

        return (int) ($resultado['total'] ?? 0);
    }
}
