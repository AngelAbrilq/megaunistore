<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_dev_create(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
.alert-error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.card h2{margin:0 0 16px;color:#172554;font-size:18px}
.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px}
.meta-item small{display:block;color:#6b7280;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
.meta-item strong{color:#172554;font-size:16px}
.form-group{margin-bottom:18px}
label{display:block;margin-bottom:8px;font-weight:800;color:#1f2937;font-size:14px}
input,select,textarea{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:13px 14px;font-size:15px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
input:focus,select:focus,textarea:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.badge{display:inline-block;padding:3px 10px;border-radius:8px;font-size:12px;font-weight:700}
.badge-completada{background:#d1fae5;color:#065f46}
.badge-pendiente{background:#fef3c7;color:#92400e}
.badge-anulada{background:#fee2e2;color:#991b1b}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid #e5e7eb;vertical-align:middle}
th{background:#f8fafc;font-weight:800;color:#172554;text-transform:uppercase;font-size:12px;letter-spacing:.04em}
.qty-input{width:90px!important;padding:8px 10px!important;border-radius:10px!important}
.help{color:#6b7280;font-size:12px;margin-top:5px;display:block}
</style>

<div style="max-width:960px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0 0 4px;color:#172554;font-size:22px">Nueva Devolución</h2>
            <p style="margin:0;color:#6b7280;font-size:14px">Selecciona los productos a devolver de la venta y especifica las cantidades.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-secondary"
                onclick="loadContent('ventas.show&id=<?= (int)$venta['id'] ?>', true)">← Volver a la venta</button>
            <button class="btn btn-secondary"
                onclick="loadContent('devoluciones.index', true)">Ver devoluciones</button>
        </div>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_dev_create($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_dev_create($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Información de la Venta</h2>
        <div class="meta-grid">
            <div class="meta-item">
                <small>Venta</small>
                <strong>#<?= e_dev_create((string) $venta['id']) ?></strong>
            </div>
            <div class="meta-item">
                <small>Tienda</small>
                <strong><?= e_dev_create($venta['tienda_nombre']) ?></strong>
            </div>
            <div class="meta-item">
                <small>Total Venta</small>
                <strong>$<?= number_format((float) $venta['total'], 2) ?></strong>
            </div>
            <div class="meta-item">
                <small>Estado</small>
                <strong>
                    <span class="badge badge-<?= e_dev_create($venta['estado']) ?>">
                        <?= e_dev_create(ucfirst($venta['estado'])) ?>
                    </span>
                </strong>
            </div>
            <div class="meta-item">
                <small>Fecha</small>
                <strong><?= e_dev_create($venta['fecha'] ?? $venta['created_at']) ?></strong>
            </div>
        </div>
    </div>

    <form action="index.php?route=devoluciones.store" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e_dev_create($csrfToken) ?>">
        <input type="hidden" name="venta_id" value="<?= e_dev_create((string) $venta['id']) ?>">

        <div class="card">
            <h2>Productos a Devolver</h2>
            <?php if (empty($detalle)): ?>
                <p style="color:#6b7280">Esta venta no tiene productos registrados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Cant. Vendida</th>
                            <th>Precio Unit.</th>
                            <th>Cant. a Devolver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle as $i => $item): ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="producto_id[<?= $i ?>]" value="<?= e_dev_create((string) $item['producto_id']) ?>">
                                    <strong><?= e_dev_create($item['producto_nombre']) ?></strong>
                                </td>
                                <td><?= e_dev_create($item['codigo_barras'] ?? '—') ?></td>
                                <td><?= number_format((float) $item['cantidad'], 2) ?></td>
                                <td>$<?= number_format((float) $item['precio_unitario'], 2) ?></td>
                                <td>
                                    <input type="number" class="qty-input"
                                        name="cantidad[<?= $i ?>]"
                                        min="0" max="<?= e_dev_create((string) $item['cantidad']) ?>"
                                        step="0.01" value="0" placeholder="0">
                                    <span class="help">Máx: <?= number_format((float) $item['cantidad'], 2) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Motivo de la Devolución</h2>
            <div class="form-group">
                <label for="motivo">Motivo *</label>
                <textarea id="motivo" name="motivo" rows="4" required
                    placeholder="Describe el motivo de la devolución (producto defectuoso, error en pedido, etc.)"></textarea>
            </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
            <button type="submit" class="btn btn-primary">Procesar Devolución</button>
            <button type="button" class="btn btn-secondary"
                onclick="loadContent('ventas.show&id=<?= (int)$venta['id'] ?>', true)">Cancelar</button>
        </div>
    </form>

</div>
