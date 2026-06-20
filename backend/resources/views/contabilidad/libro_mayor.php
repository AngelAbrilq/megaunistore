<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_lib(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$cuentaSel = (int) ($_GET['cuenta_id'] ?? 0);
$desdeSel  = (string) ($_GET['desde'] ?? date('Y-m-01'));
$hastaSel  = (string) ($_GET['hasta'] ?? date('Y-m-d'));
?>

<div class="mod-topbar">
    <div>
        <h2>📖 Libro Mayor</h2>
        <p>Movimientos y saldo por cuenta contable (CF-CON-011). Solo asientos aprobados.</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_lib($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <form method="GET" action="index.php" onsubmit="event.preventDefault(); loadContent('contabilidad.libro_mayor&cuenta_id=' + this.cuenta_id.value + '&desde=' + this.desde.value + '&hasta=' + this.hasta.value);">
        <div class="grid4">
            <div class="fg">
                <label>Cuenta *</label>
                <select name="cuenta_id" required>
                    <option value="">— Selecciona —</option>
                    <?php foreach ($cuentas as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $cuentaSel === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= e_lib($c['codigo'] . ' — ' . $c['nombre'] . (isset($c['tienda_nombre']) ? ' (' . $c['tienda_nombre'] . ')' : '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>Desde</label>
                <input type="date" name="desde" value="<?= e_lib($desdeSel) ?>">
            </div>
            <div class="fg">
                <label>Hasta</label>
                <input type="date" name="hasta" value="<?= e_lib($hastaSel) ?>">
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">Consultar</button>
            </div>
        </div>
    </form>
</div>

<?php if ($cuenta !== null): ?>
    <?php
        $saldo = 0.0;
        $esDebito = ($cuenta['naturaleza'] ?? 'debito') === 'debito';
        $totDeb = 0.0; $totCre = 0.0;
    ?>
    <div class="card">
        <div class="card-pad" style="border-bottom:1px solid #e5e7eb;">
            <strong style="color:#172554;"><?= e_lib($cuenta['codigo'] . ' — ' . $cuenta['nombre']) ?></strong>
            <span class="status st-neutral" style="margin-left:10px;">Naturaleza: <?= e_lib(ucfirst((string) $cuenta['naturaleza'])) ?></span>
        </div>
        <?php if (empty($movimientos)): ?>
            <div class="empty">Sin movimientos aprobados en el rango seleccionado.</div>
        <?php else: ?>
            <table class="mod-table">
                <thead>
                    <tr><th>Fecha</th><th>Asiento</th><th>Concepto</th><th>Débito</th><th>Crédito</th><th>Saldo</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $m): ?>
                        <?php
                            $deb = (float) $m['debito'];
                            $cre = (float) $m['credito'];
                            $totDeb += $deb; $totCre += $cre;
                            $saldo += $esDebito ? ($deb - $cre) : ($cre - $deb);
                        ?>
                        <tr>
                            <td><?= e_lib((string) $m['fecha']) ?></td>
                            <td><strong><?= e_lib($m['numero']) ?></strong></td>
                            <td><?= e_lib(mb_strimwidth((string) ($m['descripcion'] ?: $m['concepto']), 0, 60, '…')) ?></td>
                            <td><?= $deb > 0 ? '$' . number_format($deb, 2) : '—' ?></td>
                            <td><?= $cre > 0 ? '$' . number_format($cre, 2) : '—' ?></td>
                            <td><strong>$<?= number_format($saldo, 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc;">
                        <td colspan="3" style="text-align:right;"><strong>Totales</strong></td>
                        <td><strong>$<?= number_format($totDeb, 2) ?></strong></td>
                        <td><strong>$<?= number_format($totCre, 2) ?></strong></td>
                        <td><strong>$<?= number_format($saldo, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>
