<?php

declare(strict_types=1);

/**
 * Carga las variables del archivo .env en el entorno PHP.
 * Solo procesa líneas con formato CLAVE=VALOR.
 * Ignora líneas vacías y comentarios (#).
 */
function cargarEnv(string $rutaArchivo): void
{
    if (!file_exists($rutaArchivo)) {
        return;
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lineas === false) {
        return;
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        // Ignorar comentarios y líneas vacías
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }

        // Solo procesar líneas con signo igual
        if (!str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);

        $clave = trim($clave);
        $valor = trim($valor);

        // Quitar comillas simples o dobles si las hay
        if (
            (str_starts_with($valor, '"') && str_ends_with($valor, '"')) ||
            (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
        ) {
            $valor = substr($valor, 1, -1);
        }

        if ($clave === '') {
            continue;
        }

        // Poner en entorno solo si no está ya definida
        if (getenv($clave) === false) {
            putenv("{$clave}={$valor}");
            $_ENV[$clave] = $valor;
        }
    }
}

// Cargar el .env desde la raíz del backend
cargarEnv(__DIR__ . '/../.env');
