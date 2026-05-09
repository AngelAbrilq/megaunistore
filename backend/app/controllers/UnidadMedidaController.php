<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/UnidadMedida.php';

final class UnidadMedidaController
{
    private UnidadMedida $unidadModel;

    public function __construct()
    {
        $this->unidadModel = new UnidadMedida();
    }

    public function index(): void
    {
        $unidades = $this->unidadModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/unidades_medida/index.php';
    }

    public function create(): void
    {
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/unidades_medida/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=unidades.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatosUnidad($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=unidades.create');
        }

        $this->unidadModel->crear($datos);

        $this->guardarMensaje('success', 'Unidad de medida creada correctamente.');
        $this->redireccionar('index.php?route=unidades.index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Unidad de medida no válida.');
            $this->redireccionar('index.php?route=unidades.index');
        }

        $unidad = $this->unidadModel->buscarPorId($id);

        if ($unidad === null) {
            $this->guardarMensaje('error', 'La unidad de medida no existe.');
            $this->redireccionar('index.php?route=unidades.index');
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/unidades_medida/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=unidades.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Unidad de medida no válida.');
            $this->redireccionar('index.php?route=unidades.index');
        }

        $unidad = $this->unidadModel->buscarPorId($id);

        if ($unidad === null) {
            $this->guardarMensaje('error', 'La unidad de medida no existe.');
            $this->redireccionar('index.php?route=unidades.index');
        }

        $datos = $this->validarDatosUnidad($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=unidades.edit&id=' . $id);
        }

        $this->unidadModel->actualizar($id, $datos);

        $this->guardarMensaje('success', 'Unidad de medida actualizada correctamente.');
        $this->redireccionar('index.php?route=unidades.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=unidades.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Unidad de medida no válida.');
            $this->redireccionar('index.php?route=unidades.index');
        }

        try {
            $this->unidadModel->eliminar($id);
            $this->guardarMensaje('success', 'Unidad de medida eliminada correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje(
                'error',
                'No se puede eliminar esta unidad porque puede estar relacionada con productos.'
            );
        }

        $this->redireccionar('index.php?route=unidades.index');
    }

    private function validarDatosUnidad(array $input): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $simbolo = trim((string) ($input['simbolo'] ?? ''));
        $tipo = trim((string) ($input['tipo'] ?? ''));

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre de la unidad es obligatorio.');
            return null;
        }

        if (strlen($nombre) > 80) {
            $this->guardarMensaje('error', 'El nombre no puede superar 80 caracteres.');
            return null;
        }

        if ($simbolo === '') {
            $this->guardarMensaje('error', 'El símbolo de la unidad es obligatorio.');
            return null;
        }

        if (strlen($simbolo) > 10) {
            $this->guardarMensaje('error', 'El símbolo no puede superar 10 caracteres.');
            return null;
        }

        if ($tipo !== '' && strlen($tipo) > 50) {
            $this->guardarMensaje('error', 'El tipo no puede superar 50 caracteres.');
            return null;
        }

        return [
            'nombre' => $nombre,
            'simbolo' => $simbolo,
            'tipo' => $tipo !== '' ? $tipo : null,
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