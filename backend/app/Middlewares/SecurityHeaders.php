<?php

declare(strict_types=1);

/**
 * Middleware: Cabeceras de seguridad HTTP.
 * Cubre REQ-7.11.3 (prevención de accesos no autorizados / hardening).
 *
 * Se aplica globalmente desde public/index.php antes de enrutar.
 */
final class SecurityHeaders
{
    public static function aplicar(): void
    {
        if (headers_sent()) {
            return;
        }

        // Evita que el sitio sea embebido en iframes (clickjacking)
        header('X-Frame-Options: SAMEORIGIN');

        // Evita que el navegador "adivine" tipos MIME
        header('X-Content-Type-Options: nosniff');

        // Limita la información del referrer enviada a terceros
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Desactiva APIs sensibles del navegador que el sistema no usa
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

        // CSP: permite recursos propios + CDNs usados (Chart.js, Tailwind, Google Fonts)
        header(
            "Content-Security-Policy: default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com; "
            . "img-src 'self' data: https:; "
            . "connect-src 'self'; "
            . "frame-ancestors 'self'"
        );

        // HSTS solo cuando la petición ya llega por HTTPS
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        ) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
