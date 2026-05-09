<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_clie(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f3f6fb;color:#111827}
        .container{max-width:700px;margin:0 auto;padding:34px 20px}
        h1{margin:0 0 6px;color:#172554} p{margin:0 0 24px;color:#6b7280}
        .card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);padding:28px}
        .form-group{margin-bottom:18px}
        label{display:block;font-weight:700;margin-bottom:6px;color:#172554;font-size:14px}
        input,select{width:100%;padding:11px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:14px;box-sizing:border-box}
        input:focus,select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
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
    <h1>Editar cliente</h1>
    <p>Modifica los datos del cliente #<?= e_clie((string) $cliente['id']) ?>.</p>

    <?php if ($flash !== null && $flash['type'] === 'error'): ?>
        <div class="alert alert-error"><?= e_clie($flash['message']) ?></div>
    <?php endif; ?>

    <section class="card">
        <form action="index.php?route=clientes.update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_clie($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= e_clie((string) $cliente['id']) ?>">

            <div class="row">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="100"
                           value="<?= e_clie($cliente['nombre']) ?>">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" maxlength="100"
                           value="<?= e_clie($cliente['apellido'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="tipo_documento">Tipo de documento</label>
                    <select id="tipo_documento" name="tipo_documento">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach (['CC','CE','NIT','TI','PP'] as $tipo): ?>
                            <option value="<?= e_clie($tipo) ?>"
                                <?= ($cliente['tipo_documento'] ?? '') === $tipo ? 'selected' : '' ?>>
                                <?= e_clie($tipo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="numero_documento">Numero de documento</label>
                    <input type="text" id="numero_documento" name="numero_documento" maxlength="30"
                           value="<?= e_clie($cliente['numero_documento'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="email">Correo electronico</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           value="<?= e_clie($cliente['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" id="telefono" name="telefono" maxlength="20"
                           value="<?= e_clie($cliente['telefono'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="direccion">Direccion</label>
                <input type="text" id="direccion" name="direccion" maxlength="255"
                       value="<?= e_clie($cliente['direccion'] ?? '') ?>">
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a class="btn btn-secondary" href="index.php?route=clientes.index">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
