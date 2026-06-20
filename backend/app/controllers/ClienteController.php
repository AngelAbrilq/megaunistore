<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class ClienteController
{
    use ControllerHelper;

    private Cliente $clienteModel;
    private Tienda  $tiendaModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
        $this->tiendaModel  = new Tienda();
    }

    public function index(): void
    {
        $tiendaId = $this->tiendaIdPermitida();
        $clientes  = $this->clienteModel->listar($tiendaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/clientes/index.php';
    }

    public function create(): void
    {
        $tiendas   = $this->tiendaModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/clientes/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=clientes.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=clientes.create');
        }

        // Verificar documento duplicado
        if (!empty($datos['tipo_documento']) && !empty($datos['numero_documento'])) {
            $existente = $this->clienteModel->buscarPorDocumento(
                $datos['tipo_documento'],
                $datos['numero_documento']
            );

            if ($existente !== null) {
                // Si ya existe, solo asociarlo a la tienda
                $tiendaId = (int) ($_POST['tienda_id'] ?? 0);
                if ($tiendaId > 0) {
                    $this->clienteModel->asociarATienda((int) $existente['id'], $tiendaId);
                }
                $this->jsonExito('clientes.index', 'Cliente ya registrado. Asociado a la tienda.');
            }
        }

        // Verificar email duplicado
        if (!empty($datos['email']) && $this->clienteModel->existeEmail($datos['email'])) {
            $this->guardarMensaje('error', 'El correo electronico ya esta registrado.');
            $this->redireccionar('index.php?route=clientes.create');
        }

        $tiendaId = (int) ($_POST['tienda_id'] ?? 0);

        if ($tiendaId > 0) {
            $this->clienteModel->crearYAsociar($datos, $tiendaId);
        } else {
            $this->clienteModel->crear($datos);
        }

        $this->jsonExito('clientes.index', 'Cliente registrado correctamente.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Cliente no valido.');
            $this->redireccionar('index.php?route=clientes.index');
        }

        $cliente = $this->clienteModel->buscarPorId($id);
        if ($cliente === null) {
            $this->guardarMensaje('error', 'El cliente no existe o fue eliminado.');
            $this->redireccionar('index.php?route=clientes.index');
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/clientes/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=clientes.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Cliente no valido.');
            $this->redireccionar('index.php?route=clientes.index');
        }

        $cliente = $this->clienteModel->buscarPorId($id);
        if ($cliente === null) {
            $this->guardarMensaje('error', 'El cliente no existe o fue eliminado.');
            $this->redireccionar('index.php?route=clientes.index');
        }

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=clientes.edit&id=' . $id);
        }

        if (!empty($datos['email']) && $this->clienteModel->existeEmail($datos['email'], $id)) {
            $this->guardarMensaje('error', 'El correo electronico ya esta asignado a otro cliente.');
            $this->redireccionar('index.php?route=clientes.edit&id=' . $id);
        }

        $this->clienteModel->actualizar($id, $datos);

        $this->jsonExito('clientes.index', 'Cliente actualizado correctamente.');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=clientes.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Cliente no valido.');
            $this->redireccionar('index.php?route=clientes.index');
        }

        $this->clienteModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Cliente eliminado correctamente.');
        $this->redireccionar('index.php?route=clientes.index');
    }

    // -------------------------------------------------------------------------

    private function validarDatos(array $input): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre es obligatorio.');
            return null;
        }

        $email = strtolower(trim((string) ($input['email'] ?? '')));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->guardarMensaje('error', 'El correo electronico no tiene un formato valido.');
            return null;
        }

        return [
            'nombre'           => $nombre,
            'apellido'         => trim((string) ($input['apellido'] ?? '')),
            'email'            => $email,
            'telefono'         => trim((string) ($input['telefono'] ?? '')),
            'tipo_documento'   => trim((string) ($input['tipo_documento'] ?? '')),
            'numero_documento' => trim((string) ($input['numero_documento'] ?? '')),
            'direccion'        => trim((string) ($input['direccion'] ?? '')),
        ];
    }
}
