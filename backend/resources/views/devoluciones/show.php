<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $detalle
 * @var array $devolucion
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_dev_show(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.card h2{margin:0 0 16px;color:#172554;font-size:18px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
.alert-error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px}
.meta-item small{display:block;color:#6b7280;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
.meta-item strong{color:#172554;font-size:16px}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid #e5e7eb}
th{background:#f8fafc;font-weight:800;color:#172554;text-transform:uppercase;font-size:12px;letter-spacing:.04em}
tr:last-child td{border-bottom:0}
.total-row td{font-weight:900;font-size:16px;color:#172554;border-top:2px solid #e5e7eb}
.badge{display:inline-block;padding:5px 12px;border-radius:999px;font-size:13px;font-weight:700}
.badge-completada{background:#d1fae5;color:#065f46}
.badge-pendiente{background:#fef3c7;color:#92400e}
.badge-rechazada{background:#fee2e2;color:#991b1b}
.motivo-box{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:16px;color:#374151;line-height:1.6}
.spa-link{color:#2563eb;background:none;border:none;padding:0;font:inherit;cursor:pointer;text-decoration:underline}
</style>

<div style="max-width:1000px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0 0 6px;color:#172554;font-size:22px">Devolución #<?= e_dev_show((string) $devolucion['id']) ?></h2>
            <p style="margin:0;color:#6b7280;font-size:14px">
                <?= e_dev_show($devolucion['tienda_nombre']) ?> —
                <?= date('d/m/Y H:i', strtotime($devolucion['created_at'])) ?>
            </p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-secondary" onclick="loadContent('devoluciones.index', true)">← Devoluciones</button>
            <button class="btn btn-secondary" onclick="loadContent('ventas.show&id=<?= (int)$devolucion['venta_id'] ?>', true)">Ver venta original</button>
        </div>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_dev_show($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_dev_show($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Resumen</h2>
        <div class="meta-grid">
            <div class="meta-item">
                <small>Estado</small>
                <strong>
                    <span class="badge badge-<?= e_dev_show(strtolower($devolucion['estado'])) ?>">
                        <?= e_dev_show(ucfirst($devolucion['estado'])) ?>
                    </span>
                </strong>
            </div>
            <div class="meta-item">
                <small>Venta Original</small>
                <strong>
                    <button class="spa-link" onclick="loadContent('ventas.show&id=<?= (int)$devolucion['venta_id'] ?>', true)">
                        #<?= e_dev_show((string) $devolucion['venta_id']) ?>
                    </button>
                </strong>
            </div>
            <div class="meta-item">
                <small>Tienda</small>
                <strong><?= e_dev_show($devolucion['tienda_nombre']) ?></strong>
            </div>
            <div class="meta-item">
                <small>Total Venta</small>
                <strong>$<?= number_format((float) $devolucion['venta_total'], 2) ?></strong>
            </div>
            <div class="meta-item">
                <small>Monto Devuelto</small>
                <strong style="color:#991b1b;">$<?= number_format((float) $devolucion['monto_devuelto'], 2) ?></strong>
            </div>
            <div class="meta-item">
                <small>Fecha</small>
                <strong><?= date('d/m/Y H:i', strtotime($devolucion['created_at'])) ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Motivo</h2>
        <div class="motivo-box"><?= e_dev_show($devolucion['motivo']) ?></div>
    </div>

    <div class="card">
        <h2>Productos Devueltos</h2>
        <?php if (empty($detalle)): ?>
            <p style="color:#6b7280;">No hay detalle de productos para esta devolución.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $item): ?>
                        <tr>
                            <td><strong><?= e_dev_show($item['producto_nombre']) ?></strong></td>
                            <td><?= e_dev_show($item['codigo_barras'] ?? '—') ?></td>
                            <td><?= number_format((float) $item['cantidad'], 2) ?></td>
                            <td>$<?= number_format((float) $item['precio_unitario'], 2) ?></td>
                            <td><strong>$<?= number_format((float) $item['subtotal'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right;">Total Devuelto:</td>
                        <td>$<?= number_format((float) $devolucion['monto_devuelto'], 2) ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>
