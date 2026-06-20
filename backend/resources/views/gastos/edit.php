<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_gase(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';
?>

<div class="mod-topbar">
    <div>
        <h2>💸 Editar gasto #<?= (int) $gasto['id'] ?></h2>
        <p>Solo se pueden editar gastos en estado pendiente.</p>
    </div>
    <button class="btn btn-secondary" onclick="loadContent('gastos.index')">← Volver</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-error"><?= e_gase($flash['message']) ?></div>
<?php endif; ?>

<form action="index.php?route=gastos.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_gase($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= (int) $gasto['id'] ?>">

    <div class="card card-pad">
        <div class="grid3">
            <div class="fg">
                <label>Concepto *</label>
                <input type="text" name="concepto" required maxlength="255" value="<?= e_gase($gasto['concepto']) ?>">
            </div>
            <div class="fg">
                <label>Monto ($) *</label>
                <input type="number" name="monto" step="0.01" min="0.01" required value="<?= e_gase((string) $gasto['monto']) ?>">
            </div>
            <div class="fg">
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= e_gase((string) $gasto['fecha']) ?>" required>
            </div>
            <div class="fg">
                <label>Cuenta contable</label>
                <select name="cuenta_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($cuentas as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($gasto['cuenta_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= e_gase($c['codigo'] . ' — ' . $c['nombre'] . (isset($c['tienda_nombre']) ? ' (' . $c['tienda_nombre'] . ')' : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Centro de costo</label>
                <select name="centro_costo_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($centros as $cc): ?>
                        <option value="<?= (int) $cc['id'] ?>" <?= (int) ($gasto['centro_costo_id'] ?? 0) === (int) $cc['id'] ? 'selected' : '' ?>>
                            <?= e_gase($cc['codigo'] . ' — ' . $cc['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Proveedor</label>
                <select name="proveedor_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (int) ($gasto['proveedor_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>>
                            <?= e_gase($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Nº comprobante</label>
                <input type="text" name="comprobante" maxlength="100" value="<?= e_gase((string) ($gasto['comprobante'] ?? '')) ?>">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar gasto</button>
</form>
