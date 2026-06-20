<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}
function e_pr2(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
// $solicitudes y $csrfToken vienen del controller
?>

<style>
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;
      box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.card h2{margin:0 0 16px;color:#172554;font-size:18px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
table{width:100%;border-collapse:collapse}
th,td{padding:14px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle}
th{background:#eff6ff;color:#172554;font-size:12px;text-transform:uppercase;letter-spacing:.04em;font-weight:800}
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:8px 14px;
     font-weight:700;cursor:pointer;font-size:13px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-success{background:#dcfce7;color:#166534}
.btn-danger{background:#fee2e2;color:#991b1b}
.empty-state{text-align:center;padding:48px 20px;color:#6b7280}
.empty-state h3{color:#172554;margin:0 0 8px}
.badge{display:inline-block;padding:3px 10px;border-radius:8px;font-size:12px;font-weight:700;
       background:#fef3c7;color:#92400e}
/* Modal deny */
.overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:999;align-items:center;justify-content:center}
.overlay.open{display:flex}
.modal{background:#fff;border-radius:20px;padding:28px;max-width:440px;width:100%;box-shadow:0 24px 64px rgba(15,23,42,.2)}
.modal h3{margin:0 0 14px;color:#172554}
textarea{width:100%;border:1px solid #dbe3ef;border-radius:12px;padding:12px;font-size:14px;
         font-family:inherit;outline:none;resize:vertical;box-sizing:border-box}
textarea:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
</style>

<div style="max-width:1100px;margin:0 auto;padding:24px 20px">

    <div style="margin-bottom:24px">
        <h2 style="margin:0 0 6px;color:#172554;font-size:22px">Solicitudes de cambio de contraseña</h2>
        <p style="margin:0;color:#6b7280;font-size:14px">
            Revisa y aprueba o rechaza las solicitudes pendientes de tus trabajadores.
        </p>
    </div>

    <?php if (($flash ?? null) !== null): ?>
        <div class="alert alert-<?= e_pr2($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_pr2($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($solicitudes)): ?>
            <div class="empty-state">
                <h3>Sin solicitudes pendientes</h3>
                <p>No hay solicitudes de cambio de contraseña en este momento.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Trabajador</th>
                        <th>Rol</th>
                        <th>Tienda</th>
                        <th>Fecha solicitud</th>
                        <th style="text-align:center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                        <tr>
                            <td><?= e_pr2((string) $sol['id']) ?></td>
                            <td>
                                <strong><?= e_pr2(trim($sol['nombre'] . ' ' . $sol['apellido'])) ?></strong>
                                <div style="color:#6b7280;font-size:12px;margin-top:2px"><?= e_pr2($sol['email']) ?></div>
                            </td>
                            <td><span class="badge"><?= e_pr2($sol['rol_nombre'] ?? '—') ?></span></td>
                            <td><?= e_pr2($sol['tienda_nombre'] ?? '—') ?></td>
                            <td style="color:#6b7280;font-size:13px">
                                <?= e_pr2(date('d/m/Y H:i', strtotime($sol['created_at']))) ?>
                            </td>
                            <td style="text-align:center">
                                <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">

                                    <!-- Aprobar -->
                                    <form action="index.php?route=password.approve" method="POST"
                                          onsubmit="return confirm('¿Aprobar este cambio de contraseña?')">
                                        <input type="hidden" name="csrf_token" value="<?= e_pr2($csrfToken) ?>">
                                        <input type="hidden" name="solicitud_id" value="<?= e_pr2((string) $sol['id']) ?>">
                                        <button type="submit" class="btn btn-success">✓ Aprobar</button>
                                    </form>

                                    <!-- Rechazar (abre modal) -->
                                    <button type="button" class="btn btn-danger"
                                            onclick="abrirModalRechazo(<?= (int) $sol['id'] ?>, '<?= e_pr2(trim($sol['nombre'] . ' ' . $sol['apellido'])) ?>')">
                                        ✕ Rechazar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de rechazo -->
<div class="overlay" id="denyOverlay">
    <div class="modal">
        <h3>Rechazar solicitud</h3>
        <p style="color:#6b7280;font-size:14px;margin:0 0 16px">
            Indica el motivo del rechazo. <strong id="denyNombre"></strong> recibirá un email con esta información.
        </p>

        <form action="index.php?route=password.deny" method="POST" id="denyForm">
            <input type="hidden" name="csrf_token" value="<?= e_pr2($csrfToken) ?>">
            <input type="hidden" name="solicitud_id" id="denySolicitudId">

            <div style="margin-bottom:14px">
                <textarea name="motivo" id="denyMotivo" rows="4" required
                          placeholder="Ej: La contraseña no cumple las políticas de seguridad de la empresa..."></textarea>
            </div>

            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-danger" style="flex:1">Confirmar rechazo</button>
                <button type="button" class="btn" onclick="cerrarModal()"
                        style="background:#e5e7eb;color:#374151;flex:1">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalRechazo(id, nombre) {
    document.getElementById('denySolicitudId').value = id;
    document.getElementById('denyNombre').textContent = nombre;
    document.getElementById('denyMotivo').value = '';
    document.getElementById('denyOverlay').classList.add('open');
}

function cerrarModal() {
    document.getElementById('denyOverlay').classList.remove('open');
}

// Cerrar al hacer click fuera del modal
document.getElementById('denyOverlay').addEventListener('click', function (e) {
    if (e.target === this) cerrarModal();
});
</script>
