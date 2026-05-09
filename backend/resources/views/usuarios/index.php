<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

function e_user(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1220px;
            margin: 0 auto;
            padding: 34px 20px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0 0 6px;
            color: #172554;
        }

        p {
            margin: 0;
            color: #6b7280;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 12px;
            padding: 11px 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .btn-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
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

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            background: #eff6ff;
            color: #172554;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .status {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .role-pill {
            display: inline-block;
            margin: 2px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #eef2ff;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 700;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        form {
            margin: 0;
        }

        .empty {
            padding: 34px;
            text-align: center;
            color: #6b7280;
        }

        .back {
            margin-top: 20px;
        }

        @media (max-width: 920px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                border-bottom: 1px solid #e5e7eb;
                padding: 14px;
            }

            td {
                border: 0;
                padding: 7px 0;
            }

            td::before {
                content: attr(data-label);
                display: block;
                font-weight: 800;
                color: #172554;
                margin-bottom: 3px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="topbar">
            <div>
                <h1>Gestión de usuarios</h1>
                <p>Crear usuarios administrativos y asignar roles por tienda.</p>
            </div>

            <a class="btn btn-primary" href="index.php?route=usuarios.create">Nuevo usuario</a>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_user($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_user($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <?php if (empty($usuarios)): ?>
                <div class="empty">No hay usuarios registrados.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Contacto</th>
                            <th>Roles</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td data-label="ID"><?= e_user((string) $usuario['id']) ?></td>

                                <td data-label="Usuario">
                                    <strong><?= e_user(trim($usuario['nombre'] . ' ' . $usuario['apellido'])) ?></strong><br>
                                    <small>Creado: <?= e_user($usuario['created_at'] ?? '') ?></small>
                                </td>

                                <td data-label="Contacto">
                                    <?= e_user($usuario['email']) ?><br>
                                    <small><?= e_user($usuario['telefono'] ?? 'Sin teléfono') ?></small>
                                </td>

                                <td data-label="Roles">
                                    <?php if (empty($usuario['roles'])): ?>
                                        <span class="role-pill">Sin rol</span>
                                    <?php else: ?>
                                        <?php foreach ($usuario['roles'] as $rol): ?>
                                            <span class="role-pill">
                                                <?= e_user($rol['rol_nombre']) ?>
                                                <?= $rol['tienda_id'] !== null ? ' / Tienda ' . e_user((string) $rol['tienda_id']) : ' / Global' ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Estado">
                                    <?php if ((int) $usuario['estado'] === 1): ?>
                                        <span class="status status-active">Activo</span>
                                    <?php else: ?>
                                        <span class="status status-inactive">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Acciones">
                                    <div class="actions">
                                        <a class="btn btn-secondary" href="index.php?route=usuarios.edit&id=<?= e_user((string) $usuario['id']) ?>">
                                            Editar
                                        </a>

                                        <a class="btn btn-secondary" href="index.php?route=usuarios.asignar_rol&id=<?= e_user((string) $usuario['id']) ?>">
                                            Roles
                                        </a>

                                        <form action="index.php?route=usuarios.toggle" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= e_user($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= e_user((string) $usuario['id']) ?>">
                                            <input type="hidden" name="estado_actual" value="<?= e_user((string) $usuario['estado']) ?>">

                                            <button type="submit" class="btn btn-warning">
                                                <?= (int) $usuario['estado'] === 1 ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>

                                        <form action="index.php?route=usuarios.destroy" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                            <input type="hidden" name="csrf_token" value="<?= e_user($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= e_user((string) $usuario['id']) ?>">

                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <div class="back">
            <a class="btn btn-secondary" href="index.php?route=dashboard.superadmin">Volver al dashboard</a>
        </div>
    </main>
</body>
</html>