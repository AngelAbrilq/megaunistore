<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Categoria.php';

final class CategoriaController
{
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }

    public function index(): void
    {
        $categorias = $this->categoriaModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/categorias/index.php';
    }

    public function create(): void
    {
        $categoriasPadre = $this->categoriaModel->listarActivasParaSelect();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/categorias/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=categorias.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatosCategoria($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=categorias.create');
        }

        $this->categoriaModel->crear($datos);

        $this->guardarMensaje('success', 'Categoría creada correctamente.');
        $this->redireccionar('index.php?route=categorias.index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Categoría no válida.');
            $this->redireccionar('index.php?route=categorias.index');
        }

        $categoria = $this->categoriaModel->buscarPorId($id);

        if ($categoria === null) {
            $this->guardarMensaje('error', 'La categoría no existe o fue eliminada.');
            $this->redireccionar('index.php?route=categorias.index');
        }

        $categoriasPadre = $this->categoriaModel->listarActivasParaSelect($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/categorias/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=categorias.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Categoría no válida.');
            $this->redireccionar('index.php?route=categorias.index');
        }

        $categoria = $this->categoriaModel->buscarPorId($id);

        if ($categoria === null) {
            $this->guardarMensaje('error', 'La categoría no existe o fue eliminada.');
            $this->redireccionar('index.php?route=categorias.index');
        }

        $datos = $this->validarDatosCategoria($_POST, $id);

        if ($datos === null) {
            $this->redireccionar('index.php?route=categorias.edit&id=' . $id);
        }

        $this->categoriaModel->actualizar($id, $datos);

        $this->guardarMensaje('success', 'Categoría actualizada correctamente.');
        $this->redireccionar('index.php?route=categorias.index');
    }

    public function toggleEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=categorias.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Categoría no válida.');
            $this->redireccionar('index.php?route=categorias.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;

        $this->categoriaModel->cambiarEstado($id, $nuevoEstado);

        $mensaje = $nuevoEstado === 1
            ? 'Categoría activada correctamente.'
            : 'Categoría desactivada correctamente.';

        $this->guardarMensaje('success', $mensaje);
        $this->redireccionar('index.php?route=categorias.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=categorias.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Categoría no válida.');
            $this->redireccionar('index.php?route=categorias.index');
        }

        $this->categoriaModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Categoría eliminada correctamente.');
        $this->redireccionar('index.php?route=categorias.index');
    }

    private function validarDatosCategoria(array $input, ?int $categoriaIdActual = null): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $imagenUrl = trim((string) ($input['imagen_url'] ?? ''));
        $categoriaPadreId = (int) ($input['categoria_padre_id'] ?? 0);
        $activo = (int) ($input['activo'] ?? 1);

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre de la categoría es obligatorio.');
            return null;
        }

        if (strlen($nombre) > 100) {
            $this->guardarMensaje('error', 'El nombre de la categoría no puede superar 100 caracteres.');
            return null;
        }

        if ($categoriaIdActual !== null && $categoriaPadreId === $categoriaIdActual) {
            $this->guardarMensaje('error', 'Una categoría no puede ser padre de sí misma.');
            return null;
        }

        if ($categoriaPadreId > 0) {
            $categoriaPadre = $this->categoriaModel->buscarPorId($categoriaPadreId);

            if ($categoriaPadre === null) {
                $this->guardarMensaje('error', 'La categoría padre seleccionada no existe.');
                return null;
            }
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'categoria_padre_id' => $categoriaPadreId > 0 ? $categoriaPadreId : null,
            'imagen_url' => $imagenUrl !== '' ? $imagenUrl : null,
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