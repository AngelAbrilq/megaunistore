<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $movimientos
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

function e_movs(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:14px 15px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:top}
th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.type-pill{display:inline-flex;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800}
.entrada{background:#dcfce7;color:#166534}
.salida{background:#fee2e2;color:#991b1b}
.ajuste{background:#fef3c7;color:#92400e}
.empty{padding:34px;text-align:center;color:#6b7280}
</style>

<div style="max-width:1280px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap">
        <div>
            <h2 style="margin:0 0 6px;color:#172554;font-size:22px">Movimientos de inventario</h2>
            <p style="margin:0;color:#6b7280;font-size:14px">Historial de entradas, salidas y ajustes del inventario de la tienda.</p>
        </div>
        <button class="btn btn-secondary" onclick="loadContent('inventario.index', true)">&#8592; Volver al inventario</button>
    </div>

    <?php
    $fTipo  = htmlspecialchars($_GET['tipo']  ?? '', ENT_QUOTES, 'UTF-8');
    $fDesde = htmlspecialchars($_GET['desde'] ?? '', ENT_QUOTES, 'UTF-8');
    $fHasta = htmlspecialchars($_GET['hasta'] ?? '', ENT_QUOTES, 'UTF-8');
    ?>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;
                padding:14px 16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px">
        <div style="flex:1;min-width:130px">
            <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase">Tipo</label>
            <select id="movsTipo" style="width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:9px 10px;font-size:14px;background:#fff">
                <option value="" <?= $fTipo === '' ? 'selected' : '' ?>>Todos</option>
                <option value="entrada" <?= $fTipo === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                <option value="salida"  <?= $fTipo === 'salida'  ? 'selected' : '' ?>>Salida</option>
                <option value="ajuste"  <?= $fTipo === 'ajuste'  ? 'selected' : '' ?>>Ajuste</option>
            </select>
        </div>
        <div style="flex:1;min-width:130px">
            <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase">Desde</label>
            <input type="date" id="movsDesde" value="<?= $fDesde ?>"
                   style="width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:9px 10px;font-size:14px;background:#fff;box-sizing:border-box">
        </div>
        <div style="flex:1;min-width:130px">
            <label style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:4px;text-transform:uppercase">Hasta</label>
            <input type="date" id="movsHasta" value="<?= $fHasta ?>"
                   style="width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:9px 10px;font-size:14px;background:#fff;box-sizing:border-box">
        </div>
        <div style="display:flex;gap:8px">
            <button class="btn btn-primary" onclick="_movsFiltrar()">Filtrar</button>
            <button class="btn btn-secondary" onclick="loadContent('inventario.movimientos', true)">Limpiar</button>
        </div>
    </div>

    <section class="card">
        <?php if (empty($movimientos)): ?>
            <div class="empty">No hay movimientos que coincidan con los filtros.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tienda</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?= e_movs($mov['created_at']) ?></td>
                            <td><?= e_movs($mov['tienda_nombre'] ?? '—') ?></td>
                            <td><strong><?= e_movs($mov['producto_nombre'] ?? '—') ?></strong></td>
                            <td>
                                <span class="type-pill <?= e_movs($mov['tipo']) ?>">
                                    <?= e_movs(ucfirst($mov['tipo'])) ?>
                                </span>
                            </td>
                            <td><?= e_movs((string) $mov['cantidad']) ?></td>
                            <td><?= e_movs($mov['motivo'] ?? 'Sin motivo') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</div>

<script>
function _movsFiltrar() {
    var tipo  = document.getElementById('movsTipo').value;
    var desde = document.getElementById('movsDesde').value;
    var hasta = document.getElementById('movsHasta').value;
    var params = 'inventario.movimientos';
    var qs = [];
    if (tipo)  qs.push('tipo='  + encodeURIComponent(tipo));
    if (desde) qs.push('desde=' + encodeURIComponent(desde));
    if (hasta) qs.push('hasta=' + encodeURIComponent(hasta));
    if (qs.length) params += '&' + qs.join('&');
    loadContent(params, true);
}
</script>
