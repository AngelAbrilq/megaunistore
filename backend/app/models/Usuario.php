<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO usuarios (
                nombre,
                apellido,
                email,
                password_hash,
                telefono,
                avatar_url,
                estado
            ) VALUES (
                :nombre,
                :apellido,
                :email,
                :password_hash,
                :telefono,
                :avatar_url,
                :estado
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':apellido' => trim($datos['apellido']),
            ':email' => strtolower(trim($datos['email'])),
            ':password_hash' => password_hash($datos['password'], PASSWORD_DEFAULT),
            ':telefono' => $datos['telefono'] ?? null,
            ':avatar_url' => $datos['avatar_url'] ?? null,
            ':estado' => $datos['estado'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                apellido,
                email,
                telefono,
                avatar_url,
                estado,
                created_at
            FROM usuarios
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                apellido,
                email,
                password_hash,
                telefono,
                avatar_url,
                estado,
                deleted_at,
                created_at
            FROM usuarios
            WHERE email = :email
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => strtolower(trim($email)),
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function buscarActivoPorEmail(string $email): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                apellido,
                email,
                password_hash,
                telefono,
                avatar_url,
                estado,
                created_at
            FROM usuarios
            WHERE email = :email
              AND estado = 1
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => strtolower(trim($email)),
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function existeEmail(string $email): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM usuarios
            WHERE email = :email
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => strtolower(trim($email)),
        ]);

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "
            UPDATE usuarios
            SET
                nombre = :nombre,
                apellido = :apellido,
                email = :email,
                telefono = :telefono,
                avatar_url = :avatar_url,
                estado = :estado
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => trim($datos['nombre']),
            ':apellido' => trim($datos['apellido']),
            ':email' => strtolower(trim($datos['email'])),
            ':telefono' => $datos['telefono'] ?? null,
            ':avatar_url' => $datos['avatar_url'] ?? null,
            ':estado' => $datos['estado'] ?? 1,
        ]);
    }

    public function cambiarPassword(int $id, string $nuevaPassword): bool
    {
        $sql = "
            UPDATE usuarios
            SET password_hash = :password_hash
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':password_hash' => password_hash($nuevaPassword, PASSWORD_DEFAULT),
        ]);
    }

    public function eliminarLogico(int $id): bool
    {
        $sql = "
            UPDATE usuarios
            SET
                deleted_at = NOW(),
                estado = 0
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
        ]);
    }

    public function verificarPassword(string $passwordPlano, string $passwordHash): bool
    {
        return password_verify($passwordPlano, $passwordHash);
    }





    

public function listar(): array
{
    $sql = "
        SELECT
            id,
            nombre,
            apellido,
            email,
            telefono,
            avatar_url,
            estado,
            deleted_at,
            created_at
        FROM usuarios
        WHERE deleted_at IS NULL
        ORDER BY id DESC
    ";

    $stmt = $this->db->query($sql);

    return $stmt->fetchAll();
}

public function crearAdministrativo(array $datos): int
{
    $sql = "
        INSERT INTO usuarios (
            nombre,
            apellido,
            email,
            password_hash,
            telefono,
            avatar_url,
            estado
        ) VALUES (
            :nombre,
            :apellido,
            :email,
            :password_hash,
            :telefono,
            :avatar_url,
            :estado
        )
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        ':nombre' => trim($datos['nombre']),
        ':apellido' => trim($datos['apellido']),
        ':email' => strtolower(trim($datos['email'])),
        ':password_hash' => password_hash($datos['password'], PASSWORD_DEFAULT),
        ':telefono' => $datos['telefono'] ?? null,
        ':avatar_url' => $datos['avatar_url'] ?? null,
        ':estado' => $datos['estado'] ?? 1,
    ]);

    return (int) $this->db->lastInsertId();
}

public function cambiarEstado(int $id, int $estado): bool
{
    $sql = "
        UPDATE usuarios
        SET estado = :estado
        WHERE id = :id
          AND deleted_at IS NULL
    ";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':id' => $id,
        ':estado' => $estado,
    ]);
}







}