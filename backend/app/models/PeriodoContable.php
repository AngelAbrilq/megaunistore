<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo PeriodoContable — apertura y cierre de meses contables.
 * Tabla: periodos_contables. Historia: CF-CON-004.
 */
final class PeriodoContable
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        $sql = "
            SELECT p.id, p.tienda_id, p.nombre, p.fecha_inicio, p.fecha_fin,
                   p.estado, p.cerrado_at,
                   t.nombre AS tienda_nombre
            FROM periodos_contables p
            LEFT JOIN tiendas t ON t.id = p.tienda_id
            WHERE 1 = 1
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND p.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY p.fecha_inicio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM periodos_contables WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /** Encuentra el período abierto que contiene la fecha dada. */
    public function abiertoParaFecha(string $fecha, ?int $tiendaId = null): ?array
    {
        $sql = "
            SELECT * FROM periodos_contables
            WHERE estado = 'abierto'
              AND :fecha BETWEEN fecha_inicio AND fecha_fin
        ";

        $parametros = [':fecha' => $fecha];

        if ($tiendaId !== null) {
            $sql .= " AND tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY id DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO periodos_contables (tienda_id, nombre, fecha_inicio, fecha_fin, estado)
            VALUES (:tienda_id, :nombre, :fecha_inicio, :fecha_fin, 'abierto')
        ");
        $stmt->execute([
            ':tienda_id'    => $datos['tienda_id'],
            ':nombre'       => $datos['nombre'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin'    => $datos['fecha_fin'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function cerrar(int $id, int $usuarioId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE periodos_contables
            SET estado = 'cerrado', cerrado_por = :usuario, cerrado_at = NOW()
            WHERE id = :id AND estado = 'abierto'
        ");

        return $stmt->execute([':id' => $id, ':usuario' => $usuarioId]);
    }

    public function reabrir(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE periodos_contables
            SET estado = 'abierto', cerrado_por = NULL, cerrado_at = NULL
            WHERE id = :id AND estado = 'cerrado'
        ");

        return $stmt->execute([':id' => $id]);
    }
}
