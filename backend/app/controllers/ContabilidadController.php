<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/CuentaContable.php';
require_once __DIR__ . '/../models/AsientoContable.php';
require_once __DIR__ . '/../models/PeriodoContable.php';
require_once __DIR__ . '/../models/CentroCosto.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

/**
 * Controlador de Contabilidad y Finanzas.
 * Cubre: CF-CON-001 (plan de cuentas), CF-CON-002 (asientos manuales),
 * CF-CON-004 (períodos), CF-CON-007 (centros de costo),
 * CF-CON-009/010 (balance y estado de resultados), CF-CON-011 (libro mayor)
 * y REQ-7.8.1/7.8.3.
 */
final class ContabilidadController
{
    use ControllerHelper;

    private CuentaContable $cuentaModel;
    private AsientoContable $asientoModel;
    private PeriodoContable $periodoModel;
    private CentroCosto $centroModel;
    private Tienda $tiendaModel;
    private Empleado $empleadoModel;

    public function __construct()
    {
        $this->cuentaModel   = new CuentaContable();
        $this->asientoModel  = new AsientoContable();
        $this->periodoModel  = new PeriodoContable();
        $this->centroModel   = new CentroCosto();
        $this->tiendaModel   = new Tienda();
        $this->empleadoModel = new Empleado();
    }

    // =========================================================================
    // Helper: resuelve la tienda de trabajo.
    // Roles con tienda asignada → su tienda. Superadmin → selector (?tienda_id=)
    // con la primera tienda activa como valor por defecto.
    // =========================================================================

    /** @return array{0:int, 1:array} [tiendaId seleccionada, tiendas para el selector] */
    private function tiendaDeTrabajo(): array
    {
        $tiendaId = $this->tiendaIdPermitida();

        if ($tiendaId !== null) {
            return [$tiendaId, []];
        }

        $tiendas  = $this->tiendaModel->listar();
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        if ($tiendaId <= 0 && $tiendas !== []) {
            $tiendaId = (int) $tiendas[0]['id'];
        }

        return [$tiendaId, $tiendas];
    }

    // =========================================================================
    // PLAN DE CUENTAS (CF-CON-001)
    // =========================================================================

    public function cuentas(): void
    {
        [$tiendaSel, $tiendas] = $this->tiendaDeTrabajo();

        if ($tiendaSel <= 0) {
            $this->guardarMensaje('error', 'Primero debes crear al menos una tienda.');
            $this->redireccionar('index.php?route=dashboard');
        }

        // Asegurar PUC base de esta tienda en el primer uso
        $this->cuentaModel->asegurarPlanBase($tiendaSel);

        $cuentas   = $this->cuentaModel->listar($tiendaSel);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/cuentas.php';
    }

