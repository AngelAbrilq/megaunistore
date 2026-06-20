<?php
// $kpis y $chartData vienen del DashboardController vía web.php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if (!$isAjax) {
    require __DIR__ . '/layout.php';
    return;
}

// Preparar datos para Chart.js
$ventas7   = $chartData['ventas7dias']     ?? [];
$porTienda = $chartData['ventasPorTienda'] ?? [];

$labels7   = array_map(fn($r) => date('d/m', strtotime($r['fecha'])), $ventas7);
$totales7  = array_map(fn($r) => round((float) $r['total'], 2), $ventas7);
$labelsT   = array_map(fn($r) => $r['tienda'], $porTienda);
$totalesT  = array_map(fn($r) => round((float) $r['total'], 2), $porTienda);

$labels7Json  = json_encode($labels7,  JSON_HEX_TAG);
$totales7Json = json_encode($totales7, JSON_HEX_TAG);
$labelsTJson  = json_encode($labelsT,  JSON_HEX_TAG);
$totalesTJson = json_encode($totalesT, JSON_HEX_TAG);

$nombreUsuario = trim(($_SESSION['auth']['nombre'] ?? '') . ' ' . ($_SESSION['auth']['apellido'] ?? ''));
?>

<style>
.db-welcome{background:linear-gradient(135deg,#1e3a8a,#1d4ed8);color:#fff;border-radius:20px;
    padding:24px 28px;margin-bottom:28px;display:flex;align-items:center;gap:16px}
.db-welcome-emoji{font-size:42px}
.db-welcome h2{margin:0 0 4px;font-size:21px;font-weight:800}
.db-welcome p{margin:0;opacity:.85;font-size:14px}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px;margin-bottom:32px}
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
.charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px}
@media(max-width:900px){.charts-grid{grid-template-columns:1fr}}
.chart-card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;padding:22px 20px;
    box-shadow:0 4px 16px rgba(15,23,42,.07)}
.chart-title{font-size:15px;font-weight:800;color:#172554;margin:0 0 18px;
    display:flex;align-items:center;gap:8px}
.chart-wrap{position:relative;height:220px}
</style>

<div class="db-welcome">
    <span class="db-welcome-emoji">🌐</span>
    <div>
        <h2>Hola, <?= htmlspecialchars($nombreUsuario ?: 'Superadministrador') ?></h2>
        <p>Visión global de la plataforma — tiendas, usuarios, ventas y operaciones.</p>
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

<style>
.chart-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:220px;
    color:#9ca3af;gap:10px}
.chart-empty-icon{font-size:36px;opacity:.45}
.chart-empty-text{font-size:13px;font-weight:600}
</style>

<div class="charts-grid">
    <div class="chart-card">
        <p class="chart-title">📈 Ventas globales — últimos 7 días</p>
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
        <p class="chart-title">🏪 Ventas por tienda — mes actual</p>
        <div class="chart-wrap">
            <?php if (empty($labelsT)): ?>
                <div class="chart-empty">
                    <span class="chart-empty-icon">🏪</span>
                    <span class="chart-empty-text">Sin ventas registradas este mes</span>
                </div>
            <?php else: ?>
                <canvas id="chartPorTienda"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($labels7) || !empty($labelsT)): ?>
<script>
(function () {
    const labels7  = <?= $labels7Json ?>;
    const totales7 = <?= $totales7Json ?>;
    const labelsT  = <?= $labelsTJson ?>;
    const totalesT = <?= $totalesTJson ?>;

    const baseOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    };

    if (labels7.length) new Chart(document.getElementById('chartVentas7'), {
        type: 'line',
        data: {
            labels: labels7,
            datasets: [{
                data: totales7,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.10)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBackgroundColor: '#2563eb',
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            ...baseOpts,
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

    const palette = ['#2563eb','#7c3aed','#16a34a','#d97706','#dc2626',
                     '#0891b2','#db2777','#9333ea','#059669','#ea580c'];

    if (labelsT.length) {
        new Chart(document.getElementById('chartPorTienda'), {
            type: 'bar',
            data: {
                labels: labelsT,
                datasets: [{
                    data: totalesT,
                    backgroundColor: labelsT.map((_, i) => palette[i % palette.length] + 'cc'),
                    borderColor:     labelsT.map((_, i) => palette[i % palette.length]),
                    borderWidth: 1.5,
                    borderRadius: 8,
                }],
            },
            options: {
                ...baseOpts,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' $' + ctx.raw.toLocaleString() } },
                },
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
    }
})();
</script>
<?php endif; ?>
