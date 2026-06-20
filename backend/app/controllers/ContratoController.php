<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Contrato.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';
require_once __DIR__ . '/../../config/database.php';

final class ContratoController
{
    use ControllerHelper;

    private Contrato $contratoModel;
    private Empleado $empleadoModel;
    private PDO $db;

    public function __construct()
    {
        $this->contratoModel = new Contrato();
        $this->empleadoModel = new Empleado();
        $this->db = Database::getConnection();
    }

    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $tiendaId  = $this->tiendaActual();
        $contratos = $this->contratoModel->listar($tiendaId ?: null);
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/contratos/index.php';
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $tiendaId = $this->tiendaActual();
        $empleados = $this->empleadoModel->listarParaSelect($tiendaId ?: null);
        $cargos   = $this->contratoModel->listarCargos($tiendaId ?: 0);
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/contratos/create.php';
    }

    // -------------------------------------------------------------------------
    // STORE
    // -------------------------------------------------------------------------

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contratos.index');
        }

        $this->validarCsrfToken();

        $empleadoId = (int) ($_POST['empleado_id']  ?? 0);
        $cargoId    = (int) ($_POST['cargo_id']     ?? 0);
        $salario    = (float) ($_POST['salario_base'] ?? 0);

        if ($empleadoId <= 0 || $cargoId <= 0 || $salario <= 0) {
            $this->guardarMensaje('error', 'Empleado, cargo y salario son obligatorios.');
            $this->redireccionar('index.php?route=contratos.create');
        }

        $id = $this->contratoModel->crear([
            'empleado_id'      => $empleadoId,
            'tipo_contrato'    => $_POST['tipo_contrato']    ?? 'indefinido',
            'fecha_inicio'     => $_POST['fecha_inicio']     ?? date('Y-m-d'),
            'fecha_fin'        => $_POST['fecha_fin']        ?? null,
            'salario_base'     => $salario,
            'cargo_id'         => $cargoId,
            'jornada'          => $_POST['jornada']          ?? 'completa',
            'eps_id'           => $_POST['eps_id']           ?? null,
            'afp_id'           => $_POST['afp_id']           ?? null,
            'arl_id'           => $_POST['arl_id']           ?? null,
        ]);

        // Sincronizar salario_base en empleados
        $this->db->prepare("UPDATE empleados SET salario_base = :s WHERE id = :id")
            ->execute([':s' => $salario, ':id' => $empleadoId]);

        $this->jsonExito('contratos.index', 'Contrato creado correctamente.');
    }

    // -------------------------------------------------------------------------
    // EDIT
    // -------------------------------------------------------------------------

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        $contrato = $this->contratoModel->buscarPorId($id);
        if ($contrato === null) {
            $this->guardarMensaje('error', 'Contrato no encontrado.');
            $this->redireccionar('index.php?route=contratos.index');
        }

        $tiendaId = (int) ($contrato['tienda_id'] ?? $this->tiendaActual());
        $cargos   = $this->contratoModel->listarCargos($tiendaId);
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/contratos/edit.php';
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contratos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        $contrato = $this->contratoModel->buscarPorId($id);
        if ($contrato === null) {
            $this->guardarMensaje('error', 'Contrato no encontrado.');
            $this->redireccionar('index.php?route=contratos.index');
        }

        $this->contratoModel->actualizar($id, [
            'tipo_contrato' => $_POST['tipo_contrato'] ?? $contrato['tipo_contrato'],
            'fecha_inicio'  => $_POST['fecha_inicio']  ?? $contrato['fecha_inicio'],
            'fecha_fin'     => $_POST['fecha_fin']     ?? null,
            'salario_base'  => (float) ($_POST['salario_base'] ?? $contrato['salario_base']),
            'cargo_id'      => (int) ($_POST['cargo_id'] ?? $contrato['cargo_id']),
            'jornada'       => $_POST['jornada']       ?? $contrato['jornada'],
            'eps_id'        => $_POST['eps_id']        ?? null,
            'afp_id'        => $_POST['afp_id']        ?? null,
            'arl_id'        => $_POST['arl_id']        ?? null,
        ]);

        $this->jsonExito('contratos.index', 'Contrato actualizado correctamente.');
    }

    // -------------------------------------------------------------------------
    // TERMINAR — POST: finaliza el contrato
    // -------------------------------------------------------------------------

    public function terminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contratos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        $contrato = $this->contratoModel->buscarPorId($id);
        if ($contrato === null) {
            $this->guardarMensaje('error', 'Contrato no encontrado.');
            $this->redireccionar('index.php?route=contratos.index');
        }

        $this->contratoModel->terminar($id);
        $this->guardarMensaje('success', 'Contrato terminado correctamente.');
        $this->redireccionar('index.php?route=contratos.index');
    }

    // =========================================================================
    // CARGOS — gestión inline de cargos por tienda (POST desde modal)
    // =========================================================================

    public function cargos(): void
    {
        $tiendaId = $this->tiendaActual();
        $cargos   = $this->contratoModel->listarCargos($tiendaId ?: 0);
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/contratos/cargos.php';
    }

    public function cargoStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contratos.cargos');
        }

        $this->validarCsrfToken();

        $tiendaId = (int) ($_POST['tienda_id'] ?? $this->tiendaActual());
        $nombre   = trim($_POST['nombre'] ?? '');

        if ($nombre === '' || $tiendaId <= 0) {
            $this->guardarMensaje('error', 'Nombre y tienda son obligatorios.');
            $this->redireccionar('index.php?route=contratos.cargos');
        }

        $this->db->prepare("
            INSERT INTO cargos (tienda_id, nombre, descripcion, nivel_jerarquico, activo)
            VALUES (:tienda_id, :nombre, :descripcion, :nivel, 1)
        ")->execute([
            ':tienda_id'   => $tiendaId,
            ':nombre'      => $nombre,
            ':descripcion' => trim($_POST['descripcion'] ?? ''),
            ':nivel'       => (int) ($_POST['nivel_jerarquico'] ?? 1),
        ]);

        $this->jsonExito('contratos.cargos', 'Cargo creado correctamente.');
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function tiendaActual(): int
    {
        // tienda_id vive en rol_principal para roles de tienda (Admin, Supervisor, etc.)
        // Para Superadmin y Nómina y RRHH es null → devuelve 0 (listar todo)
        return (int) ($_SESSION['auth']['rol_principal']['tienda_id'] ?? 0);
    }
}