    public function cuentaStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.cuentas');
        }

        $this->validarCsrfToken();

        $codigo = trim((string) ($_POST['codigo'] ?? ''));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $tipo   = (string) ($_POST['tipo'] ?? '');

        $tiposValidos = ['activo', 'pasivo', 'patrimonio', 'ingreso', 'egreso', 'costo'];

        if ($codigo === '' || $nombre === '' || !in_array($tipo, $tiposValidos, true)) {
            $this->guardarMensaje('error', 'Código, nombre y tipo de cuenta son obligatorios.');
            $this->redireccionar('index.php?route=contabilidad.cuentas');
        }

        // tienda_id es NOT NULL: el Superadmin la envía desde el formulario
        $tiendaId = $this->tiendaIdPermitida() ?? (int) ($_POST['tienda_id'] ?? 0);

        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=contabilidad.cuentas');
        }

        $this->validarAccesoATienda($tiendaId);

        if ($this->cuentaModel->existeCodigo($codigo, $tiendaId)) {
            $this->guardarMensaje('error', 'Ya existe una cuenta con el código ' . $codigo . ' en esta tienda.');
            $this->redireccionar('index.php?route=contabilidad.cuentas&tienda_id=' . $tiendaId);
        }

        $naturaleza = in_array($tipo, ['activo', 'egreso', 'costo'], true) ? 'debito' : 'credito';

        $this->cuentaModel->crear([
            'tienda_id'       => $tiendaId,
            'codigo'          => $codigo,
            'nombre'          => $nombre,
            'tipo'            => $tipo,
            'naturaleza'      => (string) ($_POST['naturaleza'] ?? '') ?: $naturaleza,
            'cuenta_padre_id' => (int) ($_POST['cuenta_padre_id'] ?? 0) ?: null,
            'nivel'           => max(1, min(9, strlen($codigo) > 4 ? 3 : (strlen($codigo) > 2 ? 2 : 1))),
            'activo'          => 1,
        ]);

        $this->guardarMensaje('success', 'Cuenta contable creada correctamente.');
        $this->redireccionar('index.php?route=contabilidad.cuentas&tienda_id=' . $tiendaId);
    }

    public function cuentaToggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.cuentas');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $cuenta = $id > 0 ? $this->cuentaModel->buscarPorId($id) : null;

        if ($cuenta === null) {
            $this->guardarMensaje('error', 'La cuenta no existe.');
            $this->redireccionar('index.php?route=contabilidad.cuentas');
        }

        // La cuenta debe pertenecer a una tienda accesible para el rol actual
        $this->validarAccesoATienda((int) $cuenta['tienda_id']);

        $nuevoEstado = (int) $cuenta['activo'] === 1 ? 0 : 1;
        $this->cuentaModel->cambiarEstado($id, $nuevoEstado);

        $this->guardarMensaje('success', $nuevoEstado === 1 ? 'Cuenta activada.' : 'Cuenta desactivada.');
        $this->redireccionar('index.php?route=contabilidad.cuentas&tienda_id=' . (int) $cuenta['tienda_id']);
    }

    // =========================================================================
    // ASIENTOS CONTABLES (CF-CON-002, CF-CON-012)
    // =========================================================================

    public function asientos(): void
    {
        $tiendaId  = $this->tiendaIdPermitida();
        $asientos  = $this->asientoModel->listar($tiendaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/asientos.php';
    }

    public function asientoCreate(): void
    {
        [$tiendaSel, $tiendas] = $this->tiendaDeTrabajo();

        if ($tiendaSel <= 0) {
            $this->guardarMensaje('error', 'Primero debes crear al menos una tienda.');
            $this->redireccionar('index.php?route=dashboard');
        }

        // Garantizar que la tienda tenga su PUC base antes de armar el asiento
        $this->cuentaModel->asegurarPlanBase($tiendaSel);

        $cuentas   = $this->cuentaModel->listar($tiendaSel, true);
        $centros   = $this->centroModel->listar($tiendaSel);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/asiento_create.php';
    }

    public function asientoStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.asientos');
        }

        $this->validarCsrfToken();

        $tiendaId = $this->tiendaIdPermitida() ?? (int) ($_POST['tienda_id'] ?? 0);
        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=contabilidad.asiento.create');
        }

        $this->validarAccesoATienda($tiendaId);

        $fecha    = trim((string) ($_POST['fecha'] ?? date('Y-m-d')));
        $concepto = trim((string) ($_POST['concepto'] ?? ''));

        if ($concepto === '') {
            $this->guardarMensaje('error', 'El concepto del asiento es obligatorio.');
            $this->redireccionar('index.php?route=contabilidad.asiento.create');
        }

        // El asiento debe caer dentro de un período abierto (CF-CON-004)
        $periodo = $this->periodoModel->abiertoParaFecha($fecha, $tiendaId);
        if ($periodo === null) {
            $this->guardarMensaje('error', 'No hay un período contable abierto para la fecha ' . $fecha . '. Crea o abre el período primero.');
            $this->redireccionar('index.php?route=contabilidad.periodos&tienda_id=' . $tiendaId);
        }

        $cuentasIds    = (array) ($_POST['cuenta_id'] ?? []);
        $descripciones = (array) ($_POST['descripcion'] ?? []);
        $debitos       = (array) ($_POST['debito'] ?? []);
        $creditos      = (array) ($_POST['credito'] ?? []);
        $centros       = (array) ($_POST['centro_costo_id'] ?? []);

        $lineas = [];

        foreach ($cuentasIds as $i => $cuentaId) {
            $cuentaId = (int) $cuentaId;
            $debito   = (float) ($debitos[$i] ?? 0);
            $credito  = (float) ($creditos[$i] ?? 0);

            if ($cuentaId > 0 && ($debito > 0 || $credito > 0)) {
                $lineas[] = [
                    'cuenta_id'       => $cuentaId,
                    'descripcion'     => trim((string) ($descripciones[$i] ?? '')) ?: null,
                    'debito'          => $debito,
                    'credito'         => $credito,
                    'centro_costo_id' => (int) ($centros[$i] ?? 0) ?: null,
                ];
            }
        }

        if (count($lineas) < 2) {
            $this->guardarMensaje('error', 'Un asiento necesita al menos dos líneas (débito y crédito).');
            $this->redireccionar('index.php?route=contabilidad.asiento.create');
        }

        // Coherencia: cada cuenta y centro de costo deben pertenecer a la tienda del asiento
        foreach ($lineas as $linea) {
            $cuenta = $this->cuentaModel->buscarPorId((int) $linea['cuenta_id']);

            if ($cuenta === null || (int) $cuenta['tienda_id'] !== $tiendaId) {
                $this->guardarMensaje('error', 'Una de las cuentas seleccionadas no pertenece a esta tienda.');
                $this->redireccionar('index.php?route=contabilidad.asiento.create&tienda_id=' . $tiendaId);
            }

            if (!empty($linea['centro_costo_id'])) {
                $centro = $this->centroModel->buscarPorId((int) $linea['centro_costo_id']);

                if ($centro === null || (int) $centro['tienda_id'] !== $tiendaId) {
                    $this->guardarMensaje('error', 'Uno de los centros de costo no pertenece a esta tienda.');
                    $this->redireccionar('index.php?route=contabilidad.asiento.create&tienda_id=' . $tiendaId);
                }
            }
        }

        $empleado = $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual(), $tiendaId)
            ?? $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual());

        try {
            $asientoId = $this->asientoModel->crear([
                'tienda_id'   => $tiendaId,
                'periodo_id'  => (int) $periodo['id'],
                'fecha'       => $fecha,
                'concepto'    => $concepto,
                'tipo_origen' => 'manual',
                'origen_id'   => null,
                'empleado_id' => $empleado !== null ? (int) $empleado['id'] : null,
                'estado'      => 'borrador',
            ], $lineas);
        } catch (RuntimeException $error) {
            $this->guardarMensaje('error', $error->getMessage());
            $this->redireccionar('index.php?route=contabilidad.asiento.create');
        }

        $this->jsonExito('contabilidad.asiento.show&id=' . $asientoId, 'Asiento contable registrado en borrador.');
    }

    public function asientoShow(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $asiento = $id > 0 ? $this->asientoModel->buscarPorId($id) : null;

        if ($asiento === null) {
            $this->guardarMensaje('error', 'El asiento no existe.');
            $this->redireccionar('index.php?route=contabilidad.asientos');
        }

        $this->validarAccesoATienda((int) $asiento['tienda_id']);

        $detalle   = $this->asientoModel->obtenerDetalle($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/asiento_show.php';
    }

    public function asientoCambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.asientos');
        }

        $this->validarCsrfToken();

        $id      = (int) ($_POST['id'] ?? 0);
        $estado  = (string) ($_POST['estado'] ?? '');
        $asiento = $id > 0 ? $this->asientoModel->buscarPorId($id) : null;

        if ($asiento === null || !in_array($estado, ['aprobado', 'anulado'], true)) {
            $this->guardarMensaje('error', 'Solicitud no válida.');
            $this->redireccionar('index.php?route=contabilidad.asientos');
        }

        $this->validarAccesoATienda((int) $asiento['tienda_id']);

        if ($asiento['estado'] !== 'borrador' && $estado === 'aprobado') {
            $this->guardarMensaje('error', 'Solo se pueden aprobar asientos en borrador.');
            $this->redireccionar('index.php?route=contabilidad.asiento.show&id=' . $id);
        }

        $this->asientoModel->cambiarEstado($id, $estado);

        $this->guardarMensaje('success', 'Asiento ' . ($estado === 'aprobado' ? 'aprobado' : 'anulado') . ' correctamente.');
        $this->redireccionar('index.php?route=contabilidad.asiento.show&id=' . $id);
    }

    // =========================================================================
    // PERÍODOS CONTABLES (CF-CON-004)
    // =========================================================================

    public function periodos(): void
    {
        [$tiendaSel, $tiendas] = $this->tiendaDeTrabajo();

        if ($tiendaSel <= 0) {
            $this->guardarMensaje('error', 'Primero debes crear al menos una tienda.');
            $this->redireccionar('index.php?route=dashboard');
        }

        $periodos  = $this->periodoModel->listar($tiendaSel);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/periodos.php';
    }

    public function periodoStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        $this->validarCsrfToken();

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $inicio = trim((string) ($_POST['fecha_inicio'] ?? ''));
        $fin    = trim((string) ($_POST['fecha_fin'] ?? ''));

        if ($nombre === '' || $inicio === '' || $fin === '' || $inicio > $fin) {
            $this->guardarMensaje('error', 'Nombre y rango de fechas válido son obligatorios.');
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        // tienda_id es NOT NULL: el Superadmin la envía desde el formulario
        $tiendaId = $this->tiendaIdPermitida() ?? (int) ($_POST['tienda_id'] ?? 0);

        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        $this->validarAccesoATienda($tiendaId);

        $this->periodoModel->crear([
            'tienda_id'    => $tiendaId,
            'nombre'       => $nombre,
            'fecha_inicio' => $inicio,
            'fecha_fin'    => $fin,
        ]);

        $this->guardarMensaje('success', 'Período contable "' . $nombre . '" abierto.');
        $this->redireccionar('index.php?route=contabilidad.periodos&tienda_id=' . $tiendaId);
    }

    public function periodoCerrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        $this->validarCsrfToken();

        $id      = (int) ($_POST['id'] ?? 0);
        $periodo = $id > 0 ? $this->periodoModel->buscarPorId($id) : null;

        if ($periodo === null) {
            $this->guardarMensaje('error', 'El período no existe.');
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        // El período debe pertenecer a una tienda accesible para el rol actual
        $this->validarAccesoATienda((int) $periodo['tienda_id']);

        $this->periodoModel->cerrar($id, $this->usuarioIdActual());

        $this->guardarMensaje('success', 'Período cerrado. Ya no acepta nuevos asientos.');
        $this->redireccionar('index.php?route=contabilidad.periodos&tienda_id=' . (int) $periodo['tienda_id']);
    }

    public function periodoReabrir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        $this->validarCsrfToken();

        $id      = (int) ($_POST['id'] ?? 0);
        $periodo = $id > 0 ? $this->periodoModel->buscarPorId($id) : null;

        if ($periodo === null) {
            $this->guardarMensaje('error', 'El período no existe.');
            $this->redireccionar('index.php?route=contabilidad.periodos');
        }

        // El período debe pertenecer a una tienda accesible para el rol actual
        $this->validarAccesoATienda((int) $periodo['tienda_id']);

        $this->periodoModel->reabrir($id);

        $this->guardarMensaje('success', 'Período reabierto.');
        $this->redireccionar('index.php?route=contabilidad.periodos&tienda_id=' . (int) $periodo['tienda_id']);
    }

    // =========================================================================
    // CENTROS DE COSTO (CF-CON-007)
    // =========================================================================

    public function centros(): void
    {
        $tiendaId  = $this->tiendaIdPermitida();
        $centros   = $this->centroModel->listar($tiendaId);
        $tiendas   = $tiendaId === null ? $this->tiendaModel->listar() : [];
        $empleados = $this->empleadoModel->listarParaSelect($tiendaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/centros.php';
    }

    public function centroStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.centros');
        }

        $this->validarCsrfToken();

        $tiendaId = $this->tiendaIdPermitida() ?? (int) ($_POST['tienda_id'] ?? 0);
        $codigo   = trim((string) ($_POST['codigo'] ?? ''));
        $nombre   = trim((string) ($_POST['nombre'] ?? ''));

        if ($tiendaId <= 0 || $codigo === '' || $nombre === '') {
            $this->guardarMensaje('error', 'Tienda, código y nombre son obligatorios.');
            $this->redireccionar('index.php?route=contabilidad.centros');
        }

        $this->validarAccesoATienda($tiendaId);

        $this->centroModel->crear([
            'tienda_id'      => $tiendaId,
            'codigo'         => $codigo,
            'nombre'         => $nombre,
            'responsable_id' => (int) ($_POST['responsable_id'] ?? 0) ?: null,
            'activo'         => 1,
        ]);

        $this->guardarMensaje('success', 'Centro de costo creado correctamente.');
        $this->redireccionar('index.php?route=contabilidad.centros');
    }

    public function centroToggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=contabilidad.centros');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $centro = $id > 0 ? $this->centroModel->buscarPorId($id) : null;

        if ($centro === null) {
            $this->guardarMensaje('error', 'El centro de costo no existe.');
            $this->redireccionar('index.php?route=contabilidad.centros');
        }

        $this->validarAccesoATienda((int) $centro['tienda_id']);

        $nuevoEstado = (int) $centro['activo'] === 1 ? 0 : 1;
        $this->centroModel->cambiarEstado($id, $nuevoEstado);

        $this->guardarMensaje('success', $nuevoEstado === 1 ? 'Centro activado.' : 'Centro desactivado.');
        $this->redireccionar('index.php?route=contabilidad.centros');
    }

    // =========================================================================
    // REPORTES FINANCIEROS (CF-CON-009, CF-CON-010, CF-CON-011)
    // =========================================================================

    public function libroMayor(): void
    {
        $tiendaId = $this->tiendaIdPermitida();
        $cuentas  = $this->cuentaModel->listar($tiendaId, true);

        $cuentaId = (int) ($_GET['cuenta_id'] ?? 0);
        $desde    = trim((string) ($_GET['desde'] ?? date('Y-m-01')));
        $hasta    = trim((string) ($_GET['hasta'] ?? date('Y-m-d')));

        $movimientos = [];
        $cuenta      = null;

        if ($cuentaId > 0) {
            $cuenta      = $this->cuentaModel->buscarPorId($cuentaId);
            $movimientos = $this->asientoModel->libroMayor($cuentaId, $desde, $hasta, $tiendaId);
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/libro_mayor.php';
    }

    public function balance(): void
    {
        $tiendaId = $this->tiendaIdPermitida();
        $desde    = trim((string) ($_GET['desde'] ?? date('Y-01-01')));
        $hasta    = trim((string) ($_GET['hasta'] ?? date('Y-m-d')));

        $saldos = $this->asientoModel->saldosPorCuenta($desde, $hasta, $tiendaId);

        // Balance General: activo, pasivo, patrimonio (CF-CON-009)
        $grupos = ['activo' => [], 'pasivo' => [], 'patrimonio' => []];
        $totales = ['activo' => 0.0, 'pasivo' => 0.0, 'patrimonio' => 0.0];

        foreach ($saldos as $fila) {
            $tipo = $fila['tipo'];
            if (!isset($grupos[$tipo])) {
                continue;
            }

            $saldo = $fila['naturaleza'] === 'debito'
                ? (float) $fila['total_debito'] - (float) $fila['total_credito']
                : (float) $fila['total_credito'] - (float) $fila['total_debito'];

            $fila['saldo']   = $saldo;
            $grupos[$tipo][] = $fila;
            $totales[$tipo] += $saldo;
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/balance.php';
    }

    public function estadoResultados(): void
    {
        $tiendaId = $this->tiendaIdPermitida();
        $desde    = trim((string) ($_GET['desde'] ?? date('Y-m-01')));
        $hasta    = trim((string) ($_GET['hasta'] ?? date('Y-m-d')));

        $saldos = $this->asientoModel->saldosPorCuenta($desde, $hasta, $tiendaId);

        // Estado de Resultados: ingresos - costos - gastos (CF-CON-010)
        $grupos  = ['ingreso' => [], 'costo' => [], 'egreso' => []];
        $totales = ['ingreso' => 0.0, 'costo' => 0.0, 'egreso' => 0.0];

        foreach ($saldos as $fila) {
            $tipo = $fila['tipo'];
            if (!isset($grupos[$tipo])) {
                continue;
            }

            $saldo = $fila['naturaleza'] === 'debito'
                ? (float) $fila['total_debito'] - (float) $fila['total_credito']
                : (float) $fila['total_credito'] - (float) $fila['total_debito'];

            $fila['saldo']   = $saldo;
            $grupos[$tipo][] = $fila;
            $totales[$tipo] += $saldo;
        }

        $utilidadBruta = $totales['ingreso'] - $totales['costo'];
        $utilidadNeta  = $utilidadBruta - $totales['egreso'];

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/contabilidad/resultados.php';
    }
}
