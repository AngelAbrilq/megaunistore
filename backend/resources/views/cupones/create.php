<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $tiendas
 */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_ccup(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Nuevo cupón</h2>
<p class="mf-subtitle">Crea un cupón de descuento.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_ccup($flash['message']) ?></div>
<?php endif; ?>

<form id="form-crear-cupon" action="index.php?route=cupones.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_ccup($csrfToken) ?>">

    <div class="mf-section">
        <h3>Datos del cupón</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="cc-codigo">Código *</label>
                <input type="text" id="cc-codigo" name="codigo" required maxlength="50" placeholder="Ej: DESCUENTO20"
                       style="text-transform:uppercase">
            </div>
            <div class="mf-group">
                <label for="cc-tienda">Tienda</label>
                <select id="cc-tienda" name="tienda_id">
                    <option value="">Global (todas las tiendas)</option>
                    <?php foreach ($tiendas as $t): ?>
                        <?php if ($t !== null): ?>
                        <option value="<?= e_ccup((string) $t['id']) ?>"><?= e_ccup($t['nombre']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="cc-tipo">Tipo de descuento *</label>
                <select id="cc-tipo" name="tipo_descuento" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="porcentaje">Porcentaje (%)</option>
                    <option value="fijo">Valor fijo ($)</option>
                </select>
            </div>
            <div class="mf-group">
                <label for="cc-valor">Valor del descuento *</label>
                <input type="number" id="cc-valor" name="valor_descuento" required min="0.01" step="0.01" placeholder="Ej: 20">
            </div>
            <div class="mf-group">
                <label for="cc-minimo">Monto mínimo de compra</label>
                <input type="number" id="cc-minimo" name="monto_minimo" min="0" step="0.01" placeholder="Ej: 50000">
            </div>
            <div class="mf-group">
                <label for="cc-max">Descuento máximo</label>
                <input type="number" id="cc-max" name="descuento_maximo" min="0" step="0.01" placeholder="Opcional">
            </div>
            <div class="mf-group">
                <label for="cc-inicio">Fecha inicio</label>
                <input type="date" id="cc-inicio" name="fecha_inicio">
            </div>
            <div class="mf-group">
                <label for="cc-fin">Fecha fin</label>
                <input type="date" id="cc-fin" name="fecha_fin">
            </div>
            <div class="mf-group">
                <label for="cc-usos">Usos máximos</label>
                <input type="number" id="cc-usos" name="usos_maximos" min="1" placeholder="Sin límite">
            </div>
            <div class="mf-group">
                <label for="cc-activo">Estado</label>
                <select id="cc-activo" name="activo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="mf-group span2">
                <label for="cc-desc">Descripción</label>
                <textarea id="cc-desc" name="descripcion" placeholder="Descripción del cupón"></textarea>
            </div>
        </div>
    </div>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Crear cupón</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
