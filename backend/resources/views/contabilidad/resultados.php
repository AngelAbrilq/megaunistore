<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_res(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$titulos = ['ingreso' => '🟩 Ingresos', 'costo' => '🟨 Costos de venta', 'egreso' => '🟥 Gastos operacionales'];
?>

<div class="mod-topbar">
    <div>
        <h2>📈 Estado de Resultados (P&G)</h2>
        <p>Ingresos − Costos − Gastos = Utilidad (CF-CON-010). Período: <?= e_res($desde) ?> a <?= e_res($hasta) ?>.</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_res($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <form onsubmit="event.preventDefault(); loadContent('contabilidad.resultados&desde=' + this.desde.value + '&hasta=' + this.hasta.value);">
        <div class="grid3">
            <div class="fg"><label>Desde</label><input type="date" name="desde" value="<?= e_res($desde) ?>"></div>
            <div class="fg"><label>Hasta</label><input type="date" name="hasta" value="<?= e_res($hasta) ?>"></div>
            <div class="fg"><label>&nbsp;</label><button type="submit" class="btn btn-primary">Generar P&G</button></div>
        </div>
    </form>
</div>

<div class="kpi-row">
    <div class="kpi"><div class="kpi-lbl">Ingresos</div><div class="kpi-val">$<?= number_format($totales['ingreso'], 2) ?></div></div>
    <div class="kpi"><div class="kpi-lbl">Costos</div><div class="kpi-val">$<?= number_format($totales['costo'], 2) ?></div></div>
    <div class="kpi"><div class="kpi-lbl">Utilidad bruta</div><div class="kpi-val">$<?= number_format($utilidadBruta, 2) ?></div></div>
    <div class="kpi"><div class="kpi-lbl">Gastos</div><div class="kpi-val">$<?= number_format($totales['egreso'], 2) ?></div></div>
    <div class="kpi">
        <div class="kpi-lbl">Utilidad neta</div>
        <div class="kpi-val" style="color:<?= $utilidadNeta >= 0 ? '#166534' : '#991b1b' ?>;">$<?= number_format($utilidadNeta, 2) ?></div>
    </div>
</div>

<?php foreach ($titulos as $tipo => $titulo): ?>
    <div class="card">
        <div class="card-pad" style="border-bottom:1px solid #e5e7eb;"><strong style="color:#172554;"><?= $titulo ?></strong></div>
        <?php if (empty($grupos[$tipo])): ?>
            <div class="empty">Sin movimientos en este grupo.</div>
        <?php else: ?>
            <table class="mod-table">
                <thead><tr><th>Código</th><th>Cuenta</th><th>Saldo</th></tr></thead>
                <tbody>
                    <?php foreach ($grupos[$tipo] as $c): ?>
                        <tr>
                            <td><strong><?= e_res($c['codigo']) ?></strong></td>
                            <td><?= e_res($c['nombre']) ?></td>
                            <td><strong>$<?= number_format((float) $c['saldo'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc;">
                        <td colspan="2" style="text-align:right;"><strong>Total</strong></td>
                        <td><strong>$<?= number_format($totales[$tipo], 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
