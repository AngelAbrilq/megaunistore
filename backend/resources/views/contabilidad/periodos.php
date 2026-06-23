<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $periodos
 * @var int $tiendaSel
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_per(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';
?>

<div class="mod-topbar">
    <div>
        <h2>📅 Períodos Contables</h2>
        <p>Apertura y cierre de meses contables (CF-CON-004). Los asientos requieren un período abierto.</p>
    </div>
    <?php if (!empty($tiendas)): ?>
    <div class="fg" style="min-width:220px;">
        <label>Tienda</label>
        <select onchange="loadContent('contabilidad.periodos&tienda_id=' + this.value)">
            <?php foreach ($tiendas as $t): ?>
                <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tiendaSel ? 'selected' : '' ?>><?= e_per($t['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_per($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <strong style="color:#172554;">Abrir nuevo período</strong>
    <form action="index.php?route=contabilidad.periodo.store" method="POST" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e_per($csrfToken) ?>">
        <input type="hidden" name="tienda_id" value="<?= (int) $tiendaSel ?>">
        <div class="grid4">
            <div class="fg">
                <label>Nombre *</label>
                <input type="text" name="nombre" required maxlength="50" placeholder="Ej: <?= date('Y-m') ?>" value="<?= date('Y-m') ?>">
            </div>
            <div class="fg">
                <label>Fecha inicio *</label>
                <input type="date" name="fecha_inicio" value="<?= date('Y-m-01') ?>" required>
            </div>
            <div class="fg">
                <label>Fecha fin *</label>
                <input type="date" name="fecha_fin" value="<?= date('Y-m-t') ?>" required>
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">+ Abrir período</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($periodos)): ?>
        <div class="empty">No hay períodos contables. Abre el primero para empezar a registrar asientos.</div>
    <?php else: ?>
        <table class="mod-table">
            <thead>
                <tr><th>Nombre</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Cerrado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($periodos as $p): ?>
                    <tr>
                        <td><strong><?= e_per($p['nombre']) ?></strong></td>
                        <td><?= e_per((string) $p['fecha_inicio']) ?></td>
                        <td><?= e_per((string) $p['fecha_fin']) ?></td>
                        <td><span class="status <?= $p['estado'] === 'abierto' ? 'st-ok' : 'st-bad' ?>"><?= e_per(ucfirst($p['estado'])) ?></span></td>
                        <td><?= e_per((string) ($p['cerrado_at'] ?? '—')) ?></td>
                        <td>
                            <?php if ($p['estado'] === 'abierto'): ?>
                                <form class="inline-form" action="index.php?route=contabilidad.periodo.cerrar" method="POST" onsubmit="return confirm('¿Cerrar este período? Ya no aceptará asientos.');">
                                    <input type="hidden" name="csrf_token" value="<?= e_per($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Cerrar</button>
                                </form>
                            <?php else: ?>
                                <form class="inline-form" action="index.php?route=contabilidad.periodo.reabrir" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= e_per($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Reabrir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
