<?php

function e_alerta(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas de inventario | Mega_Uni_Store</title>
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

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        .notice-ok {
            padding: 30px;
            background: #f0fdf4;
            color: #166534;
            border-radius: 22px;
            border: 1px solid #bbf7d0;
            line-height: 1.6;
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
            background: #fff7ed;
            color: #92400e;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .alert-pill {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 12px;
            font-weight: 800;
        }

        @media (max-width: 850px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
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
                color: #92400e;
                margin-bottom: 3px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="topbar">
            <div>
                <h1>Alertas de stock</h1>
                <p>Productos cuya cantidad actual está por debajo o igual al mínimo definido.</p>
            </div>

            <a class="btn btn-secondary" href="index.php?route=inventario.index">Volver al inventario</a>
        </div>

        <?php if (empty($alertas)): ?>
            <div class="notice-ok">
                <strong>Sin alertas.</strong><br>
                Actualmente no hay productos por debajo del stock mínimo.
            </div>
        <?php else: ?>
            <section class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Tienda</th>
                            <th>Producto</th>
                            <th>Cantidad actual</th>
                            <th>Cantidad mínima</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($alertas as $alerta): ?>
                            <tr>
                                <td data-label="Tienda"><?= e_alerta($alerta['tienda_nombre']) ?></td>

                                <td data-label="Producto">
                                    <strong><?= e_alerta($alerta['producto_nombre']) ?></strong>
                                </td>

                                <td data-label="Cantidad actual">
                                    <?= e_alerta((string) $alerta['cantidad']) ?>
                                    <?= e_alerta($alerta['unidad_simbolo'] ?? '') ?>
                                </td>

                                <td data-label="Cantidad mínima">
                                    <?= e_alerta((string) $alerta['cantidad_minima']) ?>
                                </td>

                                <td data-label="Ubicación">
                                    <?= e_alerta($alerta['ubicacion'] ?? 'Sin ubicación') ?>
                                </td>

                                <td data-label="Estado">
                                    <span class="alert-pill">Stock bajo</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>