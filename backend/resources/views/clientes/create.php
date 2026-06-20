<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_ccli(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Nuevo cliente</h2>
<p class="mf-subtitle">Registra un nuevo cliente en el sistema.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_ccli($flash['message']) ?></div>
<?php endif; ?>

<form id="form-crear-cliente" action="index.php?route=clientes.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_ccli($csrfToken) ?>">

    <div class="mf-section">
        <h3>Información personal</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="cc-nombre">Nombre *</label>
                <input type="text" id="cc-nombre" name="nombre" required maxlength="100" placeholder="Nombre">
            </div>
            <div class="mf-group">
                <label for="cc-apellido">Apellido</label>
                <input type="text" id="cc-apellido" name="apellido" maxlength="100" placeholder="Apellido">
            </div>
            <div class="mf-group">
                <label for="cc-tipo">Tipo de documento</label>
                <select id="cc-tipo" name="tipo_documento">
                    <option value="">Sin documento</option>
                    <option value="CC">Cédula de ciudadanía</option>
                    <option value="CE">Cédula de extranjería</option>
                    <option value="NIT">NIT</option>
                    <option value="PAS">Pasaporte</option>
                </select>
            </div>
            <div class="mf-group">
                <label for="cc-doc">Número de documento</label>
                <input type="text" id="cc-doc" name="numero_documento" maxlength="30" placeholder="Ej: 123456789">
            </div>
            <div class="mf-group">
                <label for="cc-email">Correo electrónico</label>
                <input type="email" id="cc-email" name="email" maxlength="150" placeholder="cliente@correo.com">
            </div>
            <div class="mf-group">
                <label for="cc-tel">Teléfono</label>
                <input type="text" id="cc-tel" name="telefono" maxlength="20" placeholder="Ej: 3001234567">
            </div>
            <div class="mf-group span2">
                <label for="cc-dir">Dirección</label>
                <input type="text" id="cc-dir" name="direccion" maxlength="200" placeholder="Dirección del cliente">
            </div>
        </div>
    </div>

    <?php if (!empty($tiendas)): ?>
    <div class="mf-section">
        <h3>Tienda asociada</h3>
        <div class="mf-group">
            <label for="cc-tienda">Tienda (opcional)</label>
            <select id="cc-tienda" name="tienda_id">
                <option value="">Sin tienda específica</option>
                <?php foreach ($tiendas as $t): ?>
                    <option value="<?= e_ccli((string) $t['id']) ?>"><?= e_ccli($t['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Guardar cliente</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
