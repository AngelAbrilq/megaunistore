<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_gas(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';

$estadoClase = ['pendiente' => 'st-warn', 'pagado' => 'st-ok', 'anulado' => 'st-bad'];
?>

<div class="mod-topbar">
    <div>
        <h2>💸 Gastos Operacionales</h2>
        <p>Registro y control de egresos por tienda (CF-CON-006).</p>
    </div>
    <button class="btn btn-primary" onclick="loadContent('gastos.create')">+ Nuevo gasto</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_gas($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <?php if (empty($gastos)): ?>
        <div class="empty">No hay gastos registrados todavía.</div>
    <?php else: ?>
        <table class="mod-table">
            <thead>
                <tr>
                    <th>#</th><th>Fecha</th><th>Tienda</th><th>Concepto</th>
                    <th>Cuenta</th><th>Monto</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gastos as $g): ?>
                    <tr>
                        <td>#<?= (int) $g['id'] ?></td>
                        <td><?= e_gas((string) $g['fecha']) ?></td>
                        <td><?= e_gas($g['tienda_nombre']) ?></td>
                        <td><strong><?= e_gas($g['concepto']) ?></strong></td>
                        <td><?= e_gas($g['cuenta_nombre'] ?? '—') ?></td>
                        <td><strong>$<?= number_format((float) $g['monto'], 2) ?></strong></td>
                        <td><span class="status <?= $estadoClase[$g['estado']] ?? 'st-neutral' ?>"><?= e_gas(ucfirst($g['estado'])) ?></span></td>
                        <td style="white-space:nowrap;">
                            <?php if ($g['estado'] === 'pendiente'): ?>
                                <button class="btn btn-secondary btn-sm" onclick="loadContent('gastos.edit&id=<?= (int) $g['id'] ?>')">Editar</button>
                                <form class="inline-form" action="index.php?route=gastos.estado" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= e_gas($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                                    <input type="hidden" name="estado" value="pagado">
                                    <button type="submit" class="btn btn-success btn-sm">✓ Pagar</button>
                                </form>
                                <form class="inline-form" action="index.php?route=gastos.estado" method="POST" onsubmit="return confirm('¿Anular este gasto?');">
                                    <input type="hidden" name="csrf_token" value="<?= e_gas($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                                    <input type="hidden" name="estado" value="anulado">
                                    <button type="submit" class="btn btn-danger btn-sm">Anular</button>
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
