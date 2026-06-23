<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var bool $esAdminDirecto
 * @var array|null $flash
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}
function e_chpw(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$csrfToken = $_SESSION['csrf_token'] ?? '';
// $esAdminDirecto y $flash vienen del controller
?>

<style>
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;
      box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.card h2{margin:0 0 16px;color:#172554;font-size:18px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.alert-info{background:#eff6ff;color:#1e3a8a;border-color:#bfdbfe}
.form-group{margin-bottom:18px}
label{display:block;margin-bottom:8px;font-weight:700;font-size:14px;color:#1f2937}
input{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:13px 14px;
      font-size:15px;outline:none;background:#fff;box-sizing:border-box}
input:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.help{display:block;color:#6b7280;font-size:12px;margin-top:6px}
.strength{height:4px;border-radius:4px;margin-top:8px;background:#e5e7eb;overflow:hidden}
.strength-bar{height:100%;border-radius:4px;width:0;transition:width .3s,background .3s}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:12px 18px;
     font-weight:800;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
</style>

<div style="max-width:560px;margin:0 auto;padding:24px 20px">

    <div style="margin-bottom:24px">
        <h2 style="margin:0 0 6px;color:#172554;font-size:22px">Cambiar contraseña</h2>
        <p style="margin:0;color:#6b7280;font-size:14px">
            <?php if ($esAdminDirecto): ?>
                Como administrador, el cambio es inmediato.
            <?php else: ?>
                Tu solicitud será revisada por un administrador antes de aplicarse.
            <?php endif; ?>
        </p>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_chpw($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_chpw($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!$esAdminDirecto): ?>
        <div class="alert alert-info">
            ℹ️ Al enviar, un administrador recibirá tu solicitud. Te notificarán por correo cuando sea procesada.
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Nueva contraseña</h2>

        <form action="index.php?route=password.change.post" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= e_chpw($csrfToken) ?>">

            <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password"
                       required minlength="8" placeholder="Mínimo 8 caracteres"
                       autocomplete="new-password">
                <div class="strength"><div class="strength-bar" id="strengthBar"></div></div>
                <span class="help" id="strengthLabel">Escribe tu nueva contraseña</span>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       required minlength="8" placeholder="Repite la contraseña"
                       autocomplete="new-password">
                <span class="help" id="matchLabel"></span>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
                <button type="submit" class="btn btn-primary">
                    <?= $esAdminDirecto ? 'Actualizar contraseña' : 'Enviar solicitud' ?>
                </button>
                <button type="button" class="btn btn-secondary"
                        onclick="history.back()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
const pwInput  = document.getElementById('password');
const cfInput  = document.getElementById('password_confirm');
const bar      = document.getElementById('strengthBar');
const strLabel = document.getElementById('strengthLabel');
const mLabel   = document.getElementById('matchLabel');

function fuerza(pw) {
    let s = 0;
    if (pw.length >= 8)  s++;
    if (pw.length >= 12) s++;
    if (/[A-Z]/.test(pw)) s++;
    if (/[0-9]/.test(pw)) s++;
    if (/[^A-Za-z0-9]/.test(pw)) s++;
    return s;
}

pwInput.addEventListener('input', function () {
    const s = fuerza(this.value);
    const c = ['#e5e7eb','#ef4444','#f97316','#eab308','#22c55e','#16a34a'][s] || '#e5e7eb';
    const t = ['','Muy débil','Débil','Regular','Buena','Muy segura'][s] || '';
    bar.style.width = (s / 5 * 100) + '%';
    bar.style.background = c;
    strLabel.textContent = t;
    check();
});
cfInput.addEventListener('input', check);

function check() {
    if (!cfInput.value) { mLabel.textContent = ''; return; }
    const ok = pwInput.value === cfInput.value;
    mLabel.textContent = ok ? '✅ Coinciden' : '❌ No coinciden';
    mLabel.style.color = ok ? '#166534' : '#991b1b';
}
</script>
