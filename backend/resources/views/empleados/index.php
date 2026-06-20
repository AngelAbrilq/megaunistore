<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_emp(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
.mod-topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
.mod-topbar h2 { margin:0 0 4px; color:#172554; font-size:22px; }
.mod-topbar p  { margin:0; color:#6b7280; font-size:14px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:12px; padding:10px 16px; font-weight:700; text-decoration:none; cursor:pointer; font-size:14px; white-space:nowrap; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
.btn-warning   { background:#fef3c7; color:#92400e; }
.btn-danger    { background:#fee2e2; color:#991b1b; }
.btn-sm { padding:7px 12px; font-size:13px; }
.alert { padding:13px 16px; border-radius:14px; margin-bottom:16px; border:1px solid transparent; font-size:14px; }
.alert-success { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
.alert-error   { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
.card { background:#fff; border:1px solid #dbe3ef; border-radius:20px; box-shadow:0 4px 24px rgba(15,23,42,.08); overflow:hidden; }
table { width:100%; border-collapse:collapse; }
th, td { padding:13px 15px; text-align:left; border-bottom:1px solid #e5e7eb; font-size:14px; vertical-align:middle; }
th { background:#eff6ff; color:#172554; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
tr:last-child td { border-bottom:none; }
.status { display:inline-flex; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:800; }
.status-active   { background:#dcfce7; color:#166534; }
.status-inactive { background:#fee2e2; color:#991b1b; }
.actions { display:flex; flex-wrap:nowrap; gap:8px; align-items:center; }
.empty { padding:40px; text-align:center; color:#6b7280; }
</style>

<div class="mod-topbar">
    <div>
        <h2>👔 Empleados</h2>
        <p>Gestiona el personal de las tiendas.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('index.php?route=empleados.create&ajax=1')">+ Nuevo empleado</button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_emp($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <?php if (empty($empleados)): ?>
        <div class="empty">No hay empleados registrados todavía.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Empleado</th>
                    <th>Tienda</th>
                    <th>Ingreso</th>
                    <th>Salario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $e): ?>
                    <tr>
                        <td><?= e_emp($e['codigo_empleado'] ?? '—') ?></td>
                        <td>
                            <strong><?= e_emp($e['usuario_nombre'] ?? $e['nombre'] ?? '—') ?></strong>
                        </td>
                        <td><?= e_emp($e['tienda_nombre'] ?? '—') ?></td>
                        <td><?= e_emp($e['fecha_ingreso'] ?? '—') ?></td>
                        <td><?= !empty($e['salario_base']) ? '$' . number_format((float)$e['salario_base'], 0, ',', '.') : '—' ?></td>
                        <td>
                            <?php $estado = $e['estado'] ?? 'activo'; ?>
                            <?php if ($estado === 'activo'): ?>
                                <span class="status status-active">Activo</span>
                            <?php else: ?>
                                <span class="status status-inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary btn-sm"
                                        onclick="openModal('index.php?route=empleados.edit&id=<?= e_emp((string) $e['id']) ?>&ajax=1')">
                                    Editar
                                </button>
                                <form action="index.php?route=empleados.destroy" method="POST" class="form-ajax-action"
                                      data-confirm="¿Seguro que deseas desvincular a este empleado?">
                                    <input type="hidden" name="csrf_token" value="<?= e_emp($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= e_emp((string) $e['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Desvincular</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
document.querySelectorAll('.form-ajax-action').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var msg = form.dataset.confirm;
        if (msg && !confirm(msg)) return;
        fetch(form.action, { method:'POST', body:new FormData(form) })
            .then(function() { loadContent('empleados.index', false); })
            .catch(function(err) { console.error('Error:', err); });
    });
});
</script>
