<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array|null $flash
 */

function e_pf(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Recuperar contraseña | Mega Uni Store</title>
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
        .alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .form-group{margin-bottom:18px}
        label{display:block;margin-bottom:8px;font-weight:700;font-size:14px;color:#1f2937}
        input{width:100%;border:1px solid var(--border);border-radius:14px;padding:13px 14px;
              font-size:15px;outline:none;background:#fbfdff}
        input:focus{border-color:var(--primary-light);box-shadow:0 0 0 4px rgba(37,99,235,.12);background:#fff}
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
        <h2>Recuperar contraseña</h2>
        <p>Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_pf($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_pf($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?route=password.request.post" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required
                   placeholder="usuario@correo.com" autocomplete="email">
        </div>

        <button type="submit" class="btn">Enviar enlace de recuperación</button>
    </form>

    <a class="back" href="index.php?route=login">← volver al inicio de sesión</a>
</div>
</body>
</html>
