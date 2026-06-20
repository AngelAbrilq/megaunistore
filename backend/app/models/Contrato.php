<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Contrato
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null, ?int $empleadoId = null): array
    {
        $sql = "
            SELECT c.*,
                   u.nombre AS empleado_nombre, u.apellido AS empleado_apellido,
                   e.codigo_empleado, e.tienda_id,
                   t.nombre AS tienda_nombre
            FROM contratos c
            INNER JOIN empleados e ON e.id = c.empleado_id
            INNER JOIN usuarios  u ON u.id = e.usuario_id
            INNER JOIN tiendas   t ON t.id = e.tienda_id
            WHERE 1=1
        ";
        $params = [];

        if ($tiendaId !== null) {
            $sql .= ' AND e.tienda_id = :tienda_id';
            $params[':tienda_id'] = $tiendaId;
        }
        if ($empleadoId !== null) {
            $sql .= ' AND c.empleado_id = :empleado_id';
            $params[':empleado_id'] = $empleadoId;
        }

        $sql .= ' ORDER BY c.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.nombre AS empleado_nombre, u.apellido AS empleado_apellido,
                   e.codigo_empleado, e.tienda_id,
                   t.nombre AS tienda_nombre
            FROM contratos c
            INNER JOIN empleados e ON e.id = c.empleado_id
            INNER JOIN usuarios  u ON u.id = e.usuario_id
            INNER JOIN tiendas   t ON t.id = e.tienda_id
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarVigenteDeEmpleado(int $empleadoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*
            FROM contratos c
            WHERE c.empleado_id = :empleado_id
              AND c.estado = 'activo'
              AND (c.fecha_fin IS NULL OR c.fecha_fin >= CURDATE())
            ORDER BY c.id DESC
            LIMIT 1
        ");
        $stmt->execute([':empleado_id' => $empleadoId]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO contratos
                (empleado_id, tipo_contrato, fecha_inicio, fecha_fin,
                 salario_base, cargo_id, jornada, estado, eps_id, afp_id, arl_id)
            VALUES
                (:empleado_id, :tipo_contrato, :fecha_inicio, :fecha_fin,
                 :salario_base, :cargo_id, :jornada, 'activo', :eps_id, :afp_id, :arl_id)
        ");
        $stmt->execute([
            ':empleado_id'   => (int)   $datos['empleado_id'],
            ':tipo_contrato' => $datos['tipo_contrato'],
            ':fecha_inicio'  => $datos['fecha_inicio'],
            ':fecha_fin'     => $datos['fecha_fin'] ?: null,
            ':salario_base'  => (float) $datos['salario_base'],
            ':cargo_id'      => (int)   $datos['cargo_id'],
            ':jornada'       => $datos['jornada'] ?? 'completa',
            ':eps_id'        => $datos['eps_id']  ?? null,
            ':afp_id'        => $datos['afp_id']  ?? null,
            ':arl_id'        => $datos['arl_id']  ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        return $this->db->prepare("
            UPDATE contratos
            SET tipo_contrato = :tipo_contrato,
                fecha_inicio  = :fecha_inicio,
                fecha_fin     = :fecha_fin,
                salario_base  = :salario_base,
                cargo_id      = :cargo_id,
                jornada       = :jornada,
                eps_id        = :eps_id,
                afp_id        = :afp_id,
                arl_id        = :arl_id
            WHERE id = :id
        ")->execute([
            ':id'            => $id,
            ':tipo_contrato' => $datos['tipo_contrato'],
            ':fecha_inicio'  => $datos['fecha_inicio'],
            ':fecha_fin'     => $datos['fecha_fin'] ?: null,
            ':salario_base'  => (float) $datos['salario_base'],
            ':cargo_id'      => (int)   $datos['cargo_id'],
            ':jornada'       => $datos['jornada'] ?? 'completa',
            ':eps_id'        => $datos['eps_id']  ?? null,
            ':afp_id'        => $datos['afp_id']  ?? null,
            ':arl_id'        => $datos['arl_id']  ?? null,
        ]);
    }

    public function terminar(int $id): bool
    {
        return $this->db->prepare("
            UPDATE contratos SET estado = 'terminado', fecha_fin = CURDATE()
            WHERE id = :id AND estado = 'activo'
        ")->execute([':id' => $id]);
    }

    /** Cargos disponibles para una tienda */
    public function listarCargos(int $tiendaId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, nombre FROM cargos
            WHERE tienda_id = :tienda_id AND activo = 1
            ORDER BY nombre ASC
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);
        return $stmt->fetchAll();
    }
}
