<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_empe(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empleado | Mega_Uni_Store</title>
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
        .info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px;margin-bottom:18px;font-size:14px;color:#1e3a8a}
        @media(max-width:600px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<main class="container">
    <h1>Editar empleado</h1>
    <p>Modifica los datos del empleado #<?= e_empe((string) $empleado['id']) ?>.</p>

    <?php if ($flash !== null && $flash['type'] === 'error'): ?>
        <div class="alert alert-error"><?= e_empe($flash['message']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong><?= e_empe(trim($empleado['usuario_nombre'] . ' ' . $empleado['usuario_apellido'])) ?></strong>
        — <?= e_empe($empleado['usuario_email']) ?><br>
        Tienda: <strong><?= e_empe($empleado['tienda_nombre']) ?></strong>
    </div>

    <section class="card">
        <form action="index.php?route=empleados.update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_empe($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= e_empe((string) $empleado['id']) ?>">

            <div class="row">
                <div class="form-group">
                    <label for="codigo_empleado">Codigo de empleado *</label>
                    <input type="text" id="codigo_empleado" name="codigo_empleado" required maxlength="20"
                           value="<?= e_empe($empleado['codigo_empleado']) ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso">Fecha de ingreso *</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" required
                           value="<?= e_empe($empleado['fecha_ingreso'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="salario_base">Salario base (COP) *</label>
                    <input type="number" id="salario_base" name="salario_base" required min="0" step="1000"
                           value="<?= e_empe((string) ($empleado['salario_base'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="activo" <?= ($empleado['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($empleado['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a class="btn btn-secondary" href="index.php?route=empleados.index">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
