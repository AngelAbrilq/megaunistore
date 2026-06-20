<?php
/**
 * Vista: usuarios/asignar_rol.php
 * Modal partial — abre desde usuarios/index.php con openModal()
 */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_role_user(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.mf-title { font-size:20px; font-weight:800; color:#172554; margin:0 0 4px; }
.mf-subtitle { font-size:13px; color:#6b7280; margin:0 0 20px; }
.mf-alert { padding:11px 14px; border-radius:12px; margin-bottom:14px; font-size:14px; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; }
.mf-alert.success { border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
.mf-section { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:18px; margin-bottom:16px; }
.mf-section h3 { margin:0 0 14px; color:#172554; font-size:15px; }
.mf-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.mf-group { display:flex; flex-direction:column; gap:6px; }
label { font-size:13px; font-weight:700; color:#374151; }
select {
    width:100%; border:1px solid #dbe3ef; border-radius:10px;
    padding:10px 12px; font-size:14px; outline:none; background:#fff;
    box-sizing:border-box; font-family:inherit;
}
select:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.help { font-size:12px; color:#6b7280; margin-top:4px; }
.mf-actions { display:flex; gap:10px; margin-top:16px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:10px; padding:11px 18px; font-weight:700; cursor:pointer; font-size:14px; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
.role-list { display:flex; flex-direction:column; gap:8px; }
.role-item { padding:10px 14px; border-radius:12px; background:#fff; border:1px solid #e5e7eb; }
.role-item strong { color:#172554; font-size:14px; }
.role-item small { display:block; color:#6b7280; font-size:12px; margin-top:2px; }
@media(max-width:520px){.mf-grid{grid-template-columns:1fr;}}
</style>

<h2 class="mf-title">Asignar rol</h2>
<p class="mf-subtitle">
    Usuario: <strong><?= e_role_user(trim($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''))) ?></strong>
    — <?= e_role_user($usuario['email'] ?? '') ?>
</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert <?= $flash['type'] === 'success' ? 'success' : '' ?>">
        <?= e_role_user($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="mf-section">
    <h3>Asignar nuevo rol</h3>
    <form id="form-asignar-rol" action="index.php?route=usuarios.guardar_rol" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e_role_user($csrfToken) ?>">
        <input type="hidden" name="usuario_id" value="<?= e_role_user((string) $usuario['id']) ?>">

        <div class="mf-grid">
            <div class="mf-group">
                <label for="ar-rol">Rol *</label>
                <select id="ar-rol" name="rol_id" required>
                    <option value="">Seleccionar rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= e_role_user((string) $rol['id']) ?>">
                            <?= e_role_user($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="ar-tienda">Tienda</label>
                <select id="ar-tienda" name="tienda_id">
                    <option value="">Global / No aplica</option>
                    <?php foreach ($tiendas as $tienda): ?>
                        <option value="<?= e_role_user((string) $tienda['id']) ?>">
                            <?= e_role_user($tienda['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="help">Superadmin y Sistema no necesitan tienda.</span>
            </div>
        </div>

        <div class="mf-actions">
            <button type="submit" class="btn btn-primary">Asignar rol</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
        </div>
    </form>
</div>

<?php if (!empty($rolesUsuario)): ?>
<div class="mf-section">
    <h3>Roles actuales</h3>
    <div class="role-list">
        <?php foreach ($rolesUsuario as $ru): ?>
            <div class="role-item">
                <strong><?= e_role_user($ru['rol_nombre'] ?? $ru['nombre'] ?? '—') ?></strong>
                <small>
                    Alcance: <?= $ru['tienda_id'] !== null
                        ? 'Tienda ID ' . e_role_user((string) $ru['tienda_id'])
                        : 'Global'
                    ?>
                </small>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
