<?php

function e_caja_movs(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_movs(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}

function tipo_caja_class(string $tipo): string
{
    return match ($tipo) {
        'apertura' => 'type-open',
        'ingreso' => 'type-income',
        'egreso' => 'type-expense',
        'cierre' => 'type-close',
        default => 'type-neutral',
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos de caja | Mega_Uni_Store</title>
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

        .money {
            font-weight: 900;
            color: #172554;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .type-pill {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .type-open {
            background: #eef2ff;
            color: #1e3a8a;
        }

        .type-income {
            background: #dcfce7;
            color: #166534;
        }

        .type-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-close {
            background: #fef3c7;
            color: #92400e;
        }

        .type-neutral {
            background: #f3f4f6;
            color: #374151;
        }

        .empty {
            padding: 34px;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 900px) {
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
                <h1>Movimientos de caja</h1>
                <p>Historial de aperturas, ingresos, egresos y cierres de caja.</p>
            </div>

            <a class="btn btn-secondary" href="index.php?route=caja.index">Volver a caja</a>
        </div>

        <section class="card">
            <?php if (empty($movimientos)): ?>
                <div class="empty">
                    No hay movimientos de caja registrados.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tienda</th>
                            <th>Caja</th>
                            <th>Tipo</th>
                            <th>Monto sistema</th>
                            <th>Monto real</th>
                            <th>Diferencia</th>
                            <th>Venta</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($movimientos as $movimiento): ?>
                            <tr>
                                <td data-label="Fecha">
                                    <?= e_caja_movs($movimiento['created_at']) ?>
                                </td>

                                <td data-label="Tienda">
                                    <?= e_caja_movs($movimiento['tienda_nombre']) ?>
                                </td>

                                <td data-label="Caja">
                                    <?= e_caja_movs($movimiento['caja_nombre']) ?>
                                </td>

                                <td data-label="Tipo">
                                    <span class="type-pill <?= e_caja_movs(tipo_caja_class($movimiento['tipo'])) ?>">
                                        <?= e_caja_movs(ucfirst($movimiento['tipo'])) ?>
                                    </span>
                                </td>

                                <td data-label="Monto sistema">
                                    <span class="money">$<?= e_caja_movs(dinero_movs($movimiento['monto'])) ?></span>
                                </td>

                                <td data-label="Monto real">
                                    <?php if ($movimiento['monto_real'] !== null): ?>
                                        $<?= e_caja_movs(dinero_movs($movimiento['monto_real'])) ?>
                                    <?php else: ?>
                                        <span class="muted">No aplica</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Diferencia">
                                    <?php if ($movimiento['diferencia'] !== null): ?>
                                        $<?= e_caja_movs(dinero_movs($movimiento['diferencia'])) ?>
                                    <?php else: ?>
                                        <span class="muted">No aplica</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Venta">
                                    <?php if ($movimiento['venta_id'] !== null): ?>
                                        #<?= e_caja_movs((string) $movimiento['venta_id']) ?>
                                    <?php else: ?>
                                        <span class="muted">Manual</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Descripción">
                                    <?= e_caja_movs($movimiento['descripcion'] ?? 'Sin descripción') ?>
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