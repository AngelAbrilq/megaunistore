<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo Compra — órdenes de compra a proveedores.
 * Tablas: compras, compras_detalle.
 * Historias: BD-COM-001, AT-COM-001, CF-INT-013.
 */
final class Compra
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        $sql = "
            SELECT c.id, c.tienda_id, c.proveedor_id, c.fecha,
                   c.subtotal, c.impuesto, c.total, c.estado,
                   t.nombre AS tienda_nombre,
                   p.nombre AS proveedor_nombre,
                   (SELECT COUNT(*) FROM compras_detalle cd WHERE cd.compra_id = c.id) AS items
            FROM compras c
            INNER JOIN tiendas t     ON t.id = c.tienda_id
            INNER JOIN proveedores p ON p.id = c.proveedor_id
            WHERE c.deleted_at IS NULL
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND c.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, t.nombre AS tienda_nombre, p.nombre AS proveedor_nombre
            FROM compras c
            INNER JOIN tiendas t     ON t.id = c.tienda_id
            INNER JOIN proveedores p ON p.id = c.proveedor_id
            WHERE c.id = :id AND c.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function obtenerDetalle(int $compraId): array
    {
        $stmt = $this->db->prepare("
            SELECT cd.id, cd.producto_id, cd.cantidad, cd.precio_unitario, cd.subtotal,
                   pr.nombre AS producto_nombre
            FROM compras_detalle cd
            INNER JOIN productos pr ON pr.id = cd.producto_id
            WHERE cd.compra_id = :compra_id
            ORDER BY cd.id ASC
        ");
        $stmt->execute([':compra_id' => $compraId]);

        return $stmt->fetchAll();
    }

    /**
     * Crea la orden de compra con sus líneas en una transacción.
     *
     * @param array $datos  tienda_id, proveedor_id, empleado_id, fecha, impuesto
     * @param array $lineas cada una: producto_id, cantidad, precio_unitario
     */
    public function crear(array $datos, array $lineas): int
    {
        $this->db->beginTransaction();

        try {
            $subtotal = 0.0;

            foreach ($lineas as $linea) {
                $subtotal += (float) $linea['cantidad'] * (float) $linea['precio_unitario'];
            }

            $impuesto = (float) ($datos['impuesto'] ?? 0);
            $total    = $subtotal + $impuesto;

            $stmt = $this->db->prepare("
                INSERT INTO compras (
                    tienda_id, proveedor_id, empleado_id, fecha,
                    subtotal, impuesto, total, estado
                ) VALUES (
                    :tienda_id, :proveedor_id, :empleado_id, :fecha,
                    :subtotal, :impuesto, :total, 'pendiente'
                )
            ");
            $stmt->execute([
                ':tienda_id'    => $datos['tienda_id'],
                ':proveedor_id' => $datos['proveedor_id'],
                ':empleado_id'  => $datos['empleado_id'],
                ':fecha'        => $datos['fecha'],
                ':subtotal'     => number_format($subtotal, 2, '.', ''),
                ':impuesto'     => number_format($impuesto, 2, '.', ''),
                ':total'        => number_format($total, 2, '.', ''),
            ]);

            $compraId = (int) $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare("
                INSERT INTO compras_detalle (
                    compra_id, producto_id, cantidad, precio_unitario, subtotal
                ) VALUES (
                    :compra_id, :producto_id, :cantidad, :precio_unitario, :subtotal
                )
            ");

            foreach ($lineas as $linea) {
                $cantidad = (float) $linea['cantidad'];
                $precio   = (float) $linea['precio_unitario'];

                $stmtDetalle->execute([
                    ':compra_id'       => $compraId,
                    ':producto_id'     => (int) $linea['producto_id'],
                    ':cantidad'        => number_format($cantidad, 2, '.', ''),
                    ':precio_unitario' => number_format($precio, 2, '.', ''),
                    ':subtotal'        => number_format($cantidad * $precio, 2, '.', ''),
                ]);
            }

            $this->db->commit();

            return $compraId;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("
            UPDATE compras
            SET estado = :estado, updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([':id' => $id, ':estado' => $estado]);
    }

    public function eliminarLogico(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE compras SET deleted_at = NOW() WHERE id = :id
        ");

        return $stmt->execute([':id' => $id]);
    }

    /** Total de compras del mes para reportes/dashboard. */
    public function totalDelMes(?int $tiendaId = null): float
    {
        $sql = "
            SELECT COALESCE(SUM(total), 0) AS total
            FROM compras
            WHERE deleted_at IS NULL
              AND estado = 'recibida'
              AND DATE_FORMAT(fecha, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return (float) ($stmt->fetch()['total'] ?? 0);
    }
}
