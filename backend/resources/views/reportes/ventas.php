<?php
function e_reporte_ventas(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$totalVentas = count($ventas);
$totalIngresos = array_sum(array_column($ventas, 'total'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas | Mega_Uni_Store</title>
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

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            margin-bottom: 20px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #1f2937;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
        }

        .summary-card small {
            display: block;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.04em;
            font-size: 12px;
        }

        .summary-card strong {
            color: #172554;
            font-size: 28px;
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

        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Reporte de Ventas por Período</h1>
        <p>Analiza las ventas diarias en un rango de fechas específico.</p>

        <div class="actions">
            <a href="index.php?route=reportes.index" class="btn btn-secondary">Volver a reportes</a>
        </div>

        <div class="card">
            <form method="GET" action="index.php">
                <input type="hidden" name="route" value="reportes.ventas">
                
                <div class="filters">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= e_reporte_ventas($fechaInicio) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?= e_reporte_ventas($fechaFin) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="tienda_id">Tienda</label>
                        <select id="tienda_id" name="tienda_id">
                            <option value="">Todas las tiendas</option>
                            <?php foreach ($tiendas as $tienda): ?>
                                <?php if ($tienda === null) { continue; } ?>
                                <option value="<?= e_reporte_ventas((string) $tienda['id']) ?>" <?= (int) $tienda['id'] === $tiendaId ? 'selected' : '' ?>>
                                    <?= e_reporte_ventas($tienda['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <small>Total de Ventas</small>
                <strong><?= $totalVentas ?></strong>
            </div>

            <div class="summary-card">
                <small>Ingresos Totales</small>
                <strong>$<?= number_format($totalIngresos, 2) ?></strong>
            </div>

            <div class="summary-card">
                <small>Promedio por Venta</small>
                <strong>$<?= $totalVentas > 0 ? number_format($totalIngresos / $totalVentas, 2) : '0.00' ?></strong>
            </div>
        </div>

        <div class="card">
            <?php if (empty($ventas)): ?>
                <p style="text-align: center; color: #6b7280;">No hay ventas en el período seleccionado.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Total Ventas</th>
                            <th>Subtotal</th>
                            <th>Descuento</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($venta['fecha'])) ?></td>
                                <td><strong><?= e_reporte_ventas((string) $venta['total_ventas']) ?></strong></td>
                                <td>$<?= number_format((float) $venta['subtotal'], 2) ?></td>
                                <td>$<?= number_format((float) $venta['descuento'], 2) ?></td>
                                <td>$<?= number_format((float) $venta['impuesto'], 2) ?></td>
                                <td><strong>$<?= number_format((float) $venta['total'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
