<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_show_venta(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function estado_show_class(string $estado): string {
    return match ($estado) {
        'completada' => 'status-success',
        'anulada'    => 'status-danger',
        'pendiente'  => 'status-warning',
        default      => 'status-neutral',
    };
}

$rolActual = $_SESSION['auth']['rol_principal']['rol_nombre'] ?? '';
$puedeAnularVenta = in_array($rolActual, ['Superadministrador', 'Administrador de Tienda', 'Supervisor'], true);
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.btn-danger{background:#fee2e2;color:#991b1b}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.card h2{margin:0 0 16px;color:#172554;font-size:18px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.summary-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:16px;padding:14px}
.summary-item small{display:block;color:#6b7280;font-weight:800;text-transform:uppercase;margin-bottom:6px;letter-spacing:.04em}
.summary-item strong{color:#172554;font-size:19px}
.status{display:inline-flex;padding:7px 11px;border-radius:999px;font-size:13px;font-weight:900}
.status-success{background:#dcfce7;color:#166534}
.status-danger{background:#fee2e2;color:#991b1b}
.status-warning{background:#fef3c7;color:#92400e}
.status-neutral{background:#eef2ff;color:#1e3a8a}
table{width:100%;border-collapse:collapse}
th,td{padding:15px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:top;font-size:14px}
th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.money{font-weight:900;color:#172554}
.muted{color:#6b7280;font-size:13px}
form{margin:0}
@media(max-width:850px){.summary-grid{grid-template-columns:1fr}}
</style>

<div style="max-width:1180px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0 0 6px;color:#172554;font-size:22px">Venta #<?= e_show_venta((string) $venta['id']) ?></h2>
            <p style="margin:0;color:#6b7280;font-size:14px">
                <?= e_show_venta($venta['tienda_nombre']) ?> — <?= e_show_venta($venta['created_at']) ?>
            </p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-secondary" onclick="_ventaShowVolver()">← Volver a ventas</button>
            <?php if ($puedeAnularVenta && $venta['estado'] === 'completada'): ?>
                <button type="button" class="btn btn-secondary"
                    onclick="_ventaShowDevolucion(<?= (int) $venta['id'] ?>)">
                    ↩ Nueva devolución
                </button>
            <?php endif; ?>
            <?php if ($puedeAnularVenta && $venta['estado'] !== 'anulada'): ?>
                <form action="index.php?route=ventas.anular" method="POST"
                      onsubmit="return confirm('¿Seguro que deseas anular esta venta? El stock será devuelto al inventario.');">
                    <input type="hidden" name="csrf_token" value="<?= e_show_venta($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= e_show_venta((string) $venta['id']) ?>">
                    <button type="submit" class="btn btn-danger">Anular venta</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_show_venta($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_show_venta($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <h2>Resumen</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <small>Estado</small>
                <strong>
                    <span class="status <?= e_show_venta(estado_show_class($venta['estado'])) ?>">
                        <?= e_show_venta(ucfirst($venta['estado'])) ?>
                    </span>
                </strong>
            </div>
            <div class="summary-item">
                <small>Subtotal</small>
                <strong>$<?= e_show_venta((string) $venta['subtotal']) ?></strong>
            </div>
            <?php if (!empty($venta['cupon_id']) && (float) $venta['descuento'] > 0): ?>
            <div class="summary-item" style="border-color:#bbf7d0;background:#f0fdf4">
                <small style="color:#166534">Descuento cupón</small>
                <strong style="color:#166534;font-size:17px">
                    -$<?= e_show_venta(number_format((float) $venta['descuento'], 2)) ?>
                    <?php if (!empty($venta['cupon_codigo'])): ?>
                        <span style="font-size:12px;font-weight:700;display:block;margin-top:3px">
                            Código: <?= e_show_venta($venta['cupon_codigo']) ?>
                        </span>
                    <?php endif; ?>
                </strong>
            </div>
            <?php endif; ?>
            <div class="summary-item">
                <small>Impuesto</small>
                <strong>$<?= e_show_venta((string) $venta['impuesto']) ?></strong>
            </div>
            <div class="summary-item">
                <small>Total</small>
                <strong>$<?= e_show_venta((string) $venta['total']) ?></strong>
            </div>
        </div>
    </section>

    <section class="card">
        <h2>Datos generales</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <small>Tienda</small>
                <strong><?= e_show_venta($venta['tienda_nombre']) ?></strong>
            </div>
            <div class="summary-item">
                <small>Cliente</small>
                <strong>
                    <?php if (!empty($venta['cliente_nombre'])): ?>
                        <?= e_show_venta(trim($venta['cliente_nombre'] . ' ' . ($venta['cliente_apellido'] ?? ''))) ?>
                    <?php else: ?>
                        Cliente general
                    <?php endif; ?>
                </strong>
            </div>
            <div class="summary-item">
                <small>Fecha</small>
                <strong><?= e_show_venta($venta['fecha'] ?? $venta['created_at']) ?></strong>
            </div>
            <div class="summary-item">
                <small>Creada por</small>
                <strong><?= e_show_venta((string) ($venta['created_by'] ?? 'Sistema')) ?></strong>
            </div>
        </div>
    </section>

    <section class="card">
        <h2>Detalle de productos</h2>
        <?php if (empty($detalle)): ?>
            <p style="color:#6b7280">No hay productos en esta venta.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Descuento</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $item): ?>
                        <tr>
                            <td data-label="Producto"><strong><?= e_show_venta($item['producto_nombre']) ?></strong></td>
                            <td data-label="Código"><?= e_show_venta($item['codigo_barras'] ?? 'Sin código') ?></td>
                            <td data-label="Cantidad"><?= e_show_venta((string) $item['cantidad']) ?></td>
                            <td data-label="Precio unitario">$<?= e_show_venta((string) $item['precio_unitario']) ?></td>
                            <td data-label="Descuento">$<?= e_show_venta((string) $item['descuento']) ?></td>
                            <td data-label="Subtotal"><span class="money">$<?= e_show_venta((string) $item['subtotal']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Pagos</h2>
        <?php if (empty($pagos)): ?>
            <p style="color:#6b7280">No hay pagos registrados para esta venta.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td data-label="Método"><?= e_show_venta($pago['metodo_pago_nombre']) ?></td>
                            <td data-label="Monto"><span class="money">$<?= e_show_venta((string) $pago['monto']) ?></span></td>
                            <td data-label="Referencia"><?= e_show_venta($pago['referencia'] ?? 'Sin referencia') ?></td>
                            <td data-label="Estado"><?= e_show_venta(ucfirst($pago['estado'])) ?></td>
                            <td data-label="Fecha"><?= e_show_venta($pago['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</div>

<script>
// Cierra el modal si estamos dentro de uno, si no navega al índice de ventas
function _ventaShowVolver() {
    var modal = document.getElementById('globalModal');
    if (modal && modal.style.display !== 'none') {
        modal.style.display = 'none';
    } else {
        loadContent('ventas.index', true);
    }
}
// Desde el modal cierra primero y luego abre la devolución en el SPA
function _ventaShowDevolucion(ventaId) {
    var modal = document.getElementById('globalModal');
    if (modal && modal.style.display !== 'none') {
        modal.style.display = 'none';
    }
    loadContent('devoluciones.create&venta_id=' + ventaId, true);
}
</script>
