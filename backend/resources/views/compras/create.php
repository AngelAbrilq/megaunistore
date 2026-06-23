<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $productos
 * @var array $proveedores
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_ccom(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
.mod-topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
.mod-topbar h2 { margin:0 0 4px; color:#172554; font-size:22px; }
.mod-topbar p  { margin:0; color:#6b7280; font-size:14px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:12px; padding:10px 16px; font-weight:700; text-decoration:none; cursor:pointer; font-size:14px; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
.btn-danger    { background:#fee2e2; color:#991b1b; }
.btn-sm { padding:7px 12px; font-size:13px; }
.alert { padding:13px 16px; border-radius:14px; margin-bottom:16px; border:1px solid transparent; font-size:14px; }
.alert-error { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
.card { background:#fff; border:1px solid #dbe3ef; border-radius:20px; box-shadow:0 4px 24px rgba(15,23,42,.08); padding:24px; margin-bottom:18px; }
.fg { display:flex; flex-direction:column; gap:6px; }
.fg label { font-size:13px; font-weight:700; color:#374151; }
.fg input, .fg select { border:1px solid #d1d5db; border-radius:10px; padding:10px 12px; font-size:14px; font-family:inherit; }
.grid3 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
table.lineas { width:100%; border-collapse:collapse; margin-top:12px; }
table.lineas th, table.lineas td { padding:8px 10px; border-bottom:1px solid #e5e7eb; font-size:14px; }
table.lineas th { background:#eff6ff; color:#172554; font-size:12px; text-transform:uppercase; }
table.lineas input, table.lineas select { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px; font-size:14px; font-family:inherit; }
.total-box { text-align:right; font-size:16px; font-weight:800; color:#172554; margin-top:12px; }
@media(max-width:640px){.grid3{grid-template-columns:1fr;}}
</style>

<div class="mod-topbar">
    <div>
        <h2>🛒 Nueva orden de compra</h2>
        <p>Selecciona el proveedor y agrega los productos a comprar.</p>
    </div>
    <button class="btn btn-secondary" onclick="loadContent('compras.index')">← Volver</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-error"><?= e_ccom($flash['message']) ?></div>
<?php endif; ?>

<form action="index.php?route=compras.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_ccom($csrfToken) ?>">

    <div class="card">
        <div class="grid3">
            <?php if (!empty($tiendas)): ?>
            <div class="fg">
                <label>Tienda *</label>
                <select name="tienda_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($tiendas as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= e_ccom($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="fg">
                <label>Proveedor *</label>
                <select name="proveedor_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($proveedores as $p): ?>
                        <?php if ((int) ($p['estado'] ?? 1) === 1): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e_ccom($p['nombre']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="fg">
                <label>Impuesto total ($)</label>
                <input type="number" name="impuesto" step="0.01" min="0" value="0">
            </div>
        </div>
    </div>

    <div class="card">
        <strong style="color:#172554;">Productos de la orden</strong>
        <table class="lineas">
            <thead>
                <tr><th style="width:45%;">Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody id="lineas-body"></tbody>
        </table>
        <button type="button" class="btn btn-secondary btn-sm" style="margin-top:12px;" onclick="agregarLinea()">+ Agregar producto</button>
        <div class="total-box">Subtotal: $<span id="subtotal-compra">0.00</span></div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar orden de compra</button>
</form>

<script>
(function () {
    window.productosCompra = [
        <?php foreach ($productos as $pr): ?>
        { id: <?= (int) $pr['id'] ?>, nombre: <?= json_encode(htmlspecialchars((string) $pr['nombre'], ENT_QUOTES, 'UTF-8')) ?> },
        <?php endforeach; ?>
    ];

    window.agregarLinea = function () {
        const tbody = document.getElementById('lineas-body');
        const tr = document.createElement('tr');
        let opts = '<option value="">— Producto —</option>';
        window.productosCompra.forEach(p => { opts += `<option value="${p.id}">${p.nombre}</option>`; });
        tr.innerHTML = `
            <td><select name="producto_id[]" required>${opts}</select></td>
            <td><input type="number" name="cantidad[]" step="0.01" min="0.01" value="1" oninput="recalcular()" required></td>
            <td><input type="number" name="precio_unitario[]" step="0.01" min="0" value="0" oninput="recalcular()" required></td>
            <td class="sub-linea" style="font-weight:700;">$0.00</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); recalcular();">✕</button></td>
        `;
        tbody.appendChild(tr);
    };

    window.recalcular = function () {
        let total = 0;
        document.querySelectorAll('#lineas-body tr').forEach(tr => {
            const cant   = parseFloat(tr.querySelector('[name="cantidad[]"]').value) || 0;
            const precio = parseFloat(tr.querySelector('[name="precio_unitario[]"]').value) || 0;
            const sub    = cant * precio;
            tr.querySelector('.sub-linea').textContent = '$' + sub.toFixed(2);
            total += sub;
        });
        document.getElementById('subtotal-compra').textContent = total.toFixed(2);
    };

    agregarLinea();
})();
</script>
