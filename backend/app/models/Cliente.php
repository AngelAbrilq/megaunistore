<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Cliente
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
                SELECT c.id, c.nombre, c.apellido, c.email, c.telefono,
                       c.tipo_documento, c.numero_documento, c.direccion, c.created_at
                FROM clientes c
                WHERE c.deleted_at IS NULL
                ORDER BY c.id DESC
            ");
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("
            SELECT c.id, c.nombre, c.apellido, c.email, c.telefono,
                   c.tipo_documento, c.numero_documento, c.direccion, c.created_at,
                   tc.puntos_fidelidad, tc.activo AS activo_tienda
            FROM clientes c
            INNER JOIN tiendas_clientes tc ON tc.cliente_id = c.id
            WHERE c.deleted_at IS NULL
              AND tc.tienda_id = :tienda_id
              AND tc.activo    = 1
            ORDER BY c.nombre ASC, c.apellido ASC
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, nombre, apellido, email, telefono,
                   tipo_documento, numero_documento, direccion, created_at
            FROM clientes
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorDocumento(string $tipo, string $numero): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, nombre, apellido, email, tipo_documento, numero_documento
            FROM clientes
            WHERE tipo_documento = :tipo AND numero_documento = :numero
              AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':tipo' => $tipo, ':numero' => $numero]);
        return $stmt->fetch() ?: null;
    }

    public function existeEmail(string $email, ?int $excluirId = null): bool
    {
        if ($excluirId === null) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM clientes
                WHERE email = :email AND deleted_at IS NULL
            ");
            $stmt->execute([':email' => strtolower(trim($email))]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total FROM clientes
                WHERE email = :email AND id <> :id AND deleted_at IS NULL
            ");
            $stmt->execute([':email' => strtolower(trim($email)), ':id' => $excluirId]);
        }
        return (int) $stmt->fetch()['total'] > 0;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO clientes (nombre, apellido, email, telefono, tipo_documento, numero_documento, direccion)
            VALUES (:nombre, :apellido, :email, :telefono, :tipo_documento, :numero_documento, :direccion)
        ");
        $stmt->execute([
            ':nombre'           => trim($datos['nombre']),
            ':apellido'         => trim($datos['apellido'] ?? ''),
            ':email'            => strtolower(trim($datos['email'] ?? '')),
            ':telefono'         => $datos['telefono'] ?: null,
            ':tipo_documento'   => $datos['tipo_documento'] ?: null,
            ':numero_documento' => $datos['numero_documento'] ?: null,
            ':direccion'        => $datos['direccion'] ?: null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clientes
            SET nombre = :nombre, apellido = :apellido, email = :email,
                telefono = :telefono, tipo_documento = :tipo_documento,
                numero_documento = :numero_documento, direccion = :direccion
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            ':id'               => $id,
            ':nombre'           => trim($datos['nombre']),
            ':apellido'         => trim($datos['apellido'] ?? ''),
            ':email'            => strtolower(trim($datos['email'] ?? '')),
            ':telefono'         => $datos['telefono'] ?: null,
            ':tipo_documento'   => $datos['tipo_documento'] ?: null,
            ':numero_documento' => $datos['numero_documento'] ?: null,
            ':direccion'        => $datos['direccion'] ?: null,
        ]);
    }

    public function eliminarLogico(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clientes SET deleted_at = NOW()
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function asociarATienda(int $clienteId, int $tiendaId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO tiendas_clientes (tienda_id, cliente_id, puntos_fidelidad, activo)
            VALUES (:tienda_id, :cliente_id, 0, 1)
            ON DUPLICATE KEY UPDATE activo = 1
        ");
        $stmt->execute([':tienda_id' => $tiendaId, ':cliente_id' => $clienteId]);
    }

    public function crearYAsociar(array $datos, int $tiendaId): int
    {
        $this->db->beginTransaction();
        try {
            $clienteId = $this->crear($datos);
            $this->asociarATienda($clienteId, $tiendaId);
            $this->db->commit();
            return $clienteId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function listarParaSelect(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $stmt = $this->db->query("
                SELECT id, nombre, apellido, numero_documento
                FROM clientes WHERE deleted_at IS NULL ORDER BY nombre ASC
            ");
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("
            SELECT c.id, c.nombre, c.apellido, c.numero_documento
            FROM clientes c
            INNER JOIN tiendas_clientes tc ON tc.cliente_id = c.id
            WHERE c.deleted_at IS NULL AND tc.tienda_id = :tienda_id AND tc.activo = 1
            ORDER BY c.nombre ASC
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);
        return $stmt->fetchAll();
    }
}
