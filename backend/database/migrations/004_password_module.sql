-- =============================================================================
-- Módulo de contraseñas — Mega_Uni_Store v3
-- MySQL 8.0 compatible (sin IF NOT EXISTS en columnas, sin DELIMITER)
-- Ejecutar desde phpMyAdmin o cliente MySQL, NO desde línea de comandos Laragon
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Tabla 1: password_resets
-- Tokens de un solo uso para reset vía email (Flujo A)
-- -----------------------------------------------------------------------------
SET @sql_pr = (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE TABLE password_resets (
            id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email       VARCHAR(255) NOT NULL,
            token       VARCHAR(64)  NOT NULL,
            expires_at  DATETIME     NOT NULL,
            used_at     DATETIME     NULL,
            created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY  uq_token (token),
            INDEX       idx_email  (email),
            INDEX       idx_token  (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'SELECT ''password_resets ya existe'''
    )
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name   = 'password_resets'
);
PREPARE stmt_pr FROM @sql_pr;
EXECUTE stmt_pr;
DEALLOCATE PREPARE stmt_pr;

-- -----------------------------------------------------------------------------
-- Tabla 2: solicitudes_cambio_contrasena
-- Flujo C: trabajador solicita → admin aprueba/rechaza
-- -----------------------------------------------------------------------------
SET @sql_sc = (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE TABLE solicitudes_cambio_contrasena (
            id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id           BIGINT UNSIGNED NOT NULL,
            nuevo_password_hash  VARCHAR(255)    NOT NULL,
            estado               ENUM(''pendiente'',''aprobada'',''rechazada'') DEFAULT ''pendiente'',
            admin_id             BIGINT UNSIGNED NULL,
            motivo_rechazo       TEXT            NULL,
            created_at           DATETIME        DEFAULT CURRENT_TIMESTAMP,
            updated_at           DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_usuario (usuario_id),
            INDEX idx_estado  (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'SELECT ''solicitudes_cambio_contrasena ya existe'''
    )
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name   = 'solicitudes_cambio_contrasena'
);
PREPARE stmt_sc FROM @sql_sc;
EXECUTE stmt_sc;
DEALLOCATE PREPARE stmt_sc;

-- FKs separadas (también protegidas contra duplicado)
SET @sql_fk1 = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE solicitudes_cambio_contrasena
         ADD CONSTRAINT fk_scc_usuario
         FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE',
        'SELECT ''fk_scc_usuario ya existe'''
    )
    FROM information_schema.key_column_usage
    WHERE constraint_schema = DATABASE()
      AND constraint_name   = 'fk_scc_usuario'
      AND table_name        = 'solicitudes_cambio_contrasena'
);
PREPARE stmt_fk1 FROM @sql_fk1;
EXECUTE stmt_fk1;
DEALLOCATE PREPARE stmt_fk1;

SET @sql_fk2 = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE solicitudes_cambio_contrasena
         ADD CONSTRAINT fk_scc_admin
         FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL',
        'SELECT ''fk_scc_admin ya existe'''
    )
    FROM information_schema.key_column_usage
    WHERE constraint_schema = DATABASE()
      AND constraint_name   = 'fk_scc_admin'
      AND table_name        = 'solicitudes_cambio_contrasena'
);
PREPARE stmt_fk2 FROM @sql_fk2;
EXECUTE stmt_fk2;
DEALLOCATE PREPARE stmt_fk2;

SELECT 'Migración 004_password_module completada OK' AS resultado;
