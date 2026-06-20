<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_bk(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$fmtIcon = ['pdf' => '🖨️', 'csv' => '📊', 'xlsx' => '📗'];
$fmtColor = [
    'pdf'  => ['#fee2e2','#991b1b'],
    'csv'  => ['#dcfce7','#166534'],
    'xlsx' => ['#dbeafe','#1e40af'],
];
?>

<style>
.bk-wrap{max-width:1300px;margin:0 auto}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.topbar h2{margin:0 0 4px;color:#172554;font-size:22px}
.topbar p{margin:0;color:#6b7280;font-size:14px}
.btn{display:inline-flex;align-items:center;gap:6px;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;white-space:nowrap;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-danger{background:#fee2e2;color:#991b1b}
.btn-sm{padding:7px 12px;font-size:13px}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:20px;box-shadow:0 4px 24px rgba(15,23,42,.08);padding:24px;margin-bottom:20px}
.card h3{margin:0 0 18px;color:#172554;font-size:16px;font-weight:800}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.stat{background:#eff6ff;border:1px solid #bfdbfe;border-radius:16px;padding:16px;text-align:center}
.stat small{display:block;color:#6b7280;font-size:11px;text-transform:uppercase;font-weight:800;letter-spacing:.04em;margin-bottom:6px}
.stat strong{color:#1e3a8a;font-size:22px}
.export-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
.export-card{background:#f8fafc;border:1px solid #e5e7eb;border-radius:16px;padding:20px}
.export-card h4{margin:0 0 4px;color:#172554;font-size:15px;display:flex;align-items:center;gap:8px}
.export-card p{margin:0 0 16px;color:#6b7280;font-size:13px}
.export-btns{display:flex;gap:8px;flex-wrap:wrap}
.ex-btn{display:inline-flex;align-items:center;gap:5px;border:0;border-radius:10px;padding:8px 14px;font-weight:700;text-decoration:none;cursor:pointer;font-size:13px;transition:opacity .15s}
.ex-btn:hover{opacity:.85}
.ex-csv{background:#dcfce7;color:#166534}
.ex-pdf{background:#fee2e2;color:#991b1b}
.ex-svg{background:#ede9fe;color:#5b21b6}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
table{width:100%;border-collapse:collapse}
th,td{padding:11px 12px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:13px;vertical-align:middle}
th{background:#f8fafc;color:#172554;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:800}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafbff}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700}
.empty{padding:32px;text-align:center;color:#9ca3af}
</style>

<div class="bk-wrap">
    <div class="topbar">
        <div>
            <h2>💾 Respaldos y Exportaciones</h2>
            <p>Exporta datos del sistema en CSV, PDF o SVG · Historial de exportaciones registradas.</p>
        </div>
        <form action="index.php?route=backups.limpiar_historial" method="POST" class="bk-form-action"
              data-confirm="¿Eliminar registros de exportaciones con más de 90 días?">
            <input type="hidden" name="csrf_token" value="<?= e_bk($csrfToken) ?>">
            <button type="submit" class="btn btn-danger btn-sm">🗑️ Limpiar historial (+90 días)</button>
        </form>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?= e_bk($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat">
            <small>Total exportaciones</small>
            <strong><?= number_format($estadisticas['total_exportaciones']) ?></strong>
        </div>
        <?php foreach ($estadisticas['por_formato'] as $fmt => $cnt): ?>
            <div class="stat">
                <small><?= strtoupper(e_bk($fmt)) ?></small>
                <strong><?= number_format((int)$cnt) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="stat">
            <small>Tamaño total</small>
            <strong><?= $estadisticas['tamano_total'] > 0 ? number_format($estadisticas['tamano_total'] / 1024, 1) . ' KB' : '—' ?></strong>
        </div>
        <div class="stat">
            <small>Última exportación</small>
            <strong style="font-size:13px"><?= $estadisticas['ultima'] ? date('d/m/Y', strtotime($estadisticas['ultima'])) : '—' ?></strong>
        </div>
    </div>

    <!-- Panel de exportación -->
    <div class="card">
        <h3>📤 Exportar datos del sistema</h3>
        <div class="export-grid">
            <?php foreach ($conjuntos as $clave => $info): ?>
                <div class="export-card">
                    <h4><?= $info['emoji'] ?> <?= e_bk($info['label']) ?></h4>
                    <p>Hasta 2 000 registros más recientes</p>
                    <div class="export-btns">
                        <a href="index.php?route=backups.exportar&conjunto=<?= $clave ?>&formato=csv"
                           class="ex-btn ex-csv">📊 CSV</a>
                        <a href="index.php?route=backups.exportar&conjunto=<?= $clave ?>&formato=pdf"
                           target="_blank" class="ex-btn ex-pdf">🖨️ PDF</a>
                        <a href="index.php?route=backups.exportar&conjunto=<?= $clave ?>&formato=svg"
                           class="ex-btn ex-svg">📈 SVG</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Historial de exportaciones -->
    <div class="card">
        <h3>📋 Historial de exportaciones</h3>
        <?php if (empty($historial)): ?>
            <div class="empty">Aún no hay exportaciones registradas.<br>
                <small>Cada descarga de CSV o PDF queda registrada automáticamente.</small>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Formato</th>
                            <th>Archivo</th>
                            <th>Reporte</th>
                            <th>Tienda</th>
                            <th>Tamaño</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $h): ?>
                            <?php
                            $fmt = $h['formato'];
                            [$bg, $cl] = $fmtColor[$fmt] ?? ['#f3f4f6','#374151'];
                            ?>
                            <tr>
                                <td style="color:#9ca3af"><?= (int)$h['id'] ?></td>
                                <td style="white-space:nowrap;font-size:12px">
                                    <?= e_bk(date('d/m/Y H:i', strtotime($h['created_at']))) ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?= $bg ?>;color:<?= $cl ?>">
                                        <?= $fmtIcon[$fmt] ?? '📄' ?> <?= strtoupper(e_bk($fmt)) ?>
                                    </span>
                                </td>
                                <td style="font-family:monospace;font-size:12px;color:#6b7280">
                                    <?= $h['archivo_url'] ? e_bk($h['archivo_url']) : '—' ?>
                                </td>
                                <td style="font-size:13px"><?= e_bk($h['reporte_nombre']) ?></td>
                                <td style="font-size:13px;color:#6b7280"><?= e_bk($h['tienda']) ?></td>
                                <td style="font-size:12px;color:#9ca3af">
                                    <?= $h['tamano_bytes'] > 0 ? number_format($h['tamano_bytes'] / 1024, 1) . ' KB' : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.bk-form-action').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = form.dataset.confirm;
        if (msg && !confirm(msg)) return;
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(() => loadContent('backups.index', true));
    });
});
</script>
