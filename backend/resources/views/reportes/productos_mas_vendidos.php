<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_rpt_pmv(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$totalUnidades = array_sum(array_column($productos, 'cantidad_vendida'));
$totalIngresos = array_sum(array_column($productos, 'total_ventas'));
?>

<style>
.rpt-pmv-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.rpt-pmv-wrap h2{margin:0 0 4px;color:#172554;font-size:22px}
.rpt-pmv-wrap p.sub{margin:0 0 20px;color:#6b7280;font-size:14px}
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
.rank{display:inline-flex;width:28px;height:28px;border-radius:50%;background:#1e3a8a;color:#fff;font-weight:900;font-size:13px;align-items:center;justify-content:center}
.paginator{display:flex;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff;border-color:#a5b4fc}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-ellipsis{padding:7px 4px;color:#9ca3af;font-size:13px}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
</style>

<div class="rpt-pmv-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;flex-wrap:wrap;gap:12px">
        <div>
            <h2>Productos más vendidos</h2>
            <p class="sub">Ranking de productos según unidades vendidas en el período seleccionado.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('reportes.index', true)">← Volver a reportes</button>
    </div>

    <div class="card">
        <form id="filtroFormPmv">
            <div class="filters">
                <div class="form-group">
                    <label for="pmv_fecha_inicio">Fecha inicio</label>
                    <input type="date" id="pmv_fecha_inicio" name="fecha_inicio" value="<?= e_rpt_pmv($fechaInicio) ?>">
                </div>
                <div class="form-group">
                    <label for="pmv_fecha_fin">Fecha fin</label>
                    <input type="date" id="pmv_fecha_fin" name="fecha_fin" value="<?= e_rpt_pmv($fechaFin) ?>">
                </div>
                <div class="form-group">
                    <label for="pmv_tienda_id">Tienda</label>
                    <select id="pmv_tienda_id" name="tienda_id">
                        <option value="">Todas las tiendas</option>
                        <?php foreach ($tiendas as $tienda): ?>
                            <?php if ($tienda === null) { continue; } ?>
                            <option value="<?= e_rpt_pmv((string) $tienda['id']) ?>" <?= (int)$tienda['id'] === $tiendaId ? 'selected' : '' ?>>
                                <?= e_rpt_pmv($tienda['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pmv_limite">Top N productos</label>
                    <select id="pmv_limite" name="limite">
                        <?php foreach ([5, 10, 20, 50] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $limite === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end">
                    <button type="submit" class="btn btn-primary" style="width:100%">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <small>Unidades Vendidas</small>
            <strong><?= number_format($totalUnidades, 2) ?></strong>
        </div>
        <div class="summary-card">
            <small>Ingresos Totales</small>
            <strong>$<?= number_format($totalIngresos, 2) ?></strong>
        </div>
        <div class="summary-card">
            <small>Productos en Ranking</small>
            <strong><?= count($productos) ?></strong>
        </div>
    </div>

    <div class="card">
        <?php if (empty($productos)): ?>
            <p style="text-align:center;color:#6b7280;padding:20px 0">No hay productos vendidos en el período seleccionado.</p>
        <?php else: ?>
            <table id="tablaPmv">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Unidades Vendidas</th>
                        <th>Número de Ventas</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $i => $producto): ?>
                        <tr>
                            <td><span class="rank"><?= $i + 1 ?></span></td>
                            <td><strong><?= e_rpt_pmv($producto['nombre']) ?></strong></td>
                            <td><?= e_rpt_pmv($producto['codigo_barras'] ?? '—') ?></td>
                            <td><strong><?= number_format((float) $producto['cantidad_vendida'], 2) ?></strong></td>
                            <td><?= e_rpt_pmv((string) $producto['numero_ventas']) ?></td>
                            <td><strong>$<?= number_format((float) $producto['total_ventas'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="paginator" id="paginatorPmv"></div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('filtroFormPmv');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const fi = document.getElementById('pmv_fecha_inicio').value;
            const ff = document.getElementById('pmv_fecha_fin').value;
            const ti = document.getElementById('pmv_tienda_id').value;
            const li = document.getElementById('pmv_limite').value;
            let params = 'reportes.productos_mas_vendidos&fecha_inicio=' + encodeURIComponent(fi) +
                         '&fecha_fin=' + encodeURIComponent(ff) +
                         '&limite=' + encodeURIComponent(li);
            if (ti) params += '&tienda_id=' + encodeURIComponent(ti);
            loadContent(params, true);
        });
    }

    const tbody = document.querySelector('#tablaPmv tbody');
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
        const c = document.getElementById('paginatorPmv');
        if (!c) return;
        let h = `<button class="pag-btn" onclick="_pmvPag(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹ Ant</button>`;
        let prev = null;
        for (const p of pages()) {
            if (prev !== null && p - prev > 1) h += '<span class="pag-ellipsis">…</span>';
            h += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" onclick="_pmvPag(${p})">${p}</button>`;
            prev = p;
        }
        h += `<button class="pag-btn" onclick="_pmvPag(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Sig ›</button>`;
        const s = (currentPage - 1) * PER_PAGE + 1;
        const e = Math.min(currentPage * PER_PAGE, allRows.length);
        h += `<span class="pag-info">${s}–${e} de ${allRows.length}</span>`;
        c.innerHTML = h;
    }

    window._pmvPag = function (p) { renderPage(p); };
    renderPage(1);
})();
</script>
