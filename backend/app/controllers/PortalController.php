<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../models/Cupon.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * PortalController — Portal de compras para clientes.
 *
 * Carrito: $_SESSION['carrito'] = [ producto_id => ['nombre','precio','cantidad','imagen_url','tienda_id'] ]
 * Cupón activo: $_SESSION['cupon'] = ['id','codigo','descuento']
 */
final class PortalController
{
    use ControllerHelper;

    private PDO $db;
    private Venta $ventaModel;
    private Categoria $categoriaModel;
    private Cupon $cuponModel;

    public function __construct()
    {
        $this->db            = Database::getConnection();
        $this->ventaModel    = new Venta();
        $this->categoriaModel = new Categoria();
        $this->cuponModel    = new Cupon();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        $this->crearTablasPortalIfNotExist();
    }

    // =========================================================================
    // CATÁLOGO
    // =========================================================================

    public function catalogo(): void
    {
        $this->requireCliente();

        $categorias  = $this->categoriaModel->listar();
        $categoriaId = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
        $busqueda    = trim($_GET['q'] ?? '');
        $precioMin   = isset($_GET['precio_min']) ? (float) $_GET['precio_min'] : null;
        $precioMax   = isset($_GET['precio_max']) ? (float) $_GET['precio_max'] : null;
        $orden       = $_GET['orden'] ?? 'nombre';
        $tiendaId    = $this->tiendaPortal();

        $productos    = $this->productosDisponibles($tiendaId, $categoriaId, $busqueda, $precioMin, $precioMax, $orden);
        $carritoCount = $this->contarItemsCarrito();
        $csrfToken    = $this->generarCsrfToken();
        $wishlistIds  = $this->obtenerWishlistIds($this->clienteIdActual());
        $precioRango  = $this->precioRangoCatalogo($tiendaId);

        require __DIR__ . '/../../resources/views/portal/catalogo.php';
    }

    // =========================================================================
    // DETALLE PRODUCTO
    // =========================================================================

    public function producto(): void
    {
        $this->requireCliente();

        $id       = (int) ($_GET['id'] ?? 0);
        $tiendaId = $this->tiendaPortal();

        $producto = $this->productoDetalle($id, $tiendaId);
        if ($producto === null) {
            $this->guardarMensaje('error', 'Producto no disponible.');
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        $valoraciones    = $this->obtenerValoraciones($id);
        $ratingPromedio  = $this->calcularRatingPromedio($valoraciones);
        $clienteId       = $this->clienteIdActual();
        $yaValoro        = $clienteId ? $this->yaValoro($clienteId, $id) : false;
        $comproProd      = $clienteId ? $this->comproProd($clienteId, $id) : false;
        $enWishlist      = $clienteId ? $this->estaEnWishlist($clienteId, $id) : false;
        $carritoCount    = $this->contarItemsCarrito();
        $csrfToken       = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/portal/producto.php';
    }

    // =========================================================================
    // WISHLIST
    // =========================================================================

    public function wishlistToggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        $this->requireCliente();
        $this->validarCsrfToken();

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $clienteId  = $this->clienteIdActual();

        if ($clienteId <= 0 || $productoId <= 0) {
            $this->guardarMensaje('error', 'Acción no válida.');
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        if ($this->estaEnWishlist($clienteId, $productoId)) {
            $this->db->prepare("DELETE FROM portal_wishlist WHERE cliente_id=:c AND producto_id=:p")
                     ->execute([':c' => $clienteId, ':p' => $productoId]);
            $this->guardarMensaje('success', 'Producto eliminado de tus favoritos.');
        } else {
            $this->db->prepare("INSERT IGNORE INTO portal_wishlist (cliente_id, producto_id) VALUES (:c, :p)")
                     ->execute([':c' => $clienteId, ':p' => $productoId]);
            $this->guardarMensaje('success', '❤️ Producto agregado a tus favoritos.');
        }

        $referer = $_POST['referer'] ?? 'portal.catalogo';
        $this->redireccionar('index.php?route=' . $referer);
    }

    public function wishlistVer(): void
    {
        $this->requireCliente();

        $clienteId    = $this->clienteIdActual();
        $tiendaId     = $this->tiendaPortal();
        $productos    = $this->obtenerWishlistProductos($clienteId, $tiendaId);
        $carritoCount = $this->contarItemsCarrito();
        $csrfToken    = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/portal/wishlist.php';
    }

    // =========================================================================
    // VALORACIONES
    // =========================================================================

    public function valorarForm(): void
    {
        $this->requireCliente();

        $productoId = (int) ($_GET['producto_id'] ?? 0);
        $clienteId  = $this->clienteIdActual();
        $tiendaId   = $this->tiendaPortal();

        $producto = $this->productoDetalle($productoId, $tiendaId);
        if ($producto === null) {
            $this->guardarMensaje('error', 'Producto no encontrado.');
            $this->redireccionar('index.php?route=portal.pedidos');
        }

        if (!$this->comproProd($clienteId, $productoId)) {
            $this->guardarMensaje('error', 'Solo puedes valorar productos que hayas comprado.');
            $this->redireccionar('index.php?route=portal.pedidos');
        }

        if ($this->yaValoro($clienteId, $productoId)) {
            $this->guardarMensaje('error', 'Ya valoraste este producto.');
            $this->redireccionar('index.php?route=portal.producto&id=' . $productoId);
        }

        $carritoCount = $this->contarItemsCarrito();
        $csrfToken    = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/portal/valorar.php';
    }

    public function valorarPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.pedidos');
        }

