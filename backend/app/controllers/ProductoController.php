<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/UnidadMedida.php';
require_once __DIR__ . '/../models/Impuesto.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class ProductoController
{
    use ControllerHelper;
    private Producto $productoModel;
    private Categoria $categoriaModel;
    private UnidadMedida $unidadModel;
    private Impuesto $impuestoModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->unidadModel = new UnidadMedida();
        $this->impuestoModel = new Impuesto();
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $productos = $this->productoModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/productos/index.php';
    }

    public function create(): void
    {
        $categorias = $this->categoriaModel->listarActivasParaSelect();
        $unidades = $this->unidadModel->listar();
        $impuestos = $this->impuestoModel->listarActivos();
        $tiendas = $this->tiendaModel->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/productos/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=productos.index');
        }

        $this->validarCsrfToken();

        $datosProducto = $this->validarDatosProducto($_POST);

        if ($datosProducto === null) {
            $this->redireccionar('index.php?route=productos.create');
        }

        if (
            $datosProducto['codigo_barras'] !== null
            && $this->productoModel->existeCodigoBarras($datosProducto['codigo_barras'])
        ) {
            $this->guardarMensaje('error', 'El código de barras ya está registrado.');
            $this->redireccionar('index.php?route=productos.create');
        }

        $tiendasProductos = $this->validarTiendasProducto($_POST);

        if ($tiendasProductos === null) {
            $this->redireccionar('index.php?route=productos.create');
        }

        $impuestosIds = $_POST['impuestos'] ?? [];

        $usuarioId = $this->usuarioIdActual();

        $this->productoModel->crearCompleto(
            [
                'nombre' => $datosProducto['nombre'],
                'descripcion' => $datosProducto['descripcion'],
                'codigo_barras' => $datosProducto['codigo_barras'],
                'imagen_url' => $datosProducto['imagen_url'],
                'categoria_id' => $datosProducto['categoria_id'],
                'unidad_medida_id' => $datosProducto['unidad_medida_id'],
                'estado' => 1,
                'created_by' => $usuarioId,
                'updated_by' => $usuarioId,
            ],
            is_array($impuestosIds) ? $impuestosIds : [],
            $tiendasProductos
        );

        $this->guardarMensaje('success', 'Producto creado correctamente.');
        $this->redireccionar('index.php?route=productos.index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Producto no válido.');
            $this->redireccionar('index.php?route=productos.index');
        }

        $producto = $this->productoModel->buscarPorId($id);

        if ($producto === null) {
            $this->guardarMensaje('error', 'El producto no existe o fue eliminado.');
            $this->redireccionar('index.php?route=productos.index');
        }

        $categorias = $this->categoriaModel->listarActivasParaSelect();
        $unidades = $this->unidadModel->listar();
        $impuestos = $this->impuestoModel->listarActivos();
        $tiendas = $this->tiendaModel->listar();

        $impuestosProducto = $this->productoModel->obtenerImpuestosProducto($id);
        $tiendasProducto = $this->productoModel->obtenerTiendasProducto($id);

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/productos/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=productos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Producto no válido.');
            $this->redireccionar('index.php?route=productos.index');
        }

        $producto = $this->productoModel->buscarPorId($id);

        if ($producto === null) {
            $this->guardarMensaje('error', 'El producto no existe o fue eliminado.');
            $this->redireccionar('index.php?route=productos.index');
        }

        $datosProducto = $this->validarDatosProducto($_POST);

        if ($datosProducto === null) {
            $this->redireccionar('index.php?route=productos.edit&id=' . $id);
        }

        if (
            $datosProducto['codigo_barras'] !== null
            && $this->productoModel->existeCodigoBarras($datosProducto['codigo_barras'], $id)
        ) {
            $this->guardarMensaje('error', 'El código de barras ya está registrado en otro producto.');
            $this->redireccionar('index.php?route=productos.edit&id=' . $id);
        }

        $tiendasProductos = $this->validarTiendasProducto($_POST);

        if ($tiendasProductos === null) {
            $this->redireccionar('index.php?route=productos.edit&id=' . $id);
        }

        $impuestosIds = $_POST['impuestos'] ?? [];
        $usuarioId = $this->usuarioIdActual();

        $this->productoModel->actualizarCompleto(
            $id,
            [
                'nombre' => $datosProducto['nombre'],
                'descripcion' => $datosProducto['descripcion'],
                'codigo_barras' => $datosProducto['codigo_barras'],
                'imagen_url' => $datosProducto['imagen_url'],
                'categoria_id' => $datosProducto['categoria_id'],
                'unidad_medida_id' => $datosProducto['unidad_medida_id'],
                'estado' => (int) ($_POST['estado'] ?? 1),
                'updated_by' => $usuarioId,
            ],
            is_array($impuestosIds) ? $impuestosIds : [],
            $tiendasProductos
        );

        $this->guardarMensaje('success', 'Producto actualizado correctamente.');
        $this->redireccionar('index.php?route=productos.index');
    }

    public function toggleEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=productos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Producto no válido.');
            $this->redireccionar('index.php?route=productos.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;

        $this->productoModel->cambiarEstado($id, $nuevoEstado, $this->usuarioIdActual());

        $mensaje = $nuevoEstado === 1
            ? 'Producto activado correctamente.'
            : 'Producto desactivado correctamente.';

        $this->guardarMensaje('success', $mensaje);
        $this->redireccionar('index.php?route=productos.index');
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=productos.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Producto no válido.');
            $this->redireccionar('index.php?route=productos.index');
        }

        $this->productoModel->eliminarLogico($id, $this->usuarioIdActual());

        $this->guardarMensaje('success', 'Producto eliminado correctamente.');
        $this->redireccionar('index.php?route=productos.index');
    }

    private function validarDatosProducto(array $input): ?array
    {
        $nombre = trim((string) ($input['nombre'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $codigoBarras = trim((string) ($input['codigo_barras'] ?? ''));
        $imagenUrl = trim((string) ($input['imagen_url'] ?? ''));
        $categoriaId = (int) ($input['categoria_id'] ?? 0);
        $unidadMedidaId = (int) ($input['unidad_medida_id'] ?? 0);

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre del producto es obligatorio.');
            return null;
        }

        if (strlen($nombre) > 200) {
            $this->guardarMensaje('error', 'El nombre no puede superar 200 caracteres.');
            return null;
        }

        if ($codigoBarras !== '' && strlen($codigoBarras) > 50) {
            $this->guardarMensaje('error', 'El código de barras no puede superar 50 caracteres.');
            return null;
        }

        if ($categoriaId > 0 && $this->categoriaModel->buscarPorId($categoriaId) === null) {
            $this->guardarMensaje('error', 'La categoría seleccionada no existe.');
            return null;
        }

        if ($unidadMedidaId > 0 && $this->unidadModel->buscarPorId($unidadMedidaId) === null) {
            $this->guardarMensaje('error', 'La unidad de medida seleccionada no existe.');
            return null;
        }

        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'codigo_barras' => $codigoBarras !== '' ? $codigoBarras : null,
            'imagen_url' => $imagenUrl !== '' ? $imagenUrl : null,
            'categoria_id' => $categoriaId > 0 ? $categoriaId : null,
            'unidad_medida_id' => $unidadMedidaId > 0 ? $unidadMedidaId : null,
        ];
    }

    private function validarTiendasProducto(array $input): ?array
    {
        $tiendasSeleccionadas = $input['tiendas'] ?? [];
        $preciosVenta = $input['precio_venta'] ?? [];
        $preciosCompra = $input['precio_compra'] ?? [];

        if (!is_array($tiendasSeleccionadas) || empty($tiendasSeleccionadas)) {
            $this->guardarMensaje('error', 'Debes asociar el producto al menos a una tienda.');
            return null;
        }

        $resultado = [];

        foreach ($tiendasSeleccionadas as $tiendaIdRaw) {
            $tiendaId = (int) $tiendaIdRaw;

            if ($tiendaId <= 0) {
                continue;
            }

            $tienda = $this->tiendaModel->buscarPorId($tiendaId);

            if ($tienda === null) {
                $this->guardarMensaje('error', 'Una de las tiendas seleccionadas no existe.');
                return null;
            }

            $precioVentaRaw = trim((string) ($preciosVenta[$tiendaId] ?? ''));

            if ($precioVentaRaw === '' || !is_numeric($precioVentaRaw)) {
                $this->guardarMensaje('error', 'El precio de venta es obligatorio para cada tienda seleccionada.');
                return null;
            }

            $precioVenta = (float) $precioVentaRaw;

            if ($precioVenta < 0) {
                $this->guardarMensaje('error', 'El precio de venta no puede ser negativo.');
                return null;
            }

            $precioCompraRaw = trim((string) ($preciosCompra[$tiendaId] ?? ''));

            $precioCompra = null;

            if ($precioCompraRaw !== '') {
                if (!is_numeric($precioCompraRaw)) {
                    $this->guardarMensaje('error', 'El precio de compra debe ser numérico.');
                    return null;
                }

                $precioCompra = (float) $precioCompraRaw;

                if ($precioCompra < 0) {
                    $this->guardarMensaje('error', 'El precio de compra no puede ser negativo.');
                    return null;
                }
            }

            $resultado[] = [
                'tienda_id' => $tiendaId,
                'precio_venta' => number_format($precioVenta, 2, '.', ''),
                'precio_compra' => $precioCompra !== null ? number_format($precioCompra, 2, '.', '') : null,
            ];
        }

        if (empty($resultado)) {
            $this->guardarMensaje('error', 'Debes asociar el producto al menos a una tienda válida.');
            return null;
        }

        return $resultado;
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