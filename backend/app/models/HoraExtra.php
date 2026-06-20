<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo HoraExtra — registro y aprobación de horas extra.
 * Tabla: horas_extra. Historia: NR-NOM-005.
 */
final class HoraExtra
{
    /** Recargos legales colombianos sobre el valor hora ordinaria. */
    public const RECARGOS = [
        'diurna'           => 1.25,
        'nocturna'         => 1.75,
        'festiva'          => 2.00,
        'nocturna_festiva' => 2.50,
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        $sql = "
            SELECT h.id, h.empleado_id, h.fecha, h.tipo, h.horas,
                   h.valor_hora, h.valor_total, h.estado,
                   e.codigo_empleado, e.tienda_id,
                   CONCAT(u.nombre, ' ', COALESCE(u.apellido, '')) AS empleado_nombre,
                   t.nombre AS tienda_nombre
            FROM horas_extra h
            INNER JOIN empleados e ON e.id = h.empleado_id
            INNER JOIN usuarios u  ON u.id = e.usuario_id
            INNER JOIN tiendas t   ON t.id = e.tienda_id
            WHERE 1 = 1
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND e.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY h.fecha DESC, h.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT h.*, e.tienda_id
            FROM horas_extra h
            INNER JOIN empleados e ON e.id = h.empleado_id
            WHERE h.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO horas_extra (
                empleado_id, fecha, tipo, horas, valor_hora, valor_total, estado
            ) VALUES (
                :empleado_id, :fecha, :tipo, :horas, :valor_hora, :valor_total, 'pendiente'
            )
        ");
        $stmt->execute([
            ':empleado_id' => $datos['empleado_id'],
            ':fecha'       => $datos['fecha'],
            ':tipo'        => $datos['tipo'],
            ':horas'       => $datos['horas'],
            ':valor_hora'  => $datos['valor_hora'],
            ':valor_total' => $datos['valor_total'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function cambiarEstado(int $id, string $estado, int $aprobadoPor): bool
    {
        $stmt = $this->db->prepare("
            UPDATE horas_extra
            SET estado = :estado, aprobado_por = :aprobado_por
            WHERE id = :id AND estado = 'pendiente'
        ");

        return $stmt->execute([
            ':id'           => $id,
            ':estado'       => $estado,
            ':aprobado_por' => $aprobadoPor,
        ]);
    }

    /** Total aprobado en un rango (para integrar a nómina — NR-INT-012). */
    public function totalAprobadoEmpleado(int $empleadoId, string $desde, string $hasta): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor_total), 0) AS total
            FROM horas_extra
            WHERE empleado_id = :empleado_id
              AND estado = 'aprobada'
              AND fecha BETWEEN :desde AND :hasta
        ");
        $stmt->execute([
            ':empleado_id' => $empleadoId,
            ':desde'       => $desde,
            ':hasta'       => $hasta,
        ]);

        return (float) ($stmt->fetch()['total'] ?? 0);
    }
}
