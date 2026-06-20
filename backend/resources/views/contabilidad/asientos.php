<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_asi(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$estadoClase = ['borrador' => 'st-warn', 'aprobado' => 'st-ok', 'anulado' => 'st-bad'];
$origenes    = ['manual' => 'Manual', 'venta' => 'Venta', 'compra' => 'Compra', 'nomina' => 'Nómina', 'gasto' => 'Gasto', 'ajuste' => 'Ajuste'];
?>

<div class="mod-topbar">
    <div>
        <h2>📑 Asientos Contables</h2>
        <p>Libro diario con partida doble (CF-CON-002 / CF-CON-012).</p>
    </div>
    <button class="btn btn-primary" onclick="loadContent('contabilidad.asiento.create')">+ Nuevo asiento</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_asi($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <?php if (empty($asientos)): ?>
        <div class="empty">No hay asientos contables registrados todavía.</div>
    <?php else: ?>
        <table class="mod-table">
            <thead>
                <tr><th>Número</th><th>Fecha</th><th>Tienda</th><th>Concepto</th><th>Origen</th><th>Débitos</th><th>Créditos</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($asientos as $a): ?>
                    <tr>
                        <td><strong><?= e_asi($a['numero']) ?></strong></td>
                        <td><?= e_asi((string) $a['fecha']) ?></td>
                        <td><?= e_asi($a['tienda_nombre']) ?></td>
                        <td><?= e_asi(mb_strimwidth((string) $a['concepto'], 0, 50, '…')) ?></td>
                        <td><?= e_asi($origenes[$a['tipo_origen']] ?? $a['tipo_origen']) ?></td>
                        <td>$<?= number_format((float) $a['total_debito'], 2) ?></td>
                        <td>$<?= number_format((float) $a['total_credito'], 2) ?></td>
                        <td><span class="status <?= $estadoClase[$a['estado']] ?? 'st-neutral' ?>"><?= e_asi(ucfirst($a['estado'])) ?></span></td>
                        <td><button class="btn btn-secondary btn-sm" onclick="loadContent('contabilidad.asiento.show&id=<?= (int) $a['id'] ?>')">Ver</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
