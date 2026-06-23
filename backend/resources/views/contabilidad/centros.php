<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $centros
 * @var string $csrfToken
 * @var array $empleados
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_cen(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';
?>

<div class="mod-topbar">
    <div>
        <h2>🏢 Centros de Costo</h2>
        <p>Clasificación de transacciones por unidad operativa (CF-CON-007).</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_cen($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <strong style="color:#172554;">Nuevo centro de costo</strong>
    <form action="index.php?route=contabilidad.centro.store" method="POST" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e_cen($csrfToken) ?>">
        <div class="grid4">
            <?php if (!empty($tiendas)): ?>
            <div class="fg">
                <label>Tienda *</label>
                <select name="tienda_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($tiendas as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= e_cen($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="fg">
                <label>Código *</label>
                <input type="text" name="codigo" required maxlength="20" placeholder="Ej: CC-01">
            </div>
            <div class="fg">
                <label>Nombre *</label>
                <input type="text" name="nombre" required maxlength="100" placeholder="Ej: Punto de venta principal">
            </div>
            <div class="fg">
                <label>Responsable</label>
                <select name="responsable_id">
                    <option value="">— Opcional —</option>
                    <?php foreach ($empleados as $e): ?>
                        <option value="<?= (int) $e['id'] ?>"><?= e_cen(trim(($e['usuario_nombre'] ?? '') . ' ' . ($e['usuario_apellido'] ?? '')) ?: ($e['codigo_empleado'] ?? ('Empleado #' . $e['id']))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">+ Crear centro</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($centros)): ?>
        <div class="empty">No hay centros de costo registrados todavía.</div>
    <?php else: ?>
        <table class="mod-table">
            <thead>
                <tr><th>Código</th><th>Nombre</th><th>Tienda</th><th>Responsable</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($centros as $c): ?>
                    <tr>
                        <td><strong><?= e_cen($c['codigo']) ?></strong></td>
                        <td><?= e_cen($c['nombre']) ?></td>
                        <td><?= e_cen($c['tienda_nombre']) ?></td>
                        <td><?= e_cen(trim((string) ($c['responsable_nombre'] ?? '')) ?: '—') ?></td>
                        <td><span class="status <?= (int) $c['activo'] === 1 ? 'st-ok' : 'st-bad' ?>"><?= (int) $c['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                        <td>
                            <form class="inline-form" action="index.php?route=contabilidad.centro.toggle" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= e_cen($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn <?= (int) $c['activo'] === 1 ? 'btn-warning' : 'btn-success' ?> btn-sm">
                                    <?= (int) $c['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
