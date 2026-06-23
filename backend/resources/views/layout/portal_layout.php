<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Tienda') ?> — Mega Uni Store</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:   #2563eb;
            --primary-d: #1d4ed8;
            --accent:    #f59e0b;
            --success:   #16a34a;
            --danger:    #dc2626;
            --gray-50:   #f9fafb;
            --gray-100:  #f3f4f6;
            --gray-200:  #e5e7eb;
            --gray-400:  #9ca3af;
            --gray-600:  #4b5563;
            --gray-800:  #1f2937;
            --white:     #ffffff;
            --radius:    10px;
            --shadow:    0 2px 12px rgba(0,0,0,.08);
        }

        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--gray-50); color: var(--gray-800); min-height: 100vh; display: flex; flex-direction: column; }

        /* ── HEADER ── */
        .portal-header {
            position: sticky; top: 0; z-index: 100;
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .header-inner {
            max-width: 1280px; margin: 0 auto;
            padding: 0 24px;
            height: 68px;
            display: flex; align-items: center; gap: 20px;
        }
        .header-logo {
            font-size: 22px; font-weight: 800;
            color: var(--primary); text-decoration: none;
            white-space: nowrap;
        }
        .header-logo span { color: var(--accent); }

        .header-search {
            flex: 1;
            display: flex; align-items: center;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 50px;
            padding: 0 16px;
            gap: 10px;
            max-width: 540px;
        }
        .header-search input {
            flex: 1; border: none; background: transparent;
            padding: 10px 0; font-size: 14px; outline: none; color: var(--gray-800);
        }
        .header-search button {
            background: none; border: none; cursor: pointer;
            color: var(--gray-400); font-size: 18px; padding: 0;
        }

        .header-actions { display: flex; align-items: center; gap: 8px; }

        .btn-icon {
            position: relative;
            background: none; border: none; cursor: pointer;
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: var(--gray-600);
            transition: background .15s;
            text-decoration: none;
        }
        .btn-icon:hover { background: var(--gray-100); }
        .badge {
            position: absolute; top: 4px; right: 4px;
            background: var(--danger); color: #fff;
            font-size: 10px; font-weight: 700;
            width: 18px; height: 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }

        .user-menu { position: relative; }
        .user-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: 50px;
            background: var(--gray-100); border: 1px solid var(--gray-200);
            cursor: pointer; font-size: 14px; color: var(--gray-800);
            transition: background .15s;
        }
        .user-btn:hover { background: var(--gray-200); }
        .user-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
        }
        .dropdown {
            display: none; position: absolute; top: calc(100% + 8px); right: 0;
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: var(--radius); box-shadow: var(--shadow);
            min-width: 180px; overflow: hidden; z-index: 200;
        }
        .dropdown.open { display: block; }
        .dropdown a {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; text-decoration: none;
            color: var(--gray-800); font-size: 14px;
            transition: background .12s;
        }
        .dropdown a:hover { background: var(--gray-50); }
        .dropdown a.danger { color: var(--danger); }
        .dropdown-divider { border: none; border-top: 1px solid var(--gray-200); }

        /* ── FLASH ── */
        .flash-bar {
            padding: 12px 24px;
            text-align: center;
            font-size: 14px; font-weight: 500;
        }
        .flash-bar.success { background: #dcfce7; color: #15803d; }
        .flash-bar.error   { background: #fee2e2; color: #991b1b; }

        /* ── CONTENT ── */
        .portal-main { flex: 1; max-width: 1280px; width: 100%; margin: 0 auto; padding: 32px 24px; }

        /* ── FOOTER ── */
        .portal-footer {
            background: var(--gray-800); color: var(--gray-400);
            text-align: center; padding: 24px;
            font-size: 13px; margin-top: auto;
        }
        .portal-footer a { color: var(--gray-400); text-decoration: none; }

        /* ── UTILIDADES ── */
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: var(--radius);
            font-size: 14px; font-weight: 600; cursor: pointer;
            border: none; text-decoration: none; transition: all .15s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-d); }
        .btn-outline { background: transparent; border: 1.5px solid var(--primary); color: var(--primary); }
        .btn-outline:hover { background: var(--primary); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-sm { padding: 6px 14px; font-size: 13px; }

        .card {
            background: var(--white); border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
        }

        /* ── PRODUCT GRID ── */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: var(--white); border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
            display: flex; flex-direction: column;
            transition: transform .2s, box-shadow .2s;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }

        .product-img {
            aspect-ratio: 1; overflow: hidden;
            background: var(--gray-100);
            display: flex; align-items: center; justify-content: center;
        }
        .product-img img { width: 100%; height: 100%; object-fit: cover; }
        .product-img-placeholder { font-size: 56px; }

        .product-body { padding: 14px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
        .product-category { font-size: 11px; color: var(--gray-400); text-transform: uppercase; letter-spacing: .5px; }
        .product-name { font-size: 15px; font-weight: 600; color: var(--gray-800); line-height: 1.3; }
        .product-price { font-size: 20px; font-weight: 800; color: var(--primary); margin-top: auto; }
        .product-stock { font-size: 12px; }
        .product-stock.ok { color: var(--success); }
        .product-stock.low { color: var(--accent); }
        .product-stock.out { color: var(--danger); }

        .product-actions { padding: 12px 14px; border-top: 1px solid var(--gray-100); display: flex; gap: 8px; }

        /* ── BREADCRUMB ── */
        .breadcrumb { display: flex; gap: 8px; align-items: center; margin-bottom: 24px; font-size: 13px; color: var(--gray-400); }
        .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb span { color: var(--gray-600); }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 16px; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-400); background: var(--gray-50); border-bottom: 1px solid var(--gray-200); }
        td { padding: 14px 16px; font-size: 14px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--gray-50); }

        /* ── BADGE ── */
        .status-badge {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .status-completada { background: #dcfce7; color: #15803d; }
        .status-pendiente  { background: #fef9c3; color: #854d0e; }
        .status-anulada    { background: #fee2e2; color: #991b1b; }

        /* ── FORM ── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-600); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius);
            font-size: 14px; color: var(--gray-800); outline: none;
            transition: border-color .15s;
        }
        .form-control:focus { border-color: var(--primary); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        /* ── QTY ── */
        .qty-control { display: flex; align-items: center; gap: 0; border: 1.5px solid var(--gray-200); border-radius: var(--radius); overflow: hidden; width: fit-content; }
        .qty-btn { background: var(--gray-100); border: none; width: 32px; height: 36px; cursor: pointer; font-size: 18px; font-weight: 700; display: flex; align-items: center; justify-content: center; transition: background .12s; }
        .qty-btn:hover { background: var(--gray-200); }
        .qty-input { width: 48px; height: 36px; border: none; border-left: 1.5px solid var(--gray-200); border-right: 1.5px solid var(--gray-200); text-align: center; font-size: 15px; font-weight: 600; outline: none; }

        /* ── EMPTY STATE ── */
        .empty-state { text-align: center; padding: 80px 20px; color: var(--gray-400); }
        .empty-state .icon { font-size: 64px; margin-bottom: 16px; }
        .empty-state h3 { font-size: 20px; color: var(--gray-600); margin-bottom: 8px; }
        .empty-state p { font-size: 14px; margin-bottom: 24px; }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="portal-header">
    <div class="header-inner">
        <a href="index.php?route=portal.catalogo" class="header-logo">Mega<span>Uni</span></a>

        <form class="header-search" action="index.php" method="get">
            <input type="hidden" name="route" value="portal.catalogo">
            <input type="text" name="q" placeholder="Buscar productos..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit">🔍</button>
        </form>

        <div class="header-actions">
            <a href="index.php?route=portal.wishlist" class="btn-icon" title="Favoritos">❤️</a>
            <a href="index.php?route=portal.carrito" class="btn-icon" title="Carrito">
                🛒
                <?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var int $carritoCount
 * @var string $pageTitle
 */

if (($carritoCount ?? 0) > 0): ?>
                    <span class="badge"><?= $carritoCount ?></span>
                <?php endif; ?>
            </a>

            <div class="user-menu">
                <button class="user-btn" onclick="this.nextElementSibling.classList.toggle('open')">
                    <span class="user-avatar">
                        <?= strtoupper(substr($_SESSION['auth']['nombre'] ?? 'U', 0, 1)) ?>
                    </span>
                    <span><?= htmlspecialchars($_SESSION['auth']['nombre'] ?? 'Mi cuenta') ?></span>
                    ▾
                </button>
                <div class="dropdown">
                    <a href="index.php?route=portal.perfil">👤 Mi perfil</a>
                    <a href="index.php?route=portal.pedidos">📦 Mis pedidos</a>
                    <hr class="dropdown-divider">
                    <a href="index.php?route=password.change">🔑 Cambiar contraseña</a>
                    <a href="index.php?route=logout" class="danger">🚪 Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- FLASH -->
<?php if (!empty($_SESSION['flash'])): ?>
    <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="flash-bar <?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<!-- CONTENIDO -->
<main class="portal-main">

<?php /* El contenido de la vista se incluye antes del footer */ ?>
