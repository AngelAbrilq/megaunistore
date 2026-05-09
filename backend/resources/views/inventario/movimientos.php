<?php

function e_movs(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos de inventario | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1180px;
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

        .empty {
            padding: 34px;
            text-align: center;
            color: #6b7280;
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
                <h1>Movimientos de inventario</h1>
                <p>Historial general de entradas, salidas y ajustes.</p>
            </div>

            <a class="btn btn-secondary" href="index.php?route=inventario.index">Volver al inventario</a>
        </div>

        <section class="card">
            <?php if (empty($movimientos)): ?>
                <div class="empty">
                    No hay movimientos registrados.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tienda</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($movimientos as $movimiento): ?>
                            <tr>
                                <td data-label="Fecha"><?= e_movs($movimiento['created_at']) ?></td>

                                <td data-label="Tienda"><?= e_movs($movimiento['tienda_nombre']) ?></td>

                                <td data-label="Producto"><?= e_movs($movimiento['producto_nombre']) ?></td>

                                <td data-label="Tipo">
                                    <span class="type-pill <?= e_movs($movimiento['tipo']) ?>">
                                        <?= e_movs(ucfirst($movimiento['tipo'])) ?>
                                    </span>
                                </td>

                                <td data-label="Cantidad"><?= e_movs((string) $movimiento['cantidad']) ?></td>

                                <td data-label="Motivo"><?= e_movs($movimiento['motivo'] ?? 'Sin motivo') ?></td>

                                <td data-label="Referencia">
                                    <?= e_movs($movimiento['ref_tipo'] ?? 'Manual') ?>
                                    <?= $movimiento['ref_id'] !== null ? '#' . e_movs((string) $movimiento['ref_id']) : '' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>