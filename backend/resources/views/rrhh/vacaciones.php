<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $empleados
 * @var array $solicitudes
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_vac(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$estadoClase = ['solicitada' => 'st-warn', 'aprobada' => 'st-ok', 'rechazada' => 'st-bad'];
$tipos = [
    'vacacion'    => 'Vacaciones',
    'incapacidad' => 'Incapacidad',
    'licencia'    => 'Licencia',
    'calamidad'   => 'Calamidad',
    'permiso'     => 'Permiso',
];
?>

<div class="mod-topbar">
    <div>
        <h2>🏖️ Vacaciones y Ausencias</h2>
        <p>Solicitudes de vacaciones, incapacidades, licencias y permisos (NR-NOM-006).</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_vac($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <strong style="color:#172554;">Nueva solicitud</strong>
    <form action="index.php?route=rrhh.vacaciones.store" method="POST" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e_vac($csrfToken) ?>">
        <div class="grid4">
            <div class="fg">
                <label>Empleado *</label>
                <select name="empleado_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($empleados as $e): ?>
                        <option value="<?= (int) $e['id'] ?>"><?= e_vac(trim(($e['usuario_nombre'] ?? '') . ' ' . ($e['usuario_apellido'] ?? '')) ?: ($e['codigo_empleado'] ?? ('Empleado #' . $e['id']))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Tipo *</label>
                <select name="tipo" required>
                    <?php foreach ($tipos as $valor => $texto): ?>
                        <option value="<?= $valor ?>"><?= $texto ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Desde *</label>
                <input type="date" name="fecha_inicio" required>
            </div>
            <div class="fg">
                <label>Hasta *</label>
                <input type="date" name="fecha_fin" required>
            </div>
            <div class="fg" style="grid-column:span 3;">
                <label>Motivo</label>
                <input type="text" name="motivo" maxlength="255" placeholder="Opcional">
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">+ Solicitar</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($solicitudes)): ?>
        <div class="empty">No hay solicitudes registradas todavía.</div>
    <?php else: ?>
        <table class="mod-table">
            <thead>
                <tr><th>Empleado</th><th>Tienda</th><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Días</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $s): ?>
                    <tr>
                        <td><strong><?= e_vac(trim((string) $s['empleado_nombre'])) ?></strong></td>
                        <td><?= e_vac($s['tienda_nombre']) ?></td>
                        <td><?= e_vac($tipos[$s['tipo']] ?? $s['tipo']) ?></td>
                        <td><?= e_vac((string) $s['fecha_inicio']) ?></td>
                        <td><?= e_vac((string) $s['fecha_fin']) ?></td>
                        <td><?= (int) $s['dias'] ?></td>
                        <td><span class="status <?= $estadoClase[$s['estado']] ?? 'st-neutral' ?>"><?= e_vac(ucfirst($s['estado'])) ?></span></td>
                        <td style="white-space:nowrap;">
                            <?php if ($s['estado'] === 'solicitada'): ?>
                                <form class="inline-form" action="index.php?route=rrhh.vacaciones.estado" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= e_vac($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                    <input type="hidden" name="estado" value="aprobada">
                                    <button type="submit" class="btn btn-success btn-sm">✓ Aprobar</button>
                                </form>
                                <form class="inline-form" action="index.php?route=rrhh.vacaciones.estado" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= e_vac($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                    <input type="hidden" name="estado" value="rechazada">
                                    <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#9ca3af; font-size:13px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
