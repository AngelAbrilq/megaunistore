<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_create_cupon(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear cupón | Mega_Uni_Store</title>
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

        input, select, textarea {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .help {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
        }

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            justify-content: center;
            align-items: center;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Crear cupón</h1>
        <p>Crea un nuevo cupón de descuento para aplicar en ventas.</p>

        <?php if ($flash !== null): ?>
            <div class="alert">
                <?= e_create_cupon($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?route=cupones.store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_create_cupon($csrfToken) ?>">

            <section class="card">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="codigo">Código del cupón *</label>
                        <input type="text" id="codigo" name="codigo" required maxlength="50" placeholder="Ej: VERANO2026">
                        <span class="help">Código único que los clientes usarán.</span>
                    </div>

                    <div class="form-group">
                        <label for="tienda_id">Tienda</label>
                        <select id="tienda_id" name="tienda_id">
                            <option value="">Todas las tiendas</option>
                            <?php foreach ($tiendas as $tienda): ?>
                                <?php if ($tienda === null) { continue; } ?>
                                <option value="<?= e_create_cupon((string) $tienda['id']) ?>">
                                    <?= e_create_cupon($tienda['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help">Deja vacío para aplicar a todas las tiendas.</span>
                    </div>

                    <div class="form-group full">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción del cupón"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tipo_descuento">Tipo de descuento *</label>
                        <select id="tipo_descuento" name="tipo_descuento" required>
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="fijo">Monto fijo ($)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="valor_descuento">Valor del descuento *</label>
                        <input type="number" id="valor_descuento" name="valor_descuento" step="0.01" min="0.01" required placeholder="10.00">
                        <span class="help">Porcentaje o monto fijo según el tipo.</span>
                    </div>

                    <div class="form-group">
                        <label for="descuento_maximo">Descuento máximo</label>
                        <input type="number" id="descuento_maximo" name="descuento_maximo" step="0.01" min="0" placeholder="50.00">
                        <span class="help">Solo para porcentaje. Deja vacío si no aplica.</span>
                    </div>

                    <div class="form-group">
                        <label for="monto_minimo">Monto mínimo de compra</label>
                        <input type="number" id="monto_minimo" name="monto_minimo" step="0.01" min="0" placeholder="100.00">
                        <span class="help">Monto mínimo para aplicar el cupón.</span>
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio">
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input type="datetime-local" id="fecha_fin" name="fecha_fin">
                    </div>

                    <div class="form-group">
                        <label for="usos_maximos">Usos máximos</label>
                        <input type="number" id="usos_maximos" name="usos_maximos" min="1" placeholder="100">
                        <span class="help">Deja vacío para usos ilimitados.</span>
                    </div>

                    <div class="form-group">
                        <label for="activo">Estado</label>
                        <select id="activo" name="activo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Crear cupón</button>
                <a href="index.php?route=cupones.index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
