<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $centros
 * @var string $csrfToken
 * @var array $cuentas
 * @var int $tiendaSel
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_asc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';
?>
<style>
table.lineas { width:100%; border-collapse:collapse; margin-top:12px; }
table.lineas th, table.lineas td { padding:8px 10px; border-bottom:1px solid #e5e7eb; font-size:14px; }
table.lineas th { background:#eff6ff; color:#172554; font-size:12px; text-transform:uppercase; }
table.lineas input, table.lineas select { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px; font-size:13px; font-family:inherit; }
.balance-box { display:flex; gap:24px; justify-content:flex-end; margin-top:12px; font-size:15px; font-weight:800; }
.balance-ok  { color:#166534; }
.balance-bad { color:#991b1b; }
</style>

<div class="mod-topbar">
    <div>
        <h2>📑 Nuevo asiento contable</h2>
        <p>Los débitos deben ser iguales a los créditos (partida doble).</p>
    </div>
    <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <?php if (!empty($tiendas)): ?>
        <div class="fg" style="min-width:220px;">
            <label>Tienda del asiento</label>
            <select onchange="loadContent('contabilidad.asiento.create&tienda_id=' + this.value)">
                <?php foreach ($tiendas as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tiendaSel ? 'selected' : '' ?>><?= e_asc($t['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <button class="btn btn-secondary" onclick="loadContent('contabilidad.asientos')">← Volver</button>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-error"><?= e_asc($flash['message']) ?></div>
<?php endif; ?>

<form action="index.php?route=contabilidad.asiento.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_asc($csrfToken) ?>">
    <input type="hidden" name="tienda_id" value="<?= (int) $tiendaSel ?>">

    <div class="card card-pad">
        <div class="grid3">
            <div class="fg">
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="fg" style="grid-column:span 2;">
                <label>Concepto *</label>
                <input type="text" name="concepto" required maxlength="255" placeholder="Descripción del asiento">
            </div>
        </div>
    </div>

    <div class="card card-pad">
        <strong style="color:#172554;">Movimientos</strong>
        <table class="lineas">
            <thead>
                <tr><th style="width:30%;">Cuenta</th><th>Descripción</th><th>Centro costo</th><th>Débito</th><th>Crédito</th><th></th></tr>
            </thead>
            <tbody id="asiento-lineas"></tbody>
        </table>
        <button type="button" class="btn btn-secondary btn-sm" style="margin-top:12px;" onclick="agregarLineaAsiento()">+ Agregar línea</button>
        <div class="balance-box">
            <span>Débitos: $<span id="tot-debitos">0.00</span></span>
            <span>Créditos: $<span id="tot-creditos">0.00</span></span>
            <span id="balance-msg" class="balance-bad">No balanceado</span>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar asiento (borrador)</button>
</form>

<script>
(function () {
    window.cuentasAsiento = [
        <?php foreach ($cuentas as $c): ?>
        { id: <?= (int) $c['id'] ?>, txt: <?= json_encode(htmlspecialchars($c['codigo'] . ' — ' . $c['nombre'], ENT_QUOTES, 'UTF-8')) ?> },
        <?php endforeach; ?>
    ];
    window.centrosAsiento = [
        <?php foreach ($centros as $cc): ?>
        { id: <?= (int) $cc['id'] ?>, txt: <?= json_encode(htmlspecialchars($cc['codigo'] . ' — ' . $cc['nombre'], ENT_QUOTES, 'UTF-8')) ?> },
        <?php endforeach; ?>
    ];

    window.agregarLineaAsiento = function () {
        const tbody = document.getElementById('asiento-lineas');
        let optsCta = '<option value="">— Cuenta —</option>';
        window.cuentasAsiento.forEach(c => { optsCta += `<option value="${c.id}">${c.txt}</option>`; });
        let optsCC = '<option value="">—</option>';
        window.centrosAsiento.forEach(c => { optsCC += `<option value="${c.id}">${c.txt}</option>`; });

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="cuenta_id[]">${optsCta}</select></td>
            <td><input type="text" name="descripcion[]" maxlength="255"></td>
            <td><select name="centro_costo_id[]">${optsCC}</select></td>
            <td><input type="number" name="debito[]" step="0.01" min="0" value="0" oninput="recalcAsiento()"></td>
            <td><input type="number" name="credito[]" step="0.01" min="0" value="0" oninput="recalcAsiento()"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); recalcAsiento();">✕</button></td>
        `;
        tbody.appendChild(tr);
    };

    window.recalcAsiento = function () {
        let deb = 0, cre = 0;
        document.querySelectorAll('#asiento-lineas tr').forEach(tr => {
            deb += parseFloat(tr.querySelector('[name="debito[]"]').value) || 0;
            cre += parseFloat(tr.querySelector('[name="credito[]"]').value) || 0;
        });
        document.getElementById('tot-debitos').textContent  = deb.toFixed(2);
        document.getElementById('tot-creditos').textContent = cre.toFixed(2);
        const msg = document.getElementById('balance-msg');
        const ok = Math.abs(deb - cre) < 0.01 && deb > 0;
        msg.textContent = ok ? '✓ Balanceado' : 'No balanceado';
        msg.className = ok ? 'balance-ok' : 'balance-bad';
    };

    agregarLineaAsiento();
    agregarLineaAsiento();
})();
</script>
