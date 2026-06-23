<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $compras
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_com(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
.mod-topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
@media(max-width:680px){ .mod-topbar{flex-direction:column;align-items:stretch;} .mod-topbar h2{font-size:18px;} }
.mod-topbar h2 { margin:0 0 4px; color:#172554; font-size:22px; }
.mod-topbar p  { margin:0; color:#6b7280; font-size:14px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:12px; padding:10px 16px; font-weight:700; text-decoration:none; cursor:pointer; font-size:14px; white-space:nowrap; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
.btn-sm { padding:7px 12px; font-size:13px; }
.alert { padding:13px 16px; border-radius:14px; margin-bottom:16px; border:1px solid transparent; font-size:14px; }
.alert-success { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
.alert-error   { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
.card { background:#fff; border:1px solid #dbe3ef; border-radius:20px; box-shadow:0 4px 24px rgba(15,23,42,.08); overflow-x:auto; -webkit-overflow-scrolling:touch; }
table { width:100%; border-collapse:collapse; min-width:640px; }
th, td { padding:13px 15px; text-align:left; border-bottom:1px solid #e5e7eb; font-size:14px; vertical-align:middle; }
th { background:#eff6ff; color:#172554; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
tr:last-child td { border-bottom:none; }
.status { display:inline-flex; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:800; }
.st-pendiente { background:#fef3c7; color:#92400e; }
.st-recibida  { background:#dcfce7; color:#166534; }
.st-cancelada { background:#fee2e2; color:#991b1b; }
.empty { padding:40px; text-align:center; color:#6b7280; }
</style>

<div class="mod-topbar">
    <div>
        <h2>🛒 Compras a Proveedores</h2>
        <p>Órdenes de compra: al recibirlas, el inventario se actualiza automáticamente.</p>
    </div>
    <button class="btn btn-primary" onclick="loadContent('compras.create')">+ Nueva orden de compra</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_com($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <?php if (empty($compras)): ?>
        <div class="empty">No hay órdenes de compra registradas todavía.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Tienda</th>
                    <th>Proveedor</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $c): ?>
                    <tr>
                        <td><strong>#<?= (int) $c['id'] ?></strong></td>
                        <td><?= e_com((string) $c['fecha']) ?></td>
                        <td><?= e_com($c['tienda_nombre']) ?></td>
                        <td><?= e_com($c['proveedor_nombre']) ?></td>
                        <td><?= (int) $c['items'] ?></td>
                        <td><strong>$<?= number_format((float) $c['total'], 2) ?></strong></td>
                        <td><span class="status st-<?= e_com($c['estado']) ?>"><?= e_com(ucfirst($c['estado'])) ?></span></td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="loadContent('compras.show&id=<?= (int) $c['id'] ?>')">Ver detalle</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
