<?php
// Verificar autenticación
if (!isset($_SESSION['auth'])) {
    header('Location: index.php?route=login');
    exit;
}

$usuario    = $_SESSION['auth'];
$rolNombre  = $usuario['rol_principal']['rol_nombre'] ?? 'Usuario';
$nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
$nombreDisplay  = $nombreCompleto ?: ($usuario['email'] ?? 'Usuario');

function e_layout(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// ────────────────────────────────────────────────────────────────────────────
// Helper: determina si el rol está en la lista
// ────────────────────────────────────────────────────────────────────────────
$enRol = fn(array $roles): bool => in_array($rolNombre, $roles, true);

// Grupos de roles por sección
$rolesVentas      = ['Superadministrador', 'Administrador de Tienda', 'Supervisor', 'Vendedor'];
$rolesInventario  = ['Superadministrador', 'Administrador de Tienda', 'Supervisor', 'Bodeguero'];
$rolesCaja        = ['Superadministrador', 'Administrador de Tienda', 'Supervisor', 'Vendedor'];
$rolesReportes    = ['Superadministrador', 'Administrador de Tienda', 'Supervisor', 'Reportero', 'Nómina y RRHH'];
$rolesAdminTienda = ['Superadministrador', 'Administrador de Tienda', 'Nómina y RRHH'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Mega Uni Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        /* ── Layout ── */
        .dashboard-container { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 12px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .sidebar-header h1 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .sidebar-header p  { font-size: 12px; opacity: 0.8; }

        .sidebar-menu { padding: 16px 0; }
        .menu-section { margin-bottom: 24px; }
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
            background: none;
            border-right: none;
            border-top: none;
            border-bottom: none;
            width: 100%;
            font-family: inherit;
            font-size: inherit;
        }
        .menu-item:hover  { background: rgba(255,255,255,0.1); border-left-color: #60a5fa; }
        .menu-item.active { background: rgba(255,255,255,0.15); border-left-color: #60a5fa; font-weight: 600; }
        .menu-item-icon { width: 20px; margin-right: 12px; font-size: 18px; text-align: center; }
        .menu-item-text { font-size: 14px; }

        /* ── Main content ── */
        .main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }

        .top-header {
            background: white;
            padding: 16px 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .page-title { font-size: 24px; font-weight: 700; color: #1e3a8a; }
        .user-info  { display: flex; align-items: center; gap: 12px; }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 16px;
        }
        .user-details { text-align: right; }
        .user-name { font-size: 14px; font-weight: 600; color: #2c3e50; }
        .user-role { font-size: 12px; color: #7f8c8d; }
        .logout-btn {
            padding: 8px 16px; background: #fee2e2; color: #991b1b;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; margin-left: 16px;
        }
        .logout-btn:hover { background: #fecaca; }

        .content-area { flex: 1; padding: 32px; }

        .loading-spinner { display: none; text-align: center; padding: 60px 20px; }
        .loading-spinner.active { display: block; }
        .spinner {
            border: 4px solid #f3f4f6; border-top: 4px solid #1e3a8a;
            border-radius: 50%; width: 50px; height: 50px;
            animation: spin 1s linear infinite; margin: 0 auto 16px;
        }
        @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }

        /* ── Flash messages ── */
        .flash-message {
            padding: 14px 20px; border-radius: 12px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 12px;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-message.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .flash-message.error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .flash-message.info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

        /* ── Modal genérico ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 2000;
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff; border-radius: 20px;
            padding: 32px; max-width: 900px; width: 95%;
            max-height: 92vh; overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            position: relative;
            animation: modalIn 0.2s ease-out;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-close {
            position: absolute; top: 16px; right: 16px;
            background: #f1f5f9; border: none; border-radius: 50%;
            width: 32px; height: 32px; font-size: 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; transition: background 0.15s;
        }
        .modal-close:hover { background: #e2e8f0; }
        .modal-loading { text-align: center; padding: 40px; color: #64748b; }

        /* ── Botón hamburguesa (oculto en escritorio) ── */
        .hamburger-btn {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
            width: 42px; height: 42px;
            border: none; border-radius: 10px;
            background: #eef2ff; cursor: pointer;
            padding: 0 10px; flex-shrink: 0;
        }
        .hamburger-btn span {
            display: block; height: 2.5px; width: 22px;
            background: #1e3a8a; border-radius: 2px;
            transition: all .25s;
        }

        /* ── Overlay del drawer (oculto por defecto) ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15, 23, 42, .5); z-index: 1500;
        }
        .sidebar-overlay.active { display: block; }

        /* ── Responsive: sidebar como drawer deslizable ── */
        @media (max-width: 768px) {
            .hamburger-btn { display: flex; }

            /* El sidebar sale de pantalla y entra como drawer */
            .sidebar {
                width: 264px;
                transform: translateX(-100%);
                transition: transform .28s ease;
            }
            .sidebar.open { transform: translateX(0); }

            /* El contenido ocupa todo el ancho (sin margen del sidebar) */
            .main-content { margin-left: 0; }

            /* Mantener textos visibles dentro del drawer */
            .menu-item-text, .menu-section-title, .sidebar-header p { display: block; }
            .menu-item { justify-content: flex-start; }
        }

        /* ════════════════════════════════════════════════════════════
           UI v2 — Capa de modernización visual (Junio 2026)
           Variables, tipografía Inter y refinamiento de componentes.
           ════════════════════════════════════════════════════════════ */
        :root {
            --ui-bg: #f1f5f9;
            --ui-surface: #ffffff;
            --ui-ink: #0f172a;
            --ui-ink-soft: #475569;
            --ui-brand: #1e3a8a;
            --ui-brand-2: #2563eb;
            --ui-accent: #38bdf8;
            --ui-radius: 16px;
            --ui-shadow: 0 4px 24px rgba(15, 23, 42, .08);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background:
                radial-gradient(1200px 400px at 80% -10%, rgba(37, 99, 235, .06), transparent),
                var(--ui-bg);
            color: var(--ui-ink);
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar refinado: gradiente profundo + items tipo "pill" */
        .sidebar {
            background: linear-gradient(180deg, #0f1b3d 0%, #172554 55%, #1e3a8a 100%);
            box-shadow: 6px 0 24px rgba(2, 6, 23, .25);
        }
        .sidebar-header {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            background: rgba(255, 255, 255, .03);
        }
        .sidebar-header h1 { letter-spacing: -.02em; }
        .sidebar-header p {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            background: rgba(56, 189, 248, .15);
            color: #7dd3fc;
            font-weight: 600;
            opacity: 1;
        }
        .menu-section-title { color: #93c5fd; opacity: .75; }
        .menu-item {
            margin: 2px 10px;
            width: calc(100% - 20px);
            border-radius: 12px;
            border-left: 3px solid transparent;
        }
        .menu-item:hover {
            background: rgba(255, 255, 255, .08);
            border-left-color: var(--ui-accent);
            transform: translateX(2px);
        }
        .menu-item.active {
            background: linear-gradient(90deg, rgba(56, 189, 248, .22), rgba(56, 189, 248, .06));
            border-left-color: var(--ui-accent);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
        }

        /* Topbar: translúcido con blur */
        .top-header {
            background: rgba(255, 255, 255, .85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }
        .page-title {
            background: linear-gradient(90deg, var(--ui-brand), var(--ui-brand-2));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -.02em;
        }
        .user-avatar {
            background: linear-gradient(135deg, var(--ui-brand-2) 0%, #7c3aed 100%);
            box-shadow: 0 2px 8px rgba(37, 99, 235, .35);
        }
        .logout-btn { border-radius: 10px; }
        .logout-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(153, 27, 27, .18); }

        .content-area { padding: 28px 32px 40px; }

        /* Scrollbars suaves en el contenido */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; border: 2px solid var(--ui-bg); }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Spinner acorde a la marca */
        .spinner { border-top-color: var(--ui-brand-2); }

        /* Modal refinado */
        .modal-overlay { background: rgba(15, 23, 42, .55); backdrop-filter: blur(3px); }
        .modal-box { border: 1px solid #e2e8f0; }

        /* Foco visible y accesible en elementos interactivos */
        .menu-item:focus-visible,
        .logout-btn:focus-visible,
        .modal-close:focus-visible {
            outline: 2px solid var(--ui-accent);
            outline-offset: 2px;
        }

        /* ── Móvil (UI v2): topbar compacta, contenido legible ── */
        @media (max-width: 768px) {
            .top-header { padding: 10px 14px; gap: 8px; }
            .page-title { font-size: 17px; }
            .user-details { display: none; }          /* nombre/rol ocultos: el avatar basta */
            .user-avatar { width: 34px; height: 34px; font-size: 14px; }
            .logout-btn { padding: 7px 10px; font-size: 12px; margin-left: 8px; }
            .content-area { padding: 14px 12px 28px; }
            .modal-box { padding: 18px 14px; border-radius: 14px; }
            .menu-item { margin: 2px 6px; width: calc(100% - 12px); }
        }

        @media (max-width: 480px) {
            .page-title { font-size: 15px; max-width: 45vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .content-area { padding: 12px 8px 24px; }
        }

        /* ════════════════════════════════════════════════════════════
           RESPONSIVE GLOBAL — aplica a TODAS las vistas inyectadas en
           #dynamicContent sin tener que tocar cada archivo de módulo.
           ════════════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            /* Cualquier tabla del contenido se vuelve deslizable horizontalmente */
            #dynamicContent table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
                max-width: 100%;
            }
            #dynamicContent table th,
            #dynamicContent table td { white-space: nowrap; }

            /* Rejillas de formularios a una sola columna */
            #dynamicContent [style*="grid-template-columns"],
            #dynamicContent .grid2,
            #dynamicContent .grid3,
            #dynamicContent .grid4,
            #dynamicContent .form-grid,
            #dynamicContent .mf-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }

            /* Encabezados de módulo: apilar título y acciones */
            #dynamicContent .mod-topbar,
            #dynamicContent .module-header,
            #dynamicContent .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            /* Botones cómodos para el dedo, sin desbordar */
            #dynamicContent .btn { padding: 10px 14px; }

            /* Modales a pantalla casi completa */
            .modal-box { max-width: 96%; padding: 18px 14px; }

            /* Imágenes y media nunca desbordan */
            #dynamicContent img { max-width: 100%; height: auto; }
        }

        /* Touch targets mínimos accesibles en móvil */
        @media (max-width: 768px) {
            #dynamicContent .btn,
            #dynamicContent button,
            #dynamicContent select,
            #dynamicContent input { min-height: 40px; }
        }
    </style>
<!-- Chart.js — local tiene prioridad sobre CDN para evitar latencia de red -->
<?php
$_chartLocal = __DIR__ . '/../../../public/assets/js/chart.umd.min.js';
$_chartSrc   = file_exists($_chartLocal)
    ? 'assets/js/chart.umd.min.js'
    : 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
?>
<script src="<?= htmlspecialchars($_chartSrc) ?>"></script>
</head>
<body>
<div class="dashboard-container">

    <!-- ═══════════════════════════════════════════════════════════
         SIDEBAR — condiciones de visibilidad correctas por rol
         ═══════════════════════════════════════════════════════════ -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>🏪 Mega Uni Store</h1>
            <p><?= e_layout($rolNombre) ?></p>
        </div>

        <nav class="sidebar-menu">

            <!-- PRINCIPAL (todos los roles) -->
            <div class="menu-section">
                <div class="menu-section-title">Principal</div>
                <button class="menu-item active" data-route="dashboard">
                    <span class="menu-item-icon">🏠</span>
                    <span class="menu-item-text">Dashboard</span>
                </button>
            </div>

            <!-- VENTAS: Superadmin, Admin Tienda, Supervisor, Vendedor -->
            <?php if ($enRol($rolesVentas)): ?>
            <div class="menu-section">
                <div class="menu-section-title">Ventas</div>
                <button class="menu-item" data-route="ventas.index">
                    <span class="menu-item-icon">💰</span>
                    <span class="menu-item-text">Ventas</span>
                </button>
                <button class="menu-item" data-route="clientes.index">
                    <span class="menu-item-icon">👥</span>
                    <span class="menu-item-text">Clientes</span>
                </button>
                <button class="menu-item" data-route="cupones.index">
                    <span class="menu-item-icon">🎫</span>
                    <span class="menu-item-text">Cupones</span>
                </button>
                <button class="menu-item" data-route="devoluciones.index">
                    <span class="menu-item-icon">🔄</span>
                    <span class="menu-item-text">Devoluciones</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- INVENTARIO: Superadmin, Admin Tienda, Supervisor, Bodeguero -->
            <?php if ($enRol($rolesInventario)): ?>
            <div class="menu-section">
                <div class="menu-section-title">Inventario</div>
                <button class="menu-item" data-route="productos.index">
                    <span class="menu-item-icon">📦</span>
                    <span class="menu-item-text">Productos</span>
                </button>
                <button class="menu-item" data-route="inventario.index">
                    <span class="menu-item-icon">📊</span>
                    <span class="menu-item-text">Inventario</span>
                </button>
                <button class="menu-item" data-route="categorias.index">
                    <span class="menu-item-icon">🏷️</span>
                    <span class="menu-item-text">Categorías</span>
                </button>
                <button class="menu-item" data-route="proveedores.index">
                    <span class="menu-item-icon">🚚</span>
                    <span class="menu-item-text">Proveedores</span>
                </button>
                <button class="menu-item" data-route="compras.index">
                    <span class="menu-item-icon">🛒</span>
                    <span class="menu-item-text">Compras</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- CAJA: Superadmin, Admin Tienda, Supervisor, Vendedor -->
            <?php if ($enRol($rolesCaja)): ?>
            <div class="menu-section">
                <div class="menu-section-title">Caja</div>
                <button class="menu-item" data-route="caja.index">
                    <span class="menu-item-icon">💵</span>
                    <span class="menu-item-text">Cajas</span>
                </button>
                <button class="menu-item" data-route="caja.movimientos">
                    <span class="menu-item-icon">📝</span>
                    <span class="menu-item-text">Movimientos</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- REPORTES: Superadmin, Admin Tienda, Supervisor, Reportero, Nómina y RRHH -->
            <?php if ($enRol($rolesReportes)): ?>
            <div class="menu-section">
                <div class="menu-section-title">Reportes</div>
                <button class="menu-item" data-route="reportes.index">
                    <span class="menu-item-icon">📊</span>
                    <span class="menu-item-text">Reportes Generales</span>
                </button>
                <button class="menu-item" data-route="reportes.ventas">
                    <span class="menu-item-icon">📈</span>
                    <span class="menu-item-text">Reporte de Ventas</span>
                </button>
                <?php if ($enRol(['Superadministrador', 'Administrador de Tienda', 'Supervisor', 'Reportero'])): ?>
                <button class="menu-item" data-route="reportes.inventario">
                    <span class="menu-item-icon">📦</span>
                    <span class="menu-item-text">Reporte Inventario</span>
                </button>
                <button class="menu-item" data-route="reportes.movimientos_caja">
                    <span class="menu-item-icon">💵</span>
                    <span class="menu-item-text">Reporte Caja</span>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>


            <!-- NÓMINA Y RRHH -->
            <?php if ($enRol(['Superadministrador', 'Administrador de Tienda', 'Nómina y RRHH'])): ?>
            <div class="menu-section">
                <div class="menu-section-title">Nómina y RRHH</div>
                <button class="menu-item" data-route="nomina.index">
                    <span class="menu-item-icon">💼</span>
                    <span class="menu-item-text">Nóminas</span>
                </button>
                <button class="menu-item" data-route="contratos.index">
                    <span class="menu-item-icon">📄</span>
                    <span class="menu-item-text">Contratos</span>
                </button>
                <button class="menu-item" data-route="rrhh.horas_extra">
                    <span class="menu-item-icon">⏱️</span>
                    <span class="menu-item-text">Horas Extra</span>
                </button>
                <button class="menu-item" data-route="rrhh.vacaciones">
                    <span class="menu-item-icon">🏖️</span>
                    <span class="menu-item-text">Vacaciones</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- CONTABILIDAD Y FINANZAS -->
            <?php if ($enRol(['Superadministrador', 'Administrador de Tienda'])): ?>
            <div class="menu-section">
                <div class="menu-section-title">Finanzas</div>
                <button class="menu-item" data-route="gastos.index">
                    <span class="menu-item-icon">💸</span>
                    <span class="menu-item-text">Gastos</span>
                </button>
                <button class="menu-item" data-route="contabilidad.cuentas">
                    <span class="menu-item-icon">📒</span>
                    <span class="menu-item-text">Plan de Cuentas</span>
                </button>
                <button class="menu-item" data-route="contabilidad.asientos">
                    <span class="menu-item-icon">📑</span>
                    <span class="menu-item-text">Asientos</span>
                </button>
                <button class="menu-item" data-route="contabilidad.periodos">
                    <span class="menu-item-icon">📅</span>
                    <span class="menu-item-text">Períodos</span>
                </button>
                <button class="menu-item" data-route="contabilidad.centros">
                    <span class="menu-item-icon">🏢</span>
                    <span class="menu-item-text">Centros de Costo</span>
                </button>
                <button class="menu-item" data-route="contabilidad.libro_mayor">
                    <span class="menu-item-icon">📖</span>
                    <span class="menu-item-text">Libro Mayor</span>
                </button>
                <button class="menu-item" data-route="contabilidad.balance">
                    <span class="menu-item-icon">⚖️</span>
                    <span class="menu-item-text">Balance General</span>
                </button>
                <button class="menu-item" data-route="contabilidad.resultados">
                    <span class="menu-item-icon">📈</span>
                    <span class="menu-item-text">Estado de Resultados</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- ADMINISTRACIÓN — ítems mostrados según rol -->
            <?php
            $verEmpleados = $enRol(['Superadministrador', 'Administrador de Tienda', 'Nómina y RRHH']);
            $verUsuarios  = $enRol(['Superadministrador', 'Nómina y RRHH']);
            $verTiendas   = $rolNombre === 'Superadministrador';
            $verSeccionAdmin = $verEmpleados || $verUsuarios || $verTiendas;
            ?>
            <?php if ($verSeccionAdmin): ?>
            <div class="menu-section">
                <div class="menu-section-title">Administración</div>
                <?php if ($verTiendas): ?>
                <button class="menu-item" data-route="tiendas.index">
                    <span class="menu-item-icon">🏪</span>
                    <span class="menu-item-text">Tiendas</span>
                </button>
                <?php endif; ?>
                <?php if ($verUsuarios): ?>
                <button class="menu-item" data-route="usuarios.index">
                    <span class="menu-item-icon">👤</span>
                    <span class="menu-item-text">Usuarios</span>
                </button>
                <?php endif; ?>
                <?php if ($verEmpleados): ?>
                <button class="menu-item" data-route="empleados.index">
                    <span class="menu-item-icon">👔</span>
                    <span class="menu-item-text">Empleados</span>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- MI CUENTA (todos los usuarios autenticados) -->
            <div class="menu-section">
                <div class="menu-section-title">Mi Cuenta</div>
                <button class="menu-item" data-route="password.change">
                    <span class="menu-item-icon">🔑</span>
                    <span class="menu-item-text">Cambiar Contraseña</span>
                </button>
                <?php if ($enRol(['Superadministrador', 'Administrador de Tienda'])): ?>
                <button class="menu-item" data-route="password.requests">
                    <span class="menu-item-icon">📋</span>
                    <span class="menu-item-text">Solicitudes de Contraseña</span>
                </button>
                <?php endif; ?>
            </div>

        </nav>
    </aside>

    <!-- ═══════════════════════════════════════════════════════════
         MAIN CONTENT
         ═══════════════════════════════════════════════════════════ -->
    <!-- Overlay oscuro detrás del drawer en móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="cerrarMenu()"></div>

    <main class="main-content">
        <header class="top-header">
            <button class="hamburger-btn" id="hamburgerBtn" onclick="abrirMenu()" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>
            <h1 class="page-title" id="pageTitle">Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($nombreDisplay, 0, 1)) ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?= e_layout($nombreDisplay) ?></div>
                    <div class="user-role"><?= e_layout($rolNombre) ?></div>
                </div>
                <button class="logout-btn" onclick="window.location.href='index.php?route=logout'">
                    Cerrar Sesión
                </button>
            </div>
        </header>

        <div class="content-area" id="contentArea">
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner"></div>
                <p>Cargando...</p>
            </div>
            <div id="dynamicContent"></div>
        </div>
    </main>


</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL GENÉRICO (reutilizable por todos los módulos)
     ═══════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="globalModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">
        <button class="modal-close" id="modalCloseBtn" aria-label="Cerrar">✕</button>
        <div id="modalBody">
            <div class="modal-loading">Cargando formulario…</div>
        </div>
    </div>
</div>

<script>
// ════════════════════════════════════════════════════════════════
//  MAPA DE TÍTULOS
// ════════════════════════════════════════════════════════════════
const PAGE_TITLES = {
    'dashboard':              'Dashboard',
    'dashboard.superadmin':   'Panel Superadministrador',
    'dashboard.admin_tienda': 'Panel Administrador de Tienda',
    'dashboard.supervisor':   'Panel Supervisor',
    'dashboard.vendedor':     'Panel Vendedor',
    'dashboard.bodeguero':    'Panel Bodeguero',
    'dashboard.reportero':    'Panel Reportero',
    'dashboard.nomina':       'Panel Nómina y RRHH',
    'dashboard.cliente':      'Panel Cliente',
    'dashboard.sistema':      'Panel Sistema',
    'ventas.index':           'Ventas',
    'ventas.create':          'Nueva Venta',
    'clientes.index':         'Clientes',
    'cupones.index':          'Cupones',
    'devoluciones.index':     'Devoluciones',
    'productos.index':        'Productos',
    'inventario.index':       'Inventario',
    'inventario.create':      'Registrar Inventario',
    'inventario.movimiento':  'Movimiento de Inventario',
    'inventario.movimientos': 'Movimientos de Inventario',
    'inventario.alertas':     'Alertas de Stock',
    'devoluciones.index':     'Devoluciones',
    'devoluciones.create':    'Nueva Devolución',
    'devoluciones.show':      'Detalle Devolución',
    'ventas.show':            'Detalle de Venta',
    'categorias.index':       'Categorías',
    'proveedores.index':      'Proveedores',
    'caja.index':             'Cajas',
    'caja.create':            'Nueva Caja',
    'caja.movimiento':        'Movimiento de Caja',
    'caja.apertura':          'Apertura de Caja',
    'caja.cierre':            'Cierre de Caja',
    'caja.movimientos':       'Movimientos de Caja',
    'reportes.index':         'Reportes Generales',
    'reportes.ventas':        'Reporte de Ventas',
    'reportes.inventario':    'Reporte de Inventario',
    'reportes.movimientos_caja': 'Reporte de Caja',
    'tiendas.index':          'Tiendas',
    'usuarios.index':         'Usuarios',
    'empleados.index':        'Empleados',
    'password.change':        'Cambiar Contraseña',
    'password.requests':      'Solicitudes de Contraseña',
    'compras.index':          'Compras a Proveedores',
    'compras.create':         'Nueva Orden de Compra',
    'compras.show':           'Detalle de Compra',
    'gastos.index':           'Gastos Operacionales',
    'gastos.create':          'Nuevo Gasto',
    'gastos.edit':            'Editar Gasto',
    'contabilidad.cuentas':   'Plan de Cuentas',
    'contabilidad.asientos':  'Asientos Contables',
    'contabilidad.asiento.create': 'Nuevo Asiento',
    'contabilidad.asiento.show':   'Detalle de Asiento',
    'contabilidad.periodos':  'Períodos Contables',
    'contabilidad.centros':   'Centros de Costo',
    'contabilidad.libro_mayor': 'Libro Mayor',
    'contabilidad.balance':   'Balance General',
    'contabilidad.resultados': 'Estado de Resultados',
    'rrhh.horas_extra':       'Horas Extra',
    'rrhh.vacaciones':        'Vacaciones y Ausencias',
};

// ════════════════════════════════════════════════════════════════
//  NAVEGACIÓN SPA
// ════════════════════════════════════════════════════════════════
const dynamicContent = document.getElementById('dynamicContent');
const loadingSpinner = document.getElementById('loadingSpinner');
const pageTitleEl    = document.getElementById('pageTitle');
const menuItems      = document.querySelectorAll('.menu-item[data-route]');

async function loadContent(route, pushState = true) {
    // Separar el nombre de ruta de parámetros extra (ej: 'inventario.movimiento&id=1')
    const ampPos     = route.indexOf('&');
    const routeName  = ampPos >= 0 ? route.slice(0, ampPos) : route;
    const extraQuery = ampPos >= 0 ? route.slice(ampPos + 1) : '';

    updateActiveMenu(routeName);
    pageTitleEl.textContent = PAGE_TITLES[routeName] || 'Dashboard';

    loadingSpinner.classList.add('active');
    dynamicContent.style.opacity = '0.4';

    try {
        const fetchUrl = `index.php?route=${encodeURIComponent(routeName)}&ajax=1${extraQuery ? '&' + extraQuery : ''}`;
        const res  = await fetch(fetchUrl);
        const html = await res.text();

        dynamicContent.innerHTML = html;
        executeScripts(dynamicContent);

        if (pushState) {
            history.pushState({ route: routeName }, '', `index.php?route=${route}`);
        }
    } catch (err) {
        console.error('Error al cargar contenido:', err);
        dynamicContent.innerHTML = '<div class="flash-message error">⚠️ Error al cargar el contenido. Intenta de nuevo.</div>';
    } finally {
        loadingSpinner.classList.remove('active');
        dynamicContent.style.opacity = '1';
    }
}

function updateActiveMenu(route) {
    const isDashboard = route === 'dashboard' || route.startsWith('dashboard.');
    menuItems.forEach(item => {
        const itemRoute = item.dataset.route;
        const isMatch   = itemRoute === route
            || (isDashboard && itemRoute === 'dashboard');
        item.classList.toggle('active', isMatch);
    });
}

function executeScripts(container) {
    container.querySelectorAll('script').forEach(oldScript => {
        const s = document.createElement('script');
        if (oldScript.src) {
            s.src = oldScript.src;
        } else {
            s.textContent = oldScript.textContent;
        }
        document.body.appendChild(s);
        document.body.removeChild(s);
    });
}

// Event listeners del menú
menuItems.forEach(item => {
    item.addEventListener('click', e => {
        e.preventDefault();
        loadContent(item.dataset.route);
        cerrarMenu(); // en móvil, cerrar el drawer al navegar
    });
});

// ── Drawer móvil ──
const sidebarEl       = document.querySelector('.sidebar');
const sidebarOverlayEl = document.getElementById('sidebarOverlay');

function abrirMenu() {
    sidebarEl?.classList.add('open');
    sidebarOverlayEl?.classList.add('active');
}
function cerrarMenu() {
    sidebarEl?.classList.remove('open');
    sidebarOverlayEl?.classList.remove('active');
}
window.abrirMenu  = abrirMenu;
window.cerrarMenu = cerrarMenu;

// Cerrar el drawer con Escape o al volver a escritorio
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarMenu(); });
window.addEventListener('resize', () => { if (window.innerWidth > 768) cerrarMenu(); });

// Botón atrás/adelante del navegador
window.addEventListener('popstate', e => {
    if (e.state?.route) loadContent(e.state.route, false);
});

// Carga inicial
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const route  = params.get('route') || 'dashboard';
    // Preservar parámetros extra del URL (id, venta_id, etc.) en recargas y redirects POST
    params.delete('route');
    const extra  = params.toString();
    loadContent(extra ? route + '&' + extra : route, false);
});

// ════════════════════════════════════════════════════════════════
//  MODAL GENÉRICO
//  Uso desde cualquier vista:
//    openModal('index.php?route=productos.create&ajax=1')
//    openModal('index.php?route=productos.edit&id=5&ajax=1')
// ════════════════════════════════════════════════════════════════
const globalModal   = document.getElementById('globalModal');
const modalBody     = document.getElementById('modalBody');
const modalCloseBtn = document.getElementById('modalCloseBtn');

// URL que se pasó a openModal (para recargar el form en caso de error)
let _modalFormUrl = '';

function openModal(url) {
    _modalFormUrl = url;
    modalBody.innerHTML = '<div class="modal-loading">⏳ Cargando formulario…</div>';
    globalModal.classList.add('active');
    document.body.style.overflow = 'hidden';

    fetch(url)
        .then(r => r.text())
        .then(html => {
            modalBody.innerHTML = html;
            executeScripts(modalBody);
            // Interceptar todos los forms dentro del modal
            modalBody.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', e => {
                    e.preventDefault();
                    submitModalForm(form);
                });
            });
        })
        .catch(() => {
            modalBody.innerHTML = '<div class="flash-message error">⚠️ Error al cargar el formulario.</div>';
        });
}

async function submitModalForm(form) {
    // Deshabilitar botón de submit mientras procesa
    const submitBtn = form.querySelector('[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Guardando…'; }

    const formData = new FormData(form);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Modal-Request': '1' },
            body: formData,
        });

        const contentType = res.headers.get('content-type') || '';

        if (contentType.includes('application/json')) {
            const data = await res.json();

            if (data.ok) {
                closeModal();
                // Mostrar mensaje de éxito en el contenido
                showFlash(data.mensaje || 'Operación exitosa', 'success');
                // Recargar el módulo en el que estamos
                const params = new URLSearchParams(window.location.search);
                const currentRoute = params.get('route') || 'dashboard';
                loadContent(currentRoute, false);
            } else {
                // Error: mostrar en el modal sin cerrarlo
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.original || 'Guardar'; }
                showModalError(data.error || 'Error al procesar la solicitud.');
            }
        } else {
            // El controller devolvió HTML (p.ej. validación CSRF fallida)
            const html = await res.text();
            modalBody.innerHTML = html;
            executeScripts(modalBody);
            modalBody.querySelectorAll('form').forEach(f => {
                f.addEventListener('submit', e => { e.preventDefault(); submitModalForm(f); });
            });
        }
    } catch (err) {
        console.error('Error en submitModalForm:', err);
        if (submitBtn) { submitBtn.disabled = false; }
        showModalError('Error de conexión. Intenta de nuevo.');
    }
}

