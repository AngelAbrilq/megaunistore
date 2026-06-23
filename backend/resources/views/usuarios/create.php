<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $roles
 * @var array $tiendas
 */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_cusr(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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

<h2 class="mf-title">Nuevo usuario</h2>
<p class="mf-subtitle">Crea una cuenta de acceso al sistema.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_cusr($flash['message']) ?></div>
<?php endif; ?>

<form id="form-crear-usuario" action="index.php?route=usuarios.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_cusr($csrfToken) ?>">

    <div class="mf-section">
        <h3>Datos personales</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="cu-nombre">Nombre *</label>
                <input type="text" id="cu-nombre" name="nombre" required maxlength="100" placeholder="Nombre">
            </div>
            <div class="mf-group">
                <label for="cu-apellido">Apellido</label>
                <input type="text" id="cu-apellido" name="apellido" maxlength="100" placeholder="Apellido">
            </div>
            <div class="mf-group">
                <label for="cu-email">Correo electrónico *</label>
                <input type="email" id="cu-email" name="email" required maxlength="150" placeholder="usuario@correo.com">
            </div>
            <div class="mf-group">
                <label for="cu-tel">Teléfono</label>
                <input type="text" id="cu-tel" name="telefono" maxlength="20" placeholder="Opcional">
            </div>
            <div class="mf-group">
                <label for="cu-pass">Contraseña *</label>
                <input type="password" id="cu-pass" name="password" required minlength="8" placeholder="Mínimo 8 caracteres">
            </div>
            <div class="mf-group">
                <label for="cu-pass2">Confirmar contraseña *</label>
                <input type="password" id="cu-pass2" name="password_confirmation" required placeholder="Repetir contraseña">
            </div>
        </div>
    </div>

    <?php if (!empty($roles)): ?>
    <div class="mf-section">
        <h3>Rol inicial</h3>
        <div class="mf-grid">
            <div class="mf-group">
                <label for="cu-rol">Rol</label>
                <select id="cu-rol" name="rol_id">
                    <option value="">Sin rol inicial</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= e_cusr((string) $r['id']) ?>"><?= e_cusr($r['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="cu-tienda">Tienda (si aplica)</label>
                <select id="cu-tienda" name="tienda_id">
                    <option value="">Sin tienda</option>
                    <?php foreach ($tiendas as $t): ?>
                        <option value="<?= e_cusr((string) $t['id']) ?>"><?= e_cusr($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Crear usuario</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
