<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_mov(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimiento de inventario | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1120px;
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

        .grid {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: 20px;
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
            margin: 0 0 16px;
            color: #172554;
        }

        .summary {
            display: grid;
            gap: 10px;
        }

        .summary-item {
            padding: 12px 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        .summary-item small {
            display: block;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .summary-item strong {
            color: #172554;
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
        select,
        textarea {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
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

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: #eff6ff;
            color: #172554;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .type-pill {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .entrada {
            background: #dcfce7;
            color: #166534;
        }

        .salida {
            background: #fee2e2;
            color: #991b1b;
        }

        .ajuste {
            background: #fef3c7;
            color: #92400e;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
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
        <h1>Movimiento de inventario</h1>
        <p>Registra entradas, salidas o ajustes para el producto seleccionado.</p>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_mov($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_mov($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <section>
                <div class="card">
                    <h2>Resumen</h2>

                    <div class="summary">
                        <div class="summary-item">
                            <small>Tienda</small>
                            <strong><?= e_mov($inventario['tienda_nombre']) ?></strong>
                        </div>

                        <div class="summary-item">
                            <small>Producto</small>
                            <strong><?= e_mov($inventario['producto_nombre']) ?></strong>
                        </div>

                        <div class="summary-item">
                            <small>Cantidad actual</small>
                            <strong>
                                <?= e_mov((string) $inventario['cantidad']) ?>
                                <?= e_mov($inventario['unidad_simbolo'] ?? '') ?>
                            </strong>
                        </div>

                        <div class="summary-item">
                            <small>Mínimo / Máximo</small>
                            <strong>
                                <?= e_mov((string) $inventario['cantidad_minima']) ?>
                                /
                                <?= e_mov($inventario['cantidad_maxima'] !== null ? (string) $inventario['cantidad_maxima'] : 'No definido') ?>
                            </strong>
                        </div>

                        <div class="summary-item">
                            <small>Ubicación</small>
                            <strong><?= e_mov($inventario['ubicacion'] ?? 'Sin ubicación') ?></strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2>Registrar movimiento</h2>

                    <form action="index.php?route=inventario.guardar_movimiento" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e_mov($csrfToken) ?>">
                        <input type="hidden" name="inventario_id" value="<?= e_mov((string) $inventario['id']) ?>">

                        <div class="form-group">
                            <label for="tipo">Tipo *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                                <option value="ajuste">Ajuste</option>
                            </select>
                            <span class="help">
                                Entrada suma stock, salida descuenta stock y ajuste reemplaza la cantidad actual.
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="cantidad">Cantidad *</label>
                            <input type="number" id="cantidad" name="cantidad" required min="0" step="0.01">
                        </div>

                        <div class="form-group">
                            <label for="motivo">Motivo</label>
                            <textarea id="motivo" name="motivo" placeholder="Ej: Compra a proveedor, venta manual, conteo físico, corrección de inventario."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar movimiento</button>
                        <a href="index.php?route=inventario.index" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </section>

            <section class="card">
                <h2>Últimos movimientos</h2>

                <?php if (empty($movimientos)): ?>
                    <p>No hay movimientos registrados para este inventario.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($movimientos as $movimiento): ?>
                                <tr>
                                    <td data-label="Fecha"><?= e_mov($movimiento['created_at']) ?></td>

                                    <td data-label="Tipo">
                                        <span class="type-pill <?= e_mov($movimiento['tipo']) ?>">
                                            <?= e_mov(ucfirst($movimiento['tipo'])) ?>
                                        </span>
                                    </td>

                                    <td data-label="Cantidad"><?= e_mov((string) $movimiento['cantidad']) ?></td>

                                    <td data-label="Motivo"><?= e_mov($movimiento['motivo'] ?? 'Sin motivo') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>