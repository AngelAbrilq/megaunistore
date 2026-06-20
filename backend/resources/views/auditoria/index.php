<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_aud(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$badgeAccion = [
    'INSERT'  => ['#dcfce7', '#166534', '➕'],
    'UPDATE'  => ['#dbeafe', '#1e40af', '✏️'],
    'DELETE'  => ['#fee2e2', '#991b1b', '🗑️'],
    'LOGIN'   => ['#fef9c3', '#854d0e', '🔐'],
    'LOGOUT'  => ['#f3f4f6', '#374151', '🚪'],
    'EXPORT'  => ['#ede9fe', '#5b21b6', '📤'],
];
?>

<style>
.aud-wrap{max-width:1400px;margin:0 auto;padding:24px 20px}
.aud-wrap h2{margin:0 0 4px;color:#172554;font-size:22px}
.aud-wrap p.sub{margin:0 0 20px;color:#6b7280;font-size:14px}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px}
label{display:block;margin-bottom:8px;font-weight:800;color:#1f2937;font-size:13px}
input,select{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:11px 14px;font-size:14px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
input:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-ghost{background:#e0e7ff;color:#1e3a8a}
.summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px}
.summary-card{background:#f8fafc;border:1px solid #e5e7eb;border-radius:16px;padding:18px}
.summary-card small{display:block;color:#6b7280;font-weight:800;text-transform:uppercase;letter-spacing:.04em;font-size:11px;margin-bottom:6px}
.summary-card strong{color:#172554;font-size:24px}
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:900px}
th,td{text-align:left;padding:11px 12px;border-bottom:1px solid #e5e7eb;font-size:13px}
th{background:#f8fafc;font-weight:800;color:#172554;text-transform:uppercase;font-size:11px;letter-spacing:.04em}
tr:hover td{background:#f8fafc}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700}
.json-cell{max-width:240px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size:12px;color:#6b7280;font-family:monospace;cursor:pointer;position:relative}
.json-cell:hover{color:#1e3a8a}
.paginator{display:flex;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap}
.pag-btn{border:1px solid #dbe3ef;background:#fff;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px;font-weight:700;color:#172554;transition:all .12s}
.pag-btn:hover:not(:disabled){background:#eef2ff;border-color:#a5b4fc}
.pag-btn.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.pag-btn:disabled{opacity:.4;cursor:not-allowed}
.pag-info{margin-left:auto;color:#6b7280;font-size:13px}
.empty-state{text-align:center;padding:60px 20px;color:#9ca3af}
.empty-state span{font-size:48px;display:block;margin-bottom:12px}
/* Modal JSON */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:#fff;border-radius:20px;padding:28px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.25)}
.modal-box h4{margin:0 0 16px;color:#172554}
.modal-box pre{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:16px;font-size:13px;overflow-x:auto;white-space:pre-wrap;word-break:break-all}
.modal-close{float:right;background:none;border:0;font-size:20px;cursor:pointer;color:#6b7280}
</style>

<!-- Modal JSON -->
<div class="modal-overlay" id="jsonModal">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('jsonModal').classList.remove('open')">✕</button>
        <h4 id="jsonModalTitle">Datos</h4>
        <pre id="jsonModalBody"></pre>
    </div>
</div>

<div class="aud-wrap">

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;flex-wrap:wrap;gap:12px">
        <div>
            <h2>🔍 Auditoría del Sistema</h2>
            <p class="sub">Trazabilidad completa de acciones — <?= number_format($total) ?> registros encontrados</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <form id="audFiltroForm">
            <div class="filters">
                <div>
                    <label for="aud_tabla">Tabla</label>
                    <select id="aud_tabla" name="tabla">
                        <option value="">Todas</option>
                        <?php foreach ($tablas as $t): ?>
                            <option value="<?= e_aud($t) ?>" <?= $filtroTabla === $t ? 'selected' : '' ?>>
                                <?= e_aud($t) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="aud_accion">Acción</label>
                    <select id="aud_accion" name="accion">
                        <option value="">Todas</option>
                        <?php foreach ($acciones as $a): ?>
                            <option value="<?= e_aud($a) ?>" <?= $filtroAccion === $a ? 'selected' : '' ?>>
                                <?= e_aud($a) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="aud_desde">Desde</label>
                    <input type="date" id="aud_desde" name="desde" value="<?= e_aud($filtroDesde) ?>">
                </div>
                <div>
                    <label for="aud_hasta">Hasta</label>
                    <input type="date" id="aud_hasta" name="hasta" value="<?= e_aud($filtroHasta) ?>">
                </div>
                <div style="align-self:flex-end">
                    <button type="submit" class="btn btn-primary" style="width:100%">🔍 Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen -->
    <?php
    $contPorAccion = array_count_values(array_column($registros, 'accion'));
    ?>
    <div class="summary-grid">
        <div class="summary-card">
            <small>Total en página</small>
            <strong><?= count($registros) ?></strong>
        </div>
        <div class="summary-card">
            <small>Total global</small>
            <strong><?= number_format($total) ?></strong>
        </div>
        <?php foreach (['INSERT','UPDATE','DELETE'] as $ac): ?>
        <div class="summary-card">
            <small><?= $ac ?></small>
            <strong><?= $contPorAccion[$ac] ?? 0 ?></strong>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabla -->
    <div class="card">
        <?php if (empty($registros)): ?>
            <div class="empty-state">
                <span>🔍</span>
                No hay registros de auditoría con los filtros aplicados.
            </div>
        <?php else: ?>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>ID Reg.</th>
                        <th>Usuario</th>
                        <th>Tienda</th>
                        <th>Datos antes</th>
                        <th>Datos después</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($registros as $r): ?>
                    <?php
                        $ac = $r['accion'];
                        [$bg, $color, $icon] = $badgeAccion[$ac] ?? ['#f3f4f6', '#374151', '•'];
                    ?>
                    <tr>
                        <td style="color:#9ca3af"><?= (int)$r['id'] ?></td>
                        <td style="white-space:nowrap;font-size:12px">
                            <?= e_aud(date('d/m/Y H:i:s', strtotime($r['created_at']))) ?>
                        </td>
                        <td>
                            <span class="badge" style="background:<?= $bg ?>;color:<?= $color ?>">
                                <?= $icon ?> <?= e_aud($ac) ?>
                            </span>
                        </td>
                        <td><code style="font-size:12px"><?= e_aud($r['tabla']) ?></code></td>
                        <td style="color:#6b7280"><?= $r['registro_id'] !== null ? (int)$r['registro_id'] : '—' ?></td>
                        <td>
                            <?php if ($r['usuario_nombre']): ?>
                                <div style="font-size:13px"><?= e_aud($r['usuario_nombre']) ?></div>
                                <div style="font-size:11px;color:#9ca3af"><?= e_aud($r['usuario_email'] ?? '') ?></div>
                            <?php else: ?>
                                <span style="color:#9ca3af">Sistema</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px"><?= $r['tienda_nombre'] ? e_aud($r['tienda_nombre']) : '<span style="color:#9ca3af">Global</span>' ?></td>
                        <td>
                            <?php if ($r['datos_antes']): ?>
                                <span class="json-cell" onclick="abrirJson('Datos antes — <?= e_aud($r['tabla']) ?> #<?= (int)$r['registro_id'] ?>', this.dataset.json)"
                                      data-json="<?= e_aud($r['datos_antes']) ?>">
                                    <?= e_aud(mb_strimwidth($r['datos_antes'], 0, 40, '…')) ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#d1d5db">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['datos_despues']): ?>
                                <span class="json-cell" onclick="abrirJson('Datos después — <?= e_aud($r['tabla']) ?> #<?= (int)$r['registro_id'] ?>', this.dataset.json)"
                                      data-json="<?= e_aud($r['datos_despues']) ?>">
                                    <?= e_aud(mb_strimwidth($r['datos_despues'], 0, 40, '…')) ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#d1d5db">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#9ca3af"><?= $r['ip_address'] ? e_aud($r['ip_address']) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginador -->
        <?php if ($totalPaginas > 1): ?>
        <div class="paginator">
            <button class="pag-btn" <?= $pagina <= 1 ? 'disabled' : '' ?>
                    onclick="audCargar(<?= $pagina - 1 ?>)">‹ Anterior</button>

            <?php for ($p = max(1, $pagina - 2); $p <= min($totalPaginas, $pagina + 2); $p++): ?>
                <button class="pag-btn <?= $p === $pagina ? 'active' : '' ?>"
                        onclick="audCargar(<?= $p ?>)"><?= $p ?></button>
            <?php endfor; ?>

            <button class="pag-btn" <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>
                    onclick="audCargar(<?= $pagina + 1 ?>)">Siguiente ›</button>

            <span class="pag-info">Página <?= $pagina ?> de <?= $totalPaginas ?> — <?= number_format($total) ?> registros</span>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
function audCargar(pagina) {
    const form   = document.getElementById('audFiltroForm');
    const data   = new FormData(form);
    const params = new URLSearchParams(data);
    params.set('pagina', pagina);
    loadContent('auditoria.index&' + params.toString(), true);
}

document.getElementById('audFiltroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    audCargar(1);
});

function abrirJson(titulo, jsonStr) {
    try {
        const pretty = JSON.stringify(JSON.parse(jsonStr), null, 2);
        document.getElementById('jsonModalTitle').textContent = titulo;
        document.getElementById('jsonModalBody').textContent  = pretty;
    } catch {
        document.getElementById('jsonModalBody').textContent = jsonStr;
    }
    document.getElementById('jsonModal').classList.add('open');
}

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('jsonModal').classList.remove('open');
});
</script>
