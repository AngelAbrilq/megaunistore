<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Caja.php';
require_once __DIR__ . '/../models/Tienda.php';

final class CajaController
{
    private Caja $cajaModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->cajaModel = new Caja();
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $cajas = $this->cajaModel->listar($tiendaIdPermitida);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/caja/index.php';
    }

    public function create(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/caja/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=caja.index');
        }

        $this->validarCsrfToken();

        $esModal     = $this->isModalRequest();
        $tiendaId    = (int) ($_POST['tienda_id'] ?? 0);
        $nombre      = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        if ($tiendaId <= 0) {
            if ($esModal) { $this->jsonError('Debes seleccionar una tienda.'); }
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=caja.create');
        }

        $this->validarAccesoATienda($tiendaId);

        if ($nombre === '') {
            if ($esModal) { $this->jsonError('El nombre de la caja es obligatorio.'); }
            $this->guardarMensaje('error', 'El nombre de la caja es obligatorio.');
            $this->redireccionar('index.php?route=caja.create');
        }

        if (strlen($nombre) > 100) {
            if ($esModal) { $this->jsonError('El nombre no puede superar 100 caracteres.'); }
            $this->guardarMensaje('error', 'El nombre de la caja no puede superar 100 caracteres.');
            $this->redireccionar('index.php?route=caja.create');
        }

        $this->cajaModel->crear([
            'tienda_id'   => $tiendaId,
            'nombre'      => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'estado'      => 1,
        ]);

        if ($esModal) { $this->jsonSuccess('Caja creada correctamente.'); }
        $this->guardarMensaje('success', 'Caja creada correctamente.');
        $this->redireccionar('index.php?route=caja.index');
    }

    public function apertura(): void
    {
        $caja = $this->obtenerCajaDesdeGet();
        $this->validarAccesoATienda((int) $caja['tienda_id']);

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/caja/apertura.php';
    }

    public function abrir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=caja.index');
        }

        $this->validarCsrfToken();

        $esModal = $this->isModalRequest();

        $cajaId = (int) ($_POST['caja_id'] ?? 0);
        $montoInicialRaw = trim((string) ($_POST['monto_inicial'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        $caja = $this->obtenerCajaPorId($cajaId);
        $this->validarAccesoATienda((int) $caja['tienda_id']);

        if ($montoInicialRaw === '' || !is_numeric($montoInicialRaw)) {
            if ($esModal) { $this->jsonError('El monto inicial debe ser numérico.'); }
            $this->guardarMensaje('error', 'El monto inicial debe ser numérico.');
            $this->redireccionar('index.php?route=caja.apertura&id=' . $cajaId);
        }

        $montoInicial = (float) $montoInicialRaw;

        if ($montoInicial < 0) {
            if ($esModal) { $this->jsonError('El monto inicial no puede ser negativo.'); }
            $this->guardarMensaje('error', 'El monto inicial no puede ser negativo.');
            $this->redireccionar('index.php?route=caja.apertura&id=' . $cajaId);
        }

        try {
            $this->cajaModel->registrarApertura(
                $cajaId,
                $montoInicial,
                $descripcion !== '' ? $descripcion : null
            );

            if ($esModal) { $this->jsonSuccess('Caja abierta correctamente.'); }
            $this->guardarMensaje('success', 'Caja abierta correctamente.');
        } catch (Throwable $error) {
            if ($esModal) { $this->jsonError($error->getMessage()); }
            $this->guardarMensaje('error', $error->getMessage());
        }

        $this->redireccionar('index.php?route=caja.index');
    }

    public function cierre(): void
    {
        $caja = $this->obtenerCajaDesdeGet();
        $this->validarAccesoATienda((int) $caja['tienda_id']);

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/caja/cierre.php';
    }

    public function cerrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=caja.index');
        }

        $this->validarCsrfToken();

        $esModal = $this->isModalRequest();

        $cajaId = (int) ($_POST['caja_id'] ?? 0);
        $montoRealRaw = trim((string) ($_POST['monto_real'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        $caja = $this->obtenerCajaPorId($cajaId);
        $this->validarAccesoATienda((int) $caja['tienda_id']);

        if ($montoRealRaw === '' || !is_numeric($montoRealRaw)) {
            if ($esModal) { $this->jsonError('El monto real debe ser numérico.'); }
            $this->guardarMensaje('error', 'El monto real debe ser numérico.');
            $this->redireccionar('index.php?route=caja.cierre&id=' . $cajaId);
        }

        $montoReal = (float) $montoRealRaw;

        if ($montoReal < 0) {
            if ($esModal) { $this->jsonError('El monto real no puede ser negativo.'); }
            $this->guardarMensaje('error', 'El monto real no puede ser negativo.');
            $this->redireccionar('index.php?route=caja.cierre&id=' . $cajaId);
        }

        try {
            $this->cajaModel->registrarCierre(
                $cajaId,
                $montoReal,
                $descripcion !== '' ? $descripcion : null
            );

            if ($esModal) { $this->jsonSuccess('Caja cerrada correctamente.'); }
            $this->guardarMensaje('success', 'Caja cerrada correctamente.');
        } catch (Throwable $error) {
            if ($esModal) { $this->jsonError($error->getMessage()); }
            $this->guardarMensaje('error', $error->getMessage());
        }

        $this->redireccionar('index.php?route=caja.index');
    }

    public function movimiento(): void
    {
        $caja = $this->obtenerCajaDesdeGet();
        $this->validarAccesoATienda((int) $caja['tienda_id']);

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/caja/movimiento.php';
    }

    public function guardarMovimiento(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=caja.index');
        }

        $this->validarCsrfToken();

        $esModal = $this->isModalRequest();

        $cajaId = (int) ($_POST['caja_id'] ?? 0);
        $tipo = trim((string) ($_POST['tipo'] ?? ''));
        $montoRaw = trim((string) ($_POST['monto'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        $caja = $this->obtenerCajaPorId($cajaId);
        $this->validarAccesoATienda((int) $caja['tienda_id']);

        if (!in_array($tipo, ['ingreso', 'egreso'], true)) {
            if ($esModal) { $this->jsonError('Tipo de movimiento inválido.'); }
            $this->guardarMensaje('error', 'Tipo de movimiento inválido.');
            $this->redireccionar('index.php?route=caja.movimiento&id=' . $cajaId);
        }

        if ($montoRaw === '' || !is_numeric($montoRaw)) {
            if ($esModal) { $this->jsonError('El monto debe ser numérico.'); }
            $this->guardarMensaje('error', 'El monto debe ser numérico.');
            $this->redireccionar('index.php?route=caja.movimiento&id=' . $cajaId);
        }

        $monto = (float) $montoRaw;

        if ($monto <= 0) {
            if ($esModal) { $this->jsonError('El monto debe ser mayor que cero.'); }
            $this->guardarMensaje('error', 'El monto debe ser mayor que cero.');
            $this->redireccionar('index.php?route=caja.movimiento&id=' . $cajaId);
        }

        try {
            if ($tipo === 'ingreso') {
                $this->cajaModel->registrarIngresoManual(
                    $cajaId,
                    $monto,
                    $descripcion !== '' ? $descripcion : null
                );
            } else {
                $this->cajaModel->registrarEgresoManual(
                    $cajaId,
                    $monto,
                    $descripcion !== '' ? $descripcion : null
                );
            }

            if ($esModal) { $this->jsonSuccess('Movimiento de caja registrado correctamente.'); }
            $this->guardarMensaje('success', 'Movimiento de caja registrado correctamente.');
        } catch (Throwable $error) {
            if ($esModal) { $this->jsonError($error->getMessage()); }
            $this->guardarMensaje('error', $error->getMessage());
        }

        $this->redireccionar('index.php?route=caja.movimiento&id=' . $cajaId);
    }

    public function movimientos(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        // Filtros opcionales desde GET
        $filtroTienda = $tiendaIdPermitida !== null
            ? $tiendaIdPermitida
            : (($_GET['tienda_id'] ?? '') !== '' ? (int) $_GET['tienda_id'] : null);

        $filtroTipo  = trim((string) ($_GET['tipo']  ?? ''));
        $filtroDesde = trim((string) ($_GET['desde'] ?? ''));
        $filtroHasta = trim((string) ($_GET['hasta'] ?? ''));

        $movimientos = $this->cajaModel->listarMovimientos(
            null,
            $filtroTienda,
            $filtroTipo  !== '' ? $filtroTipo  : null,
            $filtroDesde !== '' ? $filtroDesde : null,
            $filtroHasta !== '' ? $filtroHasta : null
        );

        // Lista de tiendas para el selector (sólo roles globales)
        $tiendas = $tiendaIdPermitida === null ? $this->tiendaModel->listar() : [];

        require __DIR__ . '/../../resources/views/caja/movimientos.php';
    }

    private function obtenerCajaDesdeGet(): array
    {
        $id = (int) ($_GET['id'] ?? 0);

        return $this->obtenerCajaPorId($id);
    }

    private function obtenerCajaPorId(int $id): array
    {
        if ($id <= 0) {
            $this->guardarMensaje('error', 'Caja no válida.');
            $this->redireccionar('index.php?route=caja.index');
        }

        $caja = $this->cajaModel->buscarPorId($id);

        if ($caja === null) {
            $this->guardarMensaje('error', 'La caja no existe.');
            $this->redireccionar('index.php?route=caja.index');
        }

        return $caja;
    }

    private function tiendaIdPermitida(): ?int
    {
        $tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;

        return $tiendaId !== null ? (int) $tiendaId : null;
    }

    private function validarAccesoATienda(int $tiendaId): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null && $tiendaIdPermitida !== $tiendaId) {
            $this->denegarAcceso();
        }
    }

    private function denegarAcceso(): void
    {
        http_response_code(403);

        $errorTitulo = 'Acceso denegado';
        $errorMensaje = 'No tienes permisos para gestionar caja de esta tienda.';

        require __DIR__ . '/../../resources/errors/403.php';
        exit;
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

    private function isModalRequest(): bool
    {
        return ($_SERVER['HTTP_X_MODAL_REQUEST'] ?? '') === '1';
    }

    private function jsonSuccess(string $mensaje, string $ruta = 'caja.index'): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'ruta' => $ruta, 'mensaje' => $mensaje]);
        exit;
    }

    private function jsonError(string $mensaje): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => $mensaje]);
        exit;
    }

    private function redireccionar(string $ruta): void
    {
        header('Location: ' . $ruta);
        exit;
    }
}