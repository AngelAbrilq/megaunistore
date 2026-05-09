<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_show_venta(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function estado_show_class(string $estado): string
{
    return match ($estado) {
        'completada' => 'status-success',
        'anulada' => 'status-danger',
        'pendiente' => 'status-warning',
        default => 'status-neutral',
    };
}





$rolActual = $_SESSION['auth']['rol_principal']['rol_nombre'] ?? '';

$puedeAnularVenta = in_array($rolActual, [
    'Superadministrador',
    'Administrador de Tienda',
    'Supervisor',
], true);





?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle de venta | Mega_Uni_Store</title>
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
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0 0 8px;
            color: #172554;
        }

        p {
            margin: 0;
            color: #6b7280;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
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

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .summary-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px;
        }

        .summary-item small {
            display: block;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.04em;
        }

        .summary-item strong {
            color: #172554;
            font-size: 19px;
        }

        .status {
            display: inline-flex;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 900;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
        }

        .status-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .status-neutral {
            background: #eef2ff;
            color: #1e3a8a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
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

        form {
            margin: 0;
        }

        @media (max-width: 850px) {
            .topbar {
                flex-direction: column;
            }

            .summary-grid {
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
        <div class="topbar">
            <div>
                <h1>Venta #<?= e_show_venta((string) $venta['id']) ?></h1>
                <p>
                    <?= e_show_venta($venta['tienda_nombre']) ?>
                    —
                    <?= e_show_venta($venta['created_at']) ?>
                </p>
            </div>

            <div class="actions">
                <a class="btn btn-secondary" href="index.php?route=ventas.index">Volver a ventas</a>

                <?php if ($puedeAnularVenta && $venta['estado'] !== 'anulada'): ?>
                    <form action="index.php?route=ventas.anular" method="POST" onsubmit="return confirm('¿Seguro que deseas anular esta venta? El stock será devuelto al inventario.');">
                        <input type="hidden" name="csrf_token" value="<?= e_show_venta($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= e_show_venta((string) $venta['id']) ?>">

                        <button type="submit" class="btn btn-danger">
                            Anular venta
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_show_venta($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_show_venta($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2>Resumen</h2>

            <div class="summary-grid">
                <div class="summary-item">
                    <small>Estado</small>
                    <strong>
                        <span class="status <?= e_show_venta(estado_show_class($venta['estado'])) ?>">
                            <?= e_show_venta(ucfirst($venta['estado'])) ?>
                        </span>
                    </strong>
                </div>

                <div class="summary-item">
                    <small>Subtotal</small>
                    <strong>$<?= e_show_venta((string) $venta['subtotal']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Impuesto</small>
                    <strong>$<?= e_show_venta((string) $venta['impuesto']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Total</small>
                    <strong>$<?= e_show_venta((string) $venta['total']) ?></strong>
                </div>
            </div>
        </section>

        <section class="card">
            <h2>Datos generales</h2>

            <div class="summary-grid">
                <div class="summary-item">
                    <small>Tienda</small>
                    <strong><?= e_show_venta($venta['tienda_nombre']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Cliente</small>
                    <strong>
                        <?php if (!empty($venta['cliente_nombre'])): ?>
                            <?= e_show_venta(trim($venta['cliente_nombre'] . ' ' . ($venta['cliente_apellido'] ?? ''))) ?>
                        <?php else: ?>
                            Cliente general
                        <?php endif; ?>
                    </strong>
                </div>

                <div class="summary-item">
                    <small>Fecha</small>
                    <strong><?= e_show_venta($venta['fecha'] ?? $venta['created_at']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Creada por</small>
                    <strong><?= e_show_venta((string) ($venta['created_by'] ?? 'Sistema')) ?></strong>
                </div>
            </div>
        </section>

        <section class="card">
            <h2>Detalle de productos</h2>

            <?php if (empty($detalle)): ?>
                <p>No hay productos en esta venta.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Descuento</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td data-label="Producto">
                                    <strong><?= e_show_venta($item['producto_nombre']) ?></strong>
                                </td>

                                <td data-label="Código">
                                    <?= e_show_venta($item['codigo_barras'] ?? 'Sin código') ?>
                                </td>

                                <td data-label="Cantidad">
                                    <?= e_show_venta((string) $item['cantidad']) ?>
                                </td>

                                <td data-label="Precio unitario">
                                    $<?= e_show_venta((string) $item['precio_unitario']) ?>
                                </td>

                                <td data-label="Descuento">
                                    $<?= e_show_venta((string) $item['descuento']) ?>
                                </td>

                                <td data-label="Subtotal">
                                    <span class="money">$<?= e_show_venta((string) $item['subtotal']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>Pagos</h2>

            <?php if (empty($pagos)): ?>
                <p>No hay pagos registrados para esta venta.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td data-label="Método">
                                    <?= e_show_venta($pago['metodo_pago_nombre']) ?>
                                </td>

                                <td data-label="Monto">
                                    <span class="money">$<?= e_show_venta((string) $pago['monto']) ?></span>
                                </td>

                                <td data-label="Referencia">
                                    <?= e_show_venta($pago['referencia'] ?? 'Sin referencia') ?>
                                </td>

                                <td data-label="Estado">
                                    <?= e_show_venta(ucfirst($pago['estado'])) ?>
                                </td>

                                <td data-label="Fecha">
                                    <?= e_show_venta($pago['created_at']) ?>
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