        $this->requireCliente();
        $this->validarCsrfToken();

        $productoId  = (int) ($_POST['producto_id'] ?? 0);
        $estrellas   = max(1, min(5, (int) ($_POST['estrellas'] ?? 5)));
        $comentario  = trim($_POST['comentario'] ?? '');
        $clienteId   = $this->clienteIdActual();

        if (!$this->comproProd($clienteId, $productoId)) {
            $this->guardarMensaje('error', 'No puedes valorar este producto.');
            $this->redireccionar('index.php?route=portal.pedidos');
        }

        $this->db->prepare("
            INSERT IGNORE INTO portal_valoraciones (cliente_id, producto_id, estrellas, comentario)
            VALUES (:c, :p, :e, :com)
        ")->execute([
            ':c'   => $clienteId,
            ':p'   => $productoId,
            ':e'   => $estrellas,
            ':com' => $comentario !== '' ? $comentario : null,
        ]);

        $this->guardarMensaje('success', '⭐ ¡Gracias por tu valoración!');
        $this->redireccionar('index.php?route=portal.producto&id=' . $productoId);
    }

    // =========================================================================
    // CARRITO — agregar
    // =========================================================================

    public function carritoAgregar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        $this->requireCliente();
        $this->validarCsrfToken();

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $cantidad   = max(1, (int) ($_POST['cantidad'] ?? 1));
        $tiendaId   = $this->tiendaPortal();
        $producto   = $this->productoDetalle($productoId, $tiendaId);

        if ($producto === null) {
            $this->guardarMensaje('error', 'Producto no disponible.');
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        $carrito = $_SESSION['carrito'];

        if (isset($carrito[$productoId])) {
            $carrito[$productoId]['cantidad'] += $cantidad;
        } else {
            $carrito[$productoId] = [
                'nombre'     => $producto['nombre'],
                'precio'     => (float) $producto['precio_venta'],
                'cantidad'   => $cantidad,
                'imagen_url' => $producto['imagen_url'],
                'tienda_id'  => $tiendaId,
            ];
        }

        $_SESSION['carrito'] = $carrito;
        // Invalidar cupón si cambia el carrito
        unset($_SESSION['cupon']);

        $this->guardarMensaje('success', '"' . $producto['nombre'] . '" agregado al carrito.');
        $this->redireccionar('index.php?route=portal.carrito');
    }

    // =========================================================================
    // CARRITO — ver
    // =========================================================================

    public function carritoVer(): void
    {
        $this->requireCliente();

        $carrito      = $_SESSION['carrito'];
        $subtotal     = $this->calcularSubtotal($carrito);
        $cuponActivo  = $_SESSION['cupon'] ?? null;
        $descuento    = $cuponActivo ? (float)($cuponActivo['descuento'] ?? 0) : 0.0;
        $total        = max(0, $subtotal - $descuento);
        $carritoCount = $this->contarItemsCarrito();
        $csrfToken    = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/portal/carrito.php';
    }

    // =========================================================================
    // CARRITO — actualizar
    // =========================================================================

    public function carritoActualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.carrito');
        }

        $this->requireCliente();
        $this->validarCsrfToken();

        $cantidades = $_POST['cantidades'] ?? [];
        $carrito    = $_SESSION['carrito'];

        foreach ($cantidades as $productoId => $cantidad) {
            $productoId = (int) $productoId;
            $cantidad   = (int) $cantidad;

            if ($cantidad <= 0) {
                unset($carrito[$productoId]);
            } elseif (isset($carrito[$productoId])) {
                $carrito[$productoId]['cantidad'] = $cantidad;
            }
        }

        $_SESSION['carrito'] = $carrito;
        unset($_SESSION['cupon']); // Recalcular cupón
        $this->redireccionar('index.php?route=portal.carrito');
    }

    // =========================================================================
    // CARRITO — vaciar
    // =========================================================================

    public function carritoVaciar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.carrito');
        }

        $this->requireCliente();
        $this->validarCsrfToken();
        $_SESSION['carrito'] = [];
        unset($_SESSION['cupon']);
        $this->redireccionar('index.php?route=portal.carrito');
    }

    // =========================================================================
    // CUPÓN — validar (responde JSON)
    // =========================================================================

    public function cuponValidar(): void
    {
        $this->requireCliente();
        header('Content-Type: application/json');

        $codigo   = trim($_GET['codigo'] ?? '');
        $carrito  = $_SESSION['carrito'];
        $subtotal = $this->calcularSubtotal($carrito);
        $tiendaId = $this->tiendaPortal();

        if ($codigo === '') {
            echo json_encode(['ok' => false, 'mensaje' => 'Ingresa un código de cupón.']);
            exit;
        }

        $resultado = $this->cuponModel->validarCupon($codigo, $subtotal, $tiendaId);

        if ($resultado['valido']) {
            $_SESSION['cupon'] = [
                'id'       => $resultado['cupon_id'],
                'codigo'   => strtoupper($codigo),
                'descuento'=> $resultado['descuento'],
            ];
        } else {
            unset($_SESSION['cupon']);
        }

        echo json_encode([
            'ok'       => $resultado['valido'],
            'mensaje'  => $resultado['mensaje'],
            'descuento'=> $resultado['descuento'] ?? 0,
            'subtotal' => $subtotal,
            'total'    => max(0, $subtotal - ($resultado['descuento'] ?? 0)),
        ]);
        exit;
    }

    // =========================================================================
    // CHECKOUT — formulario
    // =========================================================================

    public function checkout(): void
    {
        $this->requireCliente();

        if (empty($_SESSION['carrito'])) {
            $this->guardarMensaje('error', 'Tu carrito está vacío.');
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        $usuario      = $this->usuarioActual();
        $cliente      = $this->obtenerOCrearCliente($usuario);
        $carrito      = $_SESSION['carrito'];
        $subtotal     = $this->calcularSubtotal($carrito);
        $cuponActivo  = $_SESSION['cupon'] ?? null;
        $descuento    = $cuponActivo ? (float)($cuponActivo['descuento'] ?? 0) : 0.0;
        $total        = max(0, $subtotal - $descuento);
        $carritoCount = $this->contarItemsCarrito();
        $csrfToken    = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/portal/checkout.php';
    }

    // =========================================================================
    // CHECKOUT — procesar
    // =========================================================================

    public function checkoutPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.checkout');
        }

        $this->requireCliente();
        $this->validarCsrfToken();

        if (empty($_SESSION['carrito'])) {
            $this->guardarMensaje('error', 'Tu carrito está vacío.');
            $this->redireccionar('index.php?route=portal.catalogo');
        }

        $usuario      = $this->usuarioActual();
        $cliente      = $this->obtenerOCrearCliente($usuario);
        $carrito      = $_SESSION['carrito'];
        $tiendaId     = $this->tiendaPortal();
        $subtotal     = $this->calcularSubtotal($carrito);

        $this->actualizarDatosCliente($cliente['id'], $_POST);

        // Procesar cupón
        $cuponActivo = $_SESSION['cupon'] ?? null;
        $descuento   = 0.0;
        $cuponId     = null;

        if ($cuponActivo) {
            $reValidar = $this->cuponModel->validarCupon($cuponActivo['codigo'], $subtotal, $tiendaId);
            if ($reValidar['valido']) {
                $descuento = (float) $reValidar['descuento'];
                $cuponId   = (int) $reValidar['cupon_id'];
            }
        }

        $total = max(0, $subtotal - $descuento);

        $items = [];
        foreach ($carrito as $productoId => $item) {
            $items[] = [
                'producto_id'     => $productoId,
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio'],
            ];
        }

        $metodoPagoId = $this->obtenerMetodoPagoEfectivo();

        try {
            $ventaId = $this->ventaModel->crearVentaPortal(
                [
                    'tienda_id'      => $tiendaId,
                    'cliente_id'     => $cliente['id'],
                    'cupon_id'       => $cuponId,
                    'descuento_cupon'=> $descuento,
                ],
                $items
            );

            if ($cuponId) {
                $this->cuponModel->incrementarUsos($cuponId);
            }

            $_SESSION['carrito'] = [];
            unset($_SESSION['cupon']);

            $this->guardarMensaje('success', '¡Pedido #' . $ventaId . ' realizado con éxito! Puedes valorar tus productos desde "Mis pedidos".');
            $this->redireccionar('index.php?route=portal.pedidos');
        } catch (Throwable $e) {
            $this->guardarMensaje('error', 'Error al procesar el pedido: ' . $e->getMessage());
            $this->redireccionar('index.php?route=portal.checkout');
        }
    }

    // =========================================================================
    // MIS PEDIDOS
    // =========================================================================

    public function pedidos(): void
    {
        $this->requireCliente();

        $usuario      = $this->usuarioActual();
        $cliente      = $this->obtenerOCrearCliente($usuario);
        $pedidos      = $this->obtenerPedidosCliente($cliente['id']);
        $carritoCount = $this->contarItemsCarrito();

        require __DIR__ . '/../../resources/views/portal/pedidos.php';
    }

    // =========================================================================
    // DETALLE PEDIDO
    // =========================================================================

    public function pedido(): void
    {
        $this->requireCliente();

        $id      = (int) ($_GET['id'] ?? 0);
        $usuario = $this->usuarioActual();
        $cliente = $this->obtenerOCrearCliente($usuario);
        $pedido  = $this->ventaModel->buscarPorId($id);

        if ($pedido === null || (int) $pedido['cliente_id'] !== (int) $cliente['id']) {
            $this->guardarMensaje('error', 'Pedido no encontrado.');
            $this->redireccionar('index.php?route=portal.pedidos');
        }

        $detalle      = $this->ventaModel->obtenerDetalle($id);
        $carritoCount = $this->contarItemsCarrito();

        // IDs de productos ya valorados por el cliente
        $valoradosIds = $this->obtenerProductosValorados($cliente['id']);

        require __DIR__ . '/../../resources/views/portal/pedido.php';
    }

    // =========================================================================
    // PERFIL
    // =========================================================================

    public function perfil(): void
    {
        $this->requireCliente();

        $usuario      = $this->usuarioActual();
        $cliente      = $this->obtenerOCrearCliente($usuario);
        $carritoCount = $this->contarItemsCarrito();
        $csrfToken    = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/portal/perfil.php';
    }

    public function perfilPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=portal.perfil');
        }

        $this->requireCliente();
        $this->validarCsrfToken();

        $usuario = $this->usuarioActual();
        $cliente = $this->obtenerOCrearCliente($usuario);

        $this->actualizarDatosCliente($cliente['id'], $_POST);

        $this->guardarMensaje('success', 'Perfil actualizado correctamente.');
        $this->redireccionar('index.php?route=portal.perfil');
    }

    // =========================================================================
    // HELPERS PRIVADOS — Auth
    // =========================================================================

    private function requireCliente(): void
    {
        if (!isset($_SESSION['auth'])) {
            $this->redireccionar('index.php?route=login');
        }
    }

    private function usuarioActual(): array
    {
        return $_SESSION['auth'] ?? [];
    }

    private function clienteIdActual(): int
    {
        if (isset($_SESSION['portal_cliente_id'])) {
            return (int) $_SESSION['portal_cliente_id'];
        }

        $email = $_SESSION['auth']['email'] ?? '';
        if ($email === '') return 0;

        $stmt = $this->db->prepare("SELECT id FROM clientes WHERE email = :e AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':e' => $email]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        $_SESSION['portal_cliente_id'] = $id;
        return $id;
    }

    // =========================================================================
    // HELPERS PRIVADOS — Tienda
    // =========================================================================

    private function tiendaPortal(): int
    {
        if (isset($_SESSION['portal_tienda_id'])) {
            return (int) $_SESSION['portal_tienda_id'];
        }

        $stmt = $this->db->query("SELECT id FROM tiendas WHERE estado=1 AND deleted_at IS NULL ORDER BY id ASC LIMIT 1");
        $tienda = $stmt->fetch();
        $id = $tienda ? (int) $tienda['id'] : 1;
        $_SESSION['portal_tienda_id'] = $id;
        return $id;
    }

    // =========================================================================
    // HELPERS PRIVADOS — Productos
    // =========================================================================

    private function productosDisponibles(
        int $tiendaId,
        ?int $categoriaId,
        string $busqueda,
        ?float $precioMin,
        ?float $precioMax,
        string $orden
    ): array {
        $sql = "
            SELECT p.id, p.nombre, p.descripcion, p.imagen_url, p.categoria_id,
                   c.nombre AS categoria_nombre,
                   tp.precio_venta,
                   COALESCE(inv.cantidad, 0) AS stock,
                   COALESCE(v.rating, 0)     AS rating_promedio,
                   COALESCE(v.total_val, 0)  AS total_valoraciones
            FROM productos p
            INNER JOIN tiendas_productos tp
                ON tp.producto_id = p.id AND tp.tienda_id = :tienda_id AND tp.estado = 1
            LEFT JOIN inventario inv
                ON inv.producto_id = p.id AND inv.tienda_id = :tienda_id2
            LEFT JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN (
                SELECT producto_id,
                       ROUND(AVG(estrellas),1) AS rating,
                       COUNT(*) AS total_val
                FROM portal_valoraciones GROUP BY producto_id
            ) v ON v.producto_id = p.id
            WHERE p.estado = 1 AND p.deleted_at IS NULL
        ";

        $params = [':tienda_id' => $tiendaId, ':tienda_id2' => $tiendaId];

        if ($categoriaId !== null) {
            $sql .= ' AND p.categoria_id = :categoria_id';
            $params[':categoria_id'] = $categoriaId;
        }

        if ($busqueda !== '') {
            $sql .= ' AND (p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda2)';
            $params[':busqueda']  = '%' . $busqueda . '%';
            $params[':busqueda2'] = '%' . $busqueda . '%';
        }

        if ($precioMin !== null) {
            $sql .= ' AND tp.precio_venta >= :precio_min';
            $params[':precio_min'] = $precioMin;
        }

        if ($precioMax !== null) {
            $sql .= ' AND tp.precio_venta <= :precio_max';
            $params[':precio_max'] = $precioMax;
        }

        $ordenes = [
            'nombre'      => 'p.nombre ASC',
            'precio_asc'  => 'tp.precio_venta ASC',
            'precio_desc' => 'tp.precio_venta DESC',
            'rating'      => 'rating_promedio DESC',
            'nuevo'       => 'p.id DESC',
        ];

        $sql .= ' ORDER BY ' . ($ordenes[$orden] ?? 'p.nombre ASC');

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function productoDetalle(int $productoId, int $tiendaId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.nombre, p.descripcion, p.imagen_url, p.categoria_id,
                   c.nombre AS categoria_nombre,
                   tp.precio_venta,
                   COALESCE(inv.cantidad, 0) AS stock
            FROM productos p
            INNER JOIN tiendas_productos tp
                ON tp.producto_id = p.id AND tp.tienda_id = :tienda_id AND tp.estado = 1
            LEFT JOIN inventario inv
                ON inv.producto_id = p.id AND inv.tienda_id = :tienda_id2
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.id = :id AND p.estado = 1 AND p.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $productoId, ':tienda_id' => $tiendaId, ':tienda_id2' => $tiendaId]);
        return $stmt->fetch() ?: null;
    }

    private function precioRangoCatalogo(int $tiendaId): array
    {
        $stmt = $this->db->prepare("
            SELECT MIN(tp.precio_venta) AS min_precio, MAX(tp.precio_venta) AS max_precio
            FROM tiendas_productos tp
            INNER JOIN productos p ON p.id = tp.producto_id
            WHERE tp.tienda_id = :tid AND tp.estado = 1 AND p.estado = 1 AND p.deleted_at IS NULL
        ");
        $stmt->execute([':tid' => $tiendaId]);
        $row = $stmt->fetch();
        return ['min' => (float)($row['min_precio'] ?? 0), 'max' => (float)($row['max_precio'] ?? 9999999)];
    }

    // =========================================================================
    // HELPERS PRIVADOS — Wishlist
    // =========================================================================

    private function estaEnWishlist(int $clienteId, int $productoId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM portal_wishlist WHERE cliente_id=:c AND producto_id=:p LIMIT 1");
        $stmt->execute([':c' => $clienteId, ':p' => $productoId]);
        return (bool) $stmt->fetchColumn();
    }

    private function obtenerWishlistIds(int $clienteId): array
    {
        if ($clienteId <= 0) return [];
        $stmt = $this->db->prepare("SELECT producto_id FROM portal_wishlist WHERE cliente_id=:c");
        $stmt->execute([':c' => $clienteId]);
        return array_column($stmt->fetchAll(), 'producto_id');
    }

    private function obtenerWishlistProductos(int $clienteId, int $tiendaId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.nombre, p.imagen_url, tp.precio_venta,
                   COALESCE(inv.cantidad, 0) AS stock
            FROM portal_wishlist wl
            INNER JOIN productos p ON p.id = wl.producto_id AND p.estado = 1 AND p.deleted_at IS NULL
            LEFT  JOIN tiendas_productos tp ON tp.producto_id = p.id AND tp.tienda_id = :tid AND tp.estado = 1
            LEFT  JOIN inventario inv ON inv.producto_id = p.id AND inv.tienda_id = :tid2
            WHERE wl.cliente_id = :c
            ORDER BY wl.id DESC
        ");
        $stmt->execute([':c' => $clienteId, ':tid' => $tiendaId, ':tid2' => $tiendaId]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // HELPERS PRIVADOS — Valoraciones
    // =========================================================================

    private function obtenerValoraciones(int $productoId): array
    {
        $stmt = $this->db->prepare("
            SELECT pv.estrellas, pv.comentario, pv.created_at,
                   CONCAT(c.nombre, ' ', COALESCE(c.apellido,'')) AS cliente_nombre
            FROM portal_valoraciones pv
            INNER JOIN clientes c ON c.id = pv.cliente_id
            WHERE pv.producto_id = :pid
            ORDER BY pv.id DESC
        ");
        $stmt->execute([':pid' => $productoId]);
        return $stmt->fetchAll();
    }

    private function calcularRatingPromedio(array $valoraciones): float
    {
        if (empty($valoraciones)) return 0.0;
        return round(array_sum(array_column($valoraciones, 'estrellas')) / count($valoraciones), 1);
    }

    private function yaValoro(int $clienteId, int $productoId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM portal_valoraciones WHERE cliente_id=:c AND producto_id=:p LIMIT 1");
        $stmt->execute([':c' => $clienteId, ':p' => $productoId]);
        return (bool) $stmt->fetchColumn();
    }

    private function comproProd(int $clienteId, int $productoId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM ventas_detalle vd
            INNER JOIN ventas v ON v.id = vd.venta_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE cli.id = :c AND vd.producto_id = :p
              AND v.estado = 'completada' AND v.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':c' => $clienteId, ':p' => $productoId]);
        return (bool) $stmt->fetchColumn();
    }

    private function obtenerProductosValorados(int $clienteId): array
    {
        $stmt = $this->db->prepare("SELECT producto_id FROM portal_valoraciones WHERE cliente_id=:c");
        $stmt->execute([':c' => $clienteId]);
        return array_column($stmt->fetchAll(), 'producto_id');
    }

    // =========================================================================
    // HELPERS PRIVADOS — Cliente
    // =========================================================================

    private function obtenerOCrearCliente(array $usuario): array
    {
        $email = $usuario['email'] ?? '';
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE email=:e AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':e' => $email]);
        $cliente = $stmt->fetch();

        if ($cliente) return $cliente;

        $this->db->prepare("INSERT INTO clientes (nombre, apellido, email) VALUES (:n,:a,:e)")
            ->execute([':n' => $usuario['nombre'] ?? '', ':a' => $usuario['apellido'] ?? '', ':e' => $email]);

        $stmt->execute([':e' => $email]);
        return $stmt->fetch();
    }

    private function actualizarDatosCliente(int $clienteId, array $input): void
    {
        $this->db->prepare("
            UPDATE clientes
            SET nombre=:nombre, apellido=:apellido, telefono=:telefono,
                direccion=:direccion, tipo_documento=:tipo_doc, numero_documento=:num_doc
            WHERE id=:id
        ")->execute([
            ':id'      => $clienteId,
            ':nombre'  => trim($input['nombre']           ?? ''),
            ':apellido'=> trim($input['apellido']         ?? ''),
            ':telefono'=> trim($input['telefono']         ?? ''),
            ':direccion'=> trim($input['direccion']       ?? ''),
            ':tipo_doc' => trim($input['tipo_documento']  ?? ''),
            ':num_doc'  => trim($input['numero_documento']?? ''),
        ]);
    }

    private function obtenerPedidosCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT v.id, v.fecha, v.total, v.estado, t.nombre AS tienda_nombre,
                   COUNT(vd.id) AS total_items
            FROM ventas v
            INNER JOIN tiendas t ON t.id = v.tienda_id
            LEFT  JOIN ventas_detalle vd ON vd.venta_id = v.id
            WHERE v.cliente_id = :c AND v.deleted_at IS NULL
            GROUP BY v.id ORDER BY v.id DESC
        ");
        $stmt->execute([':c' => $clienteId]);
        return $stmt->fetchAll();
    }

    private function calcularSubtotal(array $carrito): float
    {
        return array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carrito));
    }

    private function contarItemsCarrito(): int
    {
        return array_sum(array_column($_SESSION['carrito'], 'cantidad'));
    }

    // =========================================================================
    // AUTO-CREAR TABLAS PORTAL
    // =========================================================================

    private static bool $tablasCreadas = false;

    private function crearTablasPortalIfNotExist(): void
    {
        if (self::$tablasCreadas) return;
        self::$tablasCreadas = true;

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS portal_wishlist (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                cliente_id  INT NOT NULL,
                producto_id INT NOT NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_wish (cliente_id, producto_id),
                KEY idx_wish_cli (cliente_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS portal_valoraciones (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                cliente_id  INT NOT NULL,
                producto_id INT NOT NULL,
                estrellas   TINYINT NOT NULL DEFAULT 5,
                comentario  TEXT,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_val (cliente_id, producto_id),
                KEY idx_val_prod (producto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}
