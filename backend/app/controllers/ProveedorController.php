<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class ProveedorController
{
    use ControllerHelper;

    private Proveedor $proveedorModel;

    public function __construct()
    {
        $this->proveedorModel = new Proveedor();
    }

    public function index(): void
    {
        $proveedores = $this->proveedorModel->listar();
        $csrfToken   = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/proveedores/index.php';
    }

    public function create(): void
    {
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/proveedores/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=proveedores.create');
        }

        if ($this->proveedorModel->existeNit($datos['ruc_nit'])) {
            $this->guardarMensaje('error', 'Ya existe un proveedor con ese NIT/RUC.');
            $this->redireccionar('index.php?route=proveedores.create');
        }

        $this->proveedorModel->crear($datos);

        $this->jsonExito('proveedores.index', 'Proveedor registrado correctamente.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Proveedor no valido.');
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $proveedor = $this->proveedorModel->buscarPorId($id);
        if ($proveedor === null) {
            $this->guardarMensaje('error', 'El proveedor no existe o fue eliminado.');
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/proveedores/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Proveedor no valido.');
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $proveedor = $this->proveedorModel->buscarPorId($id);
        if ($proveedor === null) {
            $this->guardarMensaje('error', 'El proveedor no existe o fue eliminado.');
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=proveedores.edit&id=' . $id);
        }

        if ($this->proveedorModel->existeNit($datos['ruc_nit'], $id)) {
            $this->guardarMensaje('error', 'Ya existe otro proveedor con ese NIT/RUC.');
            $this->redireccionar('index.php?route=proveedores.edit&id=' . $id);
        }

        $this->proveedorModel->actualizar($id, $datos);

        $this->jsonExito('proveedores.index', 'Proveedor actualizado correctamente.');
    }

    public function toggleEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $this->validarCsrfToken();

        $id           = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Proveedor no valido.');
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;
        $this->proveedorModel->cambiarEstado($id, $nuevoEstado);

        $this->guardarMensaje('success', $nuevoEstado === 1 ? 'Proveedor activado.' : 'Proveedor desactivado.');
        $this->redireccionar('index.php?route=proveedores.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Proveedor no valido.');
            $this->redireccionar('index.php?route=proveedores.index');
        }

        $this->proveedorModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Proveedor eliminado correctamente.');
        $this->redireccionar('index.php?route=proveedores.index');
    }

    // -------------------------------------------------------------------------

    private function validarDatos(array $input): ?array
    {
        $nombre  = trim((string) ($input['nombre'] ?? ''));
        $rucNit  = trim((string) ($input['ruc_nit'] ?? ''));

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre del proveedor es obligatorio.');
            return null;
        }

        if ($rucNit === '') {
            $this->guardarMensaje('error', 'El NIT/RUC es obligatorio.');
            return null;
        }

        $email = strtolower(trim((string) ($input['email'] ?? '')));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'El correo electronico no tiene un formato valido.');
            return null;
        }

        return [
            'nombre'          => $nombre,
            'ruc_nit'         => $rucNit,
            'telefono'        => trim((string) ($input['telefono'] ?? '')),
            'email'           => $email,
            'direccion'       => trim((string) ($input['direccion'] ?? '')),
            'contacto_nombre' => trim((string) ($input['contacto_nombre'] ?? '')),
            'estado'          => (int) ($input['estado'] ?? 1),
        ];
    }
}
