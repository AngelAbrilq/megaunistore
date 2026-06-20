<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_eemp(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Editar empleado</h2>
<p class="mf-subtitle">Actualiza los datos laborales del empleado.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_eemp($flash['message']) ?></div>
<?php endif; ?>

<form id="form-editar-empleado" action="index.php?route=empleados.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_eemp($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= e_eemp((string) $empleado['id']) ?>">

    <div class="mf-section">
        <h3>Datos laborales</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="ee-codigo">Código de empleado *</label>
                <input type="text" id="ee-codigo" name="codigo_empleado" required maxlength="30"
                       value="<?= e_eemp($empleado['codigo_empleado'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ee-ingreso">Fecha de ingreso *</label>
                <input type="date" id="ee-ingreso" name="fecha_ingreso" required
                       value="<?= e_eemp($empleado['fecha_ingreso'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ee-salario">Salario base *</label>
                <input type="number" id="ee-salario" name="salario_base" required min="0" step="0.01"
                       value="<?= e_eemp($empleado['salario_base'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ee-estado">Estado</label>
                <select id="ee-estado" name="estado">
                    <option value="activo" <?= ($empleado['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($empleado['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Actualizar empleado</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
