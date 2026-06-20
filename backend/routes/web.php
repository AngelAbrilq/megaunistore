<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/controllers/AuthController.php';

$route  = $_GET['route'] ?? 'login';
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

    // =========================================================================
    // Módulo de contraseñas
    // =========================================================================

    case 'password.request':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->mostrarFormularioReset();
        break;

    case 'password.request.post':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->enviarLinkReset();
        break;

    case 'password.reset':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->mostrarFormularioNuevoPassword();
        break;

    case 'password.reset.post':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->aplicarNuevoPassword();
        break;

    case 'password.change':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->mostrarFormularioCambio();
        break;

    case 'password.change.post':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->procesarCambio();
        break;

    case 'password.requests':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->listarSolicitudes();
        break;

    case 'password.approve':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->aprobarSolicitud();
        break;

    case 'password.deny':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->rechazarSolicitud();
        break;

    case 'password.admin.set':
        require_once __DIR__ . '/../app/controllers/PasswordController.php';
        $passwordController = new PasswordController();
        $passwordController->adminSetPassword();
        break;


    // =========================================================================
    // dashboard
    // =========================================================================



    case 'dashboard':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
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
            $dashCtrl   = new DashboardController();
            $dashData   = $dashCtrl->superadmin();
            $kpis       = $dashData['kpis'];
            $chartData  = $dashData['chartData'];
            require __DIR__ . '/../resources/views/dashboard/superadmin.php';
        }
        break;

    case 'dashboard.admin_tienda':
        $authController->requerirRol(['Administrador de Tienda']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            $tiendaId  = (int) ($_SESSION['auth']['rol_principal']['tienda_id'] ?? 0);
            $dashCtrl  = new DashboardController();
            $dashData  = $dashCtrl->adminTienda($tiendaId);
            $kpis      = $dashData['kpis'];
            $chartData = $dashData['chartData'];
            require __DIR__ . '/../resources/views/dashboard/admin_tienda.php';
        }
        break;

    case 'dashboard.supervisor':
        $authController->requerirRol(['Supervisor']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            $tiendaId  = (int) ($_SESSION['auth']['rol_principal']['tienda_id'] ?? 0);
            $dashCtrl  = new DashboardController();
            $dashData  = $dashCtrl->supervisor($tiendaId);
            $kpis      = $dashData['kpis'];
            $chartData = $dashData['chartData'];
            require __DIR__ . '/../resources/views/dashboard/supervisor.php';
        }
        break;

    case 'dashboard.vendedor':
        $authController->requerirRol(['Vendedor']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            $tiendaId   = (int) ($_SESSION['auth']['rol_principal']['tienda_id'] ?? 0);
            $usuarioId  = (int) ($_SESSION['auth']['usuario_id'] ?? 0);
            $dashCtrl   = new DashboardController();
            $dashData   = $dashCtrl->vendedor($tiendaId, $usuarioId);
            $kpis       = $dashData['kpis'];
            $chartData  = $dashData['chartData'];
            require __DIR__ . '/../resources/views/dashboard/vendedor.php';
        }
        break;

    case 'dashboard.bodeguero':
        $authController->requerirRol(['Bodeguero']);
        if (!$isAjax) {
            require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
        } else {
            $tiendaId  = (int) ($_SESSION['auth']['rol_principal']['tienda_id'] ?? 0);
            $dashCtrl  = new DashboardController();
            $dashData  = $dashCtrl->bodeguero($tiendaId);
            $kpis      = $dashData['kpis'];
            $chartData = $dashData['chartData'];
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
        // El cliente va directo al portal de compras
        header('Location: index.php?route=portal.catalogo');
        exit;

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
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->index();
        break;

    case 'tiendas.create':
        $authController->requerirPermiso('tiendas.create');
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->create();
        break;

    case 'tiendas.store':
        $authController->requerirPermiso('tiendas.create');
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->store();
        break;

    case 'tiendas.edit':
        $authController->requerirPermiso('tiendas.update');
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->edit();
        break;

    case 'tiendas.update':
        $authController->requerirPermiso('tiendas.update');
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->update();
        break;

    case 'tiendas.toggle':
        $authController->requerirPermiso('tiendas.toggle');
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->toggleEstado();
        break;

    case 'tiendas.destroy':
        $authController->requerirPermiso('tiendas.delete');
        require_once __DIR__ . '/../app/controllers/TiendaController.php';
        $tiendaController = new TiendaController();
        $tiendaController->destroy();
        break;


    // =========================================================================
    // usuarios
    // =========================================================================




    case 'usuarios.index':
        $authController->requerirPermiso('usuarios.view');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->index();
        break;

    case 'usuarios.create':
        $authController->requerirPermiso('usuarios.create');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->create();
        break;

    case 'usuarios.store':
        $authController->requerirPermiso('usuarios.create');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->store();
        break;

    case 'usuarios.edit':
        $authController->requerirPermiso('usuarios.update');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->edit();
        break;

    case 'usuarios.update':
        $authController->requerirPermiso('usuarios.update');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->update();
        break;

    case 'usuarios.asignar_rol':
        $authController->requerirPermiso('usuarios.roles.assign');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->asignarRol();
        break;

    case 'usuarios.guardar_rol':
        $authController->requerirPermiso('usuarios.roles.assign');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->guardarRol();
        break;

    case 'usuarios.toggle':
        $authController->requerirPermiso('usuarios.toggle');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->toggleEstado();
        break;

    case 'usuarios.destroy':
        $authController->requerirPermiso('usuarios.delete');
        require_once __DIR__ . '/../app/controllers/UsuarioController.php';
        $usuarioController = new UsuarioController();
        $usuarioController->destroy();
        break;

    // =========================================================================
    // categoria 
    // =========================================================================



    case 'categorias.index':
        $authController->requerirPermiso('productos.view');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->index();
        break;

    case 'categorias.create':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->create();
        break;

    case 'categorias.store':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->store();
        break;

    case 'categorias.edit':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->edit();
        break;

    case 'categorias.update':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->update();
        break;

    case 'categorias.toggle':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->toggleEstado();
        break;

    case 'categorias.destroy':
        $authController->requerirPermiso('productos.delete');
        require_once __DIR__ . '/../app/controllers/CategoriaController.php';
        $categoriaController = new CategoriaController();
        $categoriaController->destroy();
        break;




    // =========================================================================
    // unidades
    // =========================================================================

    case 'unidades.index':
        $authController->requerirPermiso('productos.view');
        require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
        $unidadController = new UnidadMedidaController();
        $unidadController->index();
        break;

    case 'unidades.create':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
        $unidadController = new UnidadMedidaController();
        $unidadController->create();
        break;

    case 'unidades.store':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
        $unidadController = new UnidadMedidaController();
        $unidadController->store();
        break;

    case 'unidades.edit':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
        $unidadController = new UnidadMedidaController();
        $unidadController->edit();
        break;

    case 'unidades.update':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
        $unidadController = new UnidadMedidaController();
        $unidadController->update();
        break;

    case 'unidades.destroy':
        $authController->requerirPermiso('productos.delete');
        require_once __DIR__ . '/../app/controllers/UnidadMedidaController.php';
        $unidadController = new UnidadMedidaController();
        $unidadController->destroy();
        break;



    // =========================================================================
    // inpuestos 
    // =========================================================================



    case 'impuestos.index':
        $authController->requerirPermiso('productos.view');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->index();
        break;

    case 'impuestos.create':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->create();
        break;

    case 'impuestos.store':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->store();
        break;

    case 'impuestos.edit':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->edit();
        break;

    case 'impuestos.update':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->update();
        break;

    case 'impuestos.toggle':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->toggleEstado();
        break;

    case 'impuestos.destroy':
        $authController->requerirPermiso('productos.delete');
        require_once __DIR__ . '/../app/controllers/ImpuestoController.php';
        $impuestoController = new ImpuestoController();
        $impuestoController->destroy();
        break;


    // =========================================================================
    // productos
    // =========================================================================


    case 'productos.index':
        $authController->requerirPermiso('productos.view');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->index();
        break;

    case 'productos.create':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->create();
        break;

    case 'productos.store':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->store();
        break;

    case 'productos.edit':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->edit();
        break;

    case 'productos.update':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->update();
        break;

    case 'productos.toggle':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->toggleEstado();
        break;

    case 'productos.destroy':
        $authController->requerirPermiso('productos.delete');
        require_once __DIR__ . '/../app/controllers/ProductoController.php';
        $productoController = new ProductoController();
        $productoController->destroy();
        break;

    // =========================================================================
    // Inventario 
    // =========================================================================



    case 'inventario.index':
        $authController->requerirPermiso('inventario.view');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->index();
        break;

    case 'inventario.create':
        $authController->requerirPermiso('inventario.move');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->create();
        break;

    case 'inventario.store':
        $authController->requerirPermiso('inventario.move');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->store();
        break;

    case 'inventario.movimiento':
        $authController->requerirPermiso('inventario.move');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->movimiento();
        break;

    case 'inventario.guardar_movimiento':
        $authController->requerirPermiso('inventario.move');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->guardarMovimiento();
        break;

    case 'inventario.movimientos':
        $authController->requerirPermiso('inventario.view');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->movimientos();
        break;

    case 'inventario.alertas':
        $authController->requerirPermiso('inventario.alerts');
        require_once __DIR__ . '/../app/controllers/InventarioController.php';
        $inventarioController = new InventarioController();
        $inventarioController->alertas();
        break;


    // =========================================================================
    // Ventas
    // =========================================================================


    case 'ventas.index':
        $authController->requerirPermiso('ventas.view');
        require_once __DIR__ . '/../app/controllers/VentaController.php';
        $ventaController = new VentaController();
        $ventaController->index();
        break;

    case 'ventas.create':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/VentaController.php';
        $ventaController = new VentaController();
        $ventaController->create();
        break;

    case 'ventas.store':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/VentaController.php';
        $ventaController = new VentaController();
        $ventaController->store();
        break;

    case 'ventas.show':
        $authController->requerirPermiso('ventas.view');
        require_once __DIR__ . '/../app/controllers/VentaController.php';
        $ventaController = new VentaController();
        $ventaController->show();
        break;

    case 'ventas.anular':
        $authController->requerirPermiso('ventas.cancel');
        require_once __DIR__ . '/../app/controllers/VentaController.php';
        $ventaController = new VentaController();
        $ventaController->anular();
        break;


    // =========================================================================
    // CAja
    // =========================================================================


    case 'caja.index':
        $authController->requerirPermiso('caja.view');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->index();
        break;

    case 'caja.create':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->create();
        break;

    case 'caja.store':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->store();
        break;

    case 'caja.apertura':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->apertura();
        break;

    case 'caja.abrir':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->abrir();
        break;

    case 'caja.cierre':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->cierre();
        break;

    case 'caja.cerrar':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->cerrar();
        break;

    case 'caja.movimiento':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->movimiento();
        break;

    case 'caja.guardar_movimiento':
        $authController->requerirPermiso('caja.manage');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->guardarMovimiento();
        break;

    case 'caja.movimientos':
        $authController->requerirPermiso('caja.view');
        require_once __DIR__ . '/../app/controllers/CajaController.php';
        $cajaController = new CajaController();
        $cajaController->movimientos();
        break;




    case 'setup':
        require_once __DIR__ . '/../app/controllers/SetupController.php';
        $setupController = new SetupController();
        $setupController->ejecutar();
        break;

    // =========================================================================
    // CLIENTES
    // =========================================================================
    case 'clientes.index':
        $authController->requerirPermiso('ventas.view');
        require_once __DIR__ . '/../app/controllers/ClienteController.php';
        $clienteController = new ClienteController();
        $clienteController->index();
        break;

    case 'clientes.create':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/ClienteController.php';
        $clienteController = new ClienteController();
        $clienteController->create();
        break;

    case 'clientes.store':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/ClienteController.php';
        $clienteController = new ClienteController();
        $clienteController->store();
        break;

    case 'clientes.edit':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/ClienteController.php';
        $clienteController = new ClienteController();
        $clienteController->edit();
        break;

    case 'clientes.update':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/ClienteController.php';
        $clienteController = new ClienteController();
        $clienteController->update();
        break;

    case 'clientes.destroy':
        $authController->requerirPermiso('ventas.cancel');
        require_once __DIR__ . '/../app/controllers/ClienteController.php';
        $clienteController = new ClienteController();
        $clienteController->destroy();
        break;

    // =========================================================================
    // EMPLEADOS
    // =========================================================================
    case 'empleados.index':
        $authController->requerirPermiso('empleados.view');
        require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
        $empleadoController = new EmpleadoController();
        $empleadoController->index();
        break;

    case 'empleados.create':
        $authController->requerirPermiso('empleados.manage');
        require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
        $empleadoController = new EmpleadoController();
        $empleadoController->create();
        break;

    case 'empleados.store':
        $authController->requerirPermiso('empleados.manage');
        require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
        $empleadoController = new EmpleadoController();
        $empleadoController->store();
        break;

    case 'empleados.edit':
        $authController->requerirPermiso('empleados.manage');
        require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
        $empleadoController = new EmpleadoController();
        $empleadoController->edit();
        break;

    case 'empleados.update':
        $authController->requerirPermiso('empleados.manage');
        require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
        $empleadoController = new EmpleadoController();
        $empleadoController->update();
        break;

    case 'empleados.destroy':
        $authController->requerirPermiso('empleados.manage');
        require_once __DIR__ . '/../app/controllers/EmpleadoController.php';
        $empleadoController = new EmpleadoController();
        $empleadoController->destroy();
        break;

    // =========================================================================
    // PROVEEDORES
    // =========================================================================
    case 'proveedores.index':
        $authController->requerirPermiso('productos.view');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->index();
        break;

    case 'proveedores.create':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->create();
        break;

    case 'proveedores.store':
        $authController->requerirPermiso('productos.create');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->store();
        break;

    case 'proveedores.edit':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->edit();
        break;

    case 'proveedores.update':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->update();
        break;

    case 'proveedores.toggle':
        $authController->requerirPermiso('productos.update');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->toggleEstado();
        break;

    case 'proveedores.destroy':
        $authController->requerirPermiso('productos.delete');
        require_once __DIR__ . '/../app/controllers/ProveedorController.php';
        $proveedorController = new ProveedorController();
        $proveedorController->destroy();
        break;

    // =========================================================================
    // CUPONES
    // =========================================================================
    case 'cupones.index':
        $authController->requerirPermiso('ventas.view');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->index();
        break;

    case 'cupones.create':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->create();
        break;

    case 'cupones.store':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->store();
        break;

    case 'cupones.edit':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->edit();
        break;

    case 'cupones.update':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->update();
        break;

    case 'cupones.destroy':
        $authController->requerirPermiso('ventas.cancel');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->destroy();
        break;

    case 'cupones.validar':
        $authController->requerirPermiso('ventas.create');
        require_once __DIR__ . '/../app/controllers/CuponController.php';
        $cuponController = new CuponController();
        $cuponController->validar();
        break;

    // =========================================================================
    // DEVOLUCIONES
    // =========================================================================
    case 'devoluciones.index':
        $authController->requerirPermiso('ventas.view');
        require_once __DIR__ . '/../app/controllers/DevolucionController.php';
        $devolucionController = new DevolucionController();
        $devolucionController->index();
        break;

    case 'devoluciones.create':
        $authController->requerirPermiso('ventas.cancel');
        require_once __DIR__ . '/../app/controllers/DevolucionController.php';
        $devolucionController = new DevolucionController();
        $devolucionController->create();
        break;

    case 'devoluciones.store':
        $authController->requerirPermiso('ventas.cancel');
        require_once __DIR__ . '/../app/controllers/DevolucionController.php';
        $devolucionController = new DevolucionController();
        $devolucionController->store();
        break;

    case 'devoluciones.show':
        $authController->requerirPermiso('ventas.view');
        require_once __DIR__ . '/../app/controllers/DevolucionController.php';
        $devolucionController = new DevolucionController();
        $devolucionController->show();
        break;

    // =========================================================================
    // REPORTES
    // =========================================================================
    case 'reportes.index':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->index();
        break;

    case 'reportes.ventas':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->ventas();
        break;

    case 'reportes.ventas_por_tienda':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->ventasPorTienda();
        break;

    case 'reportes.productos_mas_vendidos':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->productosMasVendidos();
        break;

    case 'reportes.ventas_por_metodo_pago':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->ventasPorMetodoPago();
        break;

    case 'reportes.inventario':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->inventario();
        break;

    case 'reportes.stock_bajo':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->stockBajo();
        break;

    case 'reportes.movimientos_inventario':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->movimientosInventario();
        break;

    case 'reportes.movimientos_caja':
        $authController->requerirPermiso('reportes.view');
        require_once __DIR__ . '/../app/controllers/ReporteController.php';
        $reporteController = new ReporteController();
        $reporteController->movimientosCaja();
        break;

    // =========================================================================
    // AUDITORÍA
    // =========================================================================

    case 'auditoria.index':
        $authController->requerirPermiso('auditoria.view');
        require_once __DIR__ . '/../app/controllers/AuditoriaController.php';
        $auditoriaController = new AuditoriaController();
        $auditoriaController->index();
        break;

    // =========================================================================
    // MÉTODOS DE PAGO
    // =========================================================================

    case 'metodos_pago.index':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->index();
        break;

    case 'metodos_pago.create':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->create();
        break;

    case 'metodos_pago.store':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->store();
        break;

    case 'metodos_pago.edit':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->edit();
        break;

    case 'metodos_pago.update':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->update();
        break;

    case 'metodos_pago.toggle':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->toggle();
        break;

    case 'metodos_pago.destroy':
        $authController->requerirRol(['Superadministrador', 'Administrador de Tienda']);
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->destroy();
        break;

    case 'metodos_pago.exportar':
        $authController->requerirPermiso('reportes.export');
        require_once __DIR__ . '/../app/controllers/MetodoPagoController.php';
        (new MetodoPagoController())->exportar();
        break;

    // =========================================================================
    // NOTIFICACIONES
    // =========================================================================

    case 'notificaciones.index':
        $authController->requerirPermiso('notificaciones.manage');
        require_once __DIR__ . '/../app/controllers/NotificacionController.php';
        (new NotificacionController())->index();
        break;

    case 'notificaciones.marcar_leida':
        $authController->requerirPermiso('notificaciones.manage');
        require_once __DIR__ . '/../app/controllers/NotificacionController.php';
        (new NotificacionController())->marcarLeida();
        break;

    case 'notificaciones.marcar_todas':
        $authController->requerirPermiso('notificaciones.manage');
        require_once __DIR__ . '/../app/controllers/NotificacionController.php';
        (new NotificacionController())->marcarTodas();
        break;

    case 'notificaciones.eliminar':
        $authController->requerirPermiso('notificaciones.manage');
        require_once __DIR__ . '/../app/controllers/NotificacionController.php';
        (new NotificacionController())->eliminar();
        break;

    case 'notificaciones.limpiar':
        $authController->requerirPermiso('notificaciones.manage');
        require_once __DIR__ . '/../app/controllers/NotificacionController.php';
        (new NotificacionController())->limpiar();
        break;

    case 'notificaciones.exportar':
        $authController->requerirPermiso('notificaciones.manage');
        require_once __DIR__ . '/../app/controllers/NotificacionController.php';
        (new NotificacionController())->exportar();
        break;

    // =========================================================================
    // BACKUPS / EXPORTACIONES DEL SISTEMA
    // =========================================================================

    case 'backups.index':
        $authController->requerirPermiso('backups.manage');
        require_once __DIR__ . '/../app/controllers/BackupController.php';
        (new BackupController())->index();
        break;

    case 'backups.exportar':
        $authController->requerirPermiso('backups.manage');
        require_once __DIR__ . '/../app/controllers/BackupController.php';
        (new BackupController())->exportar();
        break;

    case 'backups.limpiar_historial':
        $authController->requerirPermiso('backups.manage');
        require_once __DIR__ . '/../app/controllers/BackupController.php';
        (new BackupController())->limpiarHistorial();
        break;

    // =========================================================================
    // MÓDULO NÓMINA
    // =========================================================================

    case 'nomina.index':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->index();
        break;

    case 'nomina.create':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->create();
        break;

    case 'nomina.store':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->store();
        break;

    case 'nomina.show':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->show();
        break;

    case 'nomina.calcular':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->calcular();
        break;

    case 'nomina.aprobar':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->aprobar();
        break;

    case 'nomina.pagar':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/NominaController.php';
        $ctrl = new NominaController();
        $ctrl->pagar();
        break;

    // =========================================================================
    // MÓDULO CONTRATOS
    // =========================================================================

    case 'contratos.index':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->index();
        break;

    case 'contratos.create':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->create();
        break;

    case 'contratos.store':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->store();
        break;

    case 'contratos.edit':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->edit();
        break;

    case 'contratos.update':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->update();
        break;

    case 'contratos.terminar':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->terminar();
        break;

    case 'contratos.cargos':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->cargos();
        break;

    case 'contratos.cargo.create':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        $csrfToken = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
        require __DIR__ . '/../resources/views/contratos/cargo_create.php';
        break;

    case 'contratos.cargo.store':
        $authController->requerirRol(['Superadministrador','Administrador de Tienda','Nómina y RRHH']);
        require_once __DIR__ . '/../app/controllers/ContratoController.php';
        $ctrl = new ContratoController();
        $ctrl->cargoStore();
        break;

    // =========================================================================
    // PORTAL CLIENTE
    // =========================================================================

    case 'portal':
    case 'portal.catalogo':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->catalogo();
        break;

    case 'portal.producto':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->producto();
        break;

    case 'portal.carrito':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->carritoVer();
        break;

    case 'portal.carrito.agregar':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->carritoAgregar();
        break;

    case 'portal.carrito.actualizar':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->carritoActualizar();
        break;

    case 'portal.carrito.vaciar':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->carritoVaciar();
        break;

    case 'portal.checkout':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->checkout();
        break;

    case 'portal.checkout.post':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->checkoutPost();
        break;

    case 'portal.pedidos':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->pedidos();
        break;

    case 'portal.pedido':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->pedido();
        break;

    case 'portal.perfil':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->perfil();
        break;

    case 'portal.perfil.post':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->perfilPost();
        break;

    case 'portal.wishlist':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->wishlistVer();
        break;

    case 'portal.wishlist.toggle':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->wishlistToggle();
        break;

    case 'portal.valorar':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->valorarForm();
        break;

    case 'portal.valorar.post':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->valorarPost();
        break;

    case 'portal.cupon.validar':
        require_once __DIR__ . '/../app/controllers/PortalController.php';
        $portalCtrl = new PortalController();
        $portalCtrl->cuponValidar();
        break;

    case 'portal.cupon.quitar':
        unset($_SESSION['cupon']);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;

    // =========================================================================
    // COMPRAS A PROVEEDORES (BD-COM-001, CF-INT-013)
    // =========================================================================
    case 'compras.index':
        $authController->requerirPermiso('compras.view');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->index();
        break;

    case 'compras.create':
        $authController->requerirPermiso('compras.manage');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->create();
        break;

    case 'compras.store':
        $authController->requerirPermiso('compras.manage');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->store();
        break;

    case 'compras.show':
        $authController->requerirPermiso('compras.view');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->show();
        break;

    case 'compras.recibir':
        $authController->requerirPermiso('compras.manage');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->recibir();
        break;

    case 'compras.cancelar':
        $authController->requerirPermiso('compras.manage');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->cancelar();
        break;

    case 'compras.destroy':
        $authController->requerirPermiso('compras.manage');
        require_once __DIR__ . '/../app/controllers/CompraController.php';
        (new CompraController())->destroy();
        break;

    // =========================================================================
    // GASTOS OPERACIONALES (CF-CON-006, REQ-7.8.2)
    // =========================================================================
    case 'gastos.index':
        $authController->requerirPermiso('gastos.view');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->index();
        break;

    case 'gastos.create':
        $authController->requerirPermiso('gastos.manage');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->create();
        break;

    case 'gastos.store':
        $authController->requerirPermiso('gastos.manage');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->store();
        break;

    case 'gastos.edit':
        $authController->requerirPermiso('gastos.manage');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->edit();
        break;

    case 'gastos.update':
        $authController->requerirPermiso('gastos.manage');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->update();
        break;

    case 'gastos.estado':
        $authController->requerirPermiso('gastos.manage');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->cambiarEstado();
        break;

    case 'gastos.destroy':
        $authController->requerirPermiso('gastos.manage');
        require_once __DIR__ . '/../app/controllers/GastoController.php';
        (new GastoController())->destroy();
        break;

    // =========================================================================
    // CONTABILIDAD Y FINANZAS (CF-CON-001..011, REQ-7.8)
    // =========================================================================
    case 'contabilidad.cuentas':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->cuentas();
        break;

    case 'contabilidad.cuenta.store':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->cuentaStore();
        break;

    case 'contabilidad.cuenta.toggle':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->cuentaToggle();
        break;

    case 'contabilidad.asientos':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->asientos();
        break;

    case 'contabilidad.asiento.create':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->asientoCreate();
        break;

    case 'contabilidad.asiento.store':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->asientoStore();
        break;

    case 'contabilidad.asiento.show':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->asientoShow();
        break;

    case 'contabilidad.asiento.estado':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->asientoCambiarEstado();
        break;

    case 'contabilidad.periodos':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->periodos();
        break;

    case 'contabilidad.periodo.store':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->periodoStore();
        break;

    case 'contabilidad.periodo.cerrar':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->periodoCerrar();
        break;

    case 'contabilidad.periodo.reabrir':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->periodoReabrir();
        break;

    case 'contabilidad.centros':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->centros();
        break;

    case 'contabilidad.centro.store':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->centroStore();
        break;

    case 'contabilidad.centro.toggle':
        $authController->requerirPermiso('contabilidad.manage');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->centroToggle();
        break;

    case 'contabilidad.libro_mayor':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->libroMayor();
        break;

    case 'contabilidad.balance':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->balance();
        break;

    case 'contabilidad.resultados':
        $authController->requerirPermiso('contabilidad.view');
        require_once __DIR__ . '/../app/controllers/ContabilidadController.php';
        (new ContabilidadController())->estadoResultados();
        break;

    // =========================================================================
    // RRHH AVANZADO: HORAS EXTRA Y VACACIONES (NR-NOM-005/006)
    // =========================================================================
    case 'rrhh.horas_extra':
        $authController->requerirPermiso('rrhh.view');
        require_once __DIR__ . '/../app/controllers/RrhhController.php';
        (new RrhhController())->horasExtra();
        break;

    case 'rrhh.horas_extra.store':
        $authController->requerirPermiso('rrhh.manage');
        require_once __DIR__ . '/../app/controllers/RrhhController.php';
        (new RrhhController())->horaExtraStore();
        break;

    case 'rrhh.horas_extra.estado':
        $authController->requerirPermiso('rrhh.manage');
        require_once __DIR__ . '/../app/controllers/RrhhController.php';
        (new RrhhController())->horaExtraEstado();
        break;

    case 'rrhh.vacaciones':
        $authController->requerirPermiso('rrhh.view');
        require_once __DIR__ . '/../app/controllers/RrhhController.php';
        (new RrhhController())->vacaciones();
        break;

    case 'rrhh.vacaciones.store':
        $authController->requerirPermiso('rrhh.manage');
        require_once __DIR__ . '/../app/controllers/RrhhController.php';
        (new RrhhController())->vacacionStore();
        break;

    case 'rrhh.vacaciones.estado':
        $authController->requerirPermiso('rrhh.manage');
        require_once __DIR__ . '/../app/controllers/RrhhController.php';
        (new RrhhController())->vacacionEstado();
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/../resources/errors/404.php';
        break;
}

