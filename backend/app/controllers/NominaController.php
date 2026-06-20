<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Nomina.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class NominaController
{
    use ControllerHelper;

    private Nomina $nominaModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->nominaModel = new Nomina();
        $this->tiendaModel = new Tienda();
    }

    // -------------------------------------------------------------------------
    // INDEX — lista de períodos
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $tiendaId = $this->tiendaActual();
        $nominas  = $this->nominaModel->listar($tiendaId ?: null);
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/nomina/index.php';
    }

    // -------------------------------------------------------------------------
    // CREATE — formulario nuevo período
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $tiendas   = $this->tiendaModel->listar();
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/nomina/create.php';
    }

    // -------------------------------------------------------------------------
    // STORE — guardar nuevo período
    // -------------------------------------------------------------------------

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=nomina.index');
        }

        $this->validarCsrfToken();

        $tiendaId = (int) ($_POST['tienda_id'] ?? 0);
        $inicio   = trim($_POST['periodo_inicio'] ?? '');
        $fin      = trim($_POST['periodo_fin']    ?? '');
        $tipo     = trim($_POST['tipo']           ?? 'mensual');

        if ($tiendaId <= 0 || $inicio === '' || $fin === '') {
            $this->guardarMensaje('error', 'Todos los campos son obligatorios.');
            $this->redireccionar('index.php?route=nomina.create');
        }

        if ($fin < $inicio) {
            $this->guardarMensaje('error', 'La fecha fin debe ser posterior al inicio.');
            $this->redireccionar('index.php?route=nomina.create');
        }

        $id = $this->nominaModel->crear([
            'tienda_id'      => $tiendaId,
            'periodo_inicio' => $inicio,
            'periodo_fin'    => $fin,
            'tipo'           => $tipo,
        ]);

        $this->guardarMensaje('success', 'Período de nómina creado correctamente.');
        $this->redireccionar('index.php?route=nomina.show&id=' . $id);
    }

    // -------------------------------------------------------------------------
    // SHOW — detalle del período + empleados
    // -------------------------------------------------------------------------

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        $nomina = $this->nominaModel->buscarPorId($id);
        if ($nomina === null) {
            $this->guardarMensaje('error', 'Nómina no encontrada.');
            $this->redireccionar('index.php?route=nomina.index');
        }

        $empleados = $this->nominaModel->obtenerEmpleados($id);
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/nomina/show.php';
    }

    // -------------------------------------------------------------------------
    // CALCULAR — POST: calcula automáticamente para todos los empleados
    // -------------------------------------------------------------------------

    public function calcular(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=nomina.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['nomina_id'] ?? 0);

        $nomina = $this->nominaModel->buscarPorId($id);
        if ($nomina === null || $nomina['estado'] !== 'borrador') {
            $this->guardarMensaje('error', 'Solo se puede calcular una nómina en estado borrador.');
            $this->redireccionar('index.php?route=nomina.index');
        }

        $procesados = $this->nominaModel->calcular($id);

        if ($procesados === 0) {
            $this->guardarMensaje('error', 'No se encontraron empleados activos con contratos vigentes en esta tienda.');
        } else {
            $this->guardarMensaje('success', "Nómina calculada: $procesados empleado(s) procesado(s).");
        }

        $this->redireccionar('index.php?route=nomina.show&id=' . $id);
    }

    // -------------------------------------------------------------------------
    // APROBAR — POST
    // -------------------------------------------------------------------------

    public function aprobar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=nomina.index');
        }

        $this->validarCsrfToken();

        $id      = (int) ($_POST['nomina_id'] ?? 0);
        $adminId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

        $nomina = $this->nominaModel->buscarPorId($id);
        if ($nomina === null || $nomina['estado'] !== 'calculada') {
            $this->guardarMensaje('error', 'Solo se puede aprobar una nómina calculada.');
            $this->redireccionar('index.php?route=nomina.show&id=' . $id);
        }

        $this->nominaModel->aprobar($id, $adminId);
        $this->guardarMensaje('success', 'Nómina aprobada correctamente.');
        $this->redireccionar('index.php?route=nomina.show&id=' . $id);
    }

    // -------------------------------------------------------------------------
    // PAGAR — POST
    // -------------------------------------------------------------------------

    public function pagar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=nomina.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['nomina_id'] ?? 0);

        $nomina = $this->nominaModel->buscarPorId($id);
        if ($nomina === null || $nomina['estado'] !== 'aprobada') {
            $this->guardarMensaje('error', 'Solo se puede pagar una nómina aprobada.');
            $this->redireccionar('index.php?route=nomina.show&id=' . $id);
        }

        $this->nominaModel->pagar($id);
        $this->guardarMensaje('success', 'Nómina marcada como pagada. Todos los empleados han sido notificados.');
        $this->redireccionar('index.php?route=nomina.show&id=' . $id);
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
