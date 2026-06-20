<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_rpt_sb(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.rpt-sb-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.rpt-sb-wrap h2{margin:0 0 4px;color:#172554;font-size:22px}
.rpt-sb-wrap p.sub{margin:0 0 20px;color:#6b7280;font-size:14px}
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
.alert-warning{background:#fef3c7;border:1px solid #fcd34d;border-radius:14px;padding:16px 20px;margin-bottom:20px;color:#92400e;font-weight:700}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid #e5e7eb;font-size:14px}
th{background:#f8fafc;font-weight:800;color:#172554;text-transform:uppercase;font-size:12px;letter-spacing:.04em}
tr:hover{background:#fef2f2}
.stock-critical{color:#991b1b;font-weight:900}
.deficit{color:#dc2626;font-size:12px}
.top-actions{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.paginator{display:flex;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff;border-color:#a5b4fc}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-ellipsis{padding:7px 4px;color:#9ca3af;font-size:13px}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
</style>

<div class="rpt-sb-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;flex-wrap:wrap;gap:12px">
        <div>
            <h2>Productos con Stock Bajo</h2>
            <p class="sub">Productos que han alcanzado o superado el nivel mínimo de stock y requieren reabastecimiento.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('reportes.index', true)">← Volver a reportes</button>
    </div>

    <div class="top-actions">
        <button class="btn btn-primary" onclick="loadContent('inventario.alertas', true)">Ver alertas de inventario</button>
    </div>

    <div class="card">
        <form id="filtroFormSb">
            <div class="filters">
                <div class="form-group">
                    <label for="sb_tienda_id">Tienda</label>
                    <select id="sb_tienda_id" name="tienda_id">
                        <option value="">Todas las tiendas</option>
                        <?php foreach ($tiendas as $tienda): ?>
                            <?php if ($tienda === null) { continue; } ?>
                            <option value="<?= e_rpt_sb((string) $tienda['id']) ?>" <?= (int)$tienda['id'] === $tiendaId ? 'selected' : '' ?>>
                                <?= e_rpt_sb($tienda['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end">
                    <button type="submit" class="btn btn-primary" style="width:100%">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($productos)): ?>
        <div class="alert-warning">
            ⚠️ Se encontraron <strong><?= count($productos) ?></strong> producto(s) con stock por debajo del mínimo.
        </div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($productos)): ?>
            <p style="text-align:center;color:#065f46;font-weight:700;padding:20px 0">✓ No hay productos con stock bajo. Todo en orden.</p>
        <?php else: ?>
            <table id="tablaSb">
                <thead>
                    <tr>
                        <th>Tienda</th>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Déficit</th>
                        <th>Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <?php $deficit = (float)$producto['stock_minimo'] - (float)$producto['stock_actual']; ?>
                        <tr>
                            <td><?= e_rpt_sb($producto['tienda']) ?></td>
                            <td><strong><?= e_rpt_sb($producto['producto']) ?></strong></td>
                            <td><?= e_rpt_sb($producto['codigo_barras'] ?? '—') ?></td>
                            <td class="stock-critical"><?= number_format((float)$producto['stock_actual'], 2) ?></td>
                            <td><?= number_format((float)$producto['stock_minimo'], 2) ?></td>
                            <td class="deficit">-<?= number_format($deficit, 2) ?></td>
                            <td><?= e_rpt_sb($producto['unidad'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="paginator" id="paginatorSb"></div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('filtroFormSb');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const ti = document.getElementById('sb_tienda_id').value;
            let params = 'reportes.stock_bajo';
            if (ti) params += '&tienda_id=' + encodeURIComponent(ti);
            loadContent(params, true);
        });
    }

    const tbody = document.querySelector('#tablaSb tbody');
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
        const c = document.getElementById('paginatorSb');
        if (!c) return;
        let h = `<button class="pag-btn" onclick="_sbPag(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹ Ant</button>`;
        let prev = null;
        for (const p of pages()) {
            if (prev !== null && p - prev > 1) h += '<span class="pag-ellipsis">…</span>';
            h += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" onclick="_sbPag(${p})">${p}</button>`;
            prev = p;
        }
        h += `<button class="pag-btn" onclick="_sbPag(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Sig ›</button>`;
        const s = (currentPage - 1) * PER_PAGE + 1;
        const e = Math.min(currentPage * PER_PAGE, allRows.length);
        h += `<span class="pag-info">${s}–${e} de ${allRows.length}</span>`;
        c.innerHTML = h;
    }

    window._sbPag = function (p) { renderPage(p); };
    renderPage(1);
})();
</script>
