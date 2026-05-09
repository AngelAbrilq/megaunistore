<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Tienda.php';

final class TiendaController
{
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $tiendas = $this->tiendaModel->listar();

        require __DIR__ . '/../../resources/views/tiendas/index.php';
    }

    public function create(): void
    {
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/tiendas/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatosTienda($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=tiendas.create');
        }

        $usuarioId = $this->usuarioIdActual();

        $this->tiendaModel->crear([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'],
            'logo_url' => $datos['logo_url'],
            'direccion' => $datos['direccion'],
            'telefono' => $datos['telefono'],
            'email' => $datos['email'],
            'propietario_id' => $usuarioId,
            'plataforma_id' => 1,
            'estado' => 1,
            'updated_by' => $usuarioId,
        ]);

        $this->guardarMensaje('success', 'Tienda creada correctamente.');
        $this->redireccionar('index.php?route=tiendas.index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Tienda no válida.');
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $tienda = $this->tiendaModel->buscarPorId($id);

        if ($tienda === null) {
            $this->guardarMensaje('error', 'La tienda no existe o fue eliminada.');
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/tiendas/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Tienda no válida.');
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $tienda = $this->tiendaModel->buscarPorId($id);

        if ($tienda === null) {
            $this->guardarMensaje('error', 'La tienda no existe o fue eliminada.');
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $datos = $this->validarDatosTienda($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=tiendas.edit&id=' . $id);
        }

        $this->tiendaModel->actualizar($id, [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'],
            'logo_url' => $datos['logo_url'],
            'direccion' => $datos['direccion'],
            'telefono' => $datos['telefono'],
            'email' => $datos['email'],
            'estado' => (int) ($_POST['estado'] ?? 1),
            'updated_by' => $this->usuarioIdActual(),
        ]);

        $this->guardarMensaje('success', 'Tienda actualizada correctamente.');
        $this->redireccionar('index.php?route=tiendas.index');
    }

    public function toggleEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Tienda no válida.');
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;

        $this->tiendaModel->cambiarEstado($id, $nuevoEstado, $this->usuarioIdActual());

        $mensaje = $nuevoEstado === 1
            ? 'Tienda activada correctamente.'
            : 'Tienda desactivada correctamente.';

        $this->guardarMensaje('success', $mensaje);
        $this->redireccionar('index.php?route=tiendas.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Tienda no válida.');
            $this->redireccionar('index.php?route=tiendas.index');
        }

        $this->tiendaModel->eliminarLogico($id, $this->usuarioIdActual());

        $this->guardarMensaje('success', 'Tienda eliminada correctamente.');
        $this->redireccionar('index.php?route=tiendas.index');
    }

    private function validarDatosTienda(array $input): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $logoUrl = trim((string) ($input['logo_url'] ?? ''));
        $direccion = trim((string) ($input['direccion'] ?? ''));
        $telefono = trim((string) ($input['telefono'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre de la tienda es obligatorio.');
            return null;
        }

        if ($direccion === '') {
            $this->guardarMensaje('error', 'La dirección de la tienda es obligatoria.');
            return null;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'El correo de la tienda no tiene un formato válido.');
            return null;
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'logo_url' => $logoUrl !== '' ? $logoUrl : null,
            'direccion' => $direccion,
            'telefono' => $telefono !== '' ? $telefono : null,
            'email' => $email !== '' ? strtolower($email) : null,
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