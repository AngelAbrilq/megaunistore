<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Gasto.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/CuentaContable.php';
require_once __DIR__ . '/../models/CentroCosto.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

/**
 * Controlador de Gastos operacionales.
 * Cubre: CF-CON-006, REQ-7.8.2.
 */
final class GastoController
{
    use ControllerHelper;

    private Gasto $gastoModel;
    private Proveedor $proveedorModel;
    private Tienda $tiendaModel;
    private Empleado $empleadoModel;
    private CuentaContable $cuentaModel;
    private CentroCosto $centroModel;

    public function __construct()
    {
        $this->gastoModel     = new Gasto();
        $this->proveedorModel = new Proveedor();
        $this->tiendaModel    = new Tienda();
        $this->empleadoModel  = new Empleado();
        $this->cuentaModel    = new CuentaContable();
        $this->centroModel    = new CentroCosto();
    }

    public function index(): void
    {
        $tiendaId  = $this->tiendaIdPermitida();
        $gastos    = $this->gastoModel->listar($tiendaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/gastos/index.php';
    }

    public function create(): void
    {
        $tiendaId    = $this->tiendaIdPermitida();
        $proveedores = $this->proveedorModel->listar();
        $tiendas     = $tiendaId === null ? $this->tiendaModel->listar() : [];
        $cuentas     = $this->cuentaModel->listarPorTipo($tiendaId, ['egreso', 'costo']);
        $centros     = $this->centroModel->listar($tiendaId);
        $csrfToken   = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/gastos/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarCsrfToken();

        $tiendaId = $this->tiendaIdPermitida() ?? (int) ($_POST['tienda_id'] ?? 0);
        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=gastos.create');
        }

        $this->validarAccesoATienda($tiendaId);

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=gastos.create');
        }

        // Coherencia: cuenta contable y centro de costo deben ser de la misma tienda
        if (!$this->referenciasPertenecenATienda($datos, $tiendaId)) {
            $this->redireccionar('index.php?route=gastos.create');
        }

        $empleado = $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual(), $tiendaId)
            ?? $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual());

        $datos['tienda_id']   = $tiendaId;
        $datos['empleado_id'] = $empleado !== null ? (int) $empleado['id'] : null;
        $datos['estado']      = 'pendiente';

        $this->gastoModel->crear($datos);

        $this->jsonExito('gastos.index', 'Gasto registrado correctamente.');
    }

    public function edit(): void
    {
        $id    = (int) ($_GET['id'] ?? 0);
        $gasto = $id > 0 ? $this->gastoModel->buscarPorId($id) : null;

        if ($gasto === null) {
            $this->guardarMensaje('error', 'El gasto no existe o fue eliminado.');
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarAccesoATienda((int) $gasto['tienda_id']);

        $tiendaId    = $this->tiendaIdPermitida();
        $proveedores = $this->proveedorModel->listar();
        $cuentas     = $this->cuentaModel->listarPorTipo($tiendaId, ['egreso', 'costo']);
        $centros     = $this->centroModel->listar($tiendaId);
        $csrfToken   = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/gastos/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarCsrfToken();

        $id    = (int) ($_POST['id'] ?? 0);
        $gasto = $id > 0 ? $this->gastoModel->buscarPorId($id) : null;

        if ($gasto === null) {
            $this->guardarMensaje('error', 'El gasto no existe o fue eliminado.');
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarAccesoATienda((int) $gasto['tienda_id']);

        if ($gasto['estado'] !== 'pendiente') {
            $this->guardarMensaje('error', 'Solo se pueden editar gastos pendientes.');
            $this->redireccionar('index.php?route=gastos.index');
        }

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=gastos.edit&id=' . $id);
        }

        // Coherencia: cuenta contable y centro de costo deben ser de la misma tienda
        if (!$this->referenciasPertenecenATienda($datos, (int) $gasto['tienda_id'])) {
            $this->redireccionar('index.php?route=gastos.edit&id=' . $id);
        }

        $this->gastoModel->actualizar($id, $datos);

        $this->jsonExito('gastos.index', 'Gasto actualizado correctamente.');
    }

    public function cambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');
        $gasto  = $id > 0 ? $this->gastoModel->buscarPorId($id) : null;

        if ($gasto === null || !in_array($estado, ['pendiente', 'pagado', 'anulado'], true)) {
            $this->guardarMensaje('error', 'Solicitud no válida.');
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarAccesoATienda((int) $gasto['tienda_id']);

        $this->gastoModel->cambiarEstado($id, $estado);

        $this->guardarMensaje('success', 'Estado del gasto actualizado a "' . $estado . '".');
        $this->redireccionar('index.php?route=gastos.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarCsrfToken();

        $id    = (int) ($_POST['id'] ?? 0);
        $gasto = $id > 0 ? $this->gastoModel->buscarPorId($id) : null;

        if ($gasto === null) {
            $this->guardarMensaje('error', 'El gasto no existe.');
            $this->redireccionar('index.php?route=gastos.index');
        }

        $this->validarAccesoATienda((int) $gasto['tienda_id']);

        $this->gastoModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Gasto eliminado correctamente.');
        $this->redireccionar('index.php?route=gastos.index');
    }

    // -------------------------------------------------------------------------

    /**
     * Verifica que la cuenta contable y el centro de costo (si se enviaron)
     * pertenezcan a la tienda del gasto. Evita cruces entre tiendas.
     */
    private function referenciasPertenecenATienda(array $datos, int $tiendaId): bool
    {
        if (!empty($datos['cuenta_id'])) {
            $cuenta = $this->cuentaModel->buscarPorId((int) $datos['cuenta_id']);

            if ($cuenta === null || (int) $cuenta['tienda_id'] !== $tiendaId) {
                $this->guardarMensaje('error', 'La cuenta contable seleccionada no pertenece a esta tienda.');
                return false;
            }
        }

        if (!empty($datos['centro_costo_id'])) {
            $centro = $this->centroModel->buscarPorId((int) $datos['centro_costo_id']);

            if ($centro === null || (int) $centro['tienda_id'] !== $tiendaId) {
                $this->guardarMensaje('error', 'El centro de costo seleccionado no pertenece a esta tienda.');
                return false;
            }
        }

        return true;
    }

    private function validarDatos(array $input): ?array
    {
        $concepto = trim((string) ($input['concepto'] ?? ''));
        $monto    = (float) ($input['monto'] ?? 0);
        $fecha    = trim((string) ($input['fecha'] ?? ''));

        if ($concepto === '') {
            $this->guardarMensaje('error', 'El concepto del gasto es obligatorio.');
            return null;
        }

        if ($monto <= 0) {
            $this->guardarMensaje('error', 'El monto debe ser mayor que cero.');
            return null;
        }

        if ($fecha === '') {
            $fecha = date('Y-m-d');
        }

        $cuentaId    = (int) ($input['cuenta_id'] ?? 0);
        $centroId    = (int) ($input['centro_costo_id'] ?? 0);
        $proveedorId = (int) ($input['proveedor_id'] ?? 0);

        return [
            'concepto'        => $concepto,
            'monto'           => number_format($monto, 2, '.', ''),
            'fecha'           => $fecha,
            'cuenta_id'       => $cuentaId > 0 ? $cuentaId : null,
            'centro_costo_id' => $centroId > 0 ? $centroId : null,
            'proveedor_id'    => $proveedorId > 0 ? $proveedorId : null,
            'comprobante'     => trim((string) ($input['comprobante'] ?? '')) ?: null,
        ];
    }
}
