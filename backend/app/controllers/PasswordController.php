<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/Mailer.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';
require_once __DIR__ . '/../Middlewares/RateLimiter.php';

/**
 * Gestiona los 3 flujos de contraseñas:
 *
 *  Flujo A — Reset por email (público, sin sesión)
 *      GET  password.request      → formulario "olvidé mi contraseña"
 *      POST password.request.post → genera token + envía email
 *      GET  password.reset        → formulario de nueva contraseña (con token)
 *      POST password.reset.post   → aplica nuevo password
 *
 *  Flujo C — Trabajador solicita cambio (requiere aprobación admin)
 *      GET  password.change       → formulario de solicitud
 *      POST password.change.post  → crea solicitud pendiente
 *      GET  password.requests     → lista de solicitudes (solo admin/superadmin)
 *      POST password.approve      → admin aprueba
 *      POST password.deny         → admin rechaza
 *
 *  Flujo D — Admin cambia directamente la contraseña de otro usuario
 *      POST password.admin.set    → admin/superadmin aplica sin aprobación
 */
final class PasswordController
{
    use ControllerHelper;

    // Roles que pueden cambiar contraseñas directamente (sin aprobación)
    private const ROLES_ADMIN_DIRECTO = ['Superadministrador', 'Administrador de Tienda'];

    // Roles que pueden ver y gestionar solicitudes pendientes
    private const ROLES_GESTORES = ['Superadministrador', 'Administrador de Tienda'];

