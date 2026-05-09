<?php

declare(strict_types=1);

/**
 * Trait reutilizable para todos los controladores.
 * Centraliza: redirección, mensajes flash y tokens CSRF.
 */
trait ControllerHelper
{
    // -------------------------------------------------------------------------
    // Redirección
    // -------------------------------------------------------------------------

    private function redireccionar(string $ruta): never
    {
        header('Location: ' . $ruta);
        exit;
    }

    // -------------------------------------------------------------------------
    // Mensajes flash (se muestran una sola vez en la siguiente petición)
    // -------------------------------------------------------------------------

    private function guardarMensaje(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = [
            'type'    => $tipo,
            'message' => $mensaje,
        ];
    }

    // -------------------------------------------------------------------------
    // CSRF
    // -------------------------------------------------------------------------

    private function generarCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function validarCsrfToken(): void
    {
        $tokenSesion     = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = $_POST['csrf_token']    ?? '';

        if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            http_response_code(419);
            echo 'Token de seguridad invalido. Recarga la pagina e intenta de nuevo.';
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // Usuario autenticado
    // -------------------------------------------------------------------------

    private function usuarioIdActual(): int
    {
        return (int) ($_SESSION['auth']['usuario_id'] ?? 0);
    }

    private function tiendaIdPermitida(): ?int
    {
        $tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;

        return $tiendaId !== null ? (int) $tiendaId : null;
    }

    private function validarAccesoATienda(int $tiendaId): void
    {
        $permitida = $this->tiendaIdPermitida();

        if ($permitida !== null && $permitida !== $tiendaId) {
            $this->denegarAcceso('No tienes permisos para gestionar datos de esta tienda.');
        }
    }

    // -------------------------------------------------------------------------
    // Acceso denegado
    // -------------------------------------------------------------------------

    private function denegarAcceso(string $mensaje = 'No tienes permisos suficientes para realizar esta accion.'): never
    {
        http_response_code(403);

        $errorTitulo  = 'Acceso denegado';
        $errorMensaje = $mensaje;

        require __DIR__ . '/../../resources/errors/403.php';
        exit;
    }
}
