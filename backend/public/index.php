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

/*
 * Manejador global de excepciones: nunca mostrar stack traces crudos.
 * En desarrollo muestra el detalle con formato legible; en producción,
 * un mensaje amigable. Siempre registra el error en el log.
 */
set_exception_handler(function (Throwable $e) use ($appEnv): void {
    error_log(sprintf(
        '[MegaUniStore] %s: %s en %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    if (!headers_sent()) {
        http_response_code(500);
    }

    $esDev = $appEnv === 'development';

    echo '<div style="max-width:720px;margin:40px auto;padding:28px;font-family:Inter,Segoe UI,Arial,sans-serif;'
        . 'background:#fff;border:1px solid #fecaca;border-radius:16px;box-shadow:0 4px 24px rgba(15,23,42,.08);">'
        . '<div style="display:inline-flex;padding:8px 14px;border-radius:12px;background:#fee2e2;color:#991b1b;'
        . 'font-weight:800;font-size:14px;margin-bottom:12px;">⚠️ Error interno</div>'
        . '<h2 style="margin:0 0 8px;color:#0f172a;font-size:20px;">Algo salió mal al procesar la solicitud</h2>';

    if ($esDev) {
        echo '<p style="color:#475569;font-size:14px;line-height:1.6;margin:0 0 12px;">'
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p style="color:#94a3b8;font-size:12px;margin:0 0 16px;">'
            . htmlspecialchars(basename($e->getFile()) . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8')
            . ' <em>(detalle visible solo en desarrollo)</em></p>';
    } else {
        echo '<p style="color:#475569;font-size:14px;line-height:1.6;margin:0 0 16px;">'
            . 'El error fue registrado. Intenta de nuevo o contacta al administrador.</p>';
    }

    echo '<a href="index.php?route=dashboard" style="display:inline-block;padding:10px 18px;background:#1e3a8a;'
        . 'color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px;">Volver al dashboard</a>'
        . '</div>';
    exit;
});

/*
 * Middleware global: cabeceras de seguridad HTTP
 * (clickjacking, MIME sniffing, CSP, HSTS).
 */
require_once __DIR__ . '/../app/Middlewares/SecurityHeaders.php';
SecurityHeaders::aplicar();

require_once __DIR__ . '/../routes/web.php';
