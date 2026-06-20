<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_alerta(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);overflow:hidden}
.notice-ok{padding:30px;background:#f0fdf4;color:#166534;border-radius:22px;border:1px solid #bbf7d0;line-height:1.6}
table{width:100%;border-collapse:collapse}
th,td{padding:15px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:top;font-size:14px}
th{background:#fff7ed;color:#92400e;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.alert-pill{display:inline-flex;padding:6px 10px;border-radius:999px;background:#fee2e2;color:#991b1b;font-size:12px;font-weight:800}
</style>

<div style="max-width:1120px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0 0 6px;color:#172554;font-size:22px">Alertas de stock</h2>
            <p style="margin:0;color:#6b7280;font-size:14px">Productos cuya cantidad actual está por debajo o igual al mínimo definido.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('inventario.index', true)">← Volver al inventario</button>
    </div>

    <?php if (empty($alertas)): ?>
        <div class="notice-ok">
            <strong>Sin alertas.</strong><br>
            Actualmente no hay productos por debajo del stock mínimo.
        </div>
    <?php else: ?>
        <section class="card">
            <table>
                <thead>
                    <tr>
                        <th>Tienda</th>
                        <th>Producto</th>
                        <th>Cantidad actual</th>
                        <th>Cantidad mínima</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertas as $alerta): ?>
                        <tr>
                            <td><?= e_alerta($alerta['tienda_nombre']) ?></td>
                            <td><strong><?= e_alerta($alerta['producto_nombre']) ?></strong></td>
                            <td><?= e_alerta((string) $alerta['cantidad']) ?> <?= e_alerta($alerta['unidad_simbolo'] ?? '') ?></td>
                            <td><?= e_alerta((string) $alerta['cantidad_minima']) ?></td>
                            <td><?= e_alerta($alerta['ubicacion'] ?? 'Sin ubicación') ?></td>
                            <td><span class="alert-pill">Stock bajo</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

</div>
