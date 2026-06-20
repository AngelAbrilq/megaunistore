<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_eprov(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Editar proveedor</h2>
<p class="mf-subtitle">Actualiza la información del proveedor.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_eprov($flash['message']) ?></div>
<?php endif; ?>

<form id="form-editar-proveedor" action="index.php?route=proveedores.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_eprov($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= e_eprov((string) $proveedor['id']) ?>">

    <div class="mf-section">
        <h3>Información del proveedor</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="ep-nombre">Nombre *</label>
                <input type="text" id="ep-nombre" name="nombre" required maxlength="150"
                       value="<?= e_eprov($proveedor['nombre']) ?>">
            </div>
            <div class="mf-group">
                <label for="ep-nit">NIT/RUC *</label>
                <input type="text" id="ep-nit" name="ruc_nit" required maxlength="30"
                       value="<?= e_eprov($proveedor['ruc_nit'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ep-contacto">Nombre de contacto</label>
                <input type="text" id="ep-contacto" name="contacto_nombre" maxlength="100"
                       value="<?= e_eprov($proveedor['contacto_nombre'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ep-tel">Teléfono</label>
                <input type="text" id="ep-tel" name="telefono" maxlength="20"
                       value="<?= e_eprov($proveedor['telefono'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ep-email">Correo electrónico</label>
                <input type="email" id="ep-email" name="email" maxlength="150"
                       value="<?= e_eprov($proveedor['email'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ep-estado">Estado</label>
                <select id="ep-estado" name="estado">
                    <option value="1" <?= (int) ($proveedor['estado'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) ($proveedor['estado'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="mf-group span2">
                <label for="ep-dir">Dirección</label>
                <input type="text" id="ep-dir" name="direccion" maxlength="200"
                       value="<?= e_eprov($proveedor['direccion'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Actualizar proveedor</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
