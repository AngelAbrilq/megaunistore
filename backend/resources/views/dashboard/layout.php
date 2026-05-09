<?php

$usuario = $_SESSION['auth'] ?? null;

if ($usuario === null) {
    header('Location: index.php?route=login');
    exit;
}

$nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
$email = $usuario['email'] ?? '';
$rol = $usuario['rol_principal']['rol_nombre'] ?? 'Sin rol';
$tiendaId = $usuario['rol_principal']['tienda_id'] ?? null;

$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? 'Panel principal del sistema.';
$activeMenu = $activeMenu ?? 'dashboard';
$cards = $cards ?? [];
$actions = $actions ?? [];


$permisos = $usuario['permisos'] ?? [];
$rutaActual = $_GET['route'] ?? '';

$dashboardRoutes = [
    'Superadministrador' => 'index.php?route=dashboard.superadmin',
    'Administrador de Tienda' => 'index.php?route=dashboard.admin_tienda',
    'Supervisor' => 'index.php?route=dashboard.supervisor',
    'Vendedor' => 'index.php?route=dashboard.vendedor',
    'Bodeguero' => 'index.php?route=dashboard.bodeguero',
    'Reportero' => 'index.php?route=dashboard.reportero',
    'Nómina y RRHH' => 'index.php?route=dashboard.nomina',
    'Cliente' => 'index.php?route=dashboard.cliente',
    'Sistema' => 'index.php?route=dashboard.sistema',
];

$dashboardUrl = $dashboardRoutes[$rol] ?? 'index.php?route=dashboard';

$menuGroups = [
    'Principal' => [
        [
            'label' => 'Dashboard',
            'url' => $dashboardUrl,
            'permiso' => null,
            'routes' => [
                'dashboard',
                'dashboard.superadmin',
                'dashboard.admin_tienda',
                'dashboard.supervisor',
                'dashboard.vendedor',
                'dashboard.bodeguero',
                'dashboard.reportero',
                'dashboard.nomina',
                'dashboard.cliente',
                'dashboard.sistema',
            ],
        ],
    ],

    'Administración' => [
        [
            'label' => 'Tiendas',
            'url' => 'index.php?route=tiendas.index',
            'permiso' => 'tiendas.view',
            'routes' => ['tiendas.index', 'tiendas.create', 'tiendas.edit'],
        ],
        [
            'label' => 'Usuarios y roles',
            'url' => 'index.php?route=usuarios.index',
            'permiso' => 'usuarios.view',
            'routes' => ['usuarios.index', 'usuarios.create', 'usuarios.edit', 'usuarios.roles'],
        ],
        [
            'label' => 'Empleados',
            'url' => 'index.php?route=empleados.index',
            'permiso' => 'empleados.view',
            'routes' => ['empleados.index', 'empleados.create', 'empleados.edit'],
        ],
        [
            'label' => 'Proveedores',
            'url' => 'index.php?route=proveedores.index',
            'permiso' => 'productos.view',
            'routes' => ['proveedores.index', 'proveedores.create', 'proveedores.edit'],
        ],
    ],

    'Catálogo' => [
        [
            'label' => 'Productos',
            'url' => 'index.php?route=productos.index',
            'permiso' => 'productos.view',
            'routes' => ['productos.index', 'productos.create', 'productos.edit'],
        ],
        [
            'label' => 'Categorías',
            'url' => 'index.php?route=categorias.index',
            'permiso' => 'productos.view',
            'routes' => ['categorias.index', 'categorias.create', 'categorias.edit'],
        ],
        [
            'label' => 'Unidades de medida',
            'url' => 'index.php?route=unidades.index',
            'permiso' => 'productos.view',
            'routes' => ['unidades.index', 'unidades.create', 'unidades.edit'],
        ],
        [
            'label' => 'Impuestos',
            'url' => 'index.php?route=impuestos.index',
            'permiso' => 'productos.view',
            'routes' => ['impuestos.index', 'impuestos.create', 'impuestos.edit'],
        ],
    ],

    'Operación' => [
        [
            'label' => 'Clientes',
            'url' => 'index.php?route=clientes.index',
            'permiso' => 'ventas.view',
            'routes' => ['clientes.index', 'clientes.create', 'clientes.edit'],
        ],
        [
            'label' => 'Inventario',
            'url' => 'index.php?route=inventario.index',
            'permiso' => 'inventario.view',
            'routes' => ['inventario.index'],
        ],
        [
            'label' => 'Registrar inventario',
            'url' => 'index.php?route=inventario.create',
            'permiso' => 'inventario.move',
            'routes' => ['inventario.create'],
        ],
        [
            'label' => 'Movimientos inventario',
            'url' => 'index.php?route=inventario.movimientos',
            'permiso' => 'inventario.view',
            'routes' => ['inventario.movimientos', 'inventario.movimiento'],
        ],
        [
            'label' => 'Alertas de stock',
            'url' => 'index.php?route=inventario.alertas',
            'permiso' => 'inventario.alerts',
            'routes' => ['inventario.alertas'],
        ],
        [
            'label' => 'Ventas',
            'url' => 'index.php?route=ventas.index',
            'permiso' => 'ventas.view',
            'routes' => ['ventas.index', 'ventas.show'],
        ],
        [
            'label' => 'Nueva venta',
            'url' => 'index.php?route=ventas.create',
            'permiso' => 'ventas.create',
            'routes' => ['ventas.create'],
        ],
        [
            'label' => 'Caja',
            'url' => 'index.php?route=caja.index',
            'permiso' => 'caja.view',
            'routes' => ['caja.index'],
        ],
        [
            'label' => 'Movimientos caja',
            'url' => 'index.php?route=caja.movimientos',
            'permiso' => 'caja.view',
            'routes' => ['caja.movimientos'],
        ],
        [
            'label' => 'Nueva caja',
            'url' => 'index.php?route=caja.create',
            'permiso' => 'caja.manage',
            'routes' => ['caja.create'],
        ],
    ],
];


