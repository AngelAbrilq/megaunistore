<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class UnidadMedida
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
                simbolo,
                tipo
            FROM unidades_medida
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
                simbolo,
                tipo
            FROM unidades_medida
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $unidad = $stmt->fetch();

        return $unidad ?: null;
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO unidades_medida (
                nombre,
                simbolo,
                tipo
            ) VALUES (
                :nombre,
                :simbolo,
                :tipo
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':simbolo' => trim($datos['simbolo']),
            ':tipo' => $datos['tipo'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE unidades_medida
            SET
                nombre = :nombre,
                simbolo = :simbolo,
                tipo = :tipo
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => trim($datos['nombre']),
            ':simbolo' => trim($datos['simbolo']),
            ':tipo' => $datos['tipo'] ?? null,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "
            DELETE FROM unidades_medida
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
        ]);
    }
}