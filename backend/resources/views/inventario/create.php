<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_inv_create(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar inventario | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 880px;
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
            border: 1px solid transparent;
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
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
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

        @media (max-width: 720px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Registrar inventario</h1>
        <p>Crea o actualiza el inventario inicial de un producto asociado a una tienda.</p>

        <?php if ($flash !== null): ?>
            <div class="alert alert-error">
                <?= e_inv_create($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <form action="index.php?route=inventario.store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_inv_create($csrfToken) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="tienda_id">Tienda *</label>
                        <select id="tienda_id" name="tienda_id" required>
                            <option value="">Seleccionar tienda</option>

                            <?php foreach ($tiendas as $tienda): ?>
                                <?php if ($tienda === null) { continue; } ?>
                                <option value="<?= e_inv_create((string) $tienda['id']) ?>">
                                    <?= e_inv_create($tienda['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="producto_id">Producto *</label>
                        <select id="producto_id" name="producto_id" required>
                            <option value="">Seleccionar producto</option>

                            <?php foreach ($productos as $producto): ?>
                                <option value="<?= e_inv_create((string) $producto['id']) ?>">
                                    <?= e_inv_create($producto['nombre']) ?>
                                    <?= !empty($producto['codigo_barras']) ? ' - ' . e_inv_create($producto['codigo_barras']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help">Solo aparecen productos activos asociados a tiendas activas.</span>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad actual *</label>
                        <input type="number" id="cantidad" name="cantidad" required min="0" step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label for="cantidad_minima">Cantidad mínima *</label>
                        <input type="number" id="cantidad_minima" name="cantidad_minima" required min="0" step="0.01" value="0">
                    </div>

                    <div class="form-group">
                        <label for="cantidad_maxima">Cantidad máxima</label>
                        <input type="number" id="cantidad_maxima" name="cantidad_maxima" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" maxlength="255" placeholder="Ej: Bodega A - Estante 2">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Guardar inventario</button>
                    <a href="index.php?route=inventario.index" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>