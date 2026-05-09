<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_empc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Empleado | Mega_Uni_Store</title>
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
    <h1>Nuevo empleado</h1>
    <p>Vincula un usuario del sistema como empleado de una tienda.</p>

    <?php if ($flash !== null && $flash['type'] === 'error'): ?>
        <div class="alert alert-error"><?= e_empc($flash['message']) ?></div>
    <?php endif; ?>

    <section class="card">
        <form action="index.php?route=empleados.store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_empc($csrfToken) ?>">

            <div class="form-group">
                <label for="usuario_id">Usuario *</label>
                <select id="usuario_id" name="usuario_id" required>
                    <option value="">-- Seleccionar usuario --</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= e_empc((string) $u['id']) ?>"
                            <?= ((int)($_POST['usuario_id'] ?? 0)) === (int)$u['id'] ? 'selected' : '' ?>>
                            <?= e_empc(trim($u['nombre'] . ' ' . $u['apellido']) . ' — ' . $u['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tienda_id">Tienda *</label>
                <select id="tienda_id" name="tienda_id" required>
                    <option value="">-- Seleccionar tienda --</option>
                    <?php foreach ($tiendas as $t): ?>
                        <?php if ($t === null) continue; ?>
                        <option value="<?= e_empc((string) $t['id']) ?>"
                            <?= ((int)($_POST['tienda_id'] ?? 0)) === (int)$t['id'] ? 'selected' : '' ?>>
                            <?= e_empc($t['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="codigo_empleado">Codigo de empleado *</label>
                    <input type="text" id="codigo_empleado" name="codigo_empleado" required maxlength="20"
                           placeholder="EMP-001"
                           value="<?= e_empc($_POST['codigo_empleado'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso">Fecha de ingreso *</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" required
                           value="<?= e_empc($_POST['fecha_ingreso'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="salario_base">Salario base (COP) *</label>
                    <input type="number" id="salario_base" name="salario_base" required min="0" step="1000"
                           placeholder="1300000"
                           value="<?= e_empc($_POST['salario_base'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="activo" <?= ($_POST['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($_POST['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Registrar empleado</button>
                <a class="btn btn-secondary" href="index.php?route=empleados.index">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
