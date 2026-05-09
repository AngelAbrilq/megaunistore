<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_provc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Proveedor | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f3f6fb;color:#111827}
        .container{max-width:700px;margin:0 auto;padding:34px 20px}
        h1{margin:0 0 6px;color:#172554} p{margin:0 0 24px;color:#6b7280}
        .card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);padding:28px}
        .form-group{margin-bottom:18px}
        label{display:block;font-weight:700;margin-bottom:6px;color:#172554;font-size:14px}
        input{width:100%;padding:11px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:14px;box-sizing:border-box}
        input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:12px 20px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px}
        .btn-primary{background:#1e3a8a;color:#fff}
        .btn-secondary{background:#e0e7ff;color:#1e3a8a}
        .actions{display:flex;gap:12px;margin-top:8px}
        .alert{padding:13px 14px;border-radius:14px;margin-bottom:18px;border:1px solid transparent}
        .alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
        @media(max-width:600px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<main class="container">
    <h1>Nuevo proveedor</h1>
    <p>Registra un proveedor para abastecer las tiendas.</p>

    <?php if ($flash !== null && $flash['type'] === 'error'): ?>
        <div class="alert alert-error"><?= e_provc($flash['message']) ?></div>
    <?php endif; ?>

    <section class="card">
        <form action="index.php?route=proveedores.store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_provc($csrfToken) ?>">

            <div class="row">
                <div class="form-group">
                    <label for="nombre">Razon social / Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="150"
                           value="<?= e_provc($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="ruc_nit">NIT / RUC *</label>
                    <input type="text" id="ruc_nit" name="ruc_nit" required maxlength="30"
                           placeholder="900.123.456-1"
                           value="<?= e_provc($_POST['ruc_nit'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" id="telefono" name="telefono" maxlength="20"
                           value="<?= e_provc($_POST['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Correo electronico</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           value="<?= e_provc($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="direccion">Direccion</label>
                <input type="text" id="direccion" name="direccion" maxlength="255"
                       value="<?= e_provc($_POST['direccion'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="contacto_nombre">Nombre del contacto comercial</label>
                <input type="text" id="contacto_nombre" name="contacto_nombre" maxlength="100"
                       value="<?= e_provc($_POST['contacto_nombre'] ?? '') ?>">
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Registrar proveedor</button>
                <a class="btn btn-secondary" href="index.php?route=proveedores.index">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
