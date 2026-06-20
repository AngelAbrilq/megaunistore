<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo Gasto — gastos operacionales por tienda.
 * Tabla: gastos. Historias: CF-CON-006, REQ-7.8.2.
 */
final class Gasto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        $sql = "
            SELECT g.id, g.tienda_id, g.concepto, g.monto, g.fecha,
                   g.comprobante, g.estado, g.cuenta_id, g.centro_costo_id,
                   t.nombre AS tienda_nombre,
                   p.nombre AS proveedor_nombre,
                   cc.nombre AS cuenta_nombre
            FROM gastos g
            INNER JOIN tiendas t ON t.id = g.tienda_id
            LEFT JOIN proveedores p ON p.id = g.proveedor_id
            LEFT JOIN cuentas_contables cc ON cc.id = g.cuenta_id
            WHERE g.deleted_at IS NULL
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND g.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY g.fecha DESC, g.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM gastos WHERE id = :id AND deleted_at IS NULL LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO gastos (
                tienda_id, cuenta_id, centro_costo_id, concepto, monto,
                fecha, proveedor_id, comprobante, estado, empleado_id
            ) VALUES (
                :tienda_id, :cuenta_id, :centro_costo_id, :concepto, :monto,
                :fecha, :proveedor_id, :comprobante, :estado, :empleado_id
            )
        ");
        $stmt->execute([
            ':tienda_id'       => $datos['tienda_id'],
            ':cuenta_id'       => $datos['cuenta_id'],
            ':centro_costo_id' => $datos['centro_costo_id'],
            ':concepto'        => $datos['concepto'],
            ':monto'           => $datos['monto'],
            ':fecha'           => $datos['fecha'],
            ':proveedor_id'    => $datos['proveedor_id'],
            ':comprobante'     => $datos['comprobante'],
            ':estado'          => $datos['estado'],
            ':empleado_id'     => $datos['empleado_id'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE gastos
            SET concepto = :concepto,
                monto = :monto,
                fecha = :fecha,
                cuenta_id = :cuenta_id,
                centro_costo_id = :centro_costo_id,
                proveedor_id = :proveedor_id,
                comprobante = :comprobante
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([
            ':id'              => $id,
            ':concepto'        => $datos['concepto'],
            ':monto'           => $datos['monto'],
            ':fecha'           => $datos['fecha'],
            ':cuenta_id'       => $datos['cuenta_id'],
            ':centro_costo_id' => $datos['centro_costo_id'],
            ':proveedor_id'    => $datos['proveedor_id'],
            ':comprobante'     => $datos['comprobante'],
        ]);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("
            UPDATE gastos SET estado = :estado WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([':id' => $id, ':estado' => $estado]);
    }

    public function eliminarLogico(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE gastos SET deleted_at = NOW() WHERE id = :id
        ");

        return $stmt->execute([':id' => $id]);
    }

    public function totalDelMes(?int $tiendaId = null): float
    {
        $sql = "
            SELECT COALESCE(SUM(monto), 0) AS total
            FROM gastos
            WHERE deleted_at IS NULL
              AND estado <> 'anulado'
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
