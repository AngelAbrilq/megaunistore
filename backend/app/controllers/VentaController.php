<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../models/MetodoPago.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class VentaController
{
    use ControllerHelper;
    private Venta $ventaModel;
    private Tienda $tiendaModel;
    private MetodoPago $metodoPagoModel;

    public function __construct()
    {
        $this->ventaModel = new Venta();
        $this->tiendaModel = new Tienda();
        $this->metodoPagoModel = new MetodoPago();
    }

    public function index(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $ventas = $this->ventaModel->listar($tiendaIdPermitida);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/ventas/index.php';
    }

    public function create(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        $metodosPago = $this->metodoPagoModel->listarActivos();

        $productosPorTienda = [];

        foreach ($tiendas as $tienda) {
            if ($tienda === null) {
                continue;
            }

            $productosPorTienda[(int) $tienda['id']] = $this->ventaModel->productosVendiblesPorTienda((int) $tienda['id']);
        }

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/ventas/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarCsrfToken();

        $tiendaId = (int) ($_POST['tienda_id'] ?? 0);
        $metodoPagoId = (int) ($_POST['metodo_pago_id'] ?? 0);
        $referencia = trim((string) ($_POST['referencia'] ?? ''));

        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=ventas.create');
        }

        $this->validarAccesoATienda($tiendaId);

        $metodoPago = $this->metodoPagoModel->buscarPorId($metodoPagoId);

        if ($metodoPago === null || (int) $metodoPago['activo'] !== 1) {
            $this->guardarMensaje('error', 'Debes seleccionar un método de pago activo.');
            $this->redireccionar('index.php?route=ventas.create');
        }

        $items = $this->validarItemsVenta($_POST);

        if ($items === null) {
            $this->redireccionar('index.php?route=ventas.create');
        }

        try {
            $ventaId = $this->ventaModel->crearVenta(
                [
                    'tienda_id' => $tiendaId,
                    'cliente_id' => null,
                    'created_by' => $this->usuarioIdActual(),
                    'updated_by' => $this->usuarioIdActual(),
                ],
                $items,
                [
                    'metodo_pago_id' => $metodoPagoId,
                    'referencia' => $referencia !== '' ? $referencia : null,
                ]
            );

            $this->guardarMensaje('success', 'Venta registrada correctamente. ID: ' . $ventaId);
            $this->redireccionar('index.php?route=ventas.show&id=' . $ventaId);
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
            $this->redireccionar('index.php?route=ventas.create');
        }
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Venta no válida.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $venta = $this->ventaModel->buscarPorId($id);

        if ($venta === null) {
            $this->guardarMensaje('error', 'La venta no existe o fue eliminada.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarAccesoATienda((int) $venta['tienda_id']);

        $detalle = $this->ventaModel->obtenerDetalle($id);
        $pagos = $this->ventaModel->obtenerPagos($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/ventas/show.php';
    }

    public function anular(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Venta no válida.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $venta = $this->ventaModel->buscarPorId($id);

        if ($venta === null) {
            $this->guardarMensaje('error', 'La venta no existe o fue eliminada.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarAccesoATienda((int) $venta['tienda_id']);

        try {
            $this->ventaModel->anularVenta($id, $this->usuarioIdActual());
            $this->guardarMensaje('success', 'Venta anulada correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
        }

        $this->redireccionar('index.php?route=ventas.show&id=' . $id);
    }

    private function validarItemsVenta(array $input): ?array
    {
        $productos = $input['producto_id'] ?? [];
        $cantidades = $input['cantidad'] ?? [];

        if (!is_array($productos) || !is_array($cantidades) || empty($productos)) {
            $this->guardarMensaje('error', 'Debes agregar al menos un producto a la venta.');
            return null;
        }

        $items = [];

        foreach ($productos as $index => $productoIdRaw) {
            $productoId = (int) $productoIdRaw;
            $cantidadRaw = trim((string) ($cantidades[$index] ?? ''));

            if ($productoId <= 0 && $cantidadRaw === '') {
                continue;
            }

            if ($productoId <= 0) {
                $this->guardarMensaje('error', 'Hay un producto inválido en la venta.');
                return null;
            }

            if ($cantidadRaw === '' || !is_numeric($cantidadRaw)) {
                $this->guardarMensaje('error', 'La cantidad debe ser numérica.');
                return null;
            }

            $cantidad = (float) $cantidadRaw;

            if ($cantidad <= 0) {
                $this->guardarMensaje('error', 'La cantidad debe ser mayor que cero.');
                return null;
            }

            $items[] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
            ];
        }

        if (empty($items)) {
            $this->guardarMensaje('error', 'Debes agregar al menos un producto válido.');
            return null;
        }

        return $items;
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
        $errorMensaje = 'No tienes permisos para gestionar ventas de esta tienda.';

        require __DIR__ . '/../../resources/errors/403.php';
        exit;
    }

    private function usuarioIdActual(): int
    {
        return (int) ($_SESSION['auth']['usuario_id'] ?? 0);
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