<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/TiendaController.php';
require_once __DIR__ . '/../app/controllers/UsuarioController.php';
require_once __DIR__ . '/../app/controllers/CategoriaController.php';
require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
require_once __DIR__ . '/../app/controllers/ProductoController.php';
require_once __DIR__ . '/../app/controllers/InventarioController.php';
require_once __DIR__ . '/../app/controllers/VentaController.php';
require_once __DIR__ . '/../app/controllers/CajaController.php';
require_once __DIR__ . '/../app/controllers/SetupController.php';
require_once __DIR__ . '/../app/controllers/ClienteController.php';
require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
require_once __DIR__ . '/../app/controllers/ProveedorController.php';
require_once __DIR__ . '/../app/controllers/CuponController.php';
require_once __DIR__ . '/../app/controllers/DevolucionController.php';
require_once __DIR__ . '/../app/controllers/ReporteController.php';

$route = $_GET['route'] ?? 'login';
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

$authController = new AuthController();

switch ($route) {
    case '':
    case 'login':
        $authController->mostrarLogin();
        break;

    case 'login.post':
        $authController->login();
        break;

    case 'register':
        $authController->mostrarRegistro();
        break;

    case 'register.post':
        $authController->registrar();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'password.request':
        require __DIR__ . '/../resources/views/auth/password_pending.php';
        break;


    // =========================================================================
    // dashboard
    // =========================================================================



    case 'dashboard':
        $authController->requerirAutenticacion();
        
        // Si es petición AJAX, solo devolver el contenido
        if ($isAjax) {
            redirigirDashboardPrincipal($authController, true);
        } else {
            // Si es petición normal, cargar el layout completo
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        }
        break;

    case 'dashboard.superadmin':
        $authController->requerirRol(['Superadministrador']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/superadmin.php';
        }
        break;

    case 'dashboard.admin_tienda':
        $authController->requerirRol(['Administrador de Tienda']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/admin_tienda.php';
        }
        break;

    case 'dashboard.supervisor':
        $authController->requerirRol(['Supervisor']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/supervisor.php';
        }
        break;

    case 'dashboard.vendedor':
        $authController->requerirRol(['Vendedor']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/vendedor.php';
        }
        break;

    case 'dashboard.bodeguero':
        $authController->requerirRol(['Bodeguero']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/bodeguero.php';
        }
        break;

    case 'dashboard.reportero':
        $authController->requerirRol(['Reportero']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/reportero.php';
        }
        break;

    case 'dashboard.nomina':
        $authController->requerirRol(['Nómina y RRHH']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/nomina.php';
        }
        break;

    case 'dashboard.cliente':
        $authController->requerirRol(['Cliente']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/cliente.php';
        }
        break;

    case 'dashboard.sistema':
        $authController->requerirRol(['Sistema']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            require __DIR__ . '/../resources/views/dashboard/sistema.php';
        }
        break;

    // =========================================================================
    // tiendas 
    // =========================================================================


    case 'tiendas.index':
        $authController->requerirPermiso('tiendas.view');
        $tiendaController = new TiendaController();
        $tiendaController->index();
        break;

    case 'tiendas.create':
        $authController->requerirPermiso('tiendas.create');
        $tiendaController = new TiendaController();
        $tiendaController->create();
        break;

    case 'tiendas.store':
        $authController->requerirPermiso('tiendas.create');
        $tiendaController = new TiendaController();
        $tiendaController->store();
        break;

    case 'tiendas.edit':
        $authController->requerirPermiso('tiendas.update');
        $tiendaController = new TiendaController();
        $tiendaController->edit();
        break;

    case 'tiendas.update':
        $authController->requerirPermiso('tiendas.update');
        $tiendaController = new TiendaController();
        $tiendaController->update();
        break;

    case 'tiendas.toggle':
        $authController->requerirPermiso('tiendas.toggle');
        $tiendaController = new TiendaController();
        $tiendaController->toggleEstado();
        break;

    case 'tiendas.destroy':
        $authController->requerirPermiso('tiendas.delete');
        $tiendaController = new TiendaController();
        $tiendaController->destroy();
        break;


    // =========================================================================
    // usuarios
    // =========================================================================




    case 'usuarios.index':
        $authController->requerirPermiso('usuarios.view');
        $usuarioController = new UsuarioController();
        $usuarioController->index();
        break;

    case 'usuarios.create':
        $authController->requerirPermiso('usuarios.create');
        $usuarioController = new UsuarioController();
        $usuarioController->create();
        break;

    case 'usuarios.store':
        $authController->requerirPermiso('usuarios.create');
        $usuarioController = new UsuarioController();
        $usuarioController->store();
        break;

    case 'usuarios.edit':
        $authController->requerirPermiso('usuarios.update');
        $usuarioController = new UsuarioController();
        $usuarioController->edit();
        break;

    case 'usuarios.update':
        $authController->requerirPermiso('usuarios.update');
        $usuarioController = new UsuarioController();
        $usuarioController->update();
        break;

    case 'usuarios.asignar_rol':
        $authController->requerirPermiso('usuarios.roles.assign');
        $usuarioController = new UsuarioController();
        $usuarioController->asignarRol();
        break;

    case 'usuarios.guardar_rol':
        $authController->requerirPermiso('usuarios.roles.assign');
        $usuarioController = new UsuarioController();
        $usuarioController->guardarRol();
        break;

    case 'usuarios.toggle':
        $authController->requerirPermiso('usuarios.toggle');
        $usuarioController = new UsuarioController();
        $usuarioController->toggleEstado();
        break;

    case 'usuarios.destroy':
        $authController->requerirPermiso('usuarios.delete');
        $usuarioController = new UsuarioController();
        $usuarioController->destroy();
        break;

    // =========================================================================
    // categoria 
    // =========================================================================



    case 'categorias.index':
        $authController->requerirPermiso('productos.view');
        $categoriaController = new CategoriaController();
        $categoriaController->index();
        break;

    case 'categorias.create':
        $authController->requerirPermiso('productos.create');
        $categoriaController = new CategoriaController();
        $categoriaController->create();
        break;

    case 'categorias.store':
        $authController->requerirPermiso('productos.create');
        $categoriaController = new CategoriaController();
        $categoriaController->store();
        break;

    case 'categorias.edit':
        $authController->requerirPermiso('productos.update');
        $categoriaController = new CategoriaController();
        $categoriaController->edit();
        break;

    case 'categorias.update':
        $authController->requerirPermiso('productos.update');
        $categoriaController = new CategoriaController();
        $categoriaController->update();
        break;

    case 'categorias.toggle':
        $authController->requerirPermiso('productos.update');
        $categoriaController = new CategoriaController();
        $categoriaController->toggleEstado();
        break;

    case 'categorias.destroy':
        $authController->requerirPermiso('productos.delete');
        $categoriaController = new CategoriaController();
        $categoriaController->destroy();
        break;




    // =========================================================================
    // unidades
    // =========================================================================

    case 'unidades.index':
        $authController->requerirPermiso('productos.view');
        $unidadController = new UnidadMedidaController();
        $unidadController->index();
        break;

    case 'unidades.create':
        $authController->requerirPermiso('productos.create');
        $unidadController = new UnidadMedidaController();
        $unidadController->create();
        break;

    case 'unidades.store':
        $authController->requerirPermiso('productos.create');
        $unidadController = new UnidadMedidaController();
        $unidadController->store();
        break;

    case 'unidades.edit':
        $authController->requerirPermiso('productos.update');
        $unidadController = new UnidadMedidaController();
        $unidadController->edit();
        break;

    case 'unidades.update':
        $authController->requerirPermiso('productos.update');
        $unidadController = new UnidadMedidaController();
        $unidadController->update();
        break;

    case 'unidades.destroy':
        $authController->requerirPermiso('productos.delete');
        $unidadController = new UnidadMedidaController();
        $unidadController->destroy();
        break;



    // =========================================================================
    // inpuestos 
    // =========================================================================



    case 'impuestos.index':
        $authController->requerirPermiso('productos.view');
        $impuestoController = new ImpuestoController();
        $impuestoController->index();
        break;

    case 'impuestos.create':
        $authController->requerirPermiso('productos.create');
        $impuestoController = new ImpuestoController();
        $impuestoController->create();
        break;

    case 'impuestos.store':
        $authController->requerirPermiso('productos.create');
        $impuestoController = new ImpuestoController();
        $impuestoController->store();
        break;

    case 'impuestos.edit':
        $authController->requerirPermiso('productos.update');
        $impuestoController = new ImpuestoController();
        $impuestoController->edit();
        break;

    case 'impuestos.update':
        $authController->requerirPermiso('productos.update');
        $impuestoController = new ImpuestoController();
        $impuestoController->update();
        break;

    case 'impuestos.toggle':
        $authController->requerirPermiso('productos.update');
        $impuestoController = new ImpuestoController();
        $impuestoController->toggleEstado();
        break;

    case 'impuestos.destroy':
        $authController->requerirPermiso('productos.delete');
        $impuestoController = new ImpuestoController();
        $impuestoController->destroy();
        break;


    // =========================================================================
    // productos
    // =========================================================================


    case 'productos.index':
        $authController->requerirPermiso('productos.view');
        $productoController = new ProductoController();
        $productoController->index();
        break;

    case 'productos.create':
        $authController->requerirPermiso('productos.create');
        $productoController = new ProductoController();
        $productoController->create();
        break;

    case 'productos.store':
        $authController->requerirPermiso('productos.create');
        $productoController = new ProductoController();
        $productoController->store();
        break;

    case 'productos.edit':
        $authController->requerirPermiso('productos.update');
        $productoController = new ProductoController();
        $productoController->edit();
        break;

    case 'productos.update':
        $authController->requerirPermiso('productos.update');
        $productoController = new ProductoController();
        $productoController->update();
        break;

    case 'productos.toggle':
        $authController->requerirPermiso('productos.update');
        $productoController = new ProductoController();
        $productoController->toggleEstado();
        break;

    case 'productos.destroy':
        $authController->requerirPermiso('productos.delete');
        $productoController = new ProductoController();
        $productoController->destroy();
        break;

    // =========================================================================
    // Inventario 
    // =========================================================================



    case 'inventario.index':
        $authController->requerirPermiso('inventario.view');
        $inventarioController = new InventarioController();
        $inventarioController->index();
        break;

    case 'inventario.create':
        $authController->requerirPermiso('inventario.move');
        $inventarioController = new InventarioController();
        $inventarioController->create();
        break;

    case 'inventario.store':
        $authController->requerirPermiso('inventario.move');
        $inventarioController = new InventarioController();
        $inventarioController->store();
        break;

    case 'inventario.movimiento':
        $authController->requerirPermiso('inventario.move');
        $inventarioController = new InventarioController();
        $inventarioController->movimiento();
        break;

    case 'inventario.guardar_movimiento':
        $authController->requerirPermiso('inventario.move');
        $inventarioController = new InventarioController();
        $inventarioController->guardarMovimiento();
        break;

    case 'inventario.movimientos':
        $authController->requerirPermiso('inventario.view');
        $inventarioController = new InventarioController();
        $inventarioController->movimientos();
        break;

    case 'inventario.alertas':
        $authController->requerirPermiso('inventario.alerts');
        $inventarioController = new InventarioController();
        $inventarioController->alertas();
        break;


    // =========================================================================
    // Ventas
    // =========================================================================


    case 'ventas.index':
        $authController->requerirPermiso('ventas.view');
        $ventaController = new VentaController();
        $ventaController->index();
        break;

    case 'ventas.create':
        $authController->requerirPermiso('ventas.create');
        $ventaController = new VentaController();
        $ventaController->create();
        break;

    case 'ventas.store':
        $authController->requerirPermiso('ventas.create');
        $ventaController = new VentaController();
        $ventaController->store();
        break;

    case 'ventas.show':
        $authController->requerirPermiso('ventas.view');
        $ventaController = new VentaController();
        $ventaController->show();
        break;

    case 'ventas.anular':
        $authController->requerirPermiso('ventas.cancel');
        $ventaController = new VentaController();
        $ventaController->anular();
        break;


    // =========================================================================
    // CAja
    // =========================================================================


    case 'caja.index':
        $authController->requerirPermiso('caja.view');
        $cajaController = new CajaController();
        $cajaController->index();
        break;

    case 'caja.create':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->create();
        break;

    case 'caja.store':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->store();
        break;

    case 'caja.apertura':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->apertura();
        break;

    case 'caja.abrir':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->abrir();
        break;

    case 'caja.cierre':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->cierre();
        break;

    case 'caja.cerrar':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->cerrar();
        break;

    case 'caja.movimiento':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->movimiento();
        break;

    case 'caja.guardar_movimiento':
        $authController->requerirPermiso('caja.manage');
        $cajaController = new CajaController();
        $cajaController->guardarMovimiento();
        break;

    case 'caja.movimientos':
        $authController->requerirPermiso('caja.view');
        $cajaController = new CajaController();
        $cajaController->movimientos();
        break;




    case 'setup':
        $setupController = new SetupController();
        $setupController->ejecutar();
        break;

    // =========================================================================
    // CLIENTES
    // =========================================================================
    case 'clientes.index':
        $authController->requerirPermiso('ventas.view');
        $clienteController = new ClienteController();
        $clienteController->index();
        break;

    case 'clientes.create':
        $authController->requerirPermiso('ventas.create');
        $clienteController = new ClienteController();
        $clienteController->create();
        break;

    case 'clientes.store':
        $authController->requerirPermiso('ventas.create');
        $clienteController = new ClienteController();
        $clienteController->store();
        break;

    case 'clientes.edit':
        $authController->requerirPermiso('ventas.create');
        $clienteController = new ClienteController();
        $clienteController->edit();
        break;

    case 'clientes.update':
        $authController->requerirPermiso('ventas.create');
        $clienteController = new ClienteController();
        $clienteController->update();
        break;

    case 'clientes.destroy':
        $authController->requerirPermiso('ventas.cancel');
        $clienteController = new ClienteController();
        $clienteController->destroy();
        break;

    // =========================================================================
    // EMPLEADOS
    // =========================================================================
    case 'empleados.index':
        $authController->requerirPermiso('empleados.view');
        $empleadoController = new EmpleadoController();
        $empleadoController->index();
        break;

    case 'empleados.create':
        $authController->requerirPermiso('empleados.manage');
        $empleadoController = new EmpleadoController();
        $empleadoController->create();
        break;

    case 'empleados.store':
        $authController->requerirPermiso('empleados.manage');
        $empleadoController = new EmpleadoController();
        $empleadoController->store();
        break;

    case 'empleados.edit':
        $authController->requerirPermiso('empleados.manage');
        $empleadoController = new EmpleadoController();
        $empleadoController->edit();
        break;

    case 'empleados.update':
        $authController->requerirPermiso('empleados.manage');
        $empleadoController = new EmpleadoController();
        $empleadoController->update();
        break;

    case 'empleados.destroy':
        $authController->requerirPermiso('empleados.manage');
        $empleadoController = new EmpleadoController();
        $empleadoController->destroy();
        break;

    // =========================================================================
    // PROVEEDORES
    // =========================================================================
    case 'proveedores.index':
        $authController->requerirPermiso('productos.view');
        $proveedorController = new ProveedorController();
        $proveedorController->index();
        break;

    case 'proveedores.create':
        $authController->requerirPermiso('productos.create');
        $proveedorController = new ProveedorController();
        $proveedorController->create();
        break;

    case 'proveedores.store':
        $authController->requerirPermiso('productos.create');
        $proveedorController = new ProveedorController();
        $proveedorController->store();
        break;

    case 'proveedores.edit':
        $authController->requerirPermiso('productos.update');
        $proveedorController = new ProveedorController();
        $proveedorController->edit();
        break;

    case 'proveedores.update':
        $authController->requerirPermiso('productos.update');
        $proveedorController = new ProveedorController();
        $proveedorController->update();
        break;

    case 'proveedores.toggle':
        $authController->requerirPermiso('productos.update');
        $proveedorController = new ProveedorController();
        $proveedorController->toggleEstado();
        break;

    case 'proveedores.destroy':
        $authController->requerirPermiso('productos.delete');
        $proveedorController = new ProveedorController();
        $proveedorController->destroy();
        break;

    // =========================================================================
    // CUPONES
    // =========================================================================
    case 'cupones.index':
        $authController->requerirPermiso('ventas.view');
        $cuponController = new CuponController();
        $cuponController->index();
        break;

    case 'cupones.create':
        $authController->requerirPermiso('ventas.create');
        $cuponController = new CuponController();
        $cuponController->create();
        break;

    case 'cupones.store':
        $authController->requerirPermiso('ventas.create');
        $cuponController = new CuponController();
        $cuponController->store();
        break;

    case 'cupones.edit':
        $authController->requerirPermiso('ventas.create');
        $cuponController = new CuponController();
        $cuponController->edit();
        break;

    case 'cupones.update':
        $authController->requerirPermiso('ventas.create');
        $cuponController = new CuponController();
        $cuponController->update();
        break;

    case 'cupones.destroy':
        $authController->requerirPermiso('ventas.cancel');
        $cuponController = new CuponController();
        $cuponController->destroy();
        break;

    case 'cupones.validar':
        $authController->requerirPermiso('ventas.create');
        $cuponController = new CuponController();
        $cuponController->validar();
        break;

    // =========================================================================
    // DEVOLUCIONES
    // =========================================================================
    case 'devoluciones.index':
        $authController->requerirPermiso('ventas.view');
        $devolucionController = new DevolucionController();
        $devolucionController->index();
        break;

    case 'devoluciones.create':
        $authController->requerirPermiso('ventas.cancel');
        $devolucionController = new DevolucionController();
        $devolucionController->create();
        break;

    case 'devoluciones.store':
        $authController->requerirPermiso('ventas.cancel');
        $devolucionController = new DevolucionController();
        $devolucionController->store();
        break;

    case 'devoluciones.show':
        $authController->requerirPermiso('ventas.view');
        $devolucionController = new DevolucionController();
        $devolucionController->show();
        break;

    // =========================================================================
    // REPORTES
    // =========================================================================
    case 'reportes.index':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->index();
        break;

    case 'reportes.ventas':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->ventas();
        break;

    case 'reportes.ventas_por_tienda':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->ventasPorTienda();
        break;

    case 'reportes.productos_mas_vendidos':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->productosMasVendidos();
        break;

    case 'reportes.ventas_por_metodo_pago':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->ventasPorMetodoPago();
        break;

    case 'reportes.inventario':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->inventario();
        break;

    case 'reportes.stock_bajo':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->stockBajo();
        break;

    case 'reportes.movimientos_inventario':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->movimientosInventario();
        break;

    case 'reportes.movimientos_caja':
        $authController->requerirPermiso('reportes.view');
        $reporteController = new ReporteController();
        $reporteController->movimientosCaja();
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/../resources/errors/404.php';
        break;
}

