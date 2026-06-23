<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var string $filtroLeida
 * @var string $filtroTipo
 * @var int $noLeidas
 * @var int $pagina
 * @var array $registros
 * @var array $tipos
 * @var int $total
 * @var int $totalPaginas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_notif(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$badgeTipo = [
    'warning' => ['#fef3c7', '#92400e', '⚠️'],
    'error'   => ['#fee2e2', '#991b1b', '🚨'],
    'info'    => ['#dbeafe', '#1e40af', 'ℹ️'],
    'success' => ['#dcfce7', '#166534', '✅'],
];
?>

<style>
.notif-wrap{max-width:1200px;margin:0 auto}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.topbar h2{margin:0 0 4px;color:#172554;font-size:22px}
.topbar p{margin:0;color:#6b7280;font-size:14px}
.btn-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.btn{display:inline-flex;align-items:center;gap:6px;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;white-space:nowrap;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-warning{background:#fef3c7;color:#92400e}
.btn-danger{background:#fee2e2;color:#991b1b}
.btn-csv{background:#dcfce7;color:#166534}
.btn-pdf{background:#fee2e2;color:#991b1b}
.btn-svg{background:#ede9fe;color:#5b21b6}
.btn-sm{padding:7px 12px;font-size:13px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:20px;box-shadow:0 4px 24px rgba(15,23,42,.08);overflow:hidden;margin-bottom:16px}
.filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;padding:18px 20px;background:#f8fafc;border-bottom:1px solid #e5e7eb}
label{display:block;font-weight:800;color:#1f2937;font-size:12px;margin-bottom:5px}
select{width:100%;border:1px solid #dbe3ef;border-radius:12px;padding:9px 12px;font-size:13px;outline:none;background:#fff;font-family:inherit}
select:focus{border-color:#2563eb}
table{width:100%;border-collapse:collapse}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:13px;vertical-align:middle}
th{background:#eff6ff;color:#172554;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:800}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafbff}
tr.no-leida{background:#fffbeb}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700}
.leida-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#f59e0b}
.leida-dot.read{background:#d1d5db}
.msg-cell{max-width:300px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;color:#6b7280}
.summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;padding:16px 20px;border-bottom:1px solid #e5e7eb;background:#fafbff}
.stat-box{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px 16px;text-align:center}
.stat-box small{display:block;color:#6b7280;font-size:10px;text-transform:uppercase;font-weight:800;letter-spacing:.04em;margin-bottom:4px}
.stat-box strong{color:#172554;font-size:20px}
.paginator{display:flex;align-items:center;gap:6px;padding:14px 20px;flex-wrap:wrap}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
.export-bar{display:flex;gap:10px;align-items:center;padding:14px 20px;background:#f8fafc;border-top:1px solid #e5e7eb;flex-wrap:wrap}
.export-bar span{font-size:13px;color:#6b7280;font-weight:700}
.empty-state{text-align:center;padding:60px 20px;color:#9ca3af}
.empty-state span{font-size:48px;display:block;margin-bottom:12px}
</style>

<div class="notif-wrap">
    <div class="topbar">
        <div>
            <h2>🔔 Notificaciones</h2>
            <p>
                <?php if ($noLeidas > 0): ?>
                    <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:999px;font-weight:800;font-size:13px">
                        ⚠️ <?= $noLeidas ?> sin leer
                    </span>
                <?php else: ?>
                    Todo al día — sin notificaciones pendientes.
                <?php endif; ?>
            </p>
        </div>
        <div class="btn-row">
            <form action="index.php?route=notificaciones.marcar_todas" method="POST" class="notif-form-action">
                <input type="hidden" name="csrf_token" value="<?= e_notif($csrfToken) ?>">
                <button type="submit" class="btn btn-warning">✅ Marcar todas leídas</button>
            </form>
            <form action="index.php?route=notificaciones.limpiar" method="POST" class="notif-form-action"
                  data-confirm="¿Eliminar todas las notificaciones ya leídas?">
                <input type="hidden" name="csrf_token" value="<?= e_notif($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">🗑️ Limpiar leídas</button>
            </form>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?= e_notif($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <!-- Filtros -->
        <form id="notifFiltroForm">
            <div class="filters">
                <div>
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= e_notif($t) ?>" <?= ($filtroTipo ?? '') === $t ? 'selected' : '' ?>>
                                <?= e_notif($t) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Estado</label>
                    <select name="leida">
                        <option value="">Todas</option>
                        <option value="0" <?= $filtroLeida === 0 ? 'selected' : '' ?>>No leídas</option>
                        <option value="1" <?= $filtroLeida === 1 ? 'selected' : '' ?>>Leídas</option>
                    </select>
                </div>
                <div style="align-self:flex-end">
                    <button type="submit" class="btn btn-primary" style="width:100%">🔍 Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Resumen -->
        <?php
        $cntLeidas   = count(array_filter($registros, fn($r) => (int)$r['leida'] === 1));
        $cntNoLeidas = count($registros) - $cntLeidas;
        $porTipoPage = array_count_values(array_column($registros, 'tipo'));
        ?>
        <div class="summary-grid">
            <div class="stat-box"><small>En página</small><strong><?= count($registros) ?></strong></div>
            <div class="stat-box"><small>Total</small><strong><?= number_format($total) ?></strong></div>
            <div class="stat-box"><small>No leídas</small><strong style="color:#f59e0b"><?= $noLeidas ?></strong></div>
            <?php foreach ($porTipoPage as $tp => $cnt): ?>
                <div class="stat-box"><small><?= e_notif($tp) ?></small><strong><?= $cnt ?></strong></div>
            <?php endforeach; ?>
        </div>

        <!-- Tabla -->
        <?php if (empty($registros)): ?>
            <div class="empty-state">
                <span>🔔</span>
                No hay notificaciones con los filtros aplicados.
            </div>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Título</th>
                            <th>Mensaje</th>
                            <th>Usuario</th>
                            <th>Tienda</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $r): ?>
                            <?php
                            $tp = $r['tipo'] ?? 'info';
                            [$bg, $color, $icon] = $badgeTipo[$tp] ?? ['#f3f4f6', '#374151', '•'];
                            $esLeida = (int)$r['leida'] === 1;
                            ?>
                            <tr class="<?= $esLeida ? '' : 'no-leida' ?>" id="notif-row-<?= (int)$r['id'] ?>">
                                <td style="width:14px">
                                    <span class="leida-dot <?= $esLeida ? 'read' : '' ?>" title="<?= $esLeida ? 'Leída' : 'No leída' ?>"></span>
                                </td>
                                <td style="color:#9ca3af"><?= (int)$r['id'] ?></td>
                                <td style="white-space:nowrap;font-size:12px">
                                    <?= e_notif(date('d/m/Y H:i', strtotime($r['created_at']))) ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?= $bg ?>;color:<?= $color ?>">
                                        <?= $icon ?> <?= e_notif($tp) ?>
                                    </span>
                                </td>
                                <td><strong><?= e_notif($r['titulo']) ?></strong></td>
                                <td class="msg-cell" title="<?= e_notif($r['mensaje']) ?>">
                                    <?= e_notif(mb_strimwidth($r['mensaje'], 0, 60, '…')) ?>
                                </td>
                                <td style="font-size:12px"><?= e_notif($r['usuario_nombre'] ?? 'Sistema') ?></td>
                                <td style="font-size:12px;color:#6b7280">
                                    <?= $r['tienda_nombre'] ? e_notif($r['tienda_nombre']) : '<span style="color:#d1d5db">Global</span>' ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px">
                                        <?php if (!$esLeida): ?>
                                            <button class="btn btn-sm"
                                                    style="background:#fef3c7;color:#92400e;border:0"
                                                    onclick="marcarLeida(<?= (int)$r['id'] ?>, '<?= e_notif($csrfToken) ?>')">
                                                ✅
                                            </button>
                                        <?php endif; ?>
                                        <form action="index.php?route=notificaciones.eliminar" method="POST"
                                              class="notif-form-action"
                                              data-confirm="¿Eliminar esta notificación?">
                                            <input type="hidden" name="csrf_token" value="<?= e_notif($csrfToken) ?>">
                                            <input type="hidden" name="id"          value="<?= (int)$r['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginador -->
            <?php if ($totalPaginas > 1): ?>
            <div class="paginator">
                <button class="pag-btn" <?= $pagina <= 1 ? 'disabled' : '' ?>
                        onclick="notifCargar(<?= $pagina - 1 ?>)">‹ Anterior</button>

                <?php for ($p = max(1, $pagina - 2); $p <= min($totalPaginas, $pagina + 2); $p++): ?>
                    <button class="pag-btn <?= $p === $pagina ? 'active' : '' ?>"
                            onclick="notifCargar(<?= $p ?>)"><?= $p ?></button>
                <?php endfor; ?>

                <button class="pag-btn" <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>
                        onclick="notifCargar(<?= $pagina + 1 ?>)">Siguiente ›</button>

                <span class="pag-info">Página <?= $pagina ?> / <?= $totalPaginas ?> — <?= number_format($total) ?> total</span>
            </div>
            <?php endif; ?>

            <!-- Exportación -->
            <div class="export-bar">
                <span>📤 Exportar:</span>
                <a href="index.php?route=notificaciones.exportar&formato=csv" class="btn btn-csv btn-sm">📊 CSV / Excel</a>
                <a href="index.php?route=notificaciones.exportar&formato=pdf" target="_blank" class="btn btn-pdf btn-sm">🖨️ PDF</a>
                <a href="index.php?route=notificaciones.exportar&formato=svg" class="btn btn-svg btn-sm">📈 SVG</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filtro + paginación
function notifCargar(pagina) {
    const data   = new FormData(document.getElementById('notifFiltroForm'));
    const params = new URLSearchParams(data);
    params.set('pagina', pagina);
    loadContent('notificaciones.index&' + params.toString(), true);
}

document.getElementById('notifFiltroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    notifCargar(1);
});

// Marcar leída (AJAX silencioso)
function marcarLeida(id, csrf) {
    const fd = new FormData();
    fd.append('csrf_token', csrf);
    fd.append('id', id);

    fetch('index.php?route=notificaciones.marcar_leida', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                const row = document.getElementById('notif-row-' + id);
                if (row) {
                    row.classList.remove('no-leida');
                    const dot = row.querySelector('.leida-dot');
                    if (dot) { dot.classList.add('read'); dot.title = 'Leída'; }
                    // quitar botón ✅
                    const btn = row.querySelector('button[onclick^="marcarLeida"]');
                    if (btn) btn.remove();
                }
            }
        });
}

// Forms de acción (marcar todas / limpiar / eliminar)
document.querySelectorAll('.notif-form-action').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = form.dataset.confirm;
        if (msg && !confirm(msg)) return;
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(() => loadContent('notificaciones.index', true));
    });
});
</script>
