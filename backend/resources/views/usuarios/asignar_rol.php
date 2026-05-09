<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_role_user(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar rol | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 34px 20px;
        }

        h1 {
            margin: 0 0 8px;
            color: #172554;
        }

        p {
            margin: 0 0 24px;
            color: #6b7280;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            margin-bottom: 20px;
        }

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #1f2937;
            font-size: 14px;
        }

        select {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .help {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.4;
        }

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .role-list {
            display: grid;
            gap: 10px;
        }

        .role-item {
            padding: 12px 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        .role-item strong {
            color: #172554;
        }

        .role-item small {
            display: block;
            color: #6b7280;
            margin-top: 4px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 680px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Asignar rol</h1>
        <p>
            Usuario:
            <strong><?= e_role_user(trim($usuario['nombre'] . ' ' . $usuario['apellido'])) ?></strong>
            — <?= e_role_user($usuario['email']) ?>
        </p>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_role_user($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_role_user($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <form action="index.php?route=usuarios.guardar_rol" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_role_user($csrfToken) ?>">
                <input type="hidden" name="usuario_id" value="<?= e_role_user((string) $usuario['id']) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="rol_id">Rol *</label>
                        <select id="rol_id" name="rol_id" required>
                            <option value="">Seleccionar rol</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= e_role_user((string) $rol['id']) ?>">
                                    <?= e_role_user($rol['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tienda_id">Tienda</label>
                        <select id="tienda_id" name="tienda_id">
                            <option value="">Global / No aplica</option>
                            <?php foreach ($tiendas as $tienda): ?>
                                <option value="<?= e_role_user((string) $tienda['id']) ?>">
                                    <?= e_role_user($tienda['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help">
                            Superadministrador y Sistema no necesitan tienda. Los demás roles sí.
                        </span>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Asignar rol</button>
                    <a href="index.php?route=usuarios.index" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Roles actuales</h2>

            <?php if (empty($rolesUsuario)): ?>
                <p>Este usuario no tiene roles asignados.</p>
            <?php else: ?>
                <div class="role-list">
                    <?php foreach ($rolesUsuario as $rolUsuario): ?>
                        <div class="role-item">
                            <strong><?= e_role_user($rolUsuario['rol_nombre']) ?></strong>
                            <small>
                                Alcance:
                                <?= $rolUsuario['tienda_id'] !== null
                                    ? 'Tienda ID ' . e_role_user((string) $rolUsuario['tienda_id'])
                                    : 'Global'
                                ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>