<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_caja(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_caja(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Caja | Mega_Uni_Store</title>
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

        .btn-success {
            background: #dcfce7;
            color: #166534;
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

        .status-open {
            background: #dcfce7;
            color: #166534;
        }

        .status-closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-active {
            background: #eef2ff;
            color: #1e3a8a;
        }

        .status-inactive {
            background: #f3f4f6;
            color: #374151;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
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
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="topbar">
            <div>
                <h1>Caja</h1>
                <p>Administra cajas por tienda, apertura, cierre y movimientos manuales.</p>
            </div>

            <div class="top-actions">
                <a class="btn btn-primary" href="index.php?route=caja.create">Nueva caja</a>
                <a class="btn btn-secondary" href="index.php?route=caja.movimientos">Movimientos</a>
            </div>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_caja($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_caja($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <?php if (empty($cajas)): ?>
                <div class="empty">
                    No hay cajas registradas todavía.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Caja</th>
                            <th>Tienda</th>
                            <th>Estado</th>
                            <th>Apertura</th>
                            <th>Saldo actual</th>
                            <th>Último movimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($cajas as $caja): ?>
                            <tr>
                                <td data-label="ID">#<?= e_caja((string) $caja['id']) ?></td>

                                <td data-label="Caja">
                                    <strong><?= e_caja($caja['nombre']) ?></strong><br>
                                    <span class="muted"><?= e_caja($caja['descripcion'] ?? 'Sin descripción') ?></span>
                                </td>

                                <td data-label="Tienda">
                                    <?= e_caja($caja['tienda_nombre']) ?>
                                </td>

                                <td data-label="Estado">
                                    <?php if ((int) $caja['estado'] === 1): ?>
                                        <span class="status status-active">Activa</span>
                                    <?php else: ?>
                                        <span class="status status-inactive">Inactiva</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Apertura">
                                    <?php if ((bool) $caja['abierta']): ?>
                                        <span class="status status-open">Abierta</span>
                                    <?php else: ?>
                                        <span class="status status-closed">Cerrada</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Saldo actual">
                                    <span class="money">$<?= e_caja(dinero_caja($caja['saldo_actual'])) ?></span>
                                </td>

                                <td data-label="Último movimiento">
                                    <?php if (!empty($caja['ultimo_movimiento'])): ?>
                                        <?= e_caja(ucfirst($caja['ultimo_movimiento']['tipo'])) ?><br>
                                        <span class="muted"><?= e_caja($caja['ultimo_movimiento']['created_at']) ?></span>
                                    <?php else: ?>
                                        <span class="muted">Sin movimientos</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Acciones">
                                    <div class="actions">
                                        <?php if ((bool) $caja['abierta']): ?>
                                            <a class="btn btn-warning" href="index.php?route=caja.cierre&id=<?= e_caja((string) $caja['id']) ?>">
                                                Cerrar
                                            </a>

                                            <a class="btn btn-secondary" href="index.php?route=caja.movimiento&id=<?= e_caja((string) $caja['id']) ?>">
                                                Movimiento
                                            </a>
                                        <?php else: ?>
                                            <a class="btn btn-success" href="index.php?route=caja.apertura&id=<?= e_caja((string) $caja['id']) ?>">
                                                Abrir
                                            </a>
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
