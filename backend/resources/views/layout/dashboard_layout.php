<?php
// Verificar autenticación
if (!isset($_SESSION['auth'])) {
    header('Location: index.php?route=login');
    exit;
}

$usuario = $_SESSION['auth'];
$rolNombre = $usuario['rol_principal']['rol_nombre'] ?? 'Usuario';
$tiendaNombre = $usuario['rol_principal']['tienda_nombre'] ?? null;

function e_layout(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Mega_Uni_Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        /* Layout Principal */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 16px 0;
        }

        .menu-section {
            margin-bottom: 24px;
        }

        .menu-section-title {
            padding: 8px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.6;
            margin-bottom: 4px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #60a5fa;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #60a5fa;
            font-weight: 600;
        }

        .menu-item-icon {
            width: 20px;
            margin-right: 12px;
            font-size: 18px;
            text-align: center;
        }

        .menu-item-text {
            font-size: 14px;
        }

        /* Contenido Principal */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header Superior */
        .top-header {
            background: white;
            padding: 16px 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-role {
            font-size: 12px;
            color: #7f8c8d;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #fee2e2;
            color: #991b1b;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 16px;
        }

        .logout-btn:hover {
            background: #fecaca;
        }

        /* Área de Contenido Dinámico */
        .content-area {
            flex: 1;
            padding: 32px;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 60px 20px;
        }

        .loading-spinner.active {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #1e3a8a;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .main-content {
                margin-left: 70px;
            }

            .menu-item-text,
            .menu-section-title,
            .sidebar-header p {
                display: none;
            }

            .sidebar-header h1 {
                font-size: 16px;
            }

            .menu-item {
                justify-content: center;
                padding: 12px 10px;
            }

            .menu-item-icon {
                margin-right: 0;
            }
        }

        /* Mensajes Flash */
        .flash-message {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .flash-message.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .flash-message.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .flash-message.info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>🏪 Mega Uni Store</h1>
                <p>Sistema POS</p>
            </div>

            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Principal</div>
                    <a class="menu-item active" data-route="dashboard">
                        <span class="menu-item-icon">🏠</span>
                        <span class="menu-item-text">Dashboard</span>
                    </a>
                </div>

                <?php if (in_array($rolNombre, ['Superadministrador', 'Administrador de Tienda', 'Supervisor'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Ventas</div>
                    <a class="menu-item" data-route="ventas.index">
                        <span class="menu-item-icon">💰</span>
                        <span class="menu-item-text">Ventas</span>
                    </a>
                    <a class="menu-item" data-route="ventas.create">
                        <span class="menu-item-icon">➕</span>
                        <span class="menu-item-text">Nueva Venta</span>
                    </a>
                    <a class="menu-item" data-route="cupones.index">
                        <span class="menu-item-icon">🎫</span>
                        <span class="menu-item-text">Cupones</span>
                    </a>
                    <a class="menu-item" data-route="devoluciones.index">
                        <span class="menu-item-icon">🔄</span>
                        <span class="menu-item-text">Devoluciones</span>
                    </a>
                    <a class="menu-item" data-route="clientes.index">
                        <span class="menu-item-icon">👥</span>
                        <span class="menu-item-text">Clientes</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (in_array($rolNombre, ['Superadministrador', 'Administrador de Tienda', 'Bodeguero'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Inventario</div>
                    <a class="menu-item" data-route="productos.index">
                        <span class="menu-item-icon">📦</span>
                        <span class="menu-item-text">Productos</span>
                    </a>
                    <a class="menu-item" data-route="inventario.index">
                        <span class="menu-item-icon">📊</span>
                        <span class="menu-item-text">Inventario</span>
                    </a>
                    <a class="menu-item" data-route="categorias.index">
                        <span class="menu-item-icon">🏷️</span>
                        <span class="menu-item-text">Categorías</span>
                    </a>
                    <a class="menu-item" data-route="proveedores.index">
                        <span class="menu-item-icon">🚚</span>
                        <span class="menu-item-text">Proveedores</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (in_array($rolNombre, ['Superadministrador', 'Administrador de Tienda'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Caja</div>
                    <a class="menu-item" data-route="caja.index">
                        <span class="menu-item-icon">💵</span>
                        <span class="menu-item-text">Cajas</span>
                    </a>
                    <a class="menu-item" data-route="caja.movimientos">
                        <span class="menu-item-icon">📝</span>
                        <span class="menu-item-text">Movimientos</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (in_array($rolNombre, ['Superadministrador', 'Reportero'])): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Reportes</div>
                    <a class="menu-item" data-route="reportes.index">
                        <span class="menu-item-icon">📈</span>
                        <span class="menu-item-text">Reportes</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($rolNombre === 'Superadministrador'): ?>
                <div class="menu-section">
                    <div class="menu-section-title">Administración</div>
                    <a class="menu-item" data-route="tiendas.index">
                        <span class="menu-item-icon">🏪</span>
                        <span class="menu-item-text">Tiendas</span>
                    </a>
                    <a class="menu-item" data-route="usuarios.index">
                        <span class="menu-item-icon">👤</span>
                        <span class="menu-item-text">Usuarios</span>
                    </a>
                    <a class="menu-item" data-route="empleados.index">
                        <span class="menu-item-icon">👔</span>
                        <span class="menu-item-text">Empleados</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <!-- Header Superior -->
            <header class="top-header">
                <h1 class="page-title" id="pageTitle">Dashboard</h1>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($usuario['email'], 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= e_layout($usuario['email']) ?></div>
                        <div class="user-role"><?= e_layout($rolNombre) ?></div>
                    </div>
                    <button class="logout-btn" onclick="window.location.href='index.php?route=logout'">
                        Cerrar Sesión
                    </button>
                </div>
            </header>

            <!-- Área de Contenido Dinámico -->
            <div class="content-area" id="contentArea">
                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner"></div>
                    <p>Cargando...</p>
                </div>

                <!-- El contenido se cargará aquí dinámicamente -->
                <div id="dynamicContent">
                    <!-- Contenido inicial del dashboard -->
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sistema de navegación SPA
        const contentArea = document.getElementById('dynamicContent');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const pageTitle = document.getElementById('pageTitle');
        const menuItems = document.querySelectorAll('.menu-item');

        // Cargar contenido dinámicamente
        async function loadContent(route) {
            try {
                // Mostrar loading
                loadingSpinner.classList.add('active');
                contentArea.style.opacity = '0.5';

                // Hacer petición AJAX
                const response = await fetch(`index.php?route=${route}&ajax=1`);
                const html = await response.text();

                // Actualizar contenido
                contentArea.innerHTML = html;

                // Ocultar loading
                loadingSpinner.classList.remove('active');
                contentArea.style.opacity = '1';

                // Actualizar título
                updatePageTitle(route);

                // Actualizar menú activo
                updateActiveMenu(route);

                // Actualizar URL sin recargar
                history.pushState({ route }, '', `index.php?route=${route}`);

                // Ejecutar scripts del contenido cargado
                executeScripts(contentArea);

            } catch (error) {
                console.error('Error al cargar contenido:', error);
                contentArea.innerHTML = '<div class="flash-message error">Error al cargar el contenido. Por favor, intenta de nuevo.</div>';
                loadingSpinner.classList.remove('active');
                contentArea.style.opacity = '1';
            }
        }

        // Actualizar título de página
        function updatePageTitle(route) {
            const titles = {
                'dashboard': 'Dashboard',
                'ventas.index': 'Ventas',
                'ventas.create': 'Nueva Venta',
                'productos.index': 'Productos',
                'inventario.index': 'Inventario',
                'cupones.index': 'Cupones',
                'devoluciones.index': 'Devoluciones',
                'reportes.index': 'Reportes',
                'clientes.index': 'Clientes',
                'categorias.index': 'Categorías',
                'proveedores.index': 'Proveedores',
                'caja.index': 'Cajas',
                'tiendas.index': 'Tiendas',
                'usuarios.index': 'Usuarios',
                'empleados.index': 'Empleados'
            };

            pageTitle.textContent = titles[route] || 'Dashboard';
        }

        // Actualizar menú activo
        function updateActiveMenu(route) {
            menuItems.forEach(item => {
                item.classList.remove('active');
                if (item.dataset.route === route) {
                    item.classList.add('active');
                }
            });
        }

        // Ejecutar scripts del contenido cargado
        function executeScripts(container) {
            const scripts = container.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.body.appendChild(newScript);
                document.body.removeChild(newScript);
            });
        }

        // Event listeners para el menú
        menuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const route = item.dataset.route;
                loadContent(route);
            });
        });

        // Manejar botón atrás/adelante del navegador
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.route) {
                loadContent(e.state.route);
            }
        });

        // Cargar dashboard inicial
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const route = urlParams.get('route') || 'dashboard';
            loadContent(route);
        });
    </script>
</body>
</html>
