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

$criticosStock = $chartData['criticosStock'] ?? [];
$nombreUsuario = trim(($_SESSION['auth']['nombre'] ?? '') . ' ' . ($_SESSION['auth']['apellido'] ?? ''));
?>

<style>
.db-welcome{background:linear-gradient(135deg,#064e3b,#059669);color:#fff;border-radius:20px;
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
.criticos-card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;padding:22px 20px;
    box-shadow:0 4px 16px rgba(15,23,42,.07);margin-bottom:28px}
.criticos-table{width:100%;border-collapse:collapse}
.criticos-table th{background:#fef2f2;color:#991b1b;font-size:11px;font-weight:800;
    text-transform:uppercase;letter-spacing:.05em;padding:10px 14px;text-align:left}
.criticos-table td{padding:11px 14px;border-bottom:1px solid #f1f5f9;font-size:13px}
.criticos-table tr:last-child td{border-bottom:none}
.stock-bar-wrap{display:flex;align-items:center;gap:8px}
.stock-bar{height:8px;border-radius:4px;background:#fee2e2;flex:1}
.stock-bar-fill{height:100%;border-radius:4px;background:#dc2626}
.badge-critico{display:inline-block;background:#fee2e2;color:#991b1b;
    font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px}
.empty-state{text-align:center;padding:32px;color:#6b7280}
.empty-state-icon{font-size:40px;display:block;margin-bottom:8px}
.actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
.db-action{display:flex;align-items:flex-start;gap:14px;background:#f8fafc;border:1px solid #dbe3ef;
    border-radius:16px;padding:18px;cursor:pointer;
    transition:transform .15s,box-shadow .15s,border-color .15s}
.db-action:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(5,150,105,.15);border-color:#059669}
.db-action-icon{font-size:24px;width:44px;height:44px;border-radius:12px;display:flex;
    align-items:center;justify-content:center;flex-shrink:0}
.db-action-title{display:block;color:#1e3a8a;font-size:14px;font-weight:800;margin-bottom:4px}
.db-action-desc{display:block;color:#6b7280;font-size:13px;line-height:1.5}
</style>

<div class="db-welcome">
    <span class="db-welcome-emoji">📦</span>
    <div>
        <h2>Hola, <?= htmlspecialchars($nombreUsuario ?: 'Bodeguero') ?></h2>
        <p>Control de inventario: entradas, salidas, ajustes y alertas de stock.</p>
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

<!-- Tabla de productos críticos -->
<div class="criticos-card">
    <p class="section-title">🚨 Productos críticos — bajo mínimo de stock</p>
    <?php if (empty($criticosStock)): ?>
        <div class="empty-state">
            <span class="empty-state-icon">✅</span>
            <p>Todo el inventario está por encima del mínimo. ¡Buen trabajo!</p>
        </div>
    <?php else: ?>
        <table class="criticos-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Stock actual</th>
                    <th>Mínimo</th>
                    <th>Nivel</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($criticosStock as $prod):
                    $pct = $prod['cantidad_minima'] > 0
                        ? min(100, round($prod['cantidad'] / $prod['cantidad_minima'] * 100))
                        : 0;
                    $esAgotado = (int) $prod['cantidad'] === 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($prod['nombre']) ?></td>
                    <td>
                        <strong style="color:<?= $esAgotado ? '#dc2626' : '#d97706' ?>">
                            <?= (int) $prod['cantidad'] ?>
                        </strong>
                        <?php if ($esAgotado): ?>
                            <span class="badge-critico">Agotado</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#6b7280"><?= (int) $prod['cantidad_minima'] ?></td>
                    <td style="min-width:120px">
                        <div class="stock-bar-wrap">
                            <div class="stock-bar">
                                <div class="stock-bar-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span style="font-size:11px;color:#9ca3af;white-space:nowrap"><?= $pct ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin:14px 0 0;font-size:13px;color:#6b7280;text-align:right">
            <span style="cursor:pointer;color:#059669;font-weight:700"
                  onclick="loadContent('inventario.alertas', true)">
                Ver todas las alertas →
            </span>
        </p>
    <?php endif; ?>
</div>

<p class="section-title">⚡ Acciones rápidas</p>
<div class="actions-grid">
    <?php
    $acciones = [
        ['icon'=>'📊','title'=>'Inventario',          'desc'=>'Consultar y gestionar el stock completo.',      'route'=>'inventario.index',   'color'=>'#059669'],
        ['icon'=>'🚨','title'=>'Alertas de stock',    'desc'=>'Todos los productos bajo el mínimo.',           'route'=>'inventario.alertas', 'color'=>'#dc2626'],
        ['icon'=>'📦','title'=>'Productos',            'desc'=>'Catálogo de productos de la tienda.',           'route'=>'productos.index',    'color'=>'#2563eb'],
        ['icon'=>'📝','title'=>'Movimientos',          'desc'=>'Historial de entradas y salidas de inventario.','route'=>'inventario.movimientos','color'=>'#d97706'],
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
