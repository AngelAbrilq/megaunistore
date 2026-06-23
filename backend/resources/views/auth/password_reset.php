<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array|null $flash
 * @var array|null $registro
 * @var string $token
 */

function e_pr(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
// $token y $registro vienen del controller
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nueva contraseña | Mega Uni Store</title>
    <style>
        :root{--primary:#1e3a8a;--primary-light:#2563eb;--border:#dbe3ef;--muted:#6b7280;}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;
             font-family:Arial,Helvetica,sans-serif;
             background:linear-gradient(135deg,#eff6ff 0%,#f8fafc 50%,#e0f2fe 100%);color:#111827}
        .card{width:100%;max-width:440px;background:#fff;border-radius:26px;
              box-shadow:0 20px 60px rgba(15,23,42,.15);border:1px solid var(--border);padding:36px;margin:24px}
        .card-header small{display:block;color:var(--primary-light);font-weight:700;
                           text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px}
        .card-header h2{margin:0 0 10px;font-size:26px;color:#172554}
        .card-header p{margin:0;color:var(--muted);font-size:14px;line-height:1.5}
        .alert{padding:13px 14px;border-radius:14px;font-size:14px;margin-bottom:18px;border:1px solid transparent}
        .alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
        .form-group{margin-bottom:18px}
        label{display:block;margin-bottom:8px;font-weight:700;font-size:14px;color:#1f2937}
        input{width:100%;border:1px solid var(--border);border-radius:14px;padding:13px 14px;
              font-size:15px;outline:none;background:#fbfdff}
        input:focus{border-color:var(--primary-light);box-shadow:0 0 0 4px rgba(37,99,235,.12);background:#fff}
        .help{display:block;color:var(--muted);font-size:12px;margin-top:6px}
        .strength{height:4px;border-radius:4px;margin-top:8px;background:#e5e7eb;overflow:hidden}
        .strength-bar{height:100%;border-radius:4px;width:0;transition:width .3s,background .3s}
        .btn{width:100%;border:0;border-radius:14px;
             background:linear-gradient(135deg,var(--primary),var(--primary-light));
             color:#fff;padding:14px;font-size:15px;font-weight:800;cursor:pointer;
             box-shadow:0 12px 24px rgba(37,99,235,.24);transition:transform .16s}
        .btn:hover{transform:translateY(-1px)}
        .back{display:block;margin-top:20px;text-align:center;color:var(--muted);font-size:14px;text-decoration:none}
        .back:hover{color:var(--primary-light)}
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <small>Mega Uni Store</small>
        <h2>Nueva contraseña</h2>
        <p>Escoge una contraseña segura para tu cuenta <strong><?= e_pr($registro['email']) ?></strong>.</p>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-error"><?= e_pr($flash['message']) ?></div>
    <?php endif; ?>

    <form action="index.php?route=password.reset.post" method="POST" autocomplete="off">
        <input type="hidden" name="token" value="<?= e_pr($token) ?>">

        <div class="form-group">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password"
                   required minlength="8" placeholder="Mínimo 8 caracteres"
                   autocomplete="new-password">
            <div class="strength"><div class="strength-bar" id="strengthBar"></div></div>
            <span class="help" id="strengthLabel">Escribe tu contraseña</span>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirmar contraseña</label>
            <input type="password" id="password_confirm" name="password_confirm"
                   required minlength="8" placeholder="Repite la contraseña"
                   autocomplete="new-password">
            <span class="help" id="matchLabel"></span>
        </div>

        <button type="submit" class="btn">Guardar contraseña</button>
    </form>

    <a class="back" href="index.php?route=login">← Volver al inicio de sesión</a>
</div>

<script>
const passwordInput = document.getElementById('password');
const confirmInput  = document.getElementById('password_confirm');
const bar           = document.getElementById('strengthBar');
const strengthLabel = document.getElementById('strengthLabel');
const matchLabel    = document.getElementById('matchLabel');

function evaluarFuerza(pw) {
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return score;
}

passwordInput.addEventListener('input', function () {
    const score = evaluarFuerza(this.value);
    const colores = ['#e5e7eb','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    const textos  = ['','Muy débil','Débil','Regular','Buena','Muy segura'];
    const pct     = (score / 5) * 100;
    bar.style.width      = pct + '%';
    bar.style.background = colores[score] || '#e5e7eb';
    strengthLabel.textContent = textos[score] || '';
    verificarMatch();
});

confirmInput.addEventListener('input', verificarMatch);

function verificarMatch() {
    if (confirmInput.value === '') { matchLabel.textContent = ''; return; }
    if (passwordInput.value === confirmInput.value) {
        matchLabel.textContent = '✅ Las contraseñas coinciden';
        matchLabel.style.color = '#166534';
    } else {
        matchLabel.textContent = '❌ No coinciden';
        matchLabel.style.color = '#991b1b';
    }
}
</script>
</body>
</html>
