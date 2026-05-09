<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Permiso.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

/**
 * SetupController
 *
 * Inicializa roles y permisos base en la base de datos.
 * Solo accesible con la clave de setup definida en .env (SETUP_KEY).
 * Una vez ejecutado, deshabilitar la ruta en producción.
 */
final class SetupController
{
    use ControllerHelper;

    private Rol $rolModel;
    private Permiso $permisoModel;

    public function __construct()
    {
        $this->rolModel     = new Rol();
        $this->permisoModel = new Permiso();
    }

    public function ejecutar(): void
    {
        // Verificar clave de setup
        $claveEnv       = getenv('SETUP_KEY') ?: '';
        $claveRecibida  = $_GET['key'] ?? '';

        if ($claveEnv === '' || $claveRecibida !== $claveEnv) {
            http_response_code(403);
            echo '<h2>Acceso denegado.</h2><p>Clave de setup incorrecta o no configurada.</p>';
            exit;
        }

        $log = [];

        // 1. Crear roles base
        $this->rolModel->asegurarRolesBase();
        $log[] = '✅ Roles base verificados/creados.';

        // 2. Crear permisos base y asignarlos a roles
        $this->permisoModel->sincronizarPermisosRolesBase();
        $log[] = '✅ Permisos base verificados/creados y asignados a roles.';

        // Mostrar resultado
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        echo '<title>Setup MultiStore</title>';
        echo '<style>body{font-family:monospace;padding:2rem;background:#0f172a;color:#e2e8f0;}';
        echo 'h1{color:#38bdf8;}li{margin:.4rem 0;}</style></head><body>';
        echo '<h1>Setup MultiStore</h1><ul>';
        foreach ($log as $linea) {
            echo '<li>' . htmlspecialchars($linea) . '</li>';
        }
        echo '</ul>';
        echo '<p style="color:#86efac;font-weight:bold;">Setup completado correctamente.</p>';
        echo '<p style="color:#fbbf24;">Recuerda eliminar o deshabilitar la ruta <code>setup</code> en produccion.</p>';
        echo '</body></html>';
    }
}
