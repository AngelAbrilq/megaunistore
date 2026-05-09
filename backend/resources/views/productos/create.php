<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_create_producto(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo producto | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1040px;
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

        .card h2 {
            margin: 0 0 14px;
            color: #172554;
            font-size: 20px;
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
            min-height: 105px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .check-card {
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            padding: 14px;
            background: #fbfdff;
        }

        .check-card label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            cursor: pointer;
        }

        .check-card input[type="checkbox"] {
            width: auto;
        }

        .store-card {
            border: 1px solid #dbe3ef;
            border-radius: 18px;
            padding: 16px;
            background: #fbfdff;
            margin-bottom: 14px;
        }

        .store-card-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .store-card-header input {
            width: auto;
        }

        .price-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
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

        @media (max-width: 760px) {
            .form-grid,
            .price-grid,
            .checkbox-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Nuevo producto</h1>
        <p>Registra un producto, asígnalo a tiendas, define precios e impuestos.</p>

        <?php if ($flash !== null): ?>
            <div class="alert">
                <?= e_create_producto($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?route=productos.store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_create_producto($csrfToken) ?>">

            <section class="card">
                <h2>Información general</h2>

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" required maxlength="200">
                    </div>

                    <div class="form-group">
                        <label for="codigo_barras">Código de barras</label>
                        <input type="text" id="codigo_barras" name="codigo_barras" maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="imagen_url">URL de imagen</label>
                        <input type="text" id="imagen_url" name="imagen_url" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="categoria_id">Categoría</label>
                        <select id="categoria_id" name="categoria_id">
                            <option value="">Sin categoría</option>

                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= e_create_producto((string) $categoria['id']) ?>">
                                    <?= e_create_producto($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="unidad_medida_id">Unidad de medida</label>
                        <select id="unidad_medida_id" name="unidad_medida_id">
                            <option value="">Sin unidad</option>

                            <?php foreach ($unidades as $unidad): ?>
                                <option value="<?= e_create_producto((string) $unidad['id']) ?>">
                                    <?= e_create_producto($unidad['nombre'] . ' (' . $unidad['simbolo'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"></textarea>
                    </div>
                </div>
            </section>

            <section class="card">
                <h2>Impuestos</h2>

                <?php if (empty($impuestos)): ?>
                    <p>No hay impuestos activos registrados.</p>
                <?php else: ?>
                    <div class="checkbox-grid">
                        <?php foreach ($impuestos as $impuesto): ?>
                            <div class="check-card">
                                <label>
                                    <input type="checkbox" name="impuestos[]" value="<?= e_create_producto((string) $impuesto['id']) ?>">
                                    <span>
                                        <?= e_create_producto($impuesto['nombre']) ?>
                                        — <?= e_create_producto((string) $impuesto['porcentaje']) ?>%
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="card">
                <h2>Tiendas y precios</h2>

                <?php if (empty($tiendas)): ?>
                    <p>No hay tiendas registradas. Primero crea una tienda.</p>
                <?php else: ?>
                    <?php foreach ($tiendas as $tienda): ?>
                        <div class="store-card">
                            <div class="store-card-header">
                                <input
                                    type="checkbox"
                                    class="store-check"
                                    id="tienda_<?= e_create_producto((string) $tienda['id']) ?>"
                                    name="tiendas[]"
                                    value="<?= e_create_producto((string) $tienda['id']) ?>"
                                    data-store-id="<?= e_create_producto((string) $tienda['id']) ?>"
                                >

                                <label for="tienda_<?= e_create_producto((string) $tienda['id']) ?>">
                                    <?= e_create_producto($tienda['nombre']) ?>
                                </label>
                            </div>

                            <div class="price-grid">
                                <div class="form-group">
                                    <label for="precio_venta_<?= e_create_producto((string) $tienda['id']) ?>">Precio de venta *</label>
                                    <input
                                        type="number"
                                        id="precio_venta_<?= e_create_producto((string) $tienda['id']) ?>"
                                        name="precio_venta[<?= e_create_producto((string) $tienda['id']) ?>]"
                                        min="0"
                                        step="0.01"
                                        placeholder="Ej: 15000"
                                        class="precio-venta"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="precio_compra_<?= e_create_producto((string) $tienda['id']) ?>">Precio de compra</label>
                                    <input
                                        type="number"
                                        id="precio_compra_<?= e_create_producto((string) $tienda['id']) ?>"
                                        name="precio_compra[<?= e_create_producto((string) $tienda['id']) ?>]"
                                        min="0"
                                        step="0.01"
                                        placeholder="Ej: 10000"
                                    >
                                </div>
                            </div>

                            <span class="help">
                                Marca la tienda para asociar el producto. El precio de venta es obligatorio por cada tienda seleccionada.
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar producto</button>
                <a href="index.php?route=productos.index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        document.querySelectorAll('.store-check').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const storeId = this.dataset.storeId;
                const priceInput = document.getElementById('precio_venta_' + storeId);

                if (priceInput) {
                    priceInput.required = this.checked;
                }
            });
        });
    </script>
</body>
</html>