    private PasswordReset $resetModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->resetModel  = new PasswordReset();
        $this->usuarioModel = new Usuario();
    }

    // =========================================================================
    // FLUJO A — Reset por email (público)
    // =========================================================================

    /** GET password.request — formulario "Olvidé mi contraseña" */
    public function mostrarFormularioReset(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        require __DIR__ . '/../../resources/views/auth/password_forgot.php';
    }

    /** POST password.request.post — genera token y envía email */
    public function enviarLinkReset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=password.request');
        }

        // Anti abuso: máximo 3 solicitudes de reset por IP cada 10 minutos
        if (!RateLimiter::permitido('password.reset', 3, 600)) {
            $this->guardarMensaje('error', 'Demasiadas solicitudes. Intenta de nuevo en unos minutos.');
            $this->redireccionar('index.php?route=password.request');
        }

        RateLimiter::registrarIntento('password.reset');

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'Ingresa un correo electrónico válido.');
            $this->redireccionar('index.php?route=password.request');
        }

        // Siempre mostramos el mismo mensaje aunque el email no exista (anti-enumeración)
        $usuario = $this->usuarioModel->buscarActivoPorEmail($email);

        if ($usuario !== null) {
            try {
                $token   = $this->resetModel->crearToken($email);
                $appUrl  = rtrim(getenv('APP_URL') ?: 'http://localhost', '/');
                $link    = $appUrl . '/index.php?route=password.reset&token=' . $token;
                $nombre  = trim($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));

                $contenido = "
                    <p style='color:#374151;line-height:1.7;'>
                        Hola <strong>{$nombre}</strong>,<br><br>
                        Recibimos una solicitud para restablecer la contraseña de tu cuenta en
                        <strong>Mega Uni Store</strong>.
                    </p>
                    <p style='margin:24px 0;text-align:center;'>
                        <a href='{$link}' style='
                            display:inline-block;background:linear-gradient(135deg,#1e3a8a,#2563eb);
                            color:#fff;text-decoration:none;padding:14px 28px;border-radius:12px;
                            font-weight:800;font-size:15px;
                        '>Restablecer contraseña</a>
                    </p>
                    <p style='color:#6b7280;font-size:13px;line-height:1.6;'>
                        Este enlace expira en <strong>1 hora</strong>.<br>
                        Si no solicitaste este cambio, ignora este correo — tu contraseña no cambiará.<br><br>
                        O copia este enlace en tu navegador:<br>
                        <a href='{$link}' style='color:#2563eb;word-break:break-all;'>{$link}</a>
                    </p>
                ";

                Mailer::send(
                    $email,
                    $nombre,
                    'Restablece tu contraseña — Mega Uni Store',
                    Mailer::plantillaBase('Restablece tu contraseña', $contenido)
                );
            } catch (Throwable $e) {
                error_log('PasswordController::enviarLinkReset error: ' . $e->getMessage());
                // No exponemos el error al usuario
            }
        }

        $this->guardarMensaje(
            'success',
            'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.'
        );
        $this->redireccionar('index.php?route=password.request');
    }

    /** GET password.reset?token=XXX — formulario de nueva contraseña */
    public function mostrarFormularioNuevoPassword(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));

        if ($token === '') {
            $this->guardarMensaje('error', 'El enlace no es válido.');
            $this->redireccionar('index.php?route=password.request');
        }

        $registro = $this->resetModel->buscarTokenValido($token);

        if ($registro === null) {
            $this->guardarMensaje('error', 'El enlace ya fue usado o expiró. Solicita uno nuevo.');
            $this->redireccionar('index.php?route=password.request');
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        require __DIR__ . '/../../resources/views/auth/password_reset.php';
    }

    /** POST password.reset.post — aplica el nuevo password */
    public function aplicarNuevoPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=login');
        }

        $token           = trim((string) ($_POST['token'] ?? ''));
        $password        = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($token === '') {
            $this->guardarMensaje('error', 'Token inválido.');
            $this->redireccionar('index.php?route=password.request');
        }

        $registro = $this->resetModel->buscarTokenValido($token);

        if ($registro === null) {
            $this->guardarMensaje('error', 'El enlace ya fue usado o expiró. Solicita uno nuevo.');
            $this->redireccionar('index.php?route=password.request');
        }

        if (strlen($password) < 8) {
            $this->guardarMensaje('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redireccionar('index.php?route=password.reset&token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            $this->guardarMensaje('error', 'Las contraseñas no coinciden.');
            $this->redireccionar('index.php?route=password.reset&token=' . urlencode($token));
        }

        $usuario = $this->usuarioModel->buscarActivoPorEmail($registro['email']);

        if ($usuario === null) {
            $this->guardarMensaje('error', 'El usuario ya no existe en el sistema.');
            $this->redireccionar('index.php?route=login');
        }

        $this->usuarioModel->cambiarPassword((int) $usuario['id'], $password);
        $this->resetModel->marcarTokenUsado($token);

        $this->guardarMensaje('success', 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.');
        $this->redireccionar('index.php?route=login');
    }

    // =========================================================================
    // FLUJO C — Trabajador solicita cambio (requiere aprobación)
    // =========================================================================

    /** GET password.change — formulario de solicitud de cambio (autenticado) */
    public function mostrarFormularioCambio(): void
    {
        $this->requerirAutenticacion();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $rolActual    = $this->rolActual();
        $esAdminDirecto = in_array($rolActual, self::ROLES_ADMIN_DIRECTO, true);

        require __DIR__ . '/../../resources/views/password/change.php';
    }

    /** POST password.change.post — crea solicitud o aplica directamente */
    public function procesarCambio(): void
    {
        $this->requerirAutenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=password.change');
        }

        $this->validarCsrfToken();

        $usuarioId       = $this->usuarioIdActual();
        $password        = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $rolActual       = $this->rolActual();

        if (strlen($password) < 8) {
            $this->guardarMensaje('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redireccionar('index.php?route=password.change');
        }

        if ($password !== $passwordConfirm) {
            $this->guardarMensaje('error', 'Las contraseñas no coinciden.');
            $this->redireccionar('index.php?route=password.change');
        }

        // Admins aplican directamente sin aprobación
        if (in_array($rolActual, self::ROLES_ADMIN_DIRECTO, true)) {
            $this->usuarioModel->cambiarPassword($usuarioId, $password);
            $this->guardarMensaje('success', 'Contraseña actualizada correctamente.');
            $this->redireccionar('index.php?route=dashboard');
            return;
        }

        // Trabajadores: crear solicitud pendiente de aprobación
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->resetModel->crearSolicitud($usuarioId, $hash);

        $this->guardarMensaje(
            'success',
            'Tu solicitud fue enviada. Un administrador la revisará y recibirás confirmación por correo.'
        );
        $this->redireccionar('index.php?route=dashboard');
    }

    // =========================================================================
    // FLUJO C — Panel de administrador: aprobar / rechazar solicitudes
    // =========================================================================

    /** GET password.requests — lista de solicitudes pendientes */
    public function listarSolicitudes(): void
    {
        $this->requerirAutenticacion();
        $this->requerirRolGestor();

        $tiendaId    = $this->tiendaIdActual();
        $solicitudes = $this->resetModel->listarPendientes($tiendaId);
        $csrfToken   = $this->generarCsrfToken();
        $flash       = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../resources/views/password/requests.php';
    }

    /** POST password.approve — admin aprueba la solicitud */
    public function aprobarSolicitud(): void
    {
        $this->requerirAutenticacion();
        $this->requerirRolGestor();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=password.requests');
        }

        $this->validarCsrfToken();

        $solicitudId = (int) ($_POST['solicitud_id'] ?? 0);

        if ($solicitudId <= 0) {
            $this->guardarMensaje('error', 'Solicitud inválida.');
            $this->redireccionar('index.php?route=password.requests');
        }

        $solicitud = $this->resetModel->buscarSolicitudPorId($solicitudId);

        if ($solicitud === null) {
            $this->guardarMensaje('error', 'La solicitud no existe.');
            $this->redireccionar('index.php?route=password.requests');
        }

        // Admin de Tienda solo puede aprobar solicitudes de su propia tienda
        $tiendaAdmin = $this->tiendaIdActual();
        if ($tiendaAdmin !== null && (int) $solicitud['tienda_id'] !== $tiendaAdmin) {
            $this->denegarAcceso();
        }

        try {
            $this->resetModel->aprobarSolicitud($solicitudId, $this->usuarioIdActual());

            // Notificar al trabajador por email
            $usuario = $this->usuarioModel->buscarPorId((int) $solicitud['usuario_id']);
            if ($usuario !== null && !empty($usuario['email'])) {
                $nombre    = trim($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));
                $contenido = "
                    <p style='color:#374151;line-height:1.7;'>
                        Hola <strong>{$nombre}</strong>,<br><br>
                        Tu solicitud de cambio de contraseña ha sido <strong style='color:#166534;'>aprobada</strong>.
                        Puedes iniciar sesión con tu nueva contraseña a partir de ahora.
                    </p>
                    <p style='margin:24px 0;text-align:center;'>
                        <a href='" . rtrim(getenv('APP_URL') ?: '', '/') . "/index.php?route=login'
                           style='display:inline-block;background:#1e3a8a;color:#fff;text-decoration:none;
                                  padding:12px 24px;border-radius:12px;font-weight:800;'>
                            Iniciar sesión
                        </a>
                    </p>
                ";

                try {
                    Mailer::send(
                        $usuario['email'],
                        $nombre,
                        'Tu solicitud de contraseña fue aprobada — Mega Uni Store',
                        Mailer::plantillaBase('Solicitud aprobada ✓', $contenido)
                    );
                } catch (Throwable $e) {
                    error_log('Email aprobación: ' . $e->getMessage());
                }
            }

            $this->guardarMensaje('success', 'Solicitud aprobada. El trabajador ya puede acceder con su nueva contraseña.');
        } catch (Throwable $e) {
            $this->guardarMensaje('error', 'Error al aprobar: ' . $e->getMessage());
        }

        $this->redireccionar('index.php?route=password.requests');
    }

    /** POST password.deny — admin rechaza la solicitud */
    public function rechazarSolicitud(): void
    {
        $this->requerirAutenticacion();
        $this->requerirRolGestor();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=password.requests');
        }

        $this->validarCsrfToken();

        $solicitudId = (int) ($_POST['solicitud_id'] ?? 0);
        $motivo      = trim((string) ($_POST['motivo'] ?? 'Sin motivo especificado.'));

        if ($solicitudId <= 0) {
            $this->guardarMensaje('error', 'Solicitud inválida.');
            $this->redireccionar('index.php?route=password.requests');
        }

        $solicitud = $this->resetModel->buscarSolicitudPorId($solicitudId);

        if ($solicitud === null) {
            $this->guardarMensaje('error', 'La solicitud no existe.');
            $this->redireccionar('index.php?route=password.requests');
        }

        // Admin de Tienda solo puede rechazar solicitudes de su propia tienda
        $tiendaAdmin = $this->tiendaIdActual();
        if ($tiendaAdmin !== null && (int) $solicitud['tienda_id'] !== $tiendaAdmin) {
            $this->denegarAcceso();
        }

        $this->resetModel->rechazarSolicitud($solicitudId, $this->usuarioIdActual(), $motivo);

        // Notificar al trabajador
        $usuario = $this->usuarioModel->buscarPorId((int) $solicitud['usuario_id']);
        if ($usuario !== null && !empty($usuario['email'])) {
            $nombre    = trim($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));
            $motivoHtml = htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8');
            $contenido  = "
                <p style='color:#374151;line-height:1.7;'>
                    Hola <strong>{$nombre}</strong>,<br><br>
                    Tu solicitud de cambio de contraseña ha sido <strong style='color:#991b1b;'>rechazada</strong>
                    por un administrador.
                </p>
                <div style='background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px;margin:20px 0;'>
                    <strong style='color:#991b1b;'>Motivo:</strong>
                    <p style='margin:8px 0 0;color:#374151;'>{$motivoHtml}</p>
                </div>
                <p style='color:#6b7280;font-size:14px;'>
                    Si tienes dudas, comunícate con tu administrador de tienda.
                </p>
            ";

            try {
                Mailer::send(
                    $usuario['email'],
                    $nombre,
                    'Tu solicitud de contraseña fue rechazada — Mega Uni Store',
                    Mailer::plantillaBase('Solicitud rechazada', $contenido)
                );
            } catch (Throwable $e) {
                error_log('Email rechazo: ' . $e->getMessage());
            }
        }

        $this->guardarMensaje('success', 'Solicitud rechazada y notificación enviada al trabajador.');
        $this->redireccionar('index.php?route=password.requests');
    }

    // =========================================================================
    // FLUJO D — Admin cambia directamente la contraseña de otro usuario
    // =========================================================================

    /** POST password.admin.set — admin asigna nueva contraseña a un usuario */
    public function adminSetPassword(): void
    {
        $this->requerirAutenticacion();
        $this->requerirRolGestor();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=dashboard');
        }

        $this->validarCsrfToken();

        $targetUserId    = (int) ($_POST['usuario_id'] ?? 0);
        $password        = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $rolActual       = $this->rolActual();

        if ($targetUserId <= 0) {
            $this->guardarMensaje('error', 'Usuario inválido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        if (strlen($password) < 8) {
            $this->guardarMensaje('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redireccionar('index.php?route=usuarios.show&id=' . $targetUserId);
        }

        if ($password !== $passwordConfirm) {
            $this->guardarMensaje('error', 'Las contraseñas no coinciden.');
            $this->redireccionar('index.php?route=usuarios.show&id=' . $targetUserId);
        }

        // Admin de Tienda: no puede cambiar contraseñas de otros admins ni superadmin
        if ($rolActual === 'Administrador de Tienda') {
            $targetUsuario = $this->usuarioModel->buscarPorIdConRol($targetUserId);

            if ($targetUsuario === null) {
                $this->guardarMensaje('error', 'El usuario no existe.');
                $this->redireccionar('index.php?route=usuarios.index');
            }

            $rolTarget = $targetUsuario['rol_nombre'] ?? '';

            if (in_array($rolTarget, ['Superadministrador', 'Administrador de Tienda'], true)) {
                $this->guardarMensaje('error', 'No tienes permisos para cambiar la contraseña de ese usuario.');
                $this->redireccionar('index.php?route=usuarios.index');
            }

            // Verificar que el usuario pertenece a su tienda
            $tiendaAdmin = $this->tiendaIdActual();
            if ($tiendaAdmin !== null) {
                $tiendaTarget = (int) ($targetUsuario['tienda_id'] ?? 0);
                if ($tiendaTarget !== $tiendaAdmin) {
                    $this->denegarAcceso();
                }
            }
        }

        $this->usuarioModel->cambiarPassword($targetUserId, $password);

        $this->guardarMensaje('success', 'Contraseña del usuario actualizada correctamente.');
        $this->redireccionar('index.php?route=usuarios.show&id=' . $targetUserId);
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function requerirAutenticacion(): void
    {
        if (!isset($_SESSION['auth']['usuario_id'])) {
            $this->guardarMensaje('error', 'Debes iniciar sesión para continuar.');
            $this->redireccionar('index.php?route=login');
            exit;
        }
    }

    private function requerirRolGestor(): void
    {
        if (!in_array($this->rolActual(), self::ROLES_GESTORES, true)) {
            $this->denegarAcceso();
        }
    }

    private function rolActual(): string
    {
        return $_SESSION['auth']['rol_principal']['rol_nombre'] ?? '';
    }

    private function usuarioIdActual(): int
    {
        return (int) ($_SESSION['auth']['usuario_id'] ?? 0);
    }

    private function tiendaIdActual(): ?int
    {
        $tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;
        return $tiendaId !== null ? (int) $tiendaId : null;
    }

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
        $tokenFormulario = $_POST['csrf_token'] ?? '';

        if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            http_response_code(419);
            echo 'Token de seguridad inválido.';
            exit;
        }
    }

    private function guardarMensaje(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = ['type' => $tipo, 'message' => $mensaje];
    }

    private function redireccionar(string $ruta): void
    {
        header('Location: ' . $ruta);
        exit;
    }
}
