<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo Vacacion — vacaciones, licencias, incapacidades y permisos.
 * Tabla: vacaciones. Historia: NR-NOM-006.
 */
final class Vacacion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        $sql = "
            SELECT v.id, v.empleado_id, v.tipo, v.fecha_inicio, v.fecha_fin,
                   v.dias, v.motivo, v.estado,
                   e.codigo_empleado, e.tienda_id,
                   CONCAT(u.nombre, ' ', COALESCE(u.apellido, '')) AS empleado_nombre,
                   t.nombre AS tienda_nombre
            FROM vacaciones v
            INNER JOIN empleados e ON e.id = v.empleado_id
            INNER JOIN usuarios u  ON u.id = e.usuario_id
            INNER JOIN tiendas t   ON t.id = e.tienda_id
            WHERE 1 = 1
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND e.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY v.fecha_inicio DESC, v.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT v.*, e.tienda_id
            FROM vacaciones v
            INNER JOIN empleados e ON e.id = v.empleado_id
            WHERE v.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /** Detecta solapamiento con otra novedad aprobada o solicitada del mismo empleado. */
    public function existeSolape(int $empleadoId, string $inicio, string $fin, ?int $excluirId = null): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM vacaciones
            WHERE empleado_id = :empleado_id
              AND estado <> 'rechazada'
              AND fecha_inicio <= :fin
              AND fecha_fin >= :inicio
        ";

        $parametros = [
            ':empleado_id' => $empleadoId,
            ':inicio'      => $inicio,
            ':fin'         => $fin,
        ];

        if ($excluirId !== null) {
            $sql .= " AND id <> :excluir";
            $parametros[':excluir'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return (int) $stmt->fetch()['total'] > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO vacaciones (
                empleado_id, tipo, fecha_inicio, fecha_fin, dias, motivo, estado
            ) VALUES (
                :empleado_id, :tipo, :fecha_inicio, :fecha_fin, :dias, :motivo, 'solicitada'
            )
        ");
        $stmt->execute([
            ':empleado_id'  => $datos['empleado_id'],
            ':tipo'         => $datos['tipo'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin'    => $datos['fecha_fin'],
            ':dias'         => $datos['dias'],
            ':motivo'       => $datos['motivo'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function cambiarEstado(int $id, string $estado, int $aprobadoPor): bool
    {
        $stmt = $this->db->prepare("
            UPDATE vacaciones
            SET estado = :estado, aprobado_por = :aprobado_por
            WHERE id = :id AND estado = 'solicitada'
        ");

        return $stmt->execute([
            ':id'           => $id,
            ':estado'       => $estado,
            ':aprobado_por' => $aprobadoPor,
        ]);
    }
}
