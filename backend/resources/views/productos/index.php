<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $productos
 */

/**
 * Vista: productos/index.php
 * - Con ?ajax=1  → devuelve partial HTML (para loadContent del SPA)
 * - Sin ?ajax=1  → carga el shell SPA que auto-carga esta ruta
 */
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

// --- Partial AJAX desde aquí ---
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_prod(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
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
.pill { display:inline-flex; padding:4px 10px; border-radius:999px; background:#eef2ff; color:#1e3a8a; font-size:12px; font-weight:800; margin:2px 0; }
.status { display:inline-flex; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:800; }
.status-active   { background:#dcfce7; color:#166534; }
.status-inactive { background:#fee2e2; color:#991b1b; }
.actions { display:flex; flex-wrap:nowrap; gap:8px; align-items:center; }
.empty { padding:40px; text-align:center; color:#6b7280; }
</style>

<div class="mod-topbar">
    <div>
        <h2>📦 Productos</h2>
        <p>Administra el catálogo, impuestos, unidades y precios por tienda.</p>
    </div>
    <button class="btn btn-primary"
            onclick="openModal('index.php?route=productos.create&ajax=1')">
        + Nuevo producto
    </button>
</div>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
        <?= e_prod($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <?php if (empty($productos)): ?>
        <div class="empty">No hay productos registrados todavía.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Unidad</th>
                    <th>Impuestos</th>
                    <th>Tiendas / precios</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= e_prod((string) $p['id']) ?></td>
                        <td>
                            <strong><?= e_prod($p['nombre']) ?></strong>
                            <?php if (!empty($p['codigo_barras'])): ?>
                                <br><span style="color:#6b7280;font-size:12px;">Código: <?= e_prod($p['codigo_barras']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e_prod($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                        <td>
                            <?php if (!empty($p['unidad_nombre'])): ?>
                                <?= e_prod($p['unidad_nombre']) ?>
                                <span class="pill"><?= e_prod($p['unidad_simbolo'] ?? '') ?></span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td style="font-size:13px;"><?= e_prod($p['impuestos'] ?? '—') ?></td>
                        <td style="font-size:13px;"><?= e_prod($p['tiendas_precios'] ?? '—') ?></td>
                        <td>
                            <?php if ((int) $p['estado'] === 1): ?>
                                <span class="status status-active">Activo</span>
                            <?php else: ?>
                                <span class="status status-inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary btn-sm"
                                        onclick="openModal('index.php?route=productos.edit&id=<?= e_prod((string) $p['id']) ?>&ajax=1')">
                                    Editar
                                </button>

                                <form action="index.php?route=productos.toggle" method="POST" class="form-ajax-action">
                                    <input type="hidden" name="csrf_token" value="<?= e_prod($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= e_prod((string) $p['id']) ?>">
                                    <input type="hidden" name="estado_actual" value="<?= e_prod((string) $p['estado']) ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <?= (int) $p['estado'] === 1 ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </form>

                                <form action="index.php?route=productos.destroy" method="POST" class="form-ajax-action"
                                      data-confirm="¿Seguro que deseas eliminar este producto?">
                                    <input type="hidden" name="csrf_token" value="<?= e_prod($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= e_prod((string) $p['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
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
// Formularios de acción rápida (toggle, destroy) dentro del contenido AJAX
// Se envían via fetch y recargan el módulo al terminar
document.querySelectorAll('.form-ajax-action').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var confirm_msg = form.dataset.confirm;
        if (confirm_msg && !confirm(confirm_msg)) return;

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData
        }).then(function() {
            // Recargar el módulo actual
            loadContent('productos.index', false);
        }).catch(function(err) {
            console.error('Error en acción:', err);
        });
    });
});
</script>
