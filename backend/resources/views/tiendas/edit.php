<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_edit_tienda(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar tienda | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 820px;
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
            border: 1px solid #fecaca;
            background: #fef2f2;
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
        textarea,
        select {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
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
        <h1>Editar tienda</h1>
        <p>Actualiza la información operativa y de contacto de la tienda.</p>

        <?php if ($flash !== null): ?>
            <div class="alert">
                <?= e_edit_tienda($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <form action="index.php?route=tiendas.update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_edit_tienda($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= e_edit_tienda((string) $tienda['id']) ?>">

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="nombre">Nombre de la tienda *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            required
                            maxlength="150"
                            value="<?= e_edit_tienda($tienda['nombre']) ?>"
                        >
                    </div>

                    <div class="form-group full">
                        <label for="direccion">Dirección *</label>
                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            required
                            maxlength="255"
                            value="<?= e_edit_tienda($tienda['direccion'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            maxlength="20"
                            value="<?= e_edit_tienda($tienda['telefono'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Correo de contacto</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            maxlength="150"
                            value="<?= e_edit_tienda($tienda['email'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group full">
                        <label for="logo_url">URL del logo</label>
                        <input
                            type="text"
                            id="logo_url"
                            name="logo_url"
                            maxlength="255"
                            value="<?= e_edit_tienda($tienda['logo_url'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group full">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"><?= e_edit_tienda($tienda['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" <?= (int) $tienda['estado'] === 1 ? 'selected' : '' ?>>Activa</option>
                            <option value="0" <?= (int) $tienda['estado'] === 0 ? 'selected' : '' ?>>Inactiva</option>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Actualizar tienda</button>
                    <a href="index.php?route=tiendas.index" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>