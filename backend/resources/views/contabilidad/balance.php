<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_bal(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$titulos = ['activo' => '🟦 Activos', 'pasivo' => '🟧 Pasivos', 'patrimonio' => '🟩 Patrimonio'];
$ecuacionOk = abs($totales['activo'] - ($totales['pasivo'] + $totales['patrimonio'])) < 0.01;
?>

<div class="mod-topbar">
    <div>
        <h2>⚖️ Balance General</h2>
        <p>Activo = Pasivo + Patrimonio (CF-CON-009). Período: <?= e_bal($desde) ?> a <?= e_bal($hasta) ?>.</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_bal($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <form onsubmit="event.preventDefault(); loadContent('contabilidad.balance&desde=' + this.desde.value + '&hasta=' + this.hasta.value);">
        <div class="grid3">
            <div class="fg"><label>Desde</label><input type="date" name="desde" value="<?= e_bal($desde) ?>"></div>
            <div class="fg"><label>Hasta</label><input type="date" name="hasta" value="<?= e_bal($hasta) ?>"></div>
            <div class="fg"><label>&nbsp;</label><button type="submit" class="btn btn-primary">Generar balance</button></div>
        </div>
    </form>
</div>

<div class="kpi-row">
    <div class="kpi"><div class="kpi-lbl">Total Activos</div><div class="kpi-val">$<?= number_format($totales['activo'], 2) ?></div></div>
    <div class="kpi"><div class="kpi-lbl">Total Pasivos</div><div class="kpi-val">$<?= number_format($totales['pasivo'], 2) ?></div></div>
    <div class="kpi"><div class="kpi-lbl">Total Patrimonio</div><div class="kpi-val">$<?= number_format($totales['patrimonio'], 2) ?></div></div>
    <div class="kpi">
        <div class="kpi-lbl">Ecuación contable</div>
        <div class="kpi-val" style="color:<?= $ecuacionOk ? '#166534' : '#991b1b' ?>;"><?= $ecuacionOk ? '✓ Cuadra' : '✗ Descuadre' ?></div>
    </div>
</div>

<?php foreach ($titulos as $tipo => $titulo): ?>
    <div class="card">
        <div class="card-pad" style="border-bottom:1px solid #e5e7eb;"><strong style="color:#172554;"><?= $titulo ?></strong></div>
        <?php if (empty($grupos[$tipo])): ?>
            <div class="empty">Sin movimientos en este grupo.</div>
        <?php else: ?>
            <table class="mod-table">
                <thead><tr><th>Código</th><th>Cuenta</th><th>Débitos</th><th>Créditos</th><th>Saldo</th></tr></thead>
                <tbody>
                    <?php foreach ($grupos[$tipo] as $c): ?>
                        <tr>
                            <td><strong><?= e_bal($c['codigo']) ?></strong></td>
                            <td><?= e_bal($c['nombre']) ?></td>
                            <td>$<?= number_format((float) $c['total_debito'], 2) ?></td>
                            <td>$<?= number_format((float) $c['total_credito'], 2) ?></td>
                            <td><strong>$<?= number_format((float) $c['saldo'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc;">
                        <td colspan="4" style="text-align:right;"><strong>Total <?= e_bal(ucfirst($tipo)) ?></strong></td>
                        <td><strong>$<?= number_format($totales[$tipo], 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
