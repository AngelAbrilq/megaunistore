<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class EmpleadoController
{
    use ControllerHelper;

    private Empleado $empleadoModel;
    private Usuario  $usuarioModel;
    private Tienda   $tiendaModel;

    public function __construct()
    {
        $this->empleadoModel = new Empleado();
        $this->usuarioModel  = new Usuario();
        $this->tiendaModel   = new Tienda();
    }

    public function index(): void
    {
        $tiendaId  = $this->tiendaIdPermitida();
        $empleados = $this->empleadoModel->listar($tiendaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/empleados/index.php';
    }

    public function create(): void
    {
        $tiendaId = $this->tiendaIdPermitida();
        $usuarios = $this->usuarioModel->listar();
        $tiendas  = $tiendaId === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaId)];

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/empleados/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=empleados.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatos($_POST, true);
        if ($datos === null) {
            $this->redireccionar('index.php?route=empleados.create');
        }

        $this->validarAccesoATienda((int) $datos['tienda_id']);

        if ($this->empleadoModel->existeCodigoEnTienda($datos['codigo_empleado'], (int) $datos['tienda_id'])) {
            $this->guardarMensaje('error', 'El codigo de empleado ya existe en esta tienda.');
            $this->redireccionar('index.php?route=empleados.create');
        }

        if ($this->empleadoModel->usuarioYaEsEmpleadoEnTienda((int) $datos['usuario_id'], (int) $datos['tienda_id'])) {
            $this->guardarMensaje('error', 'Este usuario ya esta registrado como empleado en esta tienda.');
            $this->redireccionar('index.php?route=empleados.create');
        }

        $this->empleadoModel->crear($datos);

        $this->jsonExito('empleados.index', 'Empleado registrado correctamente.');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Empleado no valido.');
            $this->redireccionar('index.php?route=empleados.index');
        }

        $empleado = $this->empleadoModel->buscarPorId($id);
        if ($empleado === null) {
            $this->guardarMensaje('error', 'El empleado no existe o fue eliminado.');
            $this->redireccionar('index.php?route=empleados.index');
        }

        $this->validarAccesoATienda((int) $empleado['tienda_id']);

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/empleados/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=empleados.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Empleado no valido.');
            $this->redireccionar('index.php?route=empleados.index');
        }

        $empleado = $this->empleadoModel->buscarPorId($id);
        if ($empleado === null) {
            $this->guardarMensaje('error', 'El empleado no existe o fue eliminado.');
            $this->redireccionar('index.php?route=empleados.index');
        }

        $this->validarAccesoATienda((int) $empleado['tienda_id']);

        $datos = $this->validarDatos($_POST, false);
        if ($datos === null) {
            $this->redireccionar('index.php?route=empleados.edit&id=' . $id);
        }

        if ($this->empleadoModel->existeCodigoEnTienda(
            $datos['codigo_empleado'],
            (int) $empleado['tienda_id'],
            $id
        )) {
            $this->guardarMensaje('error', 'El codigo de empleado ya existe en esta tienda.');
            $this->redireccionar('index.php?route=empleados.edit&id=' . $id);
        }

        $this->empleadoModel->actualizar($id, $datos);

        $this->jsonExito('empleados.index', 'Empleado actualizado correctamente.');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=empleados.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Empleado no valido.');
            $this->redireccionar('index.php?route=empleados.index');
        }

        $empleado = $this->empleadoModel->buscarPorId($id);
        if ($empleado !== null) {
            $this->validarAccesoATienda((int) $empleado['tienda_id']);
        }

        $this->empleadoModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Empleado desvinculado correctamente.');
        $this->redireccionar('index.php?route=empleados.index');
    }

    // -------------------------------------------------------------------------

    private function validarDatos(array $input, bool $requiereTienda): ?array
    {
        $codigo       = trim((string) ($input['codigo_empleado'] ?? ''));
        $fechaIngreso = trim((string) ($input['fecha_ingreso'] ?? ''));
        $salarioRaw   = trim((string) ($input['salario_base'] ?? ''));
        $estado       = trim((string) ($input['estado'] ?? 'activo'));

        if ($codigo === '') {
            $this->guardarMensaje('error', 'El codigo de empleado es obligatorio.');
            return null;
        }

        if ($fechaIngreso === '') {
            $this->guardarMensaje('error', 'La fecha de ingreso es obligatoria.');
            return null;
        }

        if ($salarioRaw === '' || !is_numeric($salarioRaw) || (float) $salarioRaw < 0) {
            $this->guardarMensaje('error', 'El salario base debe ser un valor numerico positivo.');
            return null;
        }

        $datos = [
            'codigo_empleado' => $codigo,
            'fecha_ingreso'   => $fechaIngreso,
            'salario_base'    => number_format((float) $salarioRaw, 2, '.', ''),
            'estado'          => in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'activo',
        ];

        if ($requiereTienda) {
            $usuarioId = (int) ($input['usuario_id'] ?? 0);
            $tiendaId  = (int) ($input['tienda_id'] ?? 0);

            if ($usuarioId <= 0) {
                $this->guardarMensaje('error', 'Debes seleccionar un usuario.');
                return null;
            }

            if ($tiendaId <= 0) {
                $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
                return null;
            }

            $datos['usuario_id'] = $usuarioId;
            $datos['tienda_id']  = $tiendaId;
        }

        return $datos;
    }
}
