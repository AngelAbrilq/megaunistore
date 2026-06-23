<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Tienda
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(): array
    {
        $sql = "
            SELECT
                t.id,
                t.nombre,
                t.descripcion,
                t.logo_url,
                t.direccion,
                t.telefono,
                t.email,
                t.propietario_id,
                t.plataforma_id,
                t.estado,
                t.created_at,
                t.updated_at,
                u.nombre AS propietario_nombre,
                u.apellido AS propietario_apellido,
                u.email AS propietario_email
            FROM tiendas t
            INNER JOIN usuarios u ON u.id = t.propietario_id
            WHERE t.deleted_at IS NULL
            ORDER BY t.id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                logo_url,
                direccion,
                telefono,
                email,
                propietario_id,
                plataforma_id,
                estado,
                created_at,
                updated_at,
                updated_by
            FROM tiendas
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $tienda = $stmt->fetch();

        return $tienda ?: null;
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO tiendas (
                nombre,
                descripcion,
                logo_url,
                direccion,
                telefono,
                email,
                propietario_id,
                plataforma_id,
                estado,
                updated_by
            ) VALUES (
                :nombre,
                :descripcion,
                :logo_url,
                :direccion,
                :telefono,
                :email,
                :propietario_id,
                :plataforma_id,
                :estado,
                :updated_by
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':logo_url' => $datos['logo_url'] ?? null,
            ':direccion' => trim($datos['direccion']),
            ':telefono' => $datos['telefono'] ?? null,
            ':email' => $datos['email'] ?? null,
            ':propietario_id' => (int) $datos['propietario_id'],
            ':plataforma_id' => $datos['plataforma_id'] ?? null,
            ':estado' => $datos['estado'] ?? 1,
            ':updated_by' => $datos['updated_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE tiendas
            SET
                nombre = :nombre,
                descripcion = :descripcion,
                logo_url = :logo_url,
                direccion = :direccion,
                telefono = :telefono,
                email = :email,
                estado = :estado,
                updated_by = :updated_by
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':logo_url' => $datos['logo_url'] ?? null,
            ':direccion' => trim($datos['direccion']),
            ':telefono' => $datos['telefono'] ?? null,
            ':email' => $datos['email'] ?? null,
            ':estado' => $datos['estado'] ?? 1,
            ':updated_by' => $datos['updated_by'] ?? null,
        ]);
    }

    public function cambiarEstado(int $id, int $estado, ?int $updatedBy = null): bool
    {
        $sql = "
            UPDATE tiendas
            SET
                estado = :estado,
                updated_by = :updated_by
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':estado' => $estado,
            ':updated_by' => $updatedBy,
        ]);
    }

    public function eliminarLogico(int $id, ?int $updatedBy = null): bool
    {
        $sql = "
            UPDATE tiendas
            SET
                deleted_at = NOW(),
                estado = 0,
                updated_by = :updated_by
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':updated_by' => $updatedBy,
        ]);
    }
}