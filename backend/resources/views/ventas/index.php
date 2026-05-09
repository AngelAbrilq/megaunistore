<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_venta(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function estado_venta_class(string $estado): string
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
    <title>Ventas | Mega_Uni_Store</title>
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

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
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

        .money {
            font-weight: 900;
            color: #172554;
        }

        .status {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
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

        .actions {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: center;
        }

        td[data-label="Acciones"],
        th:last-child {
            min-width: 210px;
        }

        .empty {
            padding: 34px;
            text-align: center;
            color: #6b7280;
        }

        .back {
            margin-top: 20px;
        }

        form {
            margin: 0;
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
                <h1>Ventas</h1>
                <p>Consulta ventas registradas, pagos, estados y detalle por tienda.</p>
            </div>

            <a class="btn btn-primary" href="index.php?route=ventas.create">Nueva venta</a>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_venta($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_venta($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <?php if (empty($ventas)): ?>
                <div class="empty">
                    No hay ventas registradas todavía.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tienda</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Subtotal</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Creada por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td data-label="ID">#<?= e_venta((string) $venta['id']) ?></td>

                                <td data-label="Tienda">
                                    <strong><?= e_venta($venta['tienda_nombre']) ?></strong>
                                </td>

                                <td data-label="Cliente">
                                    <?php if (!empty($venta['cliente_nombre'])): ?>
                                        <?= e_venta(trim($venta['cliente_nombre'] . ' ' . ($venta['cliente_apellido'] ?? ''))) ?>
                                    <?php else: ?>
                                        <span class="muted">Cliente general</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Fecha">
                                    <?= e_venta($venta['fecha'] ?? $venta['created_at']) ?>
                                </td>

                                <td data-label="Subtotal">
                                    $<?= e_venta((string) $venta['subtotal']) ?>
                                </td>

                                <td data-label="Impuesto">
                                    $<?= e_venta((string) $venta['impuesto']) ?>
                                </td>

                                <td data-label="Total">
                                    <span class="money">$<?= e_venta((string) $venta['total']) ?></span>
                                </td>


                                <td data-label="Estado">
                                    <span class="status <?= e_venta(estado_venta_class($venta['estado'])) ?>">
                                        <?= e_venta(ucfirst($venta['estado'])) ?>
                                    </span>
                                </td>

                                <td data-label="Creada por">
                                    <?= e_venta($venta['creado_por_email'] ?? 'Sistema') ?>
                                </td>

                                <td data-label="Acciones">
                                    <div class="actions">
                                        <a class="btn btn-secondary" href="index.php?route=ventas.show&id=<?= e_venta((string) $venta['id']) ?>">
                                            Ver
                                        </a>

                                        <?php if ($puedeAnularVenta && $venta['estado'] !== 'anulada'): ?>
                                            <form action="index.php?route=ventas.anular" method="POST" onsubmit="return confirm('¿Seguro que deseas anular esta venta? Esta acción devolverá el stock al inventario.');">
                                                <input type="hidden" name="csrf_token" value="<?= e_venta($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= e_venta((string) $venta['id']) ?>">

                                                <button type="submit" class="btn btn-danger">
                                                    Anular
                                                </button>
                                            </form>
                                        <?php endif; ?>
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