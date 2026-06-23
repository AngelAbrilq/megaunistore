<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $chartData
 * @var array $kpis
 */

// $kpis y $chartData vienen del DashboardController vía web.php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if (!$isAjax) {
    return;
}

$ventas7      = $chartData['ventas7dias']  ?? [];
$topProductos = $chartData['topProductos'] ?? [];

$labels7     = array_map(fn($r) => date('d/m', strtotime($r['fecha'])), $ventas7);
$totales7    = array_map(fn($r) => round((float) $r['total'], 2), $ventas7);
$labelsTop   = array_map(fn($r) => mb_strimwidth($r['nombre'], 0, 22, '…'), $topProductos);
$unidadesTop = array_map(fn($r) => (int) $r['unidades'], $topProductos);

$labels7Json     = json_encode($labels7,     JSON_HEX_TAG);
$totales7Json    = json_encode($totales7,    JSON_HEX_TAG);
$labelsTopJson   = json_encode($labelsTop,   JSON_HEX_TAG);
$unidadesTopJson = json_encode($unidadesTop, JSON_HEX_TAG);

$nombreUsuario = trim(($_SESSION['auth']['nombre'] ?? '') . ' ' . ($_SESSION['auth']['apellido'] ?? ''));
?>

<style>
.db-welcome{background:linear-gradient(135deg,#0c4a6e,#0284c7);color:#fff;border-radius:20px;
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
.charts-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:20px;margin-bottom:28px}
@media(max-width:900px){.charts-grid{grid-template-columns:1fr}}
.chart-card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;padding:22px 20px;
    box-shadow:0 4px 16px rgba(15,23,42,.07)}
.chart-title{font-size:15px;font-weight:800;color:#172554;margin:0 0 18px;
    display:flex;align-items:center;gap:8px}
.chart-wrap{position:relative;height:230px}
.chart-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:230px;
    color:#9ca3af;gap:10px}
.chart-empty-icon{font-size:36px;opacity:.45}
.chart-empty-text{font-size:13px;font-weight:600}
.section-title{font-size:16px;font-weight:800;color:#172554;margin:0 0 16px;
    display:flex;align-items:center;gap:8px}
.actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
.db-action{display:flex;align-items:flex-start;gap:14px;background:#f8fafc;border:1px solid #dbe3ef;
    border-radius:16px;padding:18px;text-decoration:none;color:inherit;
    transition:transform .15s,box-shadow .15s,border-color .15s;cursor:pointer}
.db-action:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.13);border-color:#2563eb}
.db-action-icon{font-size:24px;width:44px;height:44px;border-radius:12px;display:flex;
    align-items:center;justify-content:center;flex-shrink:0}
.db-action-title{display:block;color:#1e3a8a;font-size:14px;font-weight:800;margin-bottom:4px}
.db-action-desc{display:block;color:#6b7280;font-size:13px;line-height:1.5}
</style>

<div class="db-welcome">
    <span class="db-welcome-emoji">🏪</span>
    <div>
        <h2>Hola, <?= htmlspecialchars($nombreUsuario ?: 'Administrador') ?></h2>
        <p>Administra tu tienda: catálogo, inventario, ventas y personal asignado.</p>
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

<div class="charts-grid">
    <div class="chart-card">
        <p class="chart-title">📈 Ventas — últimos 7 días</p>
        <div class="chart-wrap">
            <?php if (empty($labels7)): ?>
                <div class="chart-empty">
                    <span class="chart-empty-icon">📊</span>
                    <span class="chart-empty-text">Sin ventas en los últimos 7 días</span>
                </div>
            <?php else: ?>
                <canvas id="chartVentas7"></canvas>
            <?php endif; ?>
        </div>
    </div>
    <div class="chart-card">
        <p class="chart-title">🏆 Top productos del mes</p>
        <div class="chart-wrap">
            <?php if (empty($labelsTop)): ?>
                <div class="chart-empty">
                    <span class="chart-empty-icon">📦</span>
                    <span class="chart-empty-text">Sin productos vendidos este mes</span>
                </div>
            <?php else: ?>
                <canvas id="chartTopProd"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<p class="section-title">⚡ Acciones rápidas</p>
<div class="actions-grid">
    <?php
    $acciones = [
        ['icon'=>'📦','title'=>'Productos',        'desc'=>'Catálogo de productos de la tienda.',      'route'=>'productos.index',    'color'=>'#2563eb'],
        ['icon'=>'📊','title'=>'Inventario',        'desc'=>'Stock, mínimos, máximos y movimientos.',  'route'=>'inventario.index',   'color'=>'#16a34a'],
        ['icon'=>'💰','title'=>'Ventas',             'desc'=>'Monitorear y gestionar ventas.',          'route'=>'ventas.index',       'color'=>'#d97706'],
        ['icon'=>'👔','title'=>'Personal',           'desc'=>'Empleados asignados a la tienda.',        'route'=>'empleados.index',    'color'=>'#7c3aed'],
        ['icon'=>'🚨','title'=>'Alertas de stock',  'desc'=>'Productos bajo el mínimo de inventario.', 'route'=>'inventario.alertas', 'color'=>'#dc2626'],
        ['icon'=>'📈','title'=>'Reportes',           'desc'=>'Ventas e inventario en detalle.',         'route'=>'reportes.ventas',    'color'=>'#0891b2'],
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

<?php if (!empty($labels7) || !empty($labelsTop)): ?>
<script>
(function () {
    const labels7    = <?= $labels7Json ?>;
    const totales7   = <?= $totales7Json ?>;
    const labelsTop  = <?= $labelsTopJson ?>;
    const unidades   = <?= $unidadesTopJson ?>;

    if (labels7.length) new Chart(document.getElementById('chartVentas7'), {
        type: 'line',
        data: {
            labels: labels7,
            datasets: [{
                data: totales7,
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2,132,199,.10)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBackgroundColor: '#0284c7',
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '$' + v.toLocaleString() },
                    grid: { color: '#f1f5f9' },
                },
            },
        },
    });

    if (labelsTop.length) new Chart(document.getElementById('chartTopProd'), {
        type: 'bar',
        data: {
            labels: labelsTop,
            datasets: [{
                data: unidades,
                backgroundColor: 'rgba(124,58,237,.75)',
                borderColor: '#7c3aed',
                borderWidth: 1.5,
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' uds.' } },
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                y: { grid: { display: false } },
            },
        },
    });
})();
</script>
<?php endif; ?>
