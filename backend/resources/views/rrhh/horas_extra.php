<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $empleados
 * @var array $registros
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_hex(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$estadoClase = ['pendiente' => 'st-warn', 'aprobada' => 'st-ok', 'rechazada' => 'st-bad'];
$tipos = [
    'diurna'           => 'Diurna (+25%)',
    'nocturna'         => 'Nocturna (+75%)',
    'festiva'          => 'Festiva (+100%)',
    'nocturna_festiva' => 'Nocturna festiva (+150%)',
];
?>

<div class="mod-topbar">
    <div>
        <h2>⏱️ Horas Extra</h2>
        <p>Registro y aprobación con recargos legales (NR-NOM-005).</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_hex($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <strong style="color:#172554;">Registrar horas extra</strong>
    <form action="index.php?route=rrhh.horas_extra.store" method="POST" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e_hex($csrfToken) ?>">
        <div class="grid4">
            <div class="fg">
                <label>Empleado *</label>
                <select name="empleado_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($empleados as $e): ?>
                        <option value="<?= (int) $e['id'] ?>"><?= e_hex(trim(($e['usuario_nombre'] ?? '') . ' ' . ($e['usuario_apellido'] ?? '')) ?: ($e['codigo_empleado'] ?? ('Empleado #' . $e['id']))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
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
                <label>Horas *</label>
                <input type="number" name="horas" step="0.25" min="0.25" max="12" required>
            </div>
            <div class="fg">
                <label>Valor hora ordinaria ($) *</label>
                <input type="number" name="valor_hora" step="0.01" min="1" required>
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">+ Registrar</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($registros)): ?>
        <div class="empty">No hay horas extra registradas todavía.</div>
    <?php else: ?>
        <table class="mod-table">
            <thead>
                <tr><th>Empleado</th><th>Tienda</th><th>Fecha</th><th>Tipo</th><th>Horas</th><th>Valor total</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><strong><?= e_hex(trim((string) $r['empleado_nombre'])) ?></strong></td>
                        <td><?= e_hex($r['tienda_nombre']) ?></td>
                        <td><?= e_hex((string) $r['fecha']) ?></td>
                        <td><?= e_hex($tipos[$r['tipo']] ?? $r['tipo']) ?></td>
                        <td><?= number_format((float) $r['horas'], 2) ?></td>
                        <td><strong>$<?= number_format((float) $r['valor_total'], 2) ?></strong></td>
                        <td><span class="status <?= $estadoClase[$r['estado']] ?? 'st-neutral' ?>"><?= e_hex(ucfirst($r['estado'])) ?></span></td>
                        <td style="white-space:nowrap;">
                            <?php if ($r['estado'] === 'pendiente'): ?>
                                <form class="inline-form" action="index.php?route=rrhh.horas_extra.estado" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= e_hex($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                    <input type="hidden" name="estado" value="aprobada">
                                    <button type="submit" class="btn btn-success btn-sm">✓ Aprobar</button>
                                </form>
                                <form class="inline-form" action="index.php?route=rrhh.horas_extra.estado" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= e_hex($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
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
