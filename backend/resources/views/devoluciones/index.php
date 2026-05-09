<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_devoluciones(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Devoluciones | Mega_Uni_Store</title>
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

        h1 {
            margin: 0 0 8px;
            color: #172554;
        }

        p {
            margin: 0 0 24px;
            color: #6b7280;
        }

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            margin-bottom: 18px;
            border: 1px solid;
        }

        .alert.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert.error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
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

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f8fafc;
            font-weight: 800;
            color: #172554;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.04em;
        }

        tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Devoluciones</h1>
        <p>Gestiona las devoluciones de productos de ventas realizadas.</p>

        <?php if ($flash !== null): ?>
            <div class="alert <?= e_devoluciones($flash['type']) ?>">
                <?= e_devoluciones($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="index.php?route=ventas.index" class="btn btn-primary">Ver ventas</a>
            <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
        </div>

        <div class="card">
            <?php if (empty($devoluciones)): ?>
                <p style="text-align: center; color: #6b7280;">No hay devoluciones registradas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Venta</th>
                            <th>Tienda</th>
                            <th>Monto Devuelto</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devoluciones as $devolucion): ?>
                            <tr>
                                <td><strong>#<?= e_devoluciones((string) $devolucion['id']) ?></strong></td>
                                <td>
                                    <a href="index.php?route=ventas.show&id=<?= e_devoluciones((string) $devolucion['venta_id']) ?>" style="color: #2563eb; text-decoration: none;">
                                        Venta #<?= e_devoluciones((string) $devolucion['venta_id']) ?>
                                    </a>
                                </td>
                                <td><?= e_devoluciones($devolucion['tienda_nombre']) ?></td>
                                <td><strong>$<?= number_format((float) $devolucion['monto_devuelto'], 2) ?></strong></td>
                                <td><?= e_devoluciones(substr($devolucion['motivo'], 0, 50)) ?><?= strlen($devolucion['motivo']) > 50 ? '...' : '' ?></td>
                                <td>
                                    <?php if ($devolucion['estado'] === 'completada'): ?>
                                        <span class="badge badge-success">Completada</span>
                                    <?php elseif ($devolucion['estado'] === 'pendiente'): ?>
                                        <span class="badge badge-warning">Pendiente</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Rechazada</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($devolucion['created_at'])) ?></td>
                                <td>
                                    <a href="index.php?route=devoluciones.show&id=<?= e_devoluciones((string) $devolucion['id']) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Ver detalle</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
