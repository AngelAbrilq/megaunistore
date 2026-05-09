<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class UsuarioController
{
    use ControllerHelper;
    private Usuario $usuarioModel;
    private Rol $rolModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $usuarios = $this->usuarioModel->listar();

        foreach ($usuarios as $key => $usuario) {
            $usuarios[$key]['roles'] = $this->rolModel->obtenerRolesDeUsuario((int) $usuario['id']);
        }

        require __DIR__ . '/../../resources/views/usuarios/index.php';
    }

    public function create(): void
    {
        $roles = $this->rolModel->listarActivos();
        $tiendas = $this->tiendaModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/usuarios/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatosUsuario($_POST, true);

        if ($datos === null) {
            $this->redireccionar('index.php?route=usuarios.create');
        }

        if ($this->usuarioModel->existeEmail($datos['email'])) {
            $this->guardarMensaje('error', 'El correo electrónico ya está registrado.');
            $this->redireccionar('index.php?route=usuarios.create');
        }

        $usuarioId = $this->usuarioModel->crearAdministrativo([
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'telefono' => $datos['telefono'],
            'avatar_url' => null,
            'estado' => 1,
        ]);

        $this->asignarRolDesdeFormulario($usuarioId, $_POST);

        $this->guardarMensaje('success', 'Usuario creado correctamente.');
        $this->redireccionar('index.php?route=usuarios.index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Usuario no válido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $usuario = $this->usuarioModel->buscarPorId($id);

        if ($usuario === null) {
            $this->guardarMensaje('error', 'El usuario no existe o fue eliminado.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/usuarios/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Usuario no válido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $usuarioActual = $this->usuarioModel->buscarPorId($id);

        if ($usuarioActual === null) {
            $this->guardarMensaje('error', 'El usuario no existe o fue eliminado.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $datos = $this->validarDatosUsuario($_POST, false);

        if ($datos === null) {
            $this->redireccionar('index.php?route=usuarios.edit&id=' . $id);
        }

        $usuarioConEmail = $this->usuarioModel->buscarPorEmail($datos['email']);

        if ($usuarioConEmail !== null && (int) $usuarioConEmail['id'] !== $id) {
            $this->guardarMensaje('error', 'El correo electrónico ya está asignado a otro usuario.');
            $this->redireccionar('index.php?route=usuarios.edit&id=' . $id);
        }

        $this->usuarioModel->actualizar($id, [
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'email' => $datos['email'],
            'telefono' => $datos['telefono'],
            'avatar_url' => $usuarioActual['avatar_url'] ?? null,
            'estado' => (int) ($_POST['estado'] ?? 1),
        ]);

        if (!empty($_POST['password'])) {
            $password = (string) $_POST['password'];

            if (strlen($password) < 8) {
                $this->guardarMensaje('error', 'La nueva contraseña debe tener mínimo 8 caracteres.');
                $this->redireccionar('index.php?route=usuarios.edit&id=' . $id);
            }

            $this->usuarioModel->cambiarPassword($id, $password);
        }

        $this->guardarMensaje('success', 'Usuario actualizado correctamente.');
        $this->redireccionar('index.php?route=usuarios.index');
    }

    public function asignarRol(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Usuario no válido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $usuario = $this->usuarioModel->buscarPorId($id);

        if ($usuario === null) {
            $this->guardarMensaje('error', 'El usuario no existe o fue eliminado.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $roles = $this->rolModel->listarActivos();
        $tiendas = $this->tiendaModel->listar();
        $rolesUsuario = $this->rolModel->obtenerRolesDeUsuario($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/usuarios/asignar_rol.php';
    }

    public function guardarRol(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->validarCsrfToken();

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);

        if ($usuarioId <= 0) {
            $this->guardarMensaje('error', 'Usuario no válido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->asignarRolDesdeFormulario($usuarioId, $_POST);

        $this->guardarMensaje('success', 'Rol asignado correctamente.');
        $this->redireccionar('index.php?route=usuarios.asignar_rol&id=' . $usuarioId);
    }

    public function toggleEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 0);
        $usuarioSesionId = $this->usuarioIdActual();

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Usuario no válido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        if ($id === $usuarioSesionId) {
            $this->guardarMensaje('error', 'No puedes desactivar tu propio usuario.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;

        $this->usuarioModel->cambiarEstado($id, $nuevoEstado);

        $mensaje = $nuevoEstado === 1
            ? 'Usuario activado correctamente.'
            : 'Usuario desactivado correctamente.';

        $this->guardarMensaje('success', $mensaje);
        $this->redireccionar('index.php?route=usuarios.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $usuarioSesionId = $this->usuarioIdActual();

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Usuario no válido.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        if ($id === $usuarioSesionId) {
            $this->guardarMensaje('error', 'No puedes eliminar tu propio usuario.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $this->usuarioModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Usuario eliminado correctamente.');
        $this->redireccionar('index.php?route=usuarios.index');
    }

    private function asignarRolDesdeFormulario(int $usuarioId, array $input): void
    {
        $rolId = (int) ($input['rol_id'] ?? 0);
        $tiendaIdInput = $input['tienda_id'] ?? null;

        if ($rolId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar un rol.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $rol = $this->rolModel->buscarPorId($rolId);

        if ($rol === null) {
            $this->guardarMensaje('error', 'El rol seleccionado no existe.');
            $this->redireccionar('index.php?route=usuarios.index');
        }

        $rolesGlobales = ['Superadministrador', 'Sistema'];
        $rolNombre = $rol['nombre'];

        if (in_array($rolNombre, $rolesGlobales, true)) {
            $tiendaId = null;
        } else {
            $tiendaId = (int) $tiendaIdInput;

            if ($tiendaId <= 0) {
                $this->guardarMensaje('error', 'Debes seleccionar una tienda para este rol.');
                $this->redireccionar('index.php?route=usuarios.index');
            }

            $tienda = $this->tiendaModel->buscarPorId($tiendaId);

            if ($tienda === null) {
                $this->guardarMensaje('error', 'La tienda seleccionada no existe.');
                $this->redireccionar('index.php?route=usuarios.index');
            }
        }

        $this->rolModel->asignarRolAUsuario($usuarioId, $rolId, $tiendaId);
    }

    private function validarDatosUsuario(array $input, bool $requierePassword): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $apellido = trim((string) ($input['apellido'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telefono = trim((string) ($input['telefono'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($nombre === '' || $apellido === '' || $email === '') {
            $this->guardarMensaje('error', 'Nombre, apellido y correo son obligatorios.');
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'El correo electrónico no tiene un formato válido.');
            return null;
        }

        if ($requierePassword && strlen($password) < 8) {
            $this->guardarMensaje('error', 'La contraseña debe tener mínimo 8 caracteres.');
            return null;
        }

        return [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono !== '' ? $telefono : null,
            'password' => $password,
        ];
    }

    private function usuarioIdActual(): int
    {
        return (int) ($_SESSION['auth']['usuario_id'] ?? 0);
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
        $tokenSesion = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = $_POST['csrf_token'] ?? '';

        if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            http_response_code(419);
            echo 'Token de seguridad inválido.';
            exit;
        }
    }

    private function guardarMensaje(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = [
            'type' => $tipo,
            'message' => $mensaje,
        ];
    }

    private function redireccionar(string $ruta): void
    {
        header('Location: ' . $ruta);
        exit;
    }
}