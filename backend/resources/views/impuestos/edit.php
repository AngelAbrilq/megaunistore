<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_edit_impuesto(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$tipos = ['Ventas', 'Compras', 'Consumo', 'Retención', 'Otro'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar impuesto | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 780px;
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
            min-height: 105px;
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
    </style>
</head>
<body>
    <main class="container">
        <h1>Editar impuesto</h1>
        <p>Actualiza la tarifa tributaria seleccionada.</p>

        <?php if ($flash !== null): ?>
            <div class="alert">
                <?= e_edit_impuesto($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <form action="index.php?route=impuestos.update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_edit_impuesto($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= e_edit_impuesto((string) $impuesto['id']) ?>">

                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        required
                        maxlength="80"
                        value="<?= e_edit_impuesto($impuesto['nombre']) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="porcentaje">Porcentaje *</label>
                    <input
                        type="number"
                        id="porcentaje"
                        name="porcentaje"
                        required
                        min="0"
                        max="100"
                        step="0.01"
                        value="<?= e_edit_impuesto((string) $impuesto['porcentaje']) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Seleccionar tipo</option>

                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= e_edit_impuesto($tipo) ?>" <?= $impuesto['tipo'] === $tipo ? 'selected' : '' ?>>
                                <?= e_edit_impuesto($tipo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="activo">Estado</label>
                    <select id="activo" name="activo">
                        <option value="1" <?= (int) $impuesto['activo'] === 1 ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= (int) $impuesto['activo'] === 0 ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"><?= e_edit_impuesto($impuesto['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Actualizar impuesto</button>
                    <a href="index.php?route=impuestos.index" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>