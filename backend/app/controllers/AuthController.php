<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Permiso.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';
require_once __DIR__ . '/../Middlewares/RateLimiter.php';

final class AuthController
{
    use ControllerHelper;
    private Usuario $usuarioModel;
    private Rol $rolModel;
    private Permiso $permisoModel;

    public function __construct()
    {
        $this->iniciarSesionSegura();

        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
        $this->permisoModel = new Permiso();
    }

    public function mostrarLogin(): void
    {
        if ($this->estaAutenticado()) {
            $this->redireccionarSegunRolPrincipal();
            return;
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/auth/login.php';
    }

    public function mostrarRegistro(): void
    {
        if ($this->estaAutenticado()) {
            $this->redireccionarSegunRolPrincipal();
            return;
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/auth/register.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=login');
            return;
        }

        // Anti-CSRF en el formulario de login (login CSRF)
        $this->validarCsrfToken();

        // Anti fuerza bruta: máximo 5 intentos fallidos por IP cada 5 minutos
        if (!RateLimiter::permitido('login', 5, 300)) {
            $espera = RateLimiter::segundosRestantes('login', 300);
            $this->guardarMensaje(
                'error',
                'Demasiados intentos fallidos. Intenta de nuevo en ' . max(1, (int) ceil($espera / 60)) . ' minuto(s).'
            );
            $this->redireccionar('index.php?route=login');
            return;
        }

        $email = $this->limpiarTexto($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->guardarMensaje('error', 'El correo y la contraseña son obligatorios.');
            $this->redireccionar('index.php?route=login');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'El correo electrónico no tiene un formato válido.');
            $this->redireccionar('index.php?route=login');
            return;
        }

        $usuario = $this->usuarioModel->buscarActivoPorEmail($email);

        if ($usuario === null) {
            RateLimiter::registrarIntento('login');
            $this->guardarMensaje('error', 'Credenciales incorrectas.');
            $this->redireccionar('index.php?route=login');
            return;
        }

        if (!$this->usuarioModel->verificarPassword($password, $usuario['password_hash'])) {
            RateLimiter::registrarIntento('login');
            $this->guardarMensaje('error', 'Credenciales incorrectas.');
            $this->redireccionar('index.php?route=login');
            return;
        }

        RateLimiter::limpiar('login');

        $roles = $this->rolModel->obtenerRolesDeUsuario((int) $usuario['id']);
        $rolPrincipal = $this->rolModel->obtenerRolPrincipalDeUsuario((int) $usuario['id']);
        $accionesPermisos = $this->permisoModel->obtenerAccionesDeUsuario((int) $usuario['id']);

        if ($rolPrincipal === null) {
            $this->guardarMensaje('error', 'El usuario no tiene un rol asignado.');
            $this->redireccionar('index.php?route=login');
            return;
        }

        session_regenerate_id(true);

        // Rotar el token CSRF al iniciar sesión (evita fijación de token)
        unset($_SESSION['csrf_token']);
        $this->generarCsrfToken();

        $_SESSION['auth'] = [
            'usuario_id' => (int) $usuario['id'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'email' => $usuario['email'],
            'avatar_url' => $usuario['avatar_url'],
            'roles' => $roles,
            'permisos' => $accionesPermisos,
            'rol_principal' => [
                'usuario_rol_id' => (int) $rolPrincipal['usuario_rol_id'],
                'rol_id' => (int) $rolPrincipal['rol_id'],
                'rol_nombre' => $rolPrincipal['rol_nombre'],
                'rol_nivel' => (int) $rolPrincipal['rol_nivel'],
                'tienda_id' => $rolPrincipal['tienda_id'] !== null ? (int) $rolPrincipal['tienda_id'] : null,
            ],
            'login_at' => date('Y-m-d H:i:s'),
        ];

        $this->redireccionarSegunRolPrincipal();
    }

    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=register');
            return;
        }

        // Anti-CSRF y anti abuso de registro
        $this->validarCsrfToken();

