<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_edit_user(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar usuario | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 860px;
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
        }

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            margin-bottom: 18px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #1f2937;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        input:focus,
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

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
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
        <h1>Editar usuario</h1>
        <p>Actualiza los datos principales del usuario.</p>

        <?php if ($flash !== null): ?>
            <div class="alert"><?= e_edit_user($flash['message']) ?></div>
        <?php endif; ?>

        <section class="card">
            <form action="index.php?route=usuarios.update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_edit_user($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= e_edit_user((string) $usuario['id']) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            required
                            maxlength="80"
                            value="<?= e_edit_user($usuario['nombre']) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input
                            type="text"
                            id="apellido"
                            name="apellido"
                            required
                            maxlength="80"
                            value="<?= e_edit_user($usuario['apellido']) ?>"
                        >
                    </div>

                    <div class="form-group full">
                        <label for="email">Correo electrónico *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            maxlength="150"
                            value="<?= e_edit_user($usuario['email']) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            maxlength="20"
                            value="<?= e_edit_user($usuario['telefono'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?= (int) $usuario['estado'] === 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (int) $usuario['estado'] === 0 ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label for="password">Nueva contraseña</label>
                        <input type="password" id="password" name="password" minlength="8">
                        <span class="help">Déjala vacía si no quieres cambiarla.</span>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                    <a href="index.php?route=usuarios.index" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>