<?php

declare(strict_types=1);

/**
 * Middleware: Limitador de intentos (anti fuerza bruta).
 * Cubre REQ-7.11.3 — protege login, registro y reset de contraseña.
 *
 * Implementación basada en archivos (no requiere tablas nuevas ni Redis).
 * Los contadores se guardan en storage/ratelimit/ por clave (acción + IP).
 */
final class RateLimiter
{
    private const DIR = __DIR__ . '/../../storage/ratelimit';

    /**
     * Verifica si la acción está permitida.
     *
     * @param string $accion       Identificador (ej. 'login', 'password.reset')
     * @param int    $maxIntentos  Intentos permitidos dentro de la ventana
     * @param int    $ventana      Ventana en segundos
     */
    public static function permitido(string $accion, int $maxIntentos = 5, int $ventana = 300): bool
    {
        $registro = self::leer(self::clave($accion));
        $ahora    = time();

        // Limpiar intentos fuera de la ventana
        $registro = array_values(array_filter(
            $registro,
            static fn (int $ts): bool => ($ahora - $ts) < $ventana
        ));

        return count($registro) < $maxIntentos;
    }

    /** Registra un intento fallido. */
    public static function registrarIntento(string $accion): void
    {
        $clave    = self::clave($accion);
        $registro = self::leer($clave);
        $registro[] = time();

        self::escribir($clave, array_slice($registro, -20));
    }

    /** Limpia los intentos tras una acción exitosa. */
    public static function limpiar(string $accion): void
    {
        $ruta = self::ruta(self::clave($accion));

        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    /** Segundos restantes hasta poder reintentar. */
    public static function segundosRestantes(string $accion, int $ventana = 300): int
    {
        $registro = self::leer(self::clave($accion));

        if ($registro === []) {
            return 0;
        }

        $restante = $ventana - (time() - min($registro));

        return max(0, $restante);
    }

    // -------------------------------------------------------------------------

    private static function clave(string $accion): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $ip = trim(explode(',', $ip)[0]);

        return hash('sha256', $accion . '|' . $ip);
    }

    private static function ruta(string $clave): string
    {
        return self::DIR . '/' . $clave . '.json';
    }

    /** @return int[] */
    private static function leer(string $clave): array
    {
        $ruta = self::ruta($clave);

        if (!is_file($ruta)) {
            return [];
        }

        $contenido = file_get_contents($ruta);
        $datos     = json_decode($contenido !== false ? $contenido : '[]', true);

        return is_array($datos) ? array_map('intval', $datos) : [];
    }

    /** @param int[] $registro */
    private static function escribir(string $clave, array $registro): void
    {
        if (!is_dir(self::DIR)) {
            @mkdir(self::DIR, 0775, true);
        }

        @file_put_contents(self::ruta($clave), json_encode($registro), LOCK_EX);
    }
}
