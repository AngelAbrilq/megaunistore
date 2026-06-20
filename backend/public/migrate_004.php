<?php
/**
 * migrate_004.php — Migración: módulo contraseñas
 * Crea las tablas password_resets y solicitudes_cambio_contrasena.
 *
 * USO: Abre en el navegador  http://localhost/Mega_Uni_Store_v3/backend/public/migrate_004.php
 * ELIMINA este archivo una vez ejecutado correctamente.
 */

declare(strict_types=1);

// Carga configuración
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();

$pasos = [];
$errores = [];

// ── 1. password_resets ────────────────────────────────────────────────────────
$existe = $db->query("
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = 'password_resets'
")->fetchColumn();

if (!$existe) {
    try {
        $db->exec("
            CREATE TABLE password_resets (
                id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email       VARCHAR(255) NOT NULL,
                token       VARCHAR(64)  NOT NULL,
                expires_at  DATETIME     NOT NULL,
                used_at     DATETIME     NULL,
                created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY  uq_token (token),
                INDEX       idx_email  (email),
                INDEX       idx_token  (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = '✅ Tabla <code>password_resets</code> creada.';
    } catch (Throwable $e) {
        $errores[] = '❌ password_resets: ' . $e->getMessage();
    }
} else {
    $pasos[] = 'ℹ️ Tabla <code>password_resets</code> ya existía — omitida.';
}

// ── 2. solicitudes_cambio_contrasena ─────────────────────────────────────────
$existe2 = $db->query("
    SELECT COUNT(*) FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = 'solicitudes_cambio_contrasena'
")->fetchColumn();

if (!$existe2) {
    try {
        $db->exec("
            CREATE TABLE solicitudes_cambio_contrasena (
                id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                usuario_id           BIGINT UNSIGNED NOT NULL,
                nuevo_password_hash  VARCHAR(255)    NOT NULL,
                estado               ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
                admin_id             BIGINT UNSIGNED NULL,
                motivo_rechazo       TEXT            NULL,
                created_at           DATETIME        DEFAULT CURRENT_TIMESTAMP,
                updated_at           DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_usuario (usuario_id),
                INDEX idx_estado  (estado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = '✅ Tabla <code>solicitudes_cambio_contrasena</code> creada.';
    } catch (Throwable $e) {
        $errores[] = '❌ solicitudes_cambio_contrasena: ' . $e->getMessage();
    }
} else {
    $pasos[] = 'ℹ️ Tabla <code>solicitudes_cambio_contrasena</code> ya existía — omitida.';
}

// ── 3. Foreign Keys (solo si la tabla recién se creó) ────────────────────────
if (!$existe2) {
    $fks = [
        'fk_scc_usuario' => "ALTER TABLE solicitudes_cambio_contrasena
                             ADD CONSTRAINT fk_scc_usuario
                             FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE",
        'fk_scc_admin'   => "ALTER TABLE solicitudes_cambio_contrasena
                             ADD CONSTRAINT fk_scc_admin
                             FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL",
    ];

    foreach ($fks as $nombre => $sql) {
        try {
            $db->exec($sql);
            $pasos[] = "✅ FK <code>$nombre</code> creada.";
        } catch (Throwable $e) {
            // FK duplicada no es crítica si la tabla ya existía
            $pasos[] = "ℹ️ FK <code>$nombre</code>: " . $e->getMessage();
        }
    }
}

$ok = empty($errores);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración 004 — Módulo Contraseñas</title>
    <style>
        body  { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        h1    { color: #38bdf8; }
        ul    { list-style: none; padding: 0; }
        li    { margin: .5rem 0; font-size: 15px; }
        .ok   { color: #86efac; font-weight: bold; }
        .err  { color: #f87171; font-weight: bold; }
        .warn { color: #fbbf24; }
        code  { background: #1e3a5f; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
<h1>Migración 004 — Módulo de Contraseñas</h1>
<ul>
    <?php foreach ($pasos as $p): ?>
        <li><?= $p ?></li>
    <?php endforeach; ?>
    <?php foreach ($errores as $e): ?>
        <li class="err"><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
</ul>
<?php if ($ok): ?>
    <p class="ok">✅ Migración completada correctamente.</p>
    <p class="warn">⚠️ <strong>Elimina este archivo</strong> del servidor cuando termines:<br>
        <code>backend/public/migrate_004.php</code>
    </p>
<?php else: ?>
    <p class="err">❌ La migración tuvo errores. Revisa los mensajes de arriba.</p>
<?php endif; ?>
</body>
</html>
