<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $inventario
 * @var array $movimientos
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_mov(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10);margin-bottom:20px}
.card h2{margin:0 0 16px;color:#172554;font-size:18px}
.summary{display:grid;gap:10px}
.summary-item{padding:12px 14px;border-radius:14px;background:#f8fafc;border:1px solid #e5e7eb}
.summary-item small{display:block;color:#6b7280;font-weight:800;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
.summary-item strong{color:#172554}
.form-group{margin-bottom:18px}
label{display:block;margin-bottom:8px;font-weight:800;color:#1f2937;font-size:14px}
input,select,textarea{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:13px 14px;font-size:15px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
textarea{min-height:100px;resize:vertical}
input:focus,select:focus,textarea:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.help{display:block;color:#6b7280;font-size:12px;margin-top:6px;line-height:1.4}
.mov-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:20px}
table{width:100%;border-collapse:collapse}
th,td{padding:13px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:top}
th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.type-pill{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:800}
.entrada{background:#dcfce7;color:#166534}
.salida{background:#fee2e2;color:#991b1b}
.ajuste{background:#fef3c7;color:#92400e}
@media(max-width:900px){.mov-grid{grid-template-columns:1fr}}
</style>

<div style="max-width:1120px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0 0 4px;color:#172554;font-size:22px">Movimiento de inventario</h2>
            <p style="margin:0;color:#6b7280;font-size:14px">Registra entradas, salidas o ajustes para el producto seleccionado.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('inventario.index', true)">← Volver al inventario</button>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_mov($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_mov($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="mov-grid">
        <section>
            <div class="card">
                <h2>Resumen del stock</h2>
                <div class="summary">
                    <div class="summary-item">
                        <small>Tienda</small>
                        <strong><?= e_mov($inventario['tienda_nombre']) ?></strong>
                    </div>
                    <div class="summary-item">
                        <small>Producto</small>
                        <strong><?= e_mov($inventario['producto_nombre']) ?></strong>
                    </div>
                    <div class="summary-item">
                        <small>Cantidad actual</small>
                        <strong><?= e_mov((string) $inventario['cantidad']) ?> <?= e_mov($inventario['unidad_simbolo'] ?? '') ?></strong>
                    </div>
                    <div class="summary-item">
                        <small>Mínimo / Máximo</small>
                        <strong>
                            <?= e_mov((string) $inventario['cantidad_minima']) ?> /
                            <?= e_mov($inventario['cantidad_maxima'] !== null ? (string) $inventario['cantidad_maxima'] : 'No definido') ?>
                        </strong>
                    </div>
                    <div class="summary-item">
                        <small>Ubicación</small>
                        <strong><?= e_mov($inventario['ubicacion'] ?? 'Sin ubicación') ?></strong>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Registrar movimiento</h2>
                <form action="index.php?route=inventario.guardar_movimiento" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e_mov($csrfToken) ?>">
                    <input type="hidden" name="inventario_id" value="<?= e_mov((string) $inventario['id']) ?>">

                    <div class="form-group">
                        <label for="tipo">Tipo *</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                        <span class="help">Entrada suma stock, salida descuenta, ajuste reemplaza la cantidad.</span>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad *</label>
                        <input type="number" id="cantidad" name="cantidad" required min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <textarea id="motivo" name="motivo" placeholder="Ej: Compra a proveedor, venta manual, corrección de inventario."></textarea>
                    </div>

                    <div style="display:flex;gap:10px;flex-wrap:wrap">
                        <button type="submit" class="btn btn-primary">Guardar movimiento</button>
                        <button type="button" class="btn btn-secondary" onclick="loadContent('inventario.index', true)">Cancelar</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <h2>Historial de movimientos</h2>

            <?php
            $fTipo  = htmlspecialchars($_GET['tipo']  ?? '', ENT_QUOTES, 'UTF-8');
            $fDesde = htmlspecialchars($_GET['desde'] ?? '', ENT_QUOTES, 'UTF-8');
            $fHasta = htmlspecialchars($_GET['hasta'] ?? '', ENT_QUOTES, 'UTF-8');
            $invId  = (int) $inventario['id'];
            ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px;
                        padding:14px 16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px">
                <div style="flex:1;min-width:130px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase">Tipo</label>
                    <select id="movFiltroTipo" style="width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:9px 10px;font-size:14px;background:#fff">
                        <option value="" <?= $fTipo === '' ? 'selected' : '' ?>>Todos</option>
                        <option value="entrada" <?= $fTipo === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                        <option value="salida"  <?= $fTipo === 'salida'  ? 'selected' : '' ?>>Salida</option>
                        <option value="ajuste"  <?= $fTipo === 'ajuste'  ? 'selected' : '' ?>>Ajuste</option>
                    </select>
                </div>
                <div style="flex:1;min-width:130px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase">Desde</label>
                    <input type="date" id="movFiltroDesde" value="<?= $fDesde ?>"
                           style="width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:9px 10px;font-size:14px;background:#fff;box-sizing:border-box">
                </div>
                <div style="flex:1;min-width:130px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase">Hasta</label>
                    <input type="date" id="movFiltroHasta" value="<?= $fHasta ?>"
                           style="width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:9px 10px;font-size:14px;background:#fff;box-sizing:border-box">
                </div>
                <div style="display:flex;gap:8px">
                    <button class="btn btn-primary" onclick="_movFiltrar(<?= $invId ?>)">Filtrar</button>
                    <button class="btn btn-secondary" onclick="_movLimpiar(<?= $invId ?>)">Limpiar</button>
                </div>
            </div>

            <?php if (empty($movimientos)): ?>
                <p style="color:#6b7280">No hay movimientos que coincidan con los filtros.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $movimiento): ?>
                            <tr>
                                <td data-label="Fecha"><?= e_mov($movimiento['created_at']) ?></td>
                                <td data-label="Tipo">
                                    <span class="type-pill <?= e_mov($movimiento['tipo']) ?>">
                                        <?= e_mov(ucfirst($movimiento['tipo'])) ?>
                                    </span>
                                </td>
                                <td data-label="Cantidad"><?= e_mov((string) $movimiento['cantidad']) ?></td>
                                <td data-label="Motivo"><?= e_mov($movimiento['motivo'] ?? 'Sin motivo') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

<script>
function _movFiltrar(id) {
    var tipo  = document.getElementById('movFiltroTipo').value;
    var desde = document.getElementById('movFiltroDesde').value;
    var hasta = document.getElementById('movFiltroHasta').value;
    var params = 'inventario.movimiento&id=' + id;
    if (tipo)  params += '&tipo='  + encodeURIComponent(tipo);
    if (desde) params += '&desde=' + encodeURIComponent(desde);
    if (hasta) params += '&hasta=' + encodeURIComponent(hasta);
    loadContent(params, true);
}
function _movLimpiar(id) {
    loadContent('inventario.movimiento&id=' + id, true);
}
</script>
    </div>

</div>
