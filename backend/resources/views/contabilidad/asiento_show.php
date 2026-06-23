<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $asiento
 * @var string $csrfToken
 * @var array $detalle
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_ash(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$estadoClase = ['borrador' => 'st-warn', 'aprobado' => 'st-ok', 'anulado' => 'st-bad'];
?>

<div class="mod-topbar">
    <div>
        <h2>📑 Asiento <?= e_ash($asiento['numero']) ?></h2>
        <p><?= e_ash((string) $asiento['concepto']) ?></p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-secondary" onclick="loadContent('contabilidad.asientos')">← Volver</button>
        <?php if ($asiento['estado'] === 'borrador'): ?>
            <form class="inline-form" action="index.php?route=contabilidad.asiento.estado" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_ash($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $asiento['id'] ?>">
                <input type="hidden" name="estado" value="aprobado">
                <button type="submit" class="btn btn-success">✓ Aprobar</button>
            </form>
        <?php endif; ?>
        <?php if ($asiento['estado'] !== 'anulado'): ?>
            <form class="inline-form" action="index.php?route=contabilidad.asiento.estado" method="POST" onsubmit="return confirm('¿Anular este asiento?');">
                <input type="hidden" name="csrf_token" value="<?= e_ash($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $asiento['id'] ?>">
                <input type="hidden" name="estado" value="anulado">
                <button type="submit" class="btn btn-danger">✕ Anular</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_ash($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <div class="info-grid">
        <div class="info-item"><div class="lbl">Tienda</div><div class="val"><?= e_ash($asiento['tienda_nombre']) ?></div></div>
        <div class="info-item"><div class="lbl">Fecha</div><div class="val"><?= e_ash((string) $asiento['fecha']) ?></div></div>
        <div class="info-item"><div class="lbl">Origen</div><div class="val"><?= e_ash(ucfirst((string) $asiento['tipo_origen'])) ?></div></div>
        <div class="info-item"><div class="lbl">Estado</div><div class="val"><span class="status <?= $estadoClase[$asiento['estado']] ?? 'st-neutral' ?>"><?= e_ash(ucfirst($asiento['estado'])) ?></span></div></div>
    </div>
</div>

<div class="card">
    <table class="mod-table">
        <thead>
            <tr><th>Cuenta</th><th>Descripción</th><th>Centro costo</th><th>Débito</th><th>Crédito</th></tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $d): ?>
                <tr>
                    <td><strong><?= e_ash($d['cuenta_codigo'] . ' — ' . $d['cuenta_nombre']) ?></strong></td>
                    <td><?= e_ash((string) ($d['descripcion'] ?? '—')) ?></td>
                    <td><?= e_ash((string) ($d['centro_nombre'] ?? '—')) ?></td>
                    <td><?= (float) $d['debito'] > 0 ? '$' . number_format((float) $d['debito'], 2) : '—' ?></td>
                    <td><?= (float) $d['credito'] > 0 ? '$' . number_format((float) $d['credito'], 2) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background:#f8fafc;">
                <td colspan="3" style="text-align:right;"><strong>Totales</strong></td>
                <td><strong>$<?= number_format((float) $asiento['total_debito'], 2) ?></strong></td>
                <td><strong>$<?= number_format((float) $asiento['total_credito'], 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>
