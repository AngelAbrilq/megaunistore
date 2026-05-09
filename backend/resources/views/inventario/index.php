<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_inv(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 34px 20px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
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

        .top-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
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

        .muted {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        .pill {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 800;
        }

        .stock-ok {
            background: #dcfce7;
            color: #166534;
        }

        .stock-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .stock-mid {
            background: #fef3c7;
            color: #92400e;
        }

        .actions {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: center;
        }

        td[data-label="Acciones"],
        th:last-child {
            min-width: 190px;
        }

        .empty {
            padding: 34px;
            text-align: center;
            color: #6b7280;
        }

        .back {
            margin-top: 20px;
        }

        @media (max-width: 980px) {
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

            .actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="topbar">
            <div>
                <h1>Inventario</h1>
                <p>Consulta existencias por tienda, producto, ubicación y niveles mínimos.</p>
            </div>

            <div class="top-actions">
                <a class="btn btn-primary" href="index.php?route=inventario.create">Registrar inventario</a>
                <a class="btn btn-secondary" href="index.php?route=inventario.movimientos">Movimientos</a>
                <a class="btn btn-warning" href="index.php?route=inventario.alertas">Alertas de stock</a>
            </div>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_inv($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_inv($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <?php if (empty($inventarios)): ?>
                <div class="empty">
                    No hay registros de inventario todavía.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tienda</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Mínimo / Máximo</th>
                            <th>Ubicación</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($inventarios as $inventario): ?>
                            <?php
                                $cantidad = (float) $inventario['cantidad'];
                                $minima = (float) $inventario['cantidad_minima'];
                                $maxima = $inventario['cantidad_maxima'] !== null ? (float) $inventario['cantidad_maxima'] : null;

                                $stockClass = 'stock-ok';

                                if ($cantidad <= $minima) {
                                    $stockClass = 'stock-low';
                                } elseif ($maxima !== null && $cantidad >= $maxima) {
                                    $stockClass = 'stock-mid';
                                }
                            ?>

                            <tr>
                                <td data-label="ID"><?= e_inv((string) $inventario['id']) ?></td>

                                <td data-label="Tienda">
                                    <strong><?= e_inv($inventario['tienda_nombre']) ?></strong>
                                </td>

                                <td data-label="Producto">
                                    <strong><?= e_inv($inventario['producto_nombre']) ?></strong><br>

                                    <?php if (!empty($inventario['codigo_barras'])): ?>
                                        <span class="muted">Código: <?= e_inv($inventario['codigo_barras']) ?></span><br>
                                    <?php endif; ?>

                                    <span class="muted">
                                        <?= e_inv($inventario['categoria_nombre'] ?? 'Sin categoría') ?>
                                    </span>
                                </td>

                                <td data-label="Cantidad">
                                    <span class="pill <?= e_inv($stockClass) ?>">
                                        <?= e_inv((string) $inventario['cantidad']) ?>
                                        <?= e_inv($inventario['unidad_simbolo'] ?? '') ?>
                                    </span>
                                </td>

                                <td data-label="Mínimo / Máximo">
                                    Mín: <?= e_inv((string) $inventario['cantidad_minima']) ?><br>
                                    Máx: <?= e_inv($inventario['cantidad_maxima'] !== null ? (string) $inventario['cantidad_maxima'] : 'No definido') ?>
                                </td>

                                <td data-label="Ubicación">
                                    <?= e_inv($inventario['ubicacion'] ?? 'Sin ubicación') ?>
                                </td>

                                <td data-label="Actualizado">
                                    <?= e_inv($inventario['updated_at'] ?? '') ?>
                                </td>

                                <td data-label="Acciones">
                                    <div class="actions">
                                        <a class="btn btn-secondary" href="index.php?route=inventario.movimiento&id=<?= e_inv((string) $inventario['id']) ?>">
                                            Movimiento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <div class="back">
            <a class="btn btn-secondary" href="index.php?route=dashboard">Volver al dashboard</a>
        </div>
    </main>
</body>
</html>