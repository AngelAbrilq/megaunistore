<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_reportes(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.rpt-idx-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.rpt-idx-wrap h2{margin:0 0 4px;color:#172554;font-size:22px}
.rpt-idx-wrap p.sub{margin:0 0 20px;color:#6b7280;font-size:14px}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.rpt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:20px;margin-bottom:20px}
.rpt-card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);transition:transform .2s,box-shadow .2s}
.rpt-card:hover{transform:translateY(-4px);box-shadow:0 24px 56px rgba(15,23,42,.15)}
.rpt-card h3{margin:0 0 10px;color:#172554;font-size:18px}
.rpt-card p.rpt-desc{margin:0 0 16px;color:#6b7280;font-size:14px}
.card-icon{width:48px;height:48px;background:#e0e7ff;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:24px}
.section-title{font-size:13px;font-weight:800;color:#172554;margin:28px 0 14px;text-transform:uppercase;letter-spacing:.06em;border-bottom:2px solid #e0e7ff;padding-bottom:8px}
</style>

<div class="rpt-idx-wrap">
    <div style="margin-bottom:20px">
        <h2>Reportes y Análisis</h2>
        <p class="sub">Accede a diferentes reportes para analizar el desempeño de tu negocio.</p>
    </div>

    <div class="section-title">📊 Reportes de Ventas</div>
    <div class="rpt-grid">
        <div class="rpt-card">
            <div class="card-icon">💰</div>
            <h3>Ventas por Período</h3>
            <p class="rpt-desc">Analiza las ventas diarias en un rango de fechas específico.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.ventas', true)">Ver reporte</button>
        </div>
        <div class="rpt-card">
            <div class="card-icon">🏪</div>
            <h3>Ventas por Tienda</h3>
            <p class="rpt-desc">Compara el desempeño de ventas entre diferentes tiendas.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.ventas_por_tienda', true)">Ver reporte</button>
        </div>
        <div class="rpt-card">
            <div class="card-icon">🔥</div>
            <h3>Productos Más Vendidos</h3>
            <p class="rpt-desc">Identifica los productos con mayor demanda.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.productos_mas_vendidos', true)">Ver reporte</button>
        </div>
        <div class="rpt-card">
            <div class="card-icon">💳</div>
            <h3>Ventas por Método de Pago</h3>
            <p class="rpt-desc">Analiza qué métodos de pago son más utilizados.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.ventas_por_metodo_pago', true)">Ver reporte</button>
        </div>
    </div>

    <div class="section-title">📦 Reportes de Inventario</div>
    <div class="rpt-grid">
        <div class="rpt-card">
            <div class="card-icon">📋</div>
            <h3>Estado del Inventario</h3>
            <p class="rpt-desc">Consulta el stock actual de todos los productos.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.inventario', true)">Ver reporte</button>
        </div>
        <div class="rpt-card">
            <div class="card-icon">⚠️</div>
            <h3>Productos con Stock Bajo</h3>
            <p class="rpt-desc">Identifica productos que necesitan reabastecimiento.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.stock_bajo', true)">Ver reporte</button>
        </div>
        <div class="rpt-card">
            <div class="card-icon">📊</div>
            <h3>Movimientos de Inventario</h3>
            <p class="rpt-desc">Historial de entradas y salidas de productos.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.movimientos_inventario', true)">Ver reporte</button>
        </div>
    </div>

    <div class="section-title">💵 Reportes de Caja</div>
    <div class="rpt-grid">
        <div class="rpt-card">
            <div class="card-icon">🏦</div>
            <h3>Movimientos de Caja</h3>
            <p class="rpt-desc">Consulta todos los movimientos de caja por período.</p>
            <button class="btn btn-primary" style="width:100%" onclick="loadContent('reportes.movimientos_caja', true)">Ver reporte</button>
        </div>
    </div>
</div>
