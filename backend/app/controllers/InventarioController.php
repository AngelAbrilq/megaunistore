<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class InventarioController
{
    use ControllerHelper;
    private Inventario $inventarioModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->inventarioModel = new Inventario();
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $inventarios = $this->inventarioModel->listar($tiendaIdPermitida);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/inventario/index.php';
    }

    public function alertas(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $alertas = $this->inventarioModel->listarAlertas($tiendaIdPermitida);

        require __DIR__ . '/../../resources/views/inventario/alertas.php';
    }

    public function create(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        $productos = $this->inventarioModel->productosAsociadosATiendas($tiendaIdPermitida);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/inventario/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=inventario.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatosInventario($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=inventario.create');
        }

        $this->validarAccesoATienda((int) $datos['tienda_id']);

        if (!$this->inventarioModel->productoPerteneceATienda((int) $datos['producto_id'], (int) $datos['tienda_id'])) {
            $this->guardarMensaje('error', 'El producto seleccionado no está asociado a la tienda.');
            $this->redireccionar('index.php?route=inventario.create');
        }

        $this->inventarioModel->crearOActualizar($datos);

        $this->guardarMensaje('success', 'Inventario registrado correctamente.');
        $this->redireccionar('index.php?route=inventario.index');
    }

    public function movimiento(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Inventario no válido.');
            $this->redireccionar('index.php?route=inventario.index');
        }

        $inventario = $this->inventarioModel->buscarPorId($id);

        if ($inventario === null) {
            $this->guardarMensaje('error', 'El inventario no existe.');
            $this->redireccionar('index.php?route=inventario.index');
        }

        $this->validarAccesoATienda((int) $inventario['tienda_id']);

        $movimientos = $this->inventarioModel->listarMovimientos($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/inventario/movimiento.php';
    }

    public function guardarMovimiento(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=inventario.index');
        }

        $this->validarCsrfToken();

        $inventarioId = (int) ($_POST['inventario_id'] ?? 0);
        $tipo = trim((string) ($_POST['tipo'] ?? ''));
        $cantidadRaw = trim((string) ($_POST['cantidad'] ?? ''));
        $motivo = trim((string) ($_POST['motivo'] ?? ''));

        if ($inventarioId <= 0) {
            $this->guardarMensaje('error', 'Inventario no válido.');
            $this->redireccionar('index.php?route=inventario.index');
        }

        $inventario = $this->inventarioModel->buscarPorId($inventarioId);

        if ($inventario === null) {
            $this->guardarMensaje('error', 'El inventario no existe.');
            $this->redireccionar('index.php?route=inventario.index');
        }

        $this->validarAccesoATienda((int) $inventario['tienda_id']);

        if (!in_array($tipo, ['entrada', 'salida', 'ajuste'], true)) {
            $this->guardarMensaje('error', 'Tipo de movimiento inválido.');
            $this->redireccionar('index.php?route=inventario.movimiento&id=' . $inventarioId);
        }

        if ($cantidadRaw === '' || !is_numeric($cantidadRaw)) {
            $this->guardarMensaje('error', 'La cantidad debe ser numérica.');
            $this->redireccionar('index.php?route=inventario.movimiento&id=' . $inventarioId);
        }

        $cantidad = (float) $cantidadRaw;

        if ($cantidad < 0) {
            $this->guardarMensaje('error', 'La cantidad no puede ser negativa.');
            $this->redireccionar('index.php?route=inventario.movimiento&id=' . $inventarioId);
        }

        try {
            $this->inventarioModel->registrarMovimiento(
                $inventarioId,
                $tipo,
                $cantidad,
                $motivo !== '' ? $motivo : null,
                null,
                null,
                null
            );

            $this->guardarMensaje('success', 'Movimiento registrado correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
        }

        $this->redireccionar('index.php?route=inventario.movimiento&id=' . $inventarioId);
    }

    public function movimientos(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $movimientos = $this->inventarioModel->listarMovimientos(null, $tiendaIdPermitida);

        require __DIR__ . '/../../resources/views/inventario/movimientos.php';
    }

    private function validarDatosInventario(array $input): ?array
    {
        $tiendaId = (int) ($input['tienda_id'] ?? 0);
        $productoId = (int) ($input['producto_id'] ?? 0);

        $cantidadRaw = trim((string) ($input['cantidad'] ?? '0'));
        $cantidadMinimaRaw = trim((string) ($input['cantidad_minima'] ?? '0'));
        $cantidadMaximaRaw = trim((string) ($input['cantidad_maxima'] ?? ''));
        $ubicacion = trim((string) ($input['ubicacion'] ?? ''));

        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            return null;
        }

        if ($productoId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar un producto.');
            return null;
        }

        if ($cantidadRaw === '' || !is_numeric($cantidadRaw)) {
            $this->guardarMensaje('error', 'La cantidad debe ser numérica.');
            return null;
        }

        if ($cantidadMinimaRaw === '' || !is_numeric($cantidadMinimaRaw)) {
            $this->guardarMensaje('error', 'La cantidad mínima debe ser numérica.');
            return null;
        }

        $cantidad = (float) $cantidadRaw;
        $cantidadMinima = (float) $cantidadMinimaRaw;
        $cantidadMaxima = null;

        if ($cantidad < 0 || $cantidadMinima < 0) {
            $this->guardarMensaje('error', 'Las cantidades no pueden ser negativas.');
            return null;
        }

        if ($cantidadMaximaRaw !== '') {
            if (!is_numeric($cantidadMaximaRaw)) {
                $this->guardarMensaje('error', 'La cantidad máxima debe ser numérica.');
                return null;
            }

            $cantidadMaxima = (float) $cantidadMaximaRaw;

            if ($cantidadMaxima < 0) {
                $this->guardarMensaje('error', 'La cantidad máxima no puede ser negativa.');
                return null;
            }

            if ($cantidadMaxima < $cantidadMinima) {
                $this->guardarMensaje('error', 'La cantidad máxima no puede ser menor que la cantidad mínima.');
                return null;
            }
        }

        return [
            'tienda_id' => $tiendaId,
            'producto_id' => $productoId,
            'cantidad' => number_format($cantidad, 2, '.', ''),
            'cantidad_minima' => number_format($cantidadMinima, 2, '.', ''),
            'cantidad_maxima' => $cantidadMaxima !== null ? number_format($cantidadMaxima, 2, '.', '') : null,
            'ubicacion' => $ubicacion !== '' ? $ubicacion : null,
        ];
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
        $errorMensaje = 'No tienes permisos para gestionar inventario de esta tienda.';

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

    private function redireccionar(string $ruta): void
    {
        header('Location: ' . $ruta);
        exit;
    }
}