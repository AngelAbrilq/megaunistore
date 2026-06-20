<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_cemp(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Nuevo empleado</h2>
<p class="mf-subtitle">Registra un empleado y asígnalo a una tienda.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_cemp($flash['message']) ?></div>
<?php endif; ?>

<form id="form-crear-empleado" action="index.php?route=empleados.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_cemp($csrfToken) ?>">

    <div class="mf-section">
        <h3>Asignación</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="ce-usuario">Usuario *</label>
                <select id="ce-usuario" name="usuario_id" required>
                    <option value="">Seleccionar usuario</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= e_cemp((string) $u['id']) ?>">
                            <?= e_cemp($u['nombre'] . ' ' . ($u['apellido'] ?? '') . ' — ' . $u['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="ce-tienda">Tienda *</label>
                <select id="ce-tienda" name="tienda_id" required>
                    <option value="">Seleccionar tienda</option>
                    <?php foreach ($tiendas as $t): ?>
                        <?php if ($t !== null): ?>
                        <option value="<?= e_cemp((string) $t['id']) ?>"><?= e_cemp($t['nombre']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="mf-section">
        <h3>Datos laborales</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="ce-codigo">Código de empleado *</label>
                <input type="text" id="ce-codigo" name="codigo_empleado" required maxlength="30" placeholder="Ej: EMP-001">
            </div>
            <div class="mf-group">
                <label for="ce-ingreso">Fecha de ingreso *</label>
                <input type="date" id="ce-ingreso" name="fecha_ingreso" required>
            </div>
            <div class="mf-group">
                <label for="ce-salario">Salario base *</label>
                <input type="number" id="ce-salario" name="salario_base" required min="0" step="0.01" placeholder="Ej: 1200000">
            </div>
            <div class="mf-group">
                <label for="ce-estado">Estado</label>
                <select id="ce-estado" name="estado">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Guardar empleado</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