function e_dashboard(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function usuario_puede_ver_menu(?string $permiso, array $permisos): bool
{
    if ($permiso === null) {
        return true;
    }

    return in_array($permiso, $permisos, true);
}

function menu_activo_dashboard(array $item, string $rutaActual): string
{
    $rutas = $item['routes'] ?? [];

    return in_array($rutaActual, $rutas, true) ? 'active' : '';
}



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e_dashboard($pageTitle) ?> | Mega_Uni_Store</title>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #172554;
            --primary-light: #2563eb;
            --bg: #f3f6fb;
            --white: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #dbe3ef;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #dc2626;
            --shadow: 0 18px 48px rgba(15, 23, 42, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 50%, #e0f2fe 100%);
            color: var(--text);
        }

        .layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--primary-dark), var(--primary));
            color: var(--white);
            padding: 28px 22px;
        }

        .brand {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.74);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .profile-box {
            padding: 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            margin-bottom: 24px;
        }

        .profile-box strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .profile-box small {
            display: block;
            color: rgba(255, 255, 255, 0.75);
            word-break: break-word;
            line-height: 1.4;
        }

        .menu {
            display: grid;
            gap: 8px;
        }

        .menu-section-title {
            margin: 16px 6px 4px;
            color: rgba(255, 255, 255, 0.55);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .menu .logout-link {
            margin-top: 14px;
            background: rgba(255, 255, 255, 0.08);
        }

        .menu a,
        .menu span {
            display: block;
            padding: 12px 14px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 14px;
        }

        .menu .active {
            background: rgba(255, 255, 255, 0.16);
            color: var(--white);
            font-weight: 800;
        }

        .menu a:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .content {
            padding: 34px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 28px;
        }

        .topbar h1 {
            margin: 0 0 8px;
            color: var(--primary-dark);
            font-size: clamp(28px, 4vw, 42px);
        }

        .topbar p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            padding: 12px 16px;
            border-radius: 12px;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .logout:hover {
            background: var(--primary-light);
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 22px;
            box-shadow: var(--shadow);
        }

        .card small {
            display: block;
            color: var(--muted);
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .card strong {
            display: block;
            font-size: 22px;
            color: var(--primary-dark);
            word-break: break-word;
        }

        .main-panel {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .main-panel h2 {
            margin: 0 0 10px;
            color: var(--primary-dark);
        }

        .main-panel p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .action-card {
            display: block;
            background: #fbfdff;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            text-decoration: none;
            color: var(--text);
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }

        .action-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary-light);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.12);
        }

        .action-card strong {
            display: block;
            color: var(--primary-dark);
            margin-bottom: 6px;
        }

        .action-card span {
            display: block;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            border-radius: 18px;
            padding: 18px;
            line-height: 1.6;
        }

        code {
            background: #eef2ff;
            padding: 2px 6px;
            border-radius: 7px;
        }

        @media (max-width: 1000px) {

            .cards,
            .actions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-radius: 0 0 24px 24px;
            }

            .topbar {
                flex-direction: column;
            }
        }

        @media (max-width: 620px) {
            .content {
                padding: 24px 16px;
            }

            .cards,
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="layout">
        <aside class="sidebar">
            <div class="brand">Mega_Uni_Store</div>
            <div class="brand-subtitle">
                Sistema multitienda con acceso por roles.
            </div>

            <div class="profile-box">
                <strong><?= e_dashboard($nombreCompleto) ?></strong>
                <small><?= e_dashboard($email) ?></small>
                <small>Rol: <?= e_dashboard($rol) ?></small>
                <?php if ($tiendaId !== null): ?>
                    <small>Tienda ID: <?= e_dashboard((string) $tiendaId) ?></small>
                <?php endif; ?>
            </div>

            <nav class="menu">
                <?php foreach ($menuGroups as $groupTitle => $items): ?>
                    <?php
                    $itemsPermitidos = array_filter($items, function (array $item) use ($permisos): bool {
                        return usuario_puede_ver_menu($item['permiso'], $permisos);
                    });
                    ?>

                    <?php if (empty($itemsPermitidos)): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <div class="menu-section-title"><?= e_dashboard($groupTitle) ?></div>

                    <?php foreach ($itemsPermitidos as $item): ?>
                        <a
                            class="<?= e_dashboard(menu_activo_dashboard($item, $rutaActual)) ?>"
                            href="<?= e_dashboard($item['url']) ?>">
                            <?= e_dashboard($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <a class="logout-link" href="index.php?route=logout">Cerrar sesión</a>
            </nav>
        </aside>

        <section class="content">
            <div class="topbar">
                <div>
                    <h1><?= e_dashboard($pageTitle) ?></h1>
                    <p><?= e_dashboard($pageSubtitle) ?></p>
                </div>

                <a class="logout" href="index.php?route=logout">Salir</a>
            </div>

            <div class="cards">
                <?php foreach ($cards as $card): ?>
                    <article class="card">
                        <small><?= e_dashboard($card['label']) ?></small>
                        <strong><?= e_dashboard($card['value']) ?></strong>
                    </article>
                <?php endforeach; ?>
            </div>

            <section class="main-panel">
                <h2>Acciones principales</h2>
                <p>
                    Estas acciones son la base del módulo. En las siguientes partes conectaremos cada tarjeta
                    con controladores reales, modelos y vistas CRUD.
                </p>
            </section>

            <div class="actions-grid">
                <?php foreach ($actions as $action): ?>
                    <a class="action-card" href="<?= e_dashboard($action['url']) ?>">
                        <strong><?= e_dashboard($action['title']) ?></strong>
                        <span><?= e_dashboard($action['description']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <br>

            <div class="notice">
                Dashboard separado correctamente en <code>backend/resources/views/dashboard/</code>.
                La ruta ya no debe renderizar HTML largo directamente desde <code>routes/web.php</code>.
            </div>
        </section>
    </main>
</body>

</html>