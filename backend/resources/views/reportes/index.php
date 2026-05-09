<?php
function e_reportes(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes | Mega_Uni_Store</title>
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

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 56px rgba(15, 23, 42, 0.15);
        }

        .card h2 {
            margin: 0 0 12px;
            color: #172554;
            font-size: 20px;
        }

        .card p {
            margin: 0 0 16px;
            color: #6b7280;
            font-size: 14px;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: #e0e7ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 24px;
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
            width: 100%;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: #172554;
            margin: 32px 0 16px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Reportes y Análisis</h1>
        <p>Accede a diferentes reportes para analizar el desempeño de tu negocio.</p>

        <a href="index.php" class="btn btn-secondary" style="width: auto; margin-bottom: 24px;">Volver al inicio</a>

        <div class="section-title">📊 Reportes de Ventas</div>
        <div class="grid">
            <div class="card">
                <div class="card-icon">💰</div>
                <h2>Ventas por Período</h2>
                <p>Analiza las ventas diarias en un rango de fechas específico.</p>
                <a href="index.php?route=reportes.ventas" class="btn btn-primary">Ver reporte</a>
            </div>

            <div class="card">
                <div class="card-icon">🏪</div>
                <h2>Ventas por Tienda</h2>
                <p>Compara el desempeño de ventas entre diferentes tiendas.</p>
                <a href="index.php?route=reportes.ventas_por_tienda" class="btn btn-primary">Ver reporte</a>
            </div>

            <div class="card">
                <div class="card-icon">🔥</div>
                <h2>Productos Más Vendidos</h2>
                <p>Identifica los productos con mayor demanda.</p>
                <a href="index.php?route=reportes.productos_mas_vendidos" class="btn btn-primary">Ver reporte</a>
            </div>

            <div class="card">
                <div class="card-icon">💳</div>
                <h2>Ventas por Método de Pago</h2>
                <p>Analiza qué métodos de pago son más utilizados.</p>
                <a href="index.php?route=reportes.ventas_por_metodo_pago" class="btn btn-primary">Ver reporte</a>
            </div>
        </div>

        <div class="section-title">📦 Reportes de Inventario</div>
        <div class="grid">
            <div class="card">
                <div class="card-icon">📋</div>
                <h2>Estado del Inventario</h2>
                <p>Consulta el stock actual de todos los productos.</p>
                <a href="index.php?route=reportes.inventario" class="btn btn-primary">Ver reporte</a>
            </div>

            <div class="card">
                <div class="card-icon">⚠️</div>
                <h2>Productos con Stock Bajo</h2>
                <p>Identifica productos que necesitan reabastecimiento.</p>
                <a href="index.php?route=reportes.stock_bajo" class="btn btn-primary">Ver reporte</a>
            </div>

            <div class="card">
                <div class="card-icon">📊</div>
                <h2>Movimientos de Inventario</h2>
                <p>Historial de entradas y salidas de productos.</p>
                <a href="index.php?route=reportes.movimientos_inventario" class="btn btn-primary">Ver reporte</a>
            </div>
        </div>

        <div class="section-title">💵 Reportes de Caja</div>
        <div class="grid">
            <div class="card">
                <div class="card-icon">🏦</div>
                <h2>Movimientos de Caja</h2>
                <p>Consulta todos los movimientos de caja por período.</p>
                <a href="index.php?route=reportes.movimientos_caja" class="btn btn-primary">Ver reporte</a>
            </div>
        </div>
    </main>
</body>
</html>