function redirigirDashboardPrincipal(AuthController $authController, bool $ajax = false): void
{
    $usuario = $authController->usuarioActual();
    $rol = $usuario['rol_principal']['rol_nombre'] ?? '';

    $rutas = [
        'Superadministrador' => 'dashboard.superadmin',
        'Administrador de Tienda' => 'dashboard.admin_tienda',
        'Supervisor' => 'dashboard.supervisor',
        'Vendedor' => 'dashboard.vendedor',
        'Bodeguero' => 'dashboard.bodeguero',
        'Reportero' => 'dashboard.reportero',
        'Nómina y RRHH' => 'dashboard.nomina',
        'Cliente' => 'dashboard.cliente',
        'Sistema' => 'dashboard.sistema',
    ];

    $dashboardRoute = $rutas[$rol] ?? 'login';

    if ($ajax) {
        // Si es AJAX, redirigir internamente a la vista del dashboard
        $_GET['route'] = $dashboardRoute;
        $_GET['ajax'] = '1';
        
        // Incluir la vista correspondiente
        switch ($dashboardRoute) {
            case 'dashboard.superadmin':
                require __DIR__ . '/../resources/views/dashboard/superadmin.php';
                break;
            case 'dashboard.admin_tienda':
                require __DIR__ . '/../resources/views/dashboard/admin_tienda.php';
                break;
            case 'dashboard.supervisor':
                require __DIR__ . '/../resources/views/dashboard/supervisor.php';
                break;
            case 'dashboard.vendedor':
                require __DIR__ . '/../resources/views/dashboard/vendedor.php';
                break;
            case 'dashboard.bodeguero':
                require __DIR__ . '/../resources/views/dashboard/bodeguero.php';
                break;
            case 'dashboard.reportero':
                require __DIR__ . '/../resources/views/dashboard/reportero.php';
                break;
            case 'dashboard.nomina':
                require __DIR__ . '/../resources/views/dashboard/nomina.php';
                break;
            case 'dashboard.cliente':
                require __DIR__ . '/../resources/views/dashboard/cliente.php';
                break;
            case 'dashboard.sistema':
                require __DIR__ . '/../resources/views/dashboard/sistema.php';
                break;
        }
    } else {
        header('Location: index.php?route=' . $dashboardRoute);
        exit;
    }
}
