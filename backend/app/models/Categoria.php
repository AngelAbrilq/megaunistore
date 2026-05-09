<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Categoria
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
                c.id,
                c.nombre,
                c.descripcion,
                c.categoria_padre_id,
                c.imagen_url,
                c.activo,
                c.deleted_at,
                cp.nombre AS categoria_padre_nombre
            FROM categorias c
            LEFT JOIN categorias cp ON cp.id = c.categoria_padre_id
            WHERE c.deleted_at IS NULL
            ORDER BY c.id DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function listarActivasParaSelect(?int $excluirId = null): array
    {
        if ($excluirId === null) {
            $sql = "
                SELECT
                    id,
                    nombre
                FROM categorias
                WHERE activo = 1
                  AND deleted_at IS NULL
                ORDER BY nombre ASC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT
                id,
                nombre
            FROM categorias
            WHERE activo = 1
              AND deleted_at IS NULL
              AND id <> :excluir_id
            ORDER BY nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':excluir_id' => $excluirId,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                categoria_padre_id,
                imagen_url,
                activo,
                deleted_at
            FROM categorias
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $categoria = $stmt->fetch();

        return $categoria ?: null;
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO categorias (
                nombre,
                descripcion,
                categoria_padre_id,
                imagen_url,
                activo
            ) VALUES (
                :nombre,
                :descripcion,
                :categoria_padre_id,
                :imagen_url,
                :activo
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':categoria_padre_id' => $datos['categoria_padre_id'] ?? null,
            ':imagen_url' => $datos['imagen_url'] ?? null,
            ':activo' => $datos['activo'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE categorias
            SET
                nombre = :nombre,
                descripcion = :descripcion,
                categoria_padre_id = :categoria_padre_id,
                imagen_url = :imagen_url,
                activo = :activo
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':categoria_padre_id' => $datos['categoria_padre_id'] ?? null,
            ':imagen_url' => $datos['imagen_url'] ?? null,
            ':activo' => $datos['activo'] ?? 1,
        ]);
    }

    public function cambiarEstado(int $id, int $activo): bool
    {
        $sql = "
            UPDATE categorias
            SET activo = :activo
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':activo' => $activo,
        ]);
    }

    public function eliminarLogico(int $id): bool
    {
        $sql = "
            UPDATE categorias
            SET
                deleted_at = NOW(),
                activo = 0
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
        ]);
    }
}