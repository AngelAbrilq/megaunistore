<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Impuesto
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
                id,
                nombre,
                descripcion,
                porcentaje,
                tipo,
                activo
            FROM impuestos
            ORDER BY id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function listarActivos(): array
    {
        $sql = "
            SELECT
                id,
                nombre,
                porcentaje,
                tipo
            FROM impuestos
            WHERE activo = 1
            ORDER BY nombre ASC
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
                porcentaje,
                tipo,
                activo
            FROM impuestos
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $impuesto = $stmt->fetch();

        return $impuesto ?: null;
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO impuestos (
                nombre,
                descripcion,
                porcentaje,
                tipo,
                activo
            ) VALUES (
                :nombre,
                :descripcion,
                :porcentaje,
                :tipo,
                :activo
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':porcentaje' => $datos['porcentaje'],
            ':tipo' => trim($datos['tipo']),
            ':activo' => $datos['activo'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE impuestos
            SET
                nombre = :nombre,
                descripcion = :descripcion,
                porcentaje = :porcentaje,
                tipo = :tipo,
                activo = :activo
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':porcentaje' => $datos['porcentaje'],
            ':tipo' => trim($datos['tipo']),
            ':activo' => $datos['activo'] ?? 1,
        ]);
    }

    public function cambiarEstado(int $id, int $activo): bool
    {
        $sql = "
            UPDATE impuestos
            SET activo = :activo
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':activo' => $activo,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            DELETE FROM impuestos
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
        ]);
    }
}