<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

$pageTitle    = 'Panel Sistema';
$pageSubtitle = 'Actor lógico para automatizaciones, validaciones, alertas, respaldos y eventos internos.';

$cards = [
    ['icon' => '⚙️', 'label' => 'Rol activo',    'value' => 'Sistema'],
    ['icon' => '🤖', 'label' => 'Tipo',           'value' => 'Actor automatizado'],
    ['icon' => '🔒', 'label' => 'Acceso',         'value' => 'Solo lectura / interno'],
];

$actions = [
    [
        'icon'        => '🏪',
        'title'       => 'Estado de tiendas',
        'description' => 'Verificar el estado activo/inactivo de las tiendas.',
        'url'         => 'index.php?route=tiendas.index',
        'color'       => '#2563eb',
    ],
    [
        'icon'        => '📊',
        'title'       => 'Inventario global',
        'description' => 'Consultar el inventario de todas las tiendas.',
        'url'         => 'index.php?route=inventario.index',
        'color'       => '#16a34a',
    ],
    [
        'icon'        => '🚨',
        'title'       => 'Alertas de stock',
        'description' => 'Productos por debajo del stock mínimo definido.',
        'url'         => 'index.php?route=inventario.alertas',
        'color'       => '#dc2626',
    ],
];

if (!$isAjax) {
    return;
}
?>

<style>
    .db-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-bottom:32px; }
    .db-card { background:#fff; border:1px solid #dbe3ef; border-radius:18px; padding:22px 20px; box-shadow:0 4px 16px rgba(15,23,42,.07); display:flex; align-items:flex-start; gap:14px; }
    .db-card-icon { font-size:28px; line-height:1; flex-shrink:0; }
    .db-card-label { display:block; color:#6b7280; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
    .db-card-value { display:block; color:#1e3a8a; font-size:20px; font-weight:800; }
    .db-welcome { background:linear-gradient(135deg,#1c1917,#44403c); color:#fff; border-radius:20px; padding:24px 28px; margin-bottom:28px; display:flex; align-items:center; gap:16px; }
    .db-welcome-emoji { font-size:40px; }
    .db-welcome h2 { margin:0 0 4px; font-size:20px; font-weight:800; }
    .db-welcome p  { margin:0; opacity:.85; font-size:14px; }
    .db-section-title { font-size:16px; font-weight:800; color:#172554; margin:0 0 16px; }
    .db-actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; }
    .db-action { display:flex; align-items:flex-start; gap:14px; background:#f8fafc; border:1px solid #dbe3ef; border-radius:16px; padding:18px; text-decoration:none; color:inherit; transition:transform .15s,box-shadow .15s,border-color .15s; }
    .db-action:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(37,99,235,.13); border-color:#2563eb; }
    .db-action-icon { font-size:26px; width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .db-action-title { display:block; color:#1e3a8a; font-size:15px; font-weight:800; margin-bottom:4px; }
    .db-action-desc  { display:block; color:#6b7280; font-size:13px; line-height:1.5; }
</style>

<div class="db-welcome">
    <span class="db-welcome-emoji">⚙️</span>
    <div>
        <h2>Panel del Sistema</h2>
        <p>Actor interno para automatizaciones, alertas y procesos lógicos del sistema.</p>
    </div>
</div>

<div class="db-cards">
    <?php foreach ($cards as $card): ?>
        <div class="db-card">
            <span class="db-card-icon"><?= $card['icon'] ?></span>
            <div>
                <span class="db-card-label"><?= htmlspecialchars($card['label']) ?></span>
                <span class="db-card-value"><?= htmlspecialchars($card['value']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<p class="db-section-title">🔧 Módulos internos</p>

<div class="db-actions">
    <?php foreach ($actions as $action): ?>
        <a href="<?= htmlspecialchars($action['url']) ?>" class="db-action">
            <span class="db-action-icon" style="background:<?= $action['color'] ?>18;">
                <?= $action['icon'] ?>
            </span>
            <div>
                <span class="db-action-title"><?= htmlspecialchars($action['title']) ?></span>
                <span class="db-action-desc"><?= htmlspecialchars($action['description']) ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
