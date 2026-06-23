<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $movimientos
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_caja_movs(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_movs(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}

function tipo_caja_class(string $tipo): string
{
    return match ($tipo) {
        'apertura' => 'type-open',
        'ingreso'  => 'type-income',
        'egreso'   => 'type-expense',
        'cierre'   => 'type-close',
        default    => 'type-neutral',
    };
}

// Valores actuales de filtros para repintar el formulario
$filtroActualTienda = (string) ($_GET['tienda_id'] ?? '');
$filtroActualTipo   = (string) ($_GET['tipo']  ?? '');
$filtroActualDesde  = (string) ($_GET['desde'] ?? '');
$filtroActualHasta  = (string) ($_GET['hasta'] ?? '');
$tiendaIdPermitida  = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;
?>

<style>
.cajamovs-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.topbar-movs{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap}
h2.movs-title{margin:0 0 4px;color:#172554;font-size:22px}
p.movs-sub{margin:0;color:#6b7280;font-size:14px}
.btn{display:inline-flex;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;align-items:center}
.btn:hover{opacity:.85}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-sm{padding:7px 13px;font-size:13px}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);overflow:hidden}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;background:#f8fafc;border:1px solid #dbe3ef;border-radius:16px;padding:16px 20px;margin-bottom:20px}
.filter-group{display:flex;flex-direction:column;gap:5px;min-width:140px;flex:1}
.filter-group label{font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em}
.filter-group select,.filter-group input{border:1px solid #d1d5db;border-radius:10px;padding:9px 12px;font-size:14px;background:#fff;color:#111827;font-family:inherit;outline:none;transition:border-color .15s}
.filter-group select:focus,.filter-group input:focus{border-color:#6366f1}
.filter-actions{display:flex;gap:8px;align-items:flex-end;margin-top:auto}
table{width:100%;border-collapse:collapse}
th,td{padding:14px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:top;font-size:14px}
th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.money{font-weight:900;color:#172554}
.muted{color:#6b7280;font-size:13px}
.type-pill{display:inline-flex;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800}
.type-open{background:#eef2ff;color:#1e3a8a}
.type-income{background:#dcfce7;color:#166534}
.type-expense{background:#fee2e2;color:#991b1b}
.type-close{background:#fef3c7;color:#92400e}
.type-neutral{background:#f3f4f6;color:#374151}
.empty{padding:34px;text-align:center;color:#6b7280}
.paginator{display:flex;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap;padding:0 14px 14px}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff;border-color:#a5b4fc}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-ellipsis{padding:7px 4px;color:#9ca3af;font-size:13px}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
@media(max-width:900px){table,thead,tbody,th,td,tr{display:block}thead{display:none}tr{border-bottom:1px solid #e5e7eb;padding:14px}td{border:0;padding:7px 0}td::before{content:attr(data-label);display:block;font-weight:800;color:#172554;margin-bottom:3px}.filter-bar{flex-direction:column}.filter-group{min-width:unset;width:100%}}
</style>

<div class="cajamovs-wrap">
    <div class="topbar-movs">
        <div>
            <h2 class="movs-title">Movimientos de caja</h2>
            <p class="movs-sub">Historial de aperturas, ingresos, egresos y cierres de caja.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('caja.index', true)">← Volver a caja</button>
    </div>

    <!-- Barra de filtros -->
    <div class="filter-bar">
        <?php if ($tiendaIdPermitida === null && !empty($tiendas)): ?>
        <div class="filter-group">
            <label for="flt-tienda">Tienda</label>
            <select id="flt-tienda">
                <option value="">Todas las tiendas</option>
                <?php foreach ($tiendas as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= $filtroActualTienda === (string)(int)$t['id'] ? 'selected' : '' ?>>
                        <?= e_caja_movs($t['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="filter-group">
            <label for="flt-tipo">Tipo</label>
            <select id="flt-tipo">
                <option value="">Todos los tipos</option>
                <option value="apertura" <?= $filtroActualTipo === 'apertura' ? 'selected' : '' ?>>Apertura</option>
                <option value="ingreso"  <?= $filtroActualTipo === 'ingreso'  ? 'selected' : '' ?>>Ingreso</option>
                <option value="egreso"   <?= $filtroActualTipo === 'egreso'   ? 'selected' : '' ?>>Egreso</option>
                <option value="cierre"   <?= $filtroActualTipo === 'cierre'   ? 'selected' : '' ?>>Cierre</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="flt-desde">Desde</label>
            <input type="date" id="flt-desde" value="<?= e_caja_movs($filtroActualDesde) ?>">
        </div>

        <div class="filter-group">
            <label for="flt-hasta">Hasta</label>
            <input type="date" id="flt-hasta" value="<?= e_caja_movs($filtroActualHasta) ?>">
        </div>

        <div class="filter-actions">
            <button class="btn btn-primary btn-sm" id="btn-filtrar-movs">Filtrar</button>
            <button class="btn btn-secondary btn-sm" id="btn-limpiar-movs">Limpiar</button>
        </div>
    </div>

    <section class="card">
        <?php if (empty($movimientos)): ?>
            <div class="empty">No hay movimientos de caja registrados.</div>
        <?php else: ?>
            <table id="tablaMovsCaja">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tienda</th>
                        <th>Caja</th>
                        <th>Tipo</th>
                        <th>Monto sistema</th>
                        <th>Monto real</th>
                        <th>Diferencia</th>
                        <th>Venta</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $movimiento): ?>
                        <tr>
                            <td data-label="Fecha"><?= e_caja_movs($movimiento['created_at']) ?></td>
                            <td data-label="Tienda"><?= e_caja_movs($movimiento['tienda_nombre']) ?></td>
                            <td data-label="Caja"><?= e_caja_movs($movimiento['caja_nombre']) ?></td>
                            <td data-label="Tipo">
                                <span class="type-pill <?= e_caja_movs(tipo_caja_class($movimiento['tipo'])) ?>">
                                    <?= e_caja_movs(ucfirst($movimiento['tipo'])) ?>
                                </span>
                            </td>
                            <td data-label="Monto sistema">
                                <span class="money">$<?= e_caja_movs(dinero_movs($movimiento['monto'])) ?></span>
                            </td>
                            <td data-label="Monto real">
                                <?php if ($movimiento['monto_real'] !== null): ?>
                                    $<?= e_caja_movs(dinero_movs($movimiento['monto_real'])) ?>
                                <?php else: ?>
                                    <span class="muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Diferencia">
                                <?php if ($movimiento['diferencia'] !== null): ?>
                                    $<?= e_caja_movs(dinero_movs($movimiento['diferencia'])) ?>
                                <?php else: ?>
                                    <span class="muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Venta">
                                <?php if ($movimiento['venta_id'] !== null): ?>
                                    #<?= e_caja_movs((string)$movimiento['venta_id']) ?>
                                <?php else: ?>
                                    <span class="muted">Manual</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Descripción">
                                <?= e_caja_movs($movimiento['descripcion'] ?? 'Sin descripción') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="paginator" id="paginatorMovsCaja"></div>
        <?php endif; ?>
    </section>
</div>

<script>
(function () {
    // ── Paginación ──────────────────────────────────────────────────────────
    const tbody = document.querySelector('#tablaMovsCaja tbody');
    if (tbody) {
        const allRows   = Array.from(tbody.querySelectorAll('tr'));
        const PER_PAGE  = 10;
        let currentPage = 1;
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
            const c = document.getElementById('paginatorMovsCaja');
            if (!c) return;
            let h = '';
            h += `<button class="pag-btn" onclick="_cajaMovsP(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹ Ant</button>`;
            let prev = null;
            for (const p of paginas()) {
                if (prev !== null && p - prev > 1) h += '<span class="pag-ellipsis">…</span>';
                h += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" onclick="_cajaMovsP(${p})">${p}</button>`;
                prev = p;
            }
            h += `<button class="pag-btn" onclick="_cajaMovsP(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Sig ›</button>`;
            const s = (currentPage - 1) * PER_PAGE + 1;
            const e = Math.min(currentPage * PER_PAGE, allRows.length);
            h += `<span class="pag-info">${s}–${e} de ${allRows.length}</span>`;
            c.innerHTML = h;
        }

        window._cajaMovsP = function (p) { renderPage(p); };
        renderPage(1);
    }

    // ── Filtros ──────────────────────────────────────────────────────────────
    function buildRouteConFiltros() {
        const tienda = document.getElementById('flt-tienda')?.value ?? '';
        const tipo   = document.getElementById('flt-tipo')?.value  ?? '';
        const desde  = document.getElementById('flt-desde')?.value ?? '';
        const hasta  = document.getElementById('flt-hasta')?.value ?? '';

        let route = 'caja.movimientos';
        const parts = [];
        if (tienda) parts.push('tienda_id=' + encodeURIComponent(tienda));
        if (tipo)   parts.push('tipo='      + encodeURIComponent(tipo));
        if (desde)  parts.push('desde='     + encodeURIComponent(desde));
        if (hasta)  parts.push('hasta='     + encodeURIComponent(hasta));
        if (parts.length) route += '&' + parts.join('&');
        return route;
    }

    const btnFiltrar = document.getElementById('btn-filtrar-movs');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function () {
            loadContent(buildRouteConFiltros(), true);
        });
    }

    const btnLimpiar = document.getElementById('btn-limpiar-movs');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            loadContent('caja.movimientos', true);
        });
    }

    // Aplicar filtros también al presionar Enter en cualquier input de fecha
    document.querySelectorAll('#flt-desde, #flt-hasta').forEach(function (el) {
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') loadContent(buildRouteConFiltros(), true);
        });
    });
})();
</script>
