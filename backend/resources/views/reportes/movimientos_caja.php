<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_rpt_mc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$totalMovimientos = count($movimientos);
$ingresos  = array_filter($movimientos, fn($r) => in_array($r['tipo'], ['ingreso', 'apertura'], true));
$egresos   = array_filter($movimientos, fn($r) => in_array($r['tipo'], ['egreso', 'cierre'], true));
$totalIngresos = array_sum(array_column(array_values($ingresos), 'monto'));
$totalEgresos  = array_sum(array_column(array_values($egresos), 'monto'));
?>

<style>
.rpt-mc-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.rpt-mc-wrap h2{margin:0 0 4px;color:#172554;font-size:22px}
.rpt-mc-wrap p.sub{margin:0 0 20px;color:#6b7280;font-size:14px}
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
.badge{display:inline-block;padding:3px 10px;border-radius:8px;font-size:12px;font-weight:700}
.badge-ingreso{background:#d1fae5;color:#065f46}
.badge-egreso{background:#fee2e2;color:#991b1b}
.badge-apertura{background:#dbeafe;color:#1e3a8a}
.badge-cierre{background:#f3f4f6;color:#374151}
.badge-arqueo{background:#fef3c7;color:#92400e}
/* Paginador */
.paginator{display:flex;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff;border-color:#a5b4fc}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-ellipsis{padding:7px 4px;color:#9ca3af;font-size:13px}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
</style>

<div class="rpt-mc-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;flex-wrap:wrap;gap:12px">
        <div>
            <h2>Movimientos de Caja</h2>
            <p class="sub">Historial de aperturas, cierres, ingresos y egresos de caja.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('reportes.index', true)">← Volver a reportes</button>
    </div>

    <div class="card">
        <form id="filtroFormCaja">
            <div class="filters">
                <div class="form-group">
                    <label for="mc_fecha_inicio">Fecha inicio</label>
                    <input type="date" id="mc_fecha_inicio" name="fecha_inicio" value="<?= e_rpt_mc($fechaInicio) ?>">
                </div>
                <div class="form-group">
                    <label for="mc_fecha_fin">Fecha fin</label>
                    <input type="date" id="mc_fecha_fin" name="fecha_fin" value="<?= e_rpt_mc($fechaFin) ?>">
                </div>
                <div class="form-group">
                    <label for="mc_tienda_id">Tienda</label>
                    <select id="mc_tienda_id" name="tienda_id">
                        <option value="">Todas las tiendas</option>
                        <?php foreach ($tiendas as $tienda): ?>
                            <?php if ($tienda === null) { continue; } ?>
                            <option value="<?= e_rpt_mc((string)$tienda['id']) ?>" <?= (int)$tienda['id'] === $tiendaId ? 'selected' : '' ?>>
                                <?= e_rpt_mc($tienda['nombre']) ?>
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

    <div class="summary-grid">
        <div class="summary-card">
            <small>Total Movimientos</small>
            <strong><?= $totalMovimientos ?></strong>
        </div>
        <div class="summary-card">
            <small>Total Ingresos</small>
            <strong style="color:#065f46">$<?= number_format($totalIngresos, 2) ?></strong>
        </div>
        <div class="summary-card">
            <small>Total Egresos</small>
            <strong style="color:#991b1b">$<?= number_format($totalEgresos, 2) ?></strong>
        </div>
        <div class="summary-card">
            <small>Balance</small>
            <strong style="color:<?= ($totalIngresos - $totalEgresos) >= 0 ? '#065f46' : '#991b1b' ?>">
                $<?= number_format($totalIngresos - $totalEgresos, 2) ?>
            </strong>
        </div>
    </div>

    <div class="card">
        <?php if (empty($movimientos)): ?>
            <p style="text-align:center;color:#6b7280;padding:20px 0">No hay movimientos de caja en el período seleccionado.</p>
        <?php else: ?>
            <table id="tablaCajaMov">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tienda</th>
                        <th>Caja</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                        <th>Venta Ref.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?= e_rpt_mc($mov['fecha']) ?></td>
                            <td><?= e_rpt_mc($mov['tienda']) ?></td>
                            <td><?= e_rpt_mc($mov['caja']) ?></td>
                            <td>
                                <span class="badge badge-<?= e_rpt_mc(strtolower($mov['tipo'])) ?>">
                                    <?= e_rpt_mc(ucfirst($mov['tipo'])) ?>
                                </span>
                            </td>
                            <td><strong>$<?= number_format((float)$mov['monto'], 2) ?></strong></td>
                            <td><?= e_rpt_mc($mov['descripcion'] ?? '—') ?></td>
                            <td><?= $mov['venta_id'] ? '#' . e_rpt_mc((string)$mov['venta_id']) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="paginator" id="paginatorCajaMov"></div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    // ── Interceptar filtro (no navega fuera del SPA) ──────────────
    const filtroForm = document.getElementById('filtroFormCaja');
    if (filtroForm) {
        filtroForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fi = document.getElementById('mc_fecha_inicio').value;
            const ff = document.getElementById('mc_fecha_fin').value;
            const ti = document.getElementById('mc_tienda_id').value;
            let params = 'reportes.movimientos_caja&fecha_inicio=' + encodeURIComponent(fi) +
                         '&fecha_fin=' + encodeURIComponent(ff);
            if (ti) params += '&tienda_id=' + encodeURIComponent(ti);
            loadContent(params, true);
        });
    }

    // ── Paginación cliente (10 por página) ────────────────────────
    const tbody = document.querySelector('#tablaCajaMov tbody');
    if (!tbody) return;

    const allRows    = Array.from(tbody.querySelectorAll('tr'));
    const PER_PAGE   = 10;
    let   currentPage = 1;
    const totalPages = Math.max(1, Math.ceil(allRows.length / PER_PAGE));

    function renderPage(page) {
        currentPage = Math.min(Math.max(1, page), totalPages);
        const start = (currentPage - 1) * PER_PAGE;
        allRows.forEach((row, i) => {
            row.style.display = (i >= start && i < start + PER_PAGE) ? '' : 'none';
        });
        renderPaginator();
    }

    function paginas() {
        if (totalPages <= 7) return Array.from({ length: totalPages }, (_, i) => i + 1);
        const set = new Set([1, totalPages]);
        for (let i = Math.max(1, currentPage - 1); i <= Math.min(totalPages, currentPage + 1); i++) set.add(i);
        return Array.from(set).sort((a, b) => a - b);
    }

    function renderPaginator() {
        const c = document.getElementById('paginatorCajaMov');
        if (!c) return;
        let h = '';
        h += `<button class="pag-btn" onclick="_cajaMov(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹ Ant</button>`;
        let prev = null;
        for (const p of paginas()) {
            if (prev !== null && p - prev > 1) h += '<span class="pag-ellipsis">…</span>';
            h += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" onclick="_cajaMov(${p})">${p}</button>`;
            prev = p;
        }
        h += `<button class="pag-btn" onclick="_cajaMov(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Sig ›</button>`;
        const s = (currentPage - 1) * PER_PAGE + 1;
        const e = Math.min(currentPage * PER_PAGE, allRows.length);
        h += `<span class="pag-info">${s}–${e} de ${allRows.length}</span>`;
        c.innerHTML = h;
    }

    window._cajaMov = function (p) { renderPage(p); };
    renderPage(1);
})();
</script>
