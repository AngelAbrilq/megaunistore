<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Devolucion.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class DevolucionController
{
    use ControllerHelper;
    private Devolucion $devolucionModel;
    private Venta $ventaModel;

    public function __construct()
    {
        $this->devolucionModel = new Devolucion();
        $this->ventaModel = new Venta();
    }

    public function index(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $devoluciones = $this->devolucionModel->listar($tiendaIdPermitida);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/devoluciones/index.php';
    }

    public function create(): void
    {
        $ventaId = (int) ($_GET['venta_id'] ?? 0);

        if ($ventaId <= 0) {
            $this->guardarMensaje('error', 'Venta no válida.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $venta = $this->ventaModel->buscarPorId($ventaId);

        if ($venta === null) {
            $this->guardarMensaje('error', 'La venta no existe o fue eliminada.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarAccesoATienda((int) $venta['tienda_id']);

        if ($venta['estado'] === 'anulada') {
            $this->guardarMensaje('error', 'No se puede hacer devolución de una venta anulada.');
            $this->redireccionar('index.php?route=ventas.show&id=' . $ventaId);
        }

        $detalle = $this->ventaModel->obtenerDetalle($ventaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/devoluciones/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarCsrfToken();

        $ventaId = (int) ($_POST['venta_id'] ?? 0);
        $motivo = trim((string) ($_POST['motivo'] ?? ''));

        if ($ventaId <= 0) {
            $this->guardarMensaje('error', 'Venta no válida.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $venta = $this->ventaModel->buscarPorId($ventaId);

        if ($venta === null) {
            $this->guardarMensaje('error', 'La venta no existe o fue eliminada.');
            $this->redireccionar('index.php?route=ventas.index');
        }

        $this->validarAccesoATienda((int) $venta['tienda_id']);

        if ($motivo === '') {
            $this->guardarMensaje('error', 'Debes especificar el motivo de la devolución.');
            $this->redireccionar('index.php?route=devoluciones.create&venta_id=' . $ventaId);
        }

        $items = $this->validarItemsDevolucion($_POST);

        if ($items === null) {
            $this->redireccionar('index.php?route=devoluciones.create&venta_id=' . $ventaId);
        }

        try {
            $devolucionId = $this->devolucionModel->crearDevolucion(
                $ventaId,
                $items,
                $motivo,
                $this->usuarioIdActual()
            );

            $this->guardarMensaje('success', 'Devolución procesada correctamente. ID: ' . $devolucionId);
            $this->redireccionar('index.php?route=devoluciones.show&id=' . $devolucionId);
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
            $this->redireccionar('index.php?route=devoluciones.create&venta_id=' . $ventaId);
        }
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Devolución no válida.');
            $this->redireccionar('index.php?route=devoluciones.index');
        }

        $devolucion = $this->devolucionModel->buscarPorId($id);

        if ($devolucion === null) {
            $this->guardarMensaje('error', 'La devolución no existe o fue eliminada.');
            $this->redireccionar('index.php?route=devoluciones.index');
        }

        $this->validarAccesoATienda((int) $devolucion['tienda_id']);

        $detalle = $this->devolucionModel->obtenerDetalle($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/devoluciones/show.php';
    }

    private function validarItemsDevolucion(array $input): ?array
    {
        $productos = $input['producto_id'] ?? [];
        $cantidades = $input['cantidad'] ?? [];

        if (!is_array($productos) || !is_array($cantidades)) {
            $this->guardarMensaje('error', 'Datos de la devolución inválidos.');
            return null;
        }

        $items = [];

        foreach ($productos as $index => $productoIdRaw) {
            $productoId  = (int) $productoIdRaw;
            $cantidadRaw = trim((string) ($cantidades[$index] ?? ''));
            $cantidad    = is_numeric($cantidadRaw) ? (float) $cantidadRaw : 0.0;

            // Saltar ítems que el usuario no quiere devolver (cantidad = 0 o vacío)
            if ($cantidad <= 0) {
                continue;
            }

            if ($productoId <= 0) {
                $this->guardarMensaje('error', 'Hay un producto inválido en la devolución.');
                return null;
            }

            $items[] = [
                'producto_id' => $productoId,
                'cantidad'    => $cantidad,
            ];
        }

        if (empty($items)) {
            $this->guardarMensaje('error', 'Debes ingresar una cantidad mayor a 0 en al menos un producto.');
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
        $errorMensaje = 'No tienes permisos para gestionar devoluciones de esta tienda.';

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
