<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Impuesto.php';

final class ImpuestoController
{
    private Impuesto $impuestoModel;

    public function __construct()
    {
        $this->impuestoModel = new Impuesto();
    }

    public function index(): void
    {
        $impuestos = $this->impuestoModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/impuestos/index.php';
    }

    public function create(): void
    {
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/impuestos/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatosImpuesto($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=impuestos.create');
        }

        $this->impuestoModel->crear($datos);

        $this->guardarMensaje('success', 'Impuesto creado correctamente.');
        $this->redireccionar('index.php?route=impuestos.index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Impuesto no válido.');
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $impuesto = $this->impuestoModel->buscarPorId($id);

        if ($impuesto === null) {
            $this->guardarMensaje('error', 'El impuesto no existe.');
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/impuestos/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Impuesto no válido.');
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $impuesto = $this->impuestoModel->buscarPorId($id);

        if ($impuesto === null) {
            $this->guardarMensaje('error', 'El impuesto no existe.');
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $datos = $this->validarDatosImpuesto($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=impuestos.edit&id=' . $id);
        }

        $this->impuestoModel->actualizar($id, $datos);

        $this->guardarMensaje('success', 'Impuesto actualizado correctamente.');
        $this->redireccionar('index.php?route=impuestos.index');
    }

    public function toggleEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Impuesto no válido.');
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;

        $this->impuestoModel->cambiarEstado($id, $nuevoEstado);

        $mensaje = $nuevoEstado === 1
            ? 'Impuesto activado correctamente.'
            : 'Impuesto desactivado correctamente.';

        $this->guardarMensaje('success', $mensaje);
        $this->redireccionar('index.php?route=impuestos.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=impuestos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Impuesto no válido.');
            $this->redireccionar('index.php?route=impuestos.index');
        }

        try {
            $this->impuestoModel->eliminar($id);
            $this->guardarMensaje('success', 'Impuesto eliminado correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje(
                'error',
                'No se puede eliminar este impuesto porque puede estar relacionado con productos.'
            );
        }

        $this->redireccionar('index.php?route=impuestos.index');
    }

    private function validarDatosImpuesto(array $input): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $porcentaje = trim((string) ($input['porcentaje'] ?? ''));
        $tipo = trim((string) ($input['tipo'] ?? ''));
        $activo = (int) ($input['activo'] ?? 1);

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre del impuesto es obligatorio.');
            return null;
        }

        if (strlen($nombre) > 80) {
            $this->guardarMensaje('error', 'El nombre no puede superar 80 caracteres.');
            return null;
        }

        if ($porcentaje === '' || !is_numeric($porcentaje)) {
            $this->guardarMensaje('error', 'El porcentaje debe ser un número válido.');
            return null;
        }

        $porcentajeDecimal = (float) $porcentaje;

        if ($porcentajeDecimal < 0 || $porcentajeDecimal > 100) {
            $this->guardarMensaje('error', 'El porcentaje debe estar entre 0 y 100.');
            return null;
        }

        if ($tipo === '') {
            $this->guardarMensaje('error', 'El tipo de impuesto es obligatorio.');
            return null;
        }

        if (strlen($tipo) > 50) {
            $this->guardarMensaje('error', 'El tipo no puede superar 50 caracteres.');
            return null;
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'porcentaje' => number_format($porcentajeDecimal, 2, '.', ''),
            'tipo' => $tipo,
            'activo' => $activo === 1 ? 1 : 0,
        ];
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