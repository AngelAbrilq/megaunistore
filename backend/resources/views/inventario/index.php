<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_inv(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.inv-topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.inv-topbar h2{margin:0 0 6px;color:#172554;font-size:22px}
.inv-topbar p{margin:0;color:#6b7280;font-size:14px}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;white-space:nowrap;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.btn-warning{background:#fef3c7;color:#92400e}
.btn-sm{padding:6px 11px;font-size:13px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:15px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:top;font-size:14px}
th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.muted{color:#6b7280;font-size:13px;line-height:1.5}
.pill{display:inline-flex;padding:6px 10px;border-radius:999px;background:#eef2ff;color:#1e3a8a;font-size:12px;font-weight:800}
.stock-ok{background:#dcfce7;color:#166534}
.stock-low{background:#fee2e2;color:#991b1b}
.stock-mid{background:#fef3c7;color:#92400e}
.actions{display:flex;flex-wrap:nowrap;gap:8px;align-items:center}
.empty{padding:34px;text-align:center;color:#6b7280}
</style>

<div style="max-width:1280px;margin:0 auto;padding:24px 20px">

    <div class="inv-topbar">
        <div>
            <h2>Inventario</h2>
            <p>Consulta existencias por tienda, producto, ubicación y niveles mínimos.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-primary" onclick="openModal('index.php?route=inventario.create&ajax=1')">Registrar inventario</button>
            <button class="btn btn-secondary" onclick="loadContent('inventario.movimientos', true)">Movimientos</button>
            <button class="btn btn-warning" onclick="loadContent('inventario.alertas', true)">Alertas de stock</button>
        </div>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_inv($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_inv($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <?php if (empty($inventarios)): ?>
            <div class="empty">No hay registros de inventario todavía.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tienda</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Mínimo / Máximo</th>
                        <th>Ubicación</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventarios as $inventario): ?>
                        <?php
                            $cantidad = (float) $inventario['cantidad'];
                            $minima   = (float) $inventario['cantidad_minima'];
                            $maxima   = $inventario['cantidad_maxima'] !== null ? (float) $inventario['cantidad_maxima'] : null;
                            $stockClass = $cantidad <= $minima ? 'stock-low'
                                        : ($maxima !== null && $cantidad >= $maxima ? 'stock-mid' : 'stock-ok');
                        ?>
                        <tr>
                            <td><?= e_inv((string) $inventario['id']) ?></td>
                            <td><strong><?= e_inv($inventario['tienda_nombre']) ?></strong></td>
                            <td>
                                <strong><?= e_inv($inventario['producto_nombre']) ?></strong><br>
                                <?php if (!empty($inventario['codigo_barras'])): ?>
                                    <span class="muted">Código: <?= e_inv($inventario['codigo_barras']) ?></span><br>
                                <?php endif; ?>
                                <span class="muted"><?= e_inv($inventario['categoria_nombre'] ?? 'Sin categoría') ?></span>
                            </td>
                            <td>
                                <span class="pill <?= e_inv($stockClass) ?>">
                                    <?= e_inv((string) $inventario['cantidad']) ?>
                                    <?= e_inv($inventario['unidad_simbolo'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                Mín: <?= e_inv((string) $inventario['cantidad_minima']) ?><br>
                                Máx: <?= e_inv($inventario['cantidad_maxima'] !== null ? (string) $inventario['cantidad_maxima'] : 'No definido') ?>
                            </td>
                            <td><?= e_inv($inventario['ubicacion'] ?? 'Sin ubicación') ?></td>
                            <td><?= e_inv($inventario['updated_at'] ?? '') ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-secondary btn-sm"
                                        onclick="loadContent('inventario.movimiento&id=<?= (int)$inventario['id'] ?>', true)">
                                        Movimiento
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</div>
