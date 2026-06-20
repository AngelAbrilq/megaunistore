<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

/**
 * Controlador de Compras (órdenes de compra a proveedores).
 * Cubre: BD-COM-001, AT-COM-001, REQ-7.8.2 y la integración
 * compra → inventario (CF-INT-013).
 */
final class CompraController
{
    use ControllerHelper;

    private Compra $compraModel;
    private Proveedor $proveedorModel;
    private Producto $productoModel;
    private Tienda $tiendaModel;
    private Inventario $inventarioModel;
    private Empleado $empleadoModel;

    public function __construct()
    {
        $this->compraModel     = new Compra();
        $this->proveedorModel  = new Proveedor();
        $this->productoModel   = new Producto();
        $this->tiendaModel     = new Tienda();
        $this->inventarioModel = new Inventario();
        $this->empleadoModel   = new Empleado();
    }

    public function index(): void
    {
        $tiendaId  = $this->tiendaIdPermitida();
        $compras   = $this->compraModel->listar($tiendaId);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/compras/index.php';
    }

    public function create(): void
    {
        $tiendaId    = $this->tiendaIdPermitida();
        $proveedores = $this->proveedorModel->listar();
        $productos   = $this->productoModel->listar();
        $tiendas     = $tiendaId === null ? $this->tiendaModel->listar() : [];
        $csrfToken   = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/compras/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarCsrfToken();

        $tiendaId = $this->tiendaIdPermitida() ?? (int) ($_POST['tienda_id'] ?? 0);
        if ($tiendaId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar una tienda.');
            $this->redireccionar('index.php?route=compras.create');
        }

        $this->validarAccesoATienda($tiendaId);

        $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
        if ($proveedorId <= 0) {
            $this->guardarMensaje('error', 'Debes seleccionar un proveedor.');
            $this->redireccionar('index.php?route=compras.create');
        }

        $fecha = trim((string) ($_POST['fecha'] ?? date('Y-m-d')));

        // Líneas: arrays paralelos producto_id[], cantidad[], precio[]
        $productosIds = (array) ($_POST['producto_id'] ?? []);
        $cantidades   = (array) ($_POST['cantidad'] ?? []);
        $precios      = (array) ($_POST['precio_unitario'] ?? []);

        $lineas = [];

        foreach ($productosIds as $i => $productoId) {
            $productoId = (int) $productoId;
            $cantidad   = (float) ($cantidades[$i] ?? 0);
            $precio     = (float) ($precios[$i] ?? 0);

            if ($productoId > 0 && $cantidad > 0 && $precio >= 0) {
                $lineas[] = [
                    'producto_id'     => $productoId,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                ];
            }
        }

        if ($lineas === []) {
            $this->guardarMensaje('error', 'Agrega al menos un producto con cantidad y precio válidos.');
            $this->redireccionar('index.php?route=compras.create');
        }

        $empleado = $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual(), $tiendaId)
            ?? $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual());

        $compraId = $this->compraModel->crear([
            'tienda_id'    => $tiendaId,
            'proveedor_id' => $proveedorId,
            'empleado_id'  => $empleado !== null ? (int) $empleado['id'] : null,
            'fecha'        => $fecha,
            'impuesto'     => (float) ($_POST['impuesto'] ?? 0),
        ], $lineas);

        $this->jsonExito('compras.show&id=' . $compraId, 'Orden de compra #' . $compraId . ' registrada.');
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        $compra = $id > 0 ? $this->compraModel->buscarPorId($id) : null;
        if ($compra === null) {
            $this->guardarMensaje('error', 'La compra no existe o fue eliminada.');
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarAccesoATienda((int) $compra['tienda_id']);

        $detalle   = $this->compraModel->obtenerDetalle($id);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/compras/show.php';
    }

    /**
     * Marca la compra como recibida y carga el stock al inventario
     * registrando un movimiento de entrada por cada línea (CF-INT-013).
     */
    public function recibir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $compra = $id > 0 ? $this->compraModel->buscarPorId($id) : null;

        if ($compra === null) {
            $this->guardarMensaje('error', 'La compra no existe.');
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarAccesoATienda((int) $compra['tienda_id']);

        if ($compra['estado'] !== 'pendiente') {
            $this->guardarMensaje('error', 'Solo se pueden recibir compras en estado pendiente.');
            $this->redireccionar('index.php?route=compras.show&id=' . $id);
        }

        $detalle  = $this->compraModel->obtenerDetalle($id);
        $tiendaId = (int) $compra['tienda_id'];

        $empleado = $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual(), $tiendaId)
            ?? $this->empleadoModel->buscarPorUsuarioId($this->usuarioIdActual());
        $empleadoId = $empleado !== null ? (int) $empleado['id'] : null;

        foreach ($detalle as $linea) {
            $inventario = $this->inventarioModel->buscarPorTiendaProducto($tiendaId, (int) $linea['producto_id']);

            if ($inventario === null) {
                $inventarioId = $this->inventarioModel->crearOActualizar([
                    'tienda_id'       => $tiendaId,
                    'producto_id'     => (int) $linea['producto_id'],
                    'cantidad'        => 0,
                    'cantidad_minima' => 0,
                    'cantidad_maxima' => 0,
                    'ubicacion'       => null,
                ]);
            } else {
                $inventarioId = (int) $inventario['id'];
            }

            $this->inventarioModel->registrarMovimiento(
                $inventarioId,
                'entrada',
                (float) $linea['cantidad'],
                'Recepción orden de compra #' . $id,
                $empleadoId,
                $id,
                'compra'
            );
        }

        $this->compraModel->cambiarEstado($id, 'recibida');

        $this->guardarMensaje('success', 'Compra recibida: el inventario fue actualizado automáticamente.');
        $this->redireccionar('index.php?route=compras.show&id=' . $id);
    }

    public function cancelar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $compra = $id > 0 ? $this->compraModel->buscarPorId($id) : null;

        if ($compra === null) {
            $this->guardarMensaje('error', 'La compra no existe.');
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarAccesoATienda((int) $compra['tienda_id']);

        if ($compra['estado'] !== 'pendiente') {
            $this->guardarMensaje('error', 'Solo se pueden cancelar compras pendientes.');
            $this->redireccionar('index.php?route=compras.show&id=' . $id);
        }

        $this->compraModel->cambiarEstado($id, 'cancelada');

        $this->guardarMensaje('success', 'Orden de compra cancelada.');
        $this->redireccionar('index.php?route=compras.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $compra = $id > 0 ? $this->compraModel->buscarPorId($id) : null;

        if ($compra === null) {
            $this->guardarMensaje('error', 'La compra no existe.');
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->validarAccesoATienda((int) $compra['tienda_id']);

        if ($compra['estado'] === 'recibida') {
            $this->guardarMensaje('error', 'No se puede eliminar una compra ya recibida (el stock fue cargado).');
            $this->redireccionar('index.php?route=compras.index');
        }

        $this->compraModel->eliminarLogico($id);

        $this->guardarMensaje('success', 'Compra eliminada correctamente.');
        $this->redireccionar('index.php?route=compras.index');
    }
}
