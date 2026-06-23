<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $cupon
 * @var array $tiendas
 */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_ecup(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
.mf-title { font-size:20px; font-weight:800; color:#172554; margin:0 0 4px; }
.mf-subtitle { font-size:13px; color:#6b7280; margin:0 0 20px; }
.mf-alert { padding:11px 14px; border-radius:12px; margin-bottom:14px; font-size:14px; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; }
.mf-section { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:18px; margin-bottom:16px; }
.mf-section h3 { margin:0 0 14px; color:#172554; font-size:15px; }
.mf-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.mf-group { display:flex; flex-direction:column; gap:6px; }
.mf-group.span2 { grid-column:1/-1; }
label { font-size:13px; font-weight:700; color:#374151; }
input, textarea, select {
    width:100%; border:1px solid #dbe3ef; border-radius:10px;
    padding:10px 12px; font-size:14px; outline:none; background:#fff;
    box-sizing:border-box; font-family:inherit;
}
textarea { min-height:80px; resize:vertical; }
input:focus, textarea:focus, select:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.mf-actions { display:flex; gap:10px; margin-top:16px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:10px; padding:11px 18px; font-weight:700; cursor:pointer; font-size:14px; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
@media(max-width:520px){.mf-grid{grid-template-columns:1fr;}}
</style>

<h2 class="mf-title">Editar cupón</h2>
<p class="mf-subtitle">Actualiza la información del cupón.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_ecup($flash['message']) ?></div>
<?php endif; ?>

<form id="form-editar-cupon" action="index.php?route=cupones.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_ecup($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= e_ecup((string) $cupon['id']) ?>">

    <div class="mf-section">
        <h3>Datos del cupón</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="ec-codigo">Código *</label>
                <input type="text" id="ec-codigo" name="codigo" required maxlength="50"
                       value="<?= e_ecup($cupon['codigo']) ?>" style="text-transform:uppercase">
            </div>
            <div class="mf-group">
                <label for="ec-tienda">Tienda</label>
                <select id="ec-tienda" name="tienda_id">
                    <option value="">Global (todas las tiendas)</option>
                    <?php foreach ($tiendas as $t): ?>
                        <?php if ($t !== null): ?>
                        <option value="<?= e_ecup((string) $t['id']) ?>"
                                <?= (int)($cupon['tienda_id']??0) === (int)$t['id'] ? 'selected' : '' ?>>
                            <?= e_ecup($t['nombre']) ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="ec-tipo">Tipo de descuento *</label>
                <select id="ec-tipo" name="tipo_descuento" required>
                    <option value="porcentaje" <?= ($cupon['tipo_descuento']??'') === 'porcentaje' ? 'selected' : '' ?>>Porcentaje (%)</option>
                    <option value="fijo" <?= ($cupon['tipo_descuento']??'') === 'fijo' ? 'selected' : '' ?>>Valor fijo ($)</option>
                </select>
            </div>
            <div class="mf-group">
                <label for="ec-valor">Valor del descuento *</label>
                <input type="number" id="ec-valor" name="valor_descuento" required min="0.01" step="0.01"
                       value="<?= e_ecup((string)($cupon['valor_descuento']??'')) ?>">
            </div>
            <div class="mf-group">
                <label for="ec-minimo">Monto mínimo</label>
                <input type="number" id="ec-minimo" name="monto_minimo" min="0" step="0.01"
                       value="<?= e_ecup((string)($cupon['monto_minimo']??'')) ?>">
            </div>
            <div class="mf-group">
                <label for="ec-max">Descuento máximo</label>
                <input type="number" id="ec-max" name="descuento_maximo" min="0" step="0.01"
                       value="<?= e_ecup((string)($cupon['descuento_maximo']??'')) ?>">
            </div>
            <div class="mf-group">
                <label for="ec-inicio">Fecha inicio</label>
                <input type="date" id="ec-inicio" name="fecha_inicio"
                       value="<?= e_ecup($cupon['fecha_inicio']??'') ?>">
            </div>
            <div class="mf-group">
                <label for="ec-fin">Fecha fin</label>
                <input type="date" id="ec-fin" name="fecha_fin"
                       value="<?= e_ecup($cupon['fecha_fin']??'') ?>">
            </div>
            <div class="mf-group">
                <label for="ec-usos">Usos máximos</label>
                <input type="number" id="ec-usos" name="usos_maximos" min="1"
                       value="<?= e_ecup((string)($cupon['usos_maximos']??'')) ?>">
            </div>
            <div class="mf-group">
                <label for="ec-activo">Estado</label>
                <select id="ec-activo" name="activo">
                    <option value="1" <?= (int)($cupon['activo']??1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int)($cupon['activo']??1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="mf-group span2">
                <label for="ec-desc">Descripción</label>
                <textarea id="ec-desc" name="descripcion"><?= e_ecup($cupon['descripcion']??'') ?></textarea>
            </div>
        </div>
    </div>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Actualizar cupón</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
