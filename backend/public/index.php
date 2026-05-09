<?php

declare(strict_types=1);

/*
 * Cargar variables de entorno desde .env
 * Debe ejecutarse antes de cualquier otro require.
 */
require_once __DIR__ . '/../config/env.php';

/*
 * Mostrar errores solo en entorno de desarrollo.
 * Cambiar APP_ENV=production en .env para desactivarlos.
 */
$appEnv = getenv('APP_ENV') ?: 'development';

if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

require_once __DIR__ . '/../routes/web.php';