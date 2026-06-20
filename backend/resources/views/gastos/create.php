<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_gasc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';
?>

<div class="mod-topbar">
    <div>
        <h2>💸 Nuevo gasto</h2>
        <p>Registra un egreso operacional.</p>
    </div>
    <button class="btn btn-secondary" onclick="loadContent('gastos.index')">← Volver</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-error"><?= e_gasc($flash['message']) ?></div>
<?php endif; ?>

<form action="index.php?route=gastos.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_gasc($csrfToken) ?>">

    <div class="card card-pad">
        <div class="grid3">
            <?php if (!empty($tiendas)): ?>
            <div class="fg">
                <label>Tienda *</label>
                <select name="tienda_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($tiendas as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= e_gasc($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="fg">
                <label>Concepto *</label>
                <input type="text" name="concepto" required maxlength="255" placeholder="Ej: Pago de arriendo local">
            </div>
            <div class="fg">
                <label>Monto ($) *</label>
                <input type="number" name="monto" step="0.01" min="0.01" required>
            </div>
            <div class="fg">
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="fg">
                <label>Cuenta contable (gasto/costo)</label>
                <select name="cuenta_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($cuentas as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e_gasc($c['codigo'] . ' — ' . $c['nombre'] . (isset($c['tienda_nombre']) ? ' (' . $c['tienda_nombre'] . ')' : '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Centro de costo</label>
                <select name="centro_costo_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($centros as $cc): ?>
                        <option value="<?= (int) $cc['id'] ?>"><?= e_gasc($cc['codigo'] . ' — ' . $cc['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Proveedor</label>
                <select name="proveedor_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e_gasc($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Nº comprobante</label>
                <input type="text" name="comprobante" maxlength="100" placeholder="Factura / recibo">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar gasto</button>
</form>