function showModalError(msg) {
    // Remover errores anteriores
    modalBody.querySelectorAll('.modal-form-error').forEach(el => el.remove());
    const div = document.createElement('div');
    div.className = 'flash-message error modal-form-error';
    div.style.marginBottom = '12px';
    div.textContent = msg;
    modalBody.insertBefore(div, modalBody.firstChild);
}

function showFlash(msg, type = 'success') {
    const existing = dynamicContent.querySelector('.spa-flash');
    if (existing) existing.remove();

    const div = document.createElement('div');
    div.className = `flash-message ${type} spa-flash`;
    div.textContent = msg;
    dynamicContent.insertBefore(div, dynamicContent.firstChild);

    setTimeout(() => div.remove(), 4000);
}

function closeModal() {
    globalModal.classList.remove('active');
    document.body.style.overflow = '';
    modalBody.innerHTML = '';
    _modalFormUrl = '';
}

// Preservar texto original de los botones submit para restaurarlos en caso de error
document.addEventListener('click', e => {
    if (e.target.type === 'submit') {
        e.target.dataset.original = e.target.textContent;
    }
});

modalCloseBtn.addEventListener('click', closeModal);
globalModal.addEventListener('click', e => {
    if (e.target === globalModal) closeModal();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && globalModal.classList.contains('active')) closeModal();
});


</script>
</body>
</html>
