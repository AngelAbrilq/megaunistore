<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $cliente
 * @var string $csrfToken
 */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_ecli(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Editar cliente</h2>
<p class="mf-subtitle">Actualiza la información del cliente.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_ecli($flash['message']) ?></div>
<?php endif; ?>

<form id="form-editar-cliente" action="index.php?route=clientes.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_ecli($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= e_ecli((string) $cliente['id']) ?>">

    <div class="mf-section">
        <h3>Información personal</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="ec-nombre">Nombre *</label>
                <input type="text" id="ec-nombre" name="nombre" required maxlength="100"
                       value="<?= e_ecli($cliente['nombre']) ?>">
            </div>
            <div class="mf-group">
                <label for="ec-apellido">Apellido</label>
                <input type="text" id="ec-apellido" name="apellido" maxlength="100"
                       value="<?= e_ecli($cliente['apellido'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ec-tipo">Tipo de documento</label>
                <select id="ec-tipo" name="tipo_documento">
                    <option value="">Sin documento</option>
                    <?php foreach (['CC'=>'Cédula de ciudadanía','CE'=>'Cédula de extranjería','NIT'=>'NIT','PAS'=>'Pasaporte'] as $val => $label): ?>
                        <option value="<?= e_ecli($val) ?>" <?= ($cliente['tipo_documento'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= e_ecli($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="ec-doc">Número de documento</label>
                <input type="text" id="ec-doc" name="numero_documento" maxlength="30"
                       value="<?= e_ecli($cliente['numero_documento'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ec-email">Correo electrónico</label>
                <input type="email" id="ec-email" name="email" maxlength="150"
                       value="<?= e_ecli($cliente['email'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ec-tel">Teléfono</label>
                <input type="text" id="ec-tel" name="telefono" maxlength="20"
                       value="<?= e_ecli($cliente['telefono'] ?? '') ?>">
            </div>
            <div class="mf-group span2">
                <label for="ec-dir">Dirección</label>
                <input type="text" id="ec-dir" name="direccion" maxlength="200"
                       value="<?= e_ecli($cliente['direccion'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Actualizar cliente</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
