<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Empleado
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $stmt = $this->db->query("
                SELECT e.id, e.usuario_id, e.tienda_id, e.codigo_empleado,
                       e.fecha_ingreso, e.salario_base, e.estado, e.deleted_at,
                       u.nombre AS usuario_nombre, u.apellido AS usuario_apellido,
                       u.email  AS usuario_email,
                       t.nombre AS tienda_nombre
                FROM empleados e
                INNER JOIN usuarios u ON u.id = e.usuario_id
                INNER JOIN tiendas  t ON t.id = e.tienda_id
                WHERE e.deleted_at IS NULL
                ORDER BY e.id DESC
            ");
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("
            SELECT e.id, e.usuario_id, e.tienda_id, e.codigo_empleado,
                   e.fecha_ingreso, e.salario_base, e.estado, e.deleted_at,
                   u.nombre AS usuario_nombre, u.apellido AS usuario_apellido,
                   u.email  AS usuario_email,
                   t.nombre AS tienda_nombre
            FROM empleados e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN tiendas  t ON t.id = e.tienda_id
            WHERE e.deleted_at IS NULL
              AND e.tienda_id = :tienda_id
            ORDER BY u.nombre ASC
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT e.id, e.usuario_id, e.tienda_id, e.codigo_empleado,
                   e.fecha_ingreso, e.salario_base, e.estado, e.deleted_at,
                   u.nombre AS usuario_nombre, u.apellido AS usuario_apellido,
                   u.email  AS usuario_email,
                   t.nombre AS tienda_nombre
            FROM empleados e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN tiendas  t ON t.id = e.tienda_id
            WHERE e.id = :id AND e.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function existeCodigoEnTienda(string $codigo, int $tiendaId, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM empleados
                WHERE codigo_empleado = :codigo AND tienda_id = :tienda_id AND deleted_at IS NULL
            ");
            $stmt->execute([':codigo' => $codigo, ':tienda_id' => $tiendaId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM empleados
                WHERE codigo_empleado = :codigo AND tienda_id = :tienda_id
                  AND id <> :id AND deleted_at IS NULL
            ");
            $stmt->execute([':codigo' => $codigo, ':tienda_id' => $tiendaId, ':id' => $excluirId]);
        }
        return (int) $stmt->fetch()['total'] > 0;
    }

    public function usuarioYaEsEmpleadoEnTienda(int $usuarioId, int $tiendaId, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM empleados
                WHERE usuario_id = :usuario_id AND tienda_id = :tienda_id AND deleted_at IS NULL
            ");
            $stmt->execute([':usuario_id' => $usuarioId, ':tienda_id' => $tiendaId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM empleados
                WHERE usuario_id = :usuario_id AND tienda_id = :tienda_id
                  AND id <> :id AND deleted_at IS NULL
            ");
            $stmt->execute([':usuario_id' => $usuarioId, ':tienda_id' => $tiendaId, ':id' => $excluirId]);
        }
        return (int) $stmt->fetch()['total'] > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO empleados (usuario_id, tienda_id, codigo_empleado, fecha_ingreso, salario_base, estado)
            VALUES (:usuario_id, :tienda_id, :codigo_empleado, :fecha_ingreso, :salario_base, :estado)
        ");
        $stmt->execute([
            ':usuario_id'      => (int) $datos['usuario_id'],
            ':tienda_id'       => (int) $datos['tienda_id'],
            ':codigo_empleado' => trim($datos['codigo_empleado']),
            ':fecha_ingreso'   => $datos['fecha_ingreso'],
            ':salario_base'    => $datos['salario_base'],
            ':estado'          => $datos['estado'] ?? 'activo',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE empleados
            SET codigo_empleado = :codigo_empleado,
                fecha_ingreso   = :fecha_ingreso,
                salario_base    = :salario_base,
                estado          = :estado
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            ':id'              => $id,
            ':codigo_empleado' => trim($datos['codigo_empleado']),
            ':fecha_ingreso'   => $datos['fecha_ingreso'],
            ':salario_base'    => $datos['salario_base'],
            ':estado'          => $datos['estado'] ?? 'activo',
        ]);
    }

    public function eliminarLogico(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE empleados SET deleted_at = NOW(), estado = 'inactivo'
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function listarParaSelect(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $stmt = $this->db->query("
                SELECT e.id, e.codigo_empleado,
                       u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
                FROM empleados e
                INNER JOIN usuarios u ON u.id = e.usuario_id
                WHERE e.deleted_at IS NULL AND e.estado = 'activo'
                ORDER BY u.nombre ASC
            ");
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("
            SELECT e.id, e.codigo_empleado,
                   u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
            FROM empleados e
            INNER JOIN usuarios u ON u.id = e.usuario_id
            WHERE e.deleted_at IS NULL AND e.estado = 'activo'
              AND e.tienda_id = :tienda_id
            ORDER BY u.nombre ASC
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);
        return $stmt->fetchAll();
    }
}
