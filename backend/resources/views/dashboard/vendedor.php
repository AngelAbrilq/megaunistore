<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $kpis
 */

// $kpis y $chartData vienen del DashboardController vía web.php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if (!$isAjax) {
    return;
}

$nombreUsuario = trim(($_SESSION['auth']['nombre'] ?? '') . ' ' . ($_SESSION['auth']['apellido'] ?? ''));
?>

<style>
.db-welcome{background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border-radius:20px;
    padding:24px 28px;margin-bottom:28px;display:flex;align-items:center;gap:16px}
.db-welcome-emoji{font-size:42px}
.db-welcome h2{margin:0 0 4px;font-size:21px;font-weight:800}
.db-welcome p{margin:0;opacity:.85;font-size:14px}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:32px}
.kpi-card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;padding:22px 20px;
    box-shadow:0 4px 16px rgba(15,23,42,.07);display:flex;align-items:flex-start;gap:14px;
    transition:transform .15s,box-shadow .15s}
.kpi-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(15,23,42,.12)}
.kpi-icon{font-size:26px;width:52px;height:52px;border-radius:14px;display:flex;
    align-items:center;justify-content:center;flex-shrink:0}
.kpi-label{display:block;color:#6b7280;font-size:11px;font-weight:700;
    text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
.kpi-value{display:block;font-size:22px;font-weight:800;margin-bottom:2px}
.kpi-sub{display:block;color:#9ca3af;font-size:12px}
.section-title{font-size:16px;font-weight:800;color:#172554;margin:0 0 16px}
.actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
.db-action{display:flex;align-items:flex-start;gap:14px;background:#f8fafc;border:1px solid #dbe3ef;
    border-radius:16px;padding:18px;cursor:pointer;
    transition:transform .15s,box-shadow .15s,border-color .15s}
.db-action:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.13);border-color:#2563eb}
.db-action-icon{font-size:24px;width:44px;height:44px;border-radius:12px;display:flex;
    align-items:center;justify-content:center;flex-shrink:0}
.db-action-title{display:block;color:#1e3a8a;font-size:14px;font-weight:800;margin-bottom:4px}
.db-action-desc{display:block;color:#6b7280;font-size:13px;line-height:1.5}
</style>

<div class="db-welcome">
    <span class="db-welcome-emoji">🧑‍💼</span>
    <div>
        <h2>Hola, <?= htmlspecialchars($nombreUsuario ?: 'Vendedor') ?></h2>
        <p>Desde aquí gestionas el punto de venta: ventas, caja y atención al cliente.</p>
    </div>
</div>

<div class="kpi-grid">
    <?php foreach ($kpis as $kpi): ?>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:<?= htmlspecialchars($kpi['color']) ?>18">
            <?= $kpi['icon'] ?>
        </div>
        <div>
            <span class="kpi-label"><?= htmlspecialchars($kpi['label']) ?></span>
            <span class="kpi-value" style="color:<?= htmlspecialchars($kpi['color']) ?>">
                <?= htmlspecialchars($kpi['value']) ?>
            </span>
            <span class="kpi-sub"><?= htmlspecialchars($kpi['sub']) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<p class="section-title">⚡ Acciones rápidas</p>
<div class="actions-grid">
    <?php
    $acciones = [
        ['icon'=>'🛒','title'=>'Nueva venta',       'desc'=>'Crear una venta asistida desde el punto de venta.','route'=>'ventas.create',     'color'=>'#16a34a'],
        ['icon'=>'📋','title'=>'Historial de ventas','desc'=>'Consultar ventas realizadas y sus estados.',       'route'=>'ventas.index',      'color'=>'#2563eb'],
        ['icon'=>'💵','title'=>'Caja',               'desc'=>'Apertura, cierre y movimientos del turno.',        'route'=>'caja.index',        'color'=>'#d97706'],
        ['icon'=>'👥','title'=>'Clientes',           'desc'=>'Buscar y registrar clientes para la venta.',       'route'=>'clientes.index',    'color'=>'#7c3aed'],
        ['icon'=>'🎫','title'=>'Cupones',            'desc'=>'Validar y aplicar cupones de descuento.',          'route'=>'cupones.index',     'color'=>'#db2777'],
        ['icon'=>'🔄','title'=>'Devoluciones',       'desc'=>'Gestionar devoluciones de ventas realizadas.',     'route'=>'devoluciones.index','color'=>'#dc2626'],
    ];
    foreach ($acciones as $a):
    ?>
    <div class="db-action" onclick="loadContent('<?= htmlspecialchars($a['route']) ?>', true)">
        <div class="db-action-icon" style="background:<?= $a['color'] ?>18"><?= $a['icon'] ?></div>
        <div>
            <span class="db-action-title"><?= htmlspecialchars($a['title']) ?></span>
            <span class="db-action-desc"><?= htmlspecialchars($a['desc']) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