        if (!RateLimiter::permitido('register', 3, 600)) {
            $this->guardarMensaje('error', 'Demasiados registros desde esta conexión. Intenta más tarde.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        RateLimiter::registrarIntento('register');

        $nombre = $this->limpiarTexto($_POST['nombre'] ?? '');
        $apellido = $this->limpiarTexto($_POST['apellido'] ?? '');
        $email = $this->limpiarTexto($_POST['email'] ?? '');
        $telefono = $this->limpiarTexto($_POST['telefono'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($nombre === '' || $apellido === '' || $email === '' || $password === '' || $passwordConfirm === '') {
            $this->guardarMensaje('error', 'Nombre, apellido, correo, contraseña y confirmación son obligatorios.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'El correo electrónico no tiene un formato válido.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        if (strlen($password) < 8) {
            $this->guardarMensaje('error', 'La contraseña debe tener mínimo 8 caracteres.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->guardarMensaje('error', 'Las contraseñas no coinciden.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        if ($this->usuarioModel->existeEmail($email)) {
            $this->guardarMensaje('error', 'El correo electrónico ya está registrado.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        $usuarioId = $this->usuarioModel->crear([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'password' => $password,
            'telefono' => $telefono !== '' ? $telefono : null,
            'avatar_url' => null,
            'estado' => 1,
        ]);

        /*
         * Registro público:
         * Por seguridad, cualquier usuario que se registre desde la pantalla pública
         * entra como Cliente. Los roles administrativos se asignarán después desde
         * gestión de usuarios, no desde el formulario público.
         */
        $this->rolModel->asegurarRolesBase();

        $rolCliente = $this->rolModel->buscarPorNombre('Cliente');

        if ($rolCliente === null) {
            $this->guardarMensaje('error', 'No fue posible asignar el rol Cliente.');
            $this->redireccionar('index.php?route=register');
            return;
        }

        $this->rolModel->asignarRolAUsuario($usuarioId, (int) $rolCliente['id'], null);

        $this->guardarMensaje('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
        $this->redireccionar('index.php?route=login');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->redireccionar('index.php?route=login');
    }

    public function estaAutenticado(): bool
    {
        return isset($_SESSION['auth']['usuario_id']);
    }

    public function usuarioActual(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public function requerirAutenticacion(): void
    {
        if (!$this->estaAutenticado()) {
            $this->guardarMensaje('error', 'Debes iniciar sesión para continuar.');
            $this->redireccionar('index.php?route=login');
            exit;
        }
    }

    
    public function requerirRol(array $rolesPermitidos): void
    {
        $this->requerirAutenticacion();

        $rolActual = $_SESSION['auth']['rol_principal']['rol_nombre'] ?? null;

        if ($rolActual === null || !in_array($rolActual, $rolesPermitidos, true)) {
            $this->denegarAcceso();
        }
    }




public function requerirPermiso(string $accion, ?int $tiendaId = null): void
{
    $this->requerirAutenticacion();

    // El Superadministrador tiene acceso total por definición (rol raíz).
    // Evita falsos 403 cuando se agregan módulos/permisos nuevos.
    $rolActual = $_SESSION['auth']['rol_principal']['rol_nombre'] ?? '';
    if ($rolActual === 'Superadministrador') {
        return;
    }

    $usuarioId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

    if ($usuarioId <= 0) {
        $this->denegarAcceso();
    }

    if (!$this->permisoModel->usuarioTienePermiso($usuarioId, $accion, $tiendaId)) {
        $this->denegarAcceso();
    }
}

public function requerirPermisoEnTienda(string $accion, int $tiendaId): void
{
    if ($tiendaId <= 0) {
        $this->denegarAcceso();
    }

    $this->requerirPermiso($accion, $tiendaId);
}

// denegarAcceso() — heredado de ControllerHelper (tipo de retorno: never)


    /**
     * Mapa centralizado rol → ruta de dashboard.
     * Única fuente de verdad: usada tanto aquí como en web.php.
     *
     * @return array<string, string>
     */
    public static function mapaRolDashboard(): array
    {
        return [
            'Superadministrador'    => 'dashboard.superadmin',
            'Administrador de Tienda' => 'dashboard.admin_tienda',
            'Supervisor'            => 'dashboard.supervisor',
            'Vendedor'              => 'dashboard.vendedor',
            'Bodeguero'             => 'dashboard.bodeguero',
            'Reportero'             => 'dashboard.reportero',
            'Nómina y RRHH'         => 'dashboard.nomina',
            'Cliente'               => 'dashboard.cliente',
            'Sistema'               => 'dashboard.sistema',
        ];
    }

    private function redireccionarSegunRolPrincipal(): void
    {
        $rol  = $_SESSION['auth']['rol_principal']['rol_nombre'] ?? '';
        $mapa = self::mapaRolDashboard();

        $ruta = isset($mapa[$rol]) ? 'index.php?route=' . $mapa[$rol] : 'index.php?route=dashboard';

        $this->redireccionar($ruta);
    }

    private function iniciarSesionSegura(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        $httpsActivo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $httpsActivo,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    // guardarMensaje() y redireccionar() — heredados de ControllerHelper

    private function limpiarTexto(string $valor): string
    {
        return trim($valor);
    }
}
