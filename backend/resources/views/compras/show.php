<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $compra
 * @var string $csrfToken
 * @var array $detalle
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_scom(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
.mod-topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
@media(max-width:680px){ .mod-topbar{flex-direction:column;align-items:stretch;} .mod-topbar h2{font-size:18px;} }
.mod-topbar h2 { margin:0 0 4px; color:#172554; font-size:22px; }
.mod-topbar p  { margin:0; color:#6b7280; font-size:14px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:12px; padding:10px 16px; font-weight:700; text-decoration:none; cursor:pointer; font-size:14px; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
.btn-success   { background:#dcfce7; color:#166534; }
.btn-danger    { background:#fee2e2; color:#991b1b; }
.alert { padding:13px 16px; border-radius:14px; margin-bottom:16px; border:1px solid transparent; font-size:14px; }
.alert-success { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
.alert-error   { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
.card { background:#fff; border:1px solid #dbe3ef; border-radius:20px; box-shadow:0 4px 24px rgba(15,23,42,.08); overflow-x:auto; -webkit-overflow-scrolling:touch; margin-bottom:18px; }
.card-pad { padding:22px; }
.info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
.info-item .lbl { font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
.info-item .val { font-size:15px; font-weight:700; color:#172554; margin-top:2px; }
table { width:100%; border-collapse:collapse; min-width:640px; }
th, td { padding:12px 15px; text-align:left; border-bottom:1px solid #e5e7eb; font-size:14px; }
th { background:#eff6ff; color:#172554; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
tr:last-child td { border-bottom:none; }
.status { display:inline-flex; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:800; }
.st-pendiente { background:#fef3c7; color:#92400e; }
.st-recibida  { background:#dcfce7; color:#166534; }
.st-cancelada { background:#fee2e2; color:#991b1b; }
.tot { text-align:right; padding:16px 22px; font-size:15px; }
.tot strong { color:#172554; font-size:18px; }
</style>

<div class="mod-topbar">
    <div>
        <h2>🛒 Orden de compra #<?= (int) $compra['id'] ?></h2>
        <p>Detalle completo de la orden y su estado.</p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-secondary" onclick="loadContent('compras.index')">← Volver</button>
        <?php if ($compra['estado'] === 'pendiente'): ?>
            <form action="index.php?route=compras.recibir" method="POST" style="display:inline;" onsubmit="return confirm('¿Confirmar recepción? El stock se cargará al inventario.');">
                <input type="hidden" name="csrf_token" value="<?= e_scom($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $compra['id'] ?>">
                <button type="submit" class="btn btn-success">✓ Marcar como recibida</button>
            </form>
            <form action="index.php?route=compras.cancelar" method="POST" style="display:inline;" onsubmit="return confirm('¿Cancelar esta orden de compra?');">
                <input type="hidden" name="csrf_token" value="<?= e_scom($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $compra['id'] ?>">
                <button type="submit" class="btn btn-danger">✕ Cancelar orden</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_scom($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <div class="info-grid">
        <div class="info-item"><div class="lbl">Tienda</div><div class="val"><?= e_scom($compra['tienda_nombre']) ?></div></div>
        <div class="info-item"><div class="lbl">Proveedor</div><div class="val"><?= e_scom($compra['proveedor_nombre']) ?></div></div>
        <div class="info-item"><div class="lbl">Fecha</div><div class="val"><?= e_scom((string) $compra['fecha']) ?></div></div>
        <div class="info-item"><div class="lbl">Estado</div><div class="val"><span class="status st-<?= e_scom($compra['estado']) ?>"><?= e_scom(ucfirst($compra['estado'])) ?></span></div></div>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $d): ?>
                <tr>
                    <td><strong><?= e_scom($d['producto_nombre']) ?></strong></td>
                    <td><?= number_format((float) $d['cantidad'], 2) ?></td>
                    <td>$<?= number_format((float) $d['precio_unitario'], 2) ?></td>
                    <td>$<?= number_format((float) $d['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="tot">
        Subtotal: $<?= number_format((float) $compra['subtotal'], 2) ?> &nbsp;·&nbsp;
        Impuesto: $<?= number_format((float) $compra['impuesto'], 2) ?> &nbsp;·&nbsp;
        <strong>Total: $<?= number_format((float) $compra['total'], 2) ?></strong>
    </div>
</div>
