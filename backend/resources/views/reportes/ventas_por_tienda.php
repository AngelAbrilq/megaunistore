<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $fechaFin
 * @var string $fechaInicio
 * @var array $ventas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_rpt_tienda(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$totalTiendas  = count($ventas);
$totalIngresos = array_sum(array_column($ventas, 'total_ingresos'));
?>

<style>
.rpt-tda-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.rpt-tda-wrap h2{margin:0 0 4px;color:#172554;font-size:22px}
.rpt-tda-wrap p.sub{margin:0 0 20px;color:#6b7280;font-size:14px}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
.form-group{margin-bottom:0}
label{display:block;margin-bottom:8px;font-weight:800;color:#1f2937;font-size:14px}
input,select{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:12px 14px;font-size:14px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
input:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px}
.summary-card{background:#f8fafc;border:1px solid #e5e7eb;border-radius:16px;padding:20px}
.summary-card small{display:block;color:#6b7280;font-weight:800;text-transform:uppercase;letter-spacing:.04em;font-size:12px;margin-bottom:8px}
.summary-card strong{color:#172554;font-size:26px}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid #e5e7eb;font-size:14px}
th{background:#f8fafc;font-weight:800;color:#172554;text-transform:uppercase;font-size:12px;letter-spacing:.04em}
tr:hover{background:#f8fafc}
.paginator{display:flex;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff;border-color:#a5b4fc}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-ellipsis{padding:7px 4px;color:#9ca3af;font-size:13px}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
</style>

<div class="rpt-tda-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;flex-wrap:wrap;gap:12px">
        <div>
            <h2>Ventas por Tienda</h2>
            <p class="sub">Compara el desempeño de ventas entre todas las tiendas en un período.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('reportes.index', true)">← Volver a reportes</button>
    </div>

    <div class="card">
        <form id="filtroFormTda">
            <div class="filters">
                <div class="form-group">
                    <label for="tda_fecha_inicio">Fecha inicio</label>
                    <input type="date" id="tda_fecha_inicio" name="fecha_inicio" value="<?= e_rpt_tienda($fechaInicio) ?>">
                </div>
                <div class="form-group">
                    <label for="tda_fecha_fin">Fecha fin</label>
                    <input type="date" id="tda_fecha_fin" name="fecha_fin" value="<?= e_rpt_tienda($fechaFin) ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end">
                    <button type="submit" class="btn btn-primary" style="width:100%">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <small>Tiendas con ventas</small>
            <strong><?= $totalTiendas ?></strong>
        </div>
        <div class="summary-card">
            <small>Ingresos Totales</small>
            <strong>$<?= number_format($totalIngresos, 2) ?></strong>
        </div>
    </div>

    <div class="card">
        <?php if (empty($ventas)): ?>
            <p style="text-align:center;color:#6b7280;padding:20px 0">No hay ventas en el período seleccionado.</p>
        <?php else: ?>
            <table id="tablaTda">
                <thead>
                    <tr>
                        <th>Tienda</th>
                        <th>Total Ventas</th>
                        <th>Ingresos</th>
                        <th>Promedio por Venta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $fila): ?>
                        <tr>
                            <td><strong><?= e_rpt_tienda($fila['tienda']) ?></strong></td>
                            <td><?= e_rpt_tienda((string) $fila['total_ventas']) ?></td>
                            <td><strong>$<?= number_format((float) $fila['total_ingresos'], 2) ?></strong></td>
                            <td>$<?= (int)$fila['total_ventas'] > 0 ? number_format((float)$fila['total_ingresos'] / (int)$fila['total_ventas'], 2) : '0.00' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="paginator" id="paginatorTda"></div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('filtroFormTda');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const fi = document.getElementById('tda_fecha_inicio').value;
            const ff = document.getElementById('tda_fecha_fin').value;
            loadContent('reportes.ventas_por_tienda&fecha_inicio=' + encodeURIComponent(fi) + '&fecha_fin=' + encodeURIComponent(ff), true);
        });
    }

    const tbody = document.querySelector('#tablaTda tbody');
    if (!tbody) return;

    const allRows   = Array.from(tbody.querySelectorAll('tr'));
    const PER_PAGE  = 10;
    let   currentPage = 1;
    const totalPages  = Math.max(1, Math.ceil(allRows.length / PER_PAGE));

    function renderPage(page) {
        currentPage = Math.min(Math.max(1, page), totalPages);
        const start = (currentPage - 1) * PER_PAGE;
        allRows.forEach((r, i) => { r.style.display = (i >= start && i < start + PER_PAGE) ? '' : 'none'; });
        renderPag();
    }

    function pages() {
        if (totalPages <= 7) return Array.from({ length: totalPages }, (_, i) => i + 1);
        const s = new Set([1, totalPages]);
        for (let i = Math.max(1, currentPage - 1); i <= Math.min(totalPages, currentPage + 1); i++) s.add(i);
        return Array.from(s).sort((a, b) => a - b);
    }

    function renderPag() {
        const c = document.getElementById('paginatorTda');
        if (!c) return;
        let h = `<button class="pag-btn" onclick="_tdaPag(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹ Ant</button>`;
        let prev = null;
        for (const p of pages()) {
            if (prev !== null && p - prev > 1) h += '<span class="pag-ellipsis">…</span>';
            h += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" onclick="_tdaPag(${p})">${p}</button>`;
            prev = p;
        }
        h += `<button class="pag-btn" onclick="_tdaPag(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Sig ›</button>`;
        const s = (currentPage - 1) * PER_PAGE + 1;
        const e = Math.min(currentPage * PER_PAGE, allRows.length);
        h += `<span class="pag-info">${s}–${e} de ${allRows.length}</span>`;
        c.innerHTML = h;
    }

    window._tdaPag = function (p) { renderPage(p); };
    renderPage(1);
})();
</script>
