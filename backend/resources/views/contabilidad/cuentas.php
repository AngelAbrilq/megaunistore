<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $cuentas
 * @var int $tiendaSel
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_cta(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/../layout/_mod_styles.php';
?>

<div class="mod-topbar">
    <div>
        <h2>📒 Plan de Cuentas (PUC)</h2>
        <p>Catálogo de cuentas contables por tienda (CF-CON-001).</p>
    </div>
    <?php if (!empty($tiendas)): ?>
    <div class="fg" style="min-width:220px;">
        <label>Tienda</label>
        <select onchange="loadContent('contabilidad.cuentas&tienda_id=' + this.value)">
            <?php foreach ($tiendas as $t): ?>
                <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $tiendaSel ? 'selected' : '' ?>><?= e_cta($t['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_cta($flash['message']) ?></div>
<?php endif; ?>

<div class="card card-pad">
    <strong style="color:#172554;">Nueva cuenta</strong>
    <form action="index.php?route=contabilidad.cuenta.store" method="POST" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e_cta($csrfToken) ?>">
        <input type="hidden" name="tienda_id" value="<?= (int) $tiendaSel ?>">
        <div class="grid4">
            <div class="fg">
                <label>Código *</label>
                <input type="text" name="codigo" required maxlength="20" placeholder="Ej: 5110">
            </div>
            <div class="fg">
                <label>Nombre *</label>
                <input type="text" name="nombre" required maxlength="150" placeholder="Ej: Honorarios">
            </div>
            <div class="fg">
                <label>Tipo *</label>
                <select name="tipo" required>
                    <option value="activo">Activo</option>
                    <option value="pasivo">Pasivo</option>
                    <option value="patrimonio">Patrimonio</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Gasto</option>
                    <option value="costo">Costo</option>
                </select>
            </div>
            <div class="fg">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">+ Crear cuenta</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <table class="mod-table">
        <thead>
            <tr><th>Código</th><th>Nombre</th><th>Tipo</th><th>Naturaleza</th><th>Nivel</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($cuentas as $c): ?>
                <tr>
                    <td><strong><?= e_cta($c['codigo']) ?></strong></td>
                    <td style="padding-left:<?= 15 + ((int) $c['nivel'] - 1) * 18 ?>px;"><?= e_cta($c['nombre']) ?></td>
                    <td><?= e_cta(ucfirst($c['tipo'])) ?></td>
                    <td><span class="status st-neutral"><?= e_cta(ucfirst($c['naturaleza'])) ?></span></td>
                    <td><?= (int) $c['nivel'] ?></td>
                    <td><span class="status <?= (int) $c['activo'] === 1 ? 'st-ok' : 'st-bad' ?>"><?= (int) $c['activo'] === 1 ? 'Activa' : 'Inactiva' ?></span></td>
                    <td>
                        <form class="inline-form" action="index.php?route=contabilidad.cuenta.toggle" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= e_cta($csrfToken) ?>">
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
</div>
