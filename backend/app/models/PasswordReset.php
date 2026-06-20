<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Gestiona tokens de reset de contraseña (Flujo A) y
 * solicitudes de cambio con aprobación admin (Flujo C).
 */
final class PasswordReset
{
    private PDO $db;

    /** Evita ejecutar los 2 DDL CREATE TABLE en cada instanciación del request. */
    private static bool $tablasCreadas = false;

    public function __construct()
    {
        $this->db = Database::getConnection();

        if (!self::$tablasCreadas) {
            $this->crearTablasIfNotExist();
            self::$tablasCreadas = true;
        }
    }

    /**
     * Crea las tablas del módulo contraseñas si no existen.
     * Gracias al guard estático, se ejecuta como máximo una vez por request.
     */
    private function crearTablasIfNotExist(): void
    {
        // password_resets — tokens de reset por email (Flujo A)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `password_resets` (
                `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `email`      VARCHAR(255)    NOT NULL,
                `token`      VARCHAR(64)     NOT NULL,
                `expires_at` DATETIME        NOT NULL,
                `used_at`    DATETIME        NULL DEFAULT NULL,
                `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_token` (`token`),
                INDEX `idx_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // solicitudes_cambio_contrasena — aprobación por admin (Flujo C)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `solicitudes_cambio_contrasena` (
                `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `usuario_id`           INT UNSIGNED NOT NULL,
                `nuevo_password_hash`  VARCHAR(255) NOT NULL,
                `estado`               ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
                `admin_id`             INT UNSIGNED NULL DEFAULT NULL,
                `motivo_rechazo`       VARCHAR(255) NULL DEFAULT NULL,
                `created_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_usuario` (`usuario_id`),
                INDEX `idx_estado` (`estado`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * usuarios_roles no tiene una columna es_principal. Para mantener una sola
     * fila por usuario, usamos el rol activo de menor nivel y luego el id menor.
     */
    private function sqlUsuarioRolPrincipal(): string
    {
        return "
            SELECT ur1.usuario_id, ur1.rol_id, ur1.tienda_id
            FROM usuarios_roles ur1
            INNER JOIN roles r1 ON r1.id = ur1.rol_id
            WHERE r1.activo = 1
              AND NOT EXISTS (
                  SELECT 1
                  FROM usuarios_roles ur2
                  INNER JOIN roles r2 ON r2.id = ur2.rol_id
                  WHERE ur2.usuario_id = ur1.usuario_id
                    AND r2.activo = 1
                    AND (
                        r2.nivel < r1.nivel
                        OR (r2.nivel = r1.nivel AND r2.id < r1.id)
                        OR (r2.nivel = r1.nivel AND r2.id = r1.id AND ur2.id < ur1.id)
                    )
              )
        ";
    }

    // =========================================================================
    // Flujo A — Reset por email
    // =========================================================================

    /**
     * Genera un token único, lo guarda y devuelve el token en texto plano.
     * Invalida tokens previos del mismo email para evitar acumulación.
     */
    public function crearToken(string $email): string
    {
        // Borrar tokens previos no usados para este email
        $this->db->prepare(
            "DELETE FROM password_resets WHERE email = :email AND used_at IS NULL"
        )->execute([':email' => $email]);

        $token     = bin2hex(random_bytes(32)); // 64 chars hex
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora

        $stmt = $this->db->prepare("
            INSERT INTO password_resets (email, token, expires_at)
            VALUES (:email, :token, :expires_at)
        ");
        $stmt->execute([
            ':email'      => $email,
            ':token'      => $token,
            ':expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Busca un token válido (no usado, no expirado).
     */
    public function buscarTokenValido(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, email, token, expires_at
            FROM password_resets
            WHERE token      = :token
              AND used_at    IS NULL
              AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([':token' => $token]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Marca un token como usado (single-use).
     */
    public function marcarTokenUsado(string $token): void
    {
        $this->db->prepare(
            "UPDATE password_resets SET used_at = NOW() WHERE token = :token"
        )->execute([':token' => $token]);
    }

    // =========================================================================
    // Flujo C — Solicitud de cambio con aprobación
    // =========================================================================

    /**
     * Crea una solicitud pendiente de aprobación.
     * Cancela cualquier solicitud previa pendiente del mismo usuario.
     */
    public function crearSolicitud(int $usuarioId, string $nuevoPasswordHash): int
    {
        // Cancelar solicitudes previas pendientes
        $this->db->prepare("
            UPDATE solicitudes_cambio_contrasena
            SET estado = 'rechazada', motivo_rechazo = 'Cancelada por nueva solicitud'
            WHERE usuario_id = :uid AND estado = 'pendiente'
        ")->execute([':uid' => $usuarioId]);

        $stmt = $this->db->prepare("
            INSERT INTO solicitudes_cambio_contrasena (usuario_id, nuevo_password_hash, estado)
            VALUES (:usuario_id, :hash, 'pendiente')
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':hash'       => $nuevoPasswordHash,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Lista solicitudes pendientes según el rol del admin que consulta.
     * Superadmin ve todas; Admin de Tienda solo las de su tienda.
     */
    public function listarPendientes(?int $tiendaId = null): array
    {
        $rolPrincipalSql = $this->sqlUsuarioRolPrincipal();

        if ($tiendaId === null) {
            // Superadmin: todas
            $stmt = $this->db->query("
                SELECT
                    s.id, s.usuario_id, s.estado, s.created_at,
                    u.nombre, u.apellido, u.email,
                    r.nombre AS rol_nombre,
                    t.nombre AS tienda_nombre
                FROM solicitudes_cambio_contrasena s
                INNER JOIN usuarios u ON u.id = s.usuario_id
                LEFT JOIN ($rolPrincipalSql) ur ON ur.usuario_id = u.id
                LEFT JOIN roles r ON r.id = ur.rol_id
                LEFT JOIN tiendas t ON t.id = ur.tienda_id
                WHERE s.estado = 'pendiente'
                ORDER BY s.created_at ASC
            ");
            return $stmt->fetchAll();
        }

        // Admin de Tienda: solo su tienda
        $stmt = $this->db->prepare("
            SELECT
                s.id, s.usuario_id, s.estado, s.created_at,
                u.nombre, u.apellido, u.email,
                r.nombre AS rol_nombre,
                t.nombre AS tienda_nombre
            FROM solicitudes_cambio_contrasena s
            INNER JOIN usuarios u ON u.id = s.usuario_id
            LEFT JOIN ($rolPrincipalSql) ur ON ur.usuario_id = u.id
            LEFT JOIN roles r ON r.id = ur.rol_id
            LEFT JOIN tiendas t ON t.id = ur.tienda_id
            WHERE s.estado = 'pendiente'
              AND ur.tienda_id = :tienda_id
            ORDER BY s.created_at ASC
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);
        return $stmt->fetchAll();
    }

    /**
     * Devuelve una solicitud por id (para aprobar/rechazar).
     */
    public function buscarSolicitudPorId(int $id): ?array
    {
        $rolPrincipalSql = $this->sqlUsuarioRolPrincipal();

        $stmt = $this->db->prepare("
            SELECT
                s.id, s.usuario_id, s.nuevo_password_hash, s.estado,
                u.nombre, u.apellido, u.email,
                r.nombre AS rol_nombre,
                ur.tienda_id
            FROM solicitudes_cambio_contrasena s
            INNER JOIN usuarios u ON u.id = s.usuario_id
            LEFT JOIN ($rolPrincipalSql) ur ON ur.usuario_id = u.id
            LEFT JOIN roles r ON r.id = ur.rol_id
            WHERE s.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Aprueba la solicitud: aplica el hash y cierra la solicitud.
     */
    public function aprobarSolicitud(int $solicitudId, int $adminId): void
    {
        $solicitud = $this->buscarSolicitudPorId($solicitudId);

        if ($solicitud === null || $solicitud['estado'] !== 'pendiente') {
            throw new RuntimeException('La solicitud no existe o ya fue procesada.');
        }

        $this->db->beginTransaction();
        try {
            // Aplicar el nuevo hash al usuario
            $this->db->prepare("
                UPDATE usuarios SET password_hash = :hash WHERE id = :id
            ")->execute([
                ':hash' => $solicitud['nuevo_password_hash'],
                ':id'   => $solicitud['usuario_id'],
            ]);

            // Marcar como aprobada
            $this->db->prepare("
                UPDATE solicitudes_cambio_contrasena
                SET estado = 'aprobada', admin_id = :admin_id, updated_at = NOW()
                WHERE id = :id
            ")->execute([':admin_id' => $adminId, ':id' => $solicitudId]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Rechaza la solicitud. El controller enviará el email al trabajador.
     */
    public function rechazarSolicitud(int $solicitudId, int $adminId, string $motivo): void
    {
        $this->db->prepare("
            UPDATE solicitudes_cambio_contrasena
            SET estado = 'rechazada', admin_id = :admin_id,
                motivo_rechazo = :motivo, updated_at = NOW()
            WHERE id = :id AND estado = 'pendiente'
        ")->execute([
            ':admin_id' => $adminId,
            ':motivo'   => $motivo,
            ':id'       => $solicitudId,
        ]);
    }

    /**
     * Conteo de solicitudes pendientes — para badge en sidebar.
     */
    public function contarPendientes(?int $tiendaId = null): int
    {
        if ($tiendaId === null) {
            $stmt = $this->db->query("
                SELECT COUNT(*) AS total
                FROM solicitudes_cambio_contrasena
                WHERE estado = 'pendiente'
            ");
        } else {
            $rolPrincipalSql = $this->sqlUsuarioRolPrincipal();

            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total
                FROM solicitudes_cambio_contrasena s
                INNER JOIN ($rolPrincipalSql) ur ON ur.usuario_id = s.usuario_id
                WHERE s.estado = 'pendiente'
                  AND ur.tienda_id = :tienda_id
            ");
            $stmt->execute([':tienda_id' => $tiendaId]);
        }

        return (int) $stmt->fetch()['total'];
    }
}