function redirigirDashboardPrincipal(AuthController $authController, bool $ajax = false): void
{
    $usuario = $authController->usuarioActual();
    $rol     = $usuario['rol_principal']['rol_nombre'] ?? '';

    $rutas = AuthController::mapaRolDashboard();

    $dashboardRoute = $rutas[$rol] ?? 'login';

    if ($ajax) {
        // Redirigir internamente a la vista del dashboard correspondiente
        $_GET['route'] = $dashboardRoute;
        $_GET['ajax']  = '1';

        $vista   = str_replace('dashboard.', '', $dashboardRoute);
        $archivo = __DIR__ . '/../resources/views/dashboard/' . $vista . '.php';

        $tiendaId  = (int) ($_SESSION['auth']['rol_principal']['tienda_id'] ?? 0);
        $usuarioId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

        $dashCtrl = new DashboardController();

        switch ($rol) {
            case 'Superadministrador':
                $dashData  = $dashCtrl->superadmin();
                $kpis      = $dashData['kpis'];
                $chartData = $dashData['chartData'];
                break;
            case 'Administrador de Tienda':
                $dashData  = $dashCtrl->adminTienda($tiendaId);
                $kpis      = $dashData['kpis'];
                $chartData = $dashData['chartData'];
                break;
            case 'Supervisor':
                $dashData  = $dashCtrl->supervisor($tiendaId);
                $kpis      = $dashData['kpis'];
                $chartData = $dashData['chartData'];
                break;
            case 'Vendedor':
                $dashData  = $dashCtrl->vendedor($tiendaId, $usuarioId);
                $kpis      = $dashData['kpis'];
                $chartData = $dashData['chartData'];
                break;
            case 'Bodeguero':
                $dashData  = $dashCtrl->bodeguero($tiendaId);
                $kpis      = $dashData['kpis'];
                $chartData = $dashData['chartData'];
                break;
            // Reportero, Nómina y RRHH, Cliente, Sistema — vistas auto-contenidas sin KPIs
            default:
                break;
        }

        if (file_exists($archivo)) {
            require $archivo;
        }
    } else {
        header('Location: index.php?route=' . $dashboardRoute);
        exit;
    }
}

// ===========================================================================
// NOTA: Las rutas del portal se agregaron fuera del switch original para
// evitar problemas de parsing. El switch de abajo es complementario.
// Ver el switch principal para las demás rutas.
// ===========================================================================
