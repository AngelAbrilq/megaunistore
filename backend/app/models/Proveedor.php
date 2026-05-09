<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Proveedor
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->db->query("
            SELECT id, nombre, ruc_nit, telefono, email,
                   direccion, contacto_nombre, estado, deleted_at
            FROM proveedores
            WHERE deleted_at IS NULL
            ORDER BY id DESC
        ");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, nombre, ruc_nit, telefono, email,
                   direccion, contacto_nombre, estado, deleted_at
            FROM proveedores
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function existeNit(string $nit, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM proveedores
                WHERE ruc_nit = :nit AND deleted_at IS NULL
            ");
            $stmt->execute([':nit' => trim($nit)]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM proveedores
                WHERE ruc_nit = :nit AND id <> :id AND deleted_at IS NULL
            ");
            $stmt->execute([':nit' => trim($nit), ':id' => $excluirId]);
        }
        return (int) $stmt->fetch()['total'] > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO proveedores (nombre, ruc_nit, telefono, email, direccion, contacto_nombre, estado)
            VALUES (:nombre, :ruc_nit, :telefono, :email, :direccion, :contacto_nombre, :estado)
        ");
        $stmt->execute([
            ':nombre'          => trim($datos['nombre']),
            ':ruc_nit'         => trim($datos['ruc_nit']),
            ':telefono'        => $datos['telefono'] ?: null,
            ':email'           => strtolower(trim($datos['email'] ?? '')),
            ':direccion'       => $datos['direccion'] ?: null,
            ':contacto_nombre' => $datos['contacto_nombre'] ?: null,
            ':estado'          => 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE proveedores
            SET nombre = :nombre, ruc_nit = :ruc_nit, telefono = :telefono,
                email = :email, direccion = :direccion, contacto_nombre = :contacto_nombre,
                estado = :estado
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            ':id'              => $id,
            ':nombre'          => trim($datos['nombre']),
            ':ruc_nit'         => trim($datos['ruc_nit']),
            ':telefono'        => $datos['telefono'] ?: null,
            ':email'           => strtolower(trim($datos['email'] ?? '')),
            ':direccion'       => $datos['direccion'] ?: null,
            ':contacto_nombre' => $datos['contacto_nombre'] ?: null,
            ':estado'          => (int) ($datos['estado'] ?? 1),
        ]);
    }

    public function cambiarEstado(int $id, int $estado): bool
    {
        $stmt = $this->db->prepare("
            UPDATE proveedores SET estado = :estado
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id, ':estado' => $estado]);
    }

    public function eliminarLogico(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE proveedores SET deleted_at = NOW(), estado = 0
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function listarParaSelect(): array
    {
        $stmt = $this->db->query("
            SELECT id, nombre, ruc_nit FROM proveedores
            WHERE deleted_at IS NULL AND estado = 1
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll();
    }
}
