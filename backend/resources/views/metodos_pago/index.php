<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $metodos
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_mp(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>

<style>
.mp-wrap{max-width:1100px;margin:0 auto}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.topbar h2{margin:0 0 4px;color:#172554;font-size:22px}
.topbar p{margin:0;color:#6b7280;font-size:14px}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;white-space:nowrap;font-family:inherit;transition:opacity .15s;gap:6px}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
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
table{width:100%;border-collapse:collapse}
th,td{padding:13px 16px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle}
th{background:#eff6ff;color:#172554;font-size:11px;text-transform:uppercase;letter-spacing:.05em;font-weight:800}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8fafc}
.status{display:inline-flex;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:800}
.status-on{background:#dcfce7;color:#166534}
.status-off{background:#fee2e2;color:#991b1b}
.actions{display:flex;flex-wrap:nowrap;gap:8px;align-items:center}
.empty{padding:48px;text-align:center;color:#6b7280;font-size:15px}
.export-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;padding:14px 20px;background:#f8fafc;border-top:1px solid #e5e7eb}
.export-bar span{font-size:13px;color:#6b7280;font-weight:700;margin-right:4px}
</style>

<div class="mp-wrap">
    <div class="topbar">
        <div>
            <h2>💳 Métodos de Pago</h2>
            <p>Gestiona los medios de pago aceptados en ventas y caja.</p>
        </div>
        <button class="btn btn-primary"
                onclick="openModal('index.php?route=metodos_pago.create&ajax=1')">
            + Nuevo método
        </button>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?= e_mp($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($metodos)): ?>
            <div class="empty">No hay métodos de pago registrados.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metodos as $m): ?>
                        <tr>
                            <td style="color:#9ca3af;font-size:13px"><?= (int)$m['id'] ?></td>
                            <td><strong><?= e_mp($m['nombre']) ?></strong></td>
                            <td style="color:#6b7280;font-size:13px"><?= e_mp($m['descripcion'] ?? '—') ?></td>
                            <td>
                                <span class="status <?= (int)$m['activo'] ? 'status-on' : 'status-off' ?>">
                                    <?= (int)$m['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openModal('index.php?route=metodos_pago.edit&id=<?= (int)$m['id'] ?>&ajax=1')">
                                        ✏️ Editar
                                    </button>
                                    <form action="index.php?route=metodos_pago.toggle" method="POST" class="form-mp-action">
                                        <input type="hidden" name="csrf_token" value="<?= e_mp($csrfToken) ?>">
                                        <input type="hidden" name="id"           value="<?= (int)$m['id'] ?>">
                                        <input type="hidden" name="estado_actual" value="<?= (int)$m['activo'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <?= (int)$m['activo'] ? '🔕 Desactivar' : '✅ Activar' ?>
                                        </button>
                                    </form>
                                    <form action="index.php?route=metodos_pago.destroy" method="POST" class="form-mp-action"
                                          data-confirm="¿Eliminar este método? Solo se puede si no tiene pagos asociados.">
                                        <input type="hidden" name="csrf_token" value="<?= e_mp($csrfToken) ?>">
                                        <input type="hidden" name="id"          value="<?= (int)$m['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Barra de exportación -->
            <div class="export-bar">
                <span>📤 Exportar:</span>
                <a href="index.php?route=metodos_pago.exportar&formato=csv" class="btn btn-csv btn-sm">📊 CSV / Excel</a>
                <a href="index.php?route=metodos_pago.exportar&formato=pdf" target="_blank" class="btn btn-pdf btn-sm">🖨️ PDF</a>
                <a href="index.php?route=metodos_pago.exportar&formato=svg" class="btn btn-svg btn-sm">📈 SVG</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.form-mp-action').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = form.dataset.confirm;
        if (msg && !confirm(msg)) return;
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(() => loadContent('metodos_pago.index', true));
    });
});
</script>
