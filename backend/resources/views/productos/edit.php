<?php
/**
 * Vista: productos/edit.php
 * Solo se usa como partial dentro del modal global.
 */
function e_eprod(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<style>
.mf-title { font-size:20px; font-weight:800; color:#172554; margin:0 0 4px; }
.mf-subtitle { font-size:13px; color:#6b7280; margin:0 0 20px; }
.mf-alert { padding:11px 14px; border-radius:12px; margin-bottom:14px; font-size:14px; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; }
.mf-section { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:18px; margin-bottom:16px; }
.mf-section h3 { margin:0 0 14px; color:#172554; font-size:15px; }
.mf-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.mf-group { display:flex; flex-direction:column; gap:6px; }
.mf-group.span2 { grid-column:1/-1; }
label { font-size:13px; font-weight:700; color:#374151; }
input, textarea, select {
    width:100%; border:1px solid #dbe3ef; border-radius:10px;
    padding:10px 12px; font-size:14px; outline:none; background:#fff;
    box-sizing:border-box; font-family:inherit;
}
textarea { min-height:80px; resize:vertical; }
input:focus, textarea:focus, select:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.checkbox-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:10px; }
.check-card { border:1px solid #dbe3ef; border-radius:12px; padding:12px; background:#fff; }
.check-card label { display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; }
.check-card input[type="checkbox"] { width:auto; }
.store-card { border:1px solid #dbe3ef; border-radius:14px; padding:14px; background:#fff; margin-bottom:10px; }
.store-card-header { display:flex; align-items:center; gap:8px; margin-bottom:12px; font-weight:700; }
.store-card-header input { width:auto; }
.price-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.mf-actions { display:flex; gap:10px; margin-top:16px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:10px; padding:11px 18px; font-weight:700; cursor:pointer; font-size:14px; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
@media(max-width:520px){.mf-grid,.price-grid{grid-template-columns:1fr;}}
</style>

<h2 class="mf-title">Editar producto</h2>
<p class="mf-subtitle">Actualiza información, tiendas, precios e impuestos.</p>

<?php if ($flash !== null): ?>
    <div class="mf-alert"><?= e_eprod($flash['message']) ?></div>
<?php endif; ?>

<form id="form-editar-producto" action="index.php?route=productos.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_eprod($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= e_eprod((string) $producto['id']) ?>">

    <div class="mf-section">
        <h3>Información general</h3>
        <div class="mf-grid">
            <div class="mf-group span2">
                <label for="ep-nombre">Nombre *</label>
                <input type="text" id="ep-nombre" name="nombre" required maxlength="200"
                       value="<?= e_eprod($producto['nombre']) ?>">
            </div>
            <div class="mf-group">
                <label for="ep-codigo">Código de barras</label>
                <input type="text" id="ep-codigo" name="codigo_barras" maxlength="50"
                       value="<?= e_eprod($producto['codigo_barras'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ep-imagen">URL de imagen</label>
                <input type="text" id="ep-imagen" name="imagen_url" maxlength="255"
                       value="<?= e_eprod($producto['imagen_url'] ?? '') ?>">
            </div>
            <div class="mf-group">
                <label for="ep-categoria">Categoría</label>
                <select id="ep-categoria" name="categoria_id">
                    <option value="">Sin categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= e_eprod((string) $cat['id']) ?>"
                            <?= (int) ($producto['categoria_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= e_eprod($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="ep-unidad">Unidad de medida</label>
                <select id="ep-unidad" name="unidad_medida_id">
                    <option value="">Sin unidad</option>
                    <?php foreach ($unidades as $u): ?>
                        <option value="<?= e_eprod((string) $u['id']) ?>"
                            <?= (int) ($producto['unidad_medida_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>>
                            <?= e_eprod($u['nombre'] . ' (' . $u['simbolo'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mf-group">
                <label for="ep-estado">Estado</label>
                <select id="ep-estado" name="estado">
                    <option value="1" <?= (int) $producto['estado'] === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) $producto['estado'] === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="mf-group span2">
                <label for="ep-desc">Descripción</label>
                <textarea id="ep-desc" name="descripcion"><?= e_eprod($producto['descripcion'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <?php if (!empty($impuestos)): ?>
    <div class="mf-section">
        <h3>Impuestos</h3>
        <div class="checkbox-grid">
            <?php foreach ($impuestos as $imp): ?>
                <div class="check-card">
                    <label>
                        <input type="checkbox" name="impuestos[]"
                               value="<?= e_eprod((string) $imp['id']) ?>"
                               <?= in_array((int) $imp['id'], $impuestosProducto, true) ? 'checked' : '' ?>>
                        <?= e_eprod($imp['nombre']) ?> — <?= e_eprod((string) $imp['porcentaje']) ?>%
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($tiendas)): ?>
    <div class="mf-section">
        <h3>Tiendas y precios</h3>
        <?php foreach ($tiendas as $tienda): ?>
            <?php
                $tid       = (int) $tienda['id'];
                $asoc      = $tiendasProducto[$tid] ?? null;
                $asociada  = $asoc !== null && (int) ($asoc['estado'] ?? 0) === 1;
                $pventa    = $asoc['precio_venta']  ?? '';
                $pcompra   = $asoc['precio_compra'] ?? '';
            ?>
            <div class="store-card">
                <div class="store-card-header">
                    <input type="checkbox" class="ep-store-check"
                           id="ep-tienda-<?= e_eprod((string) $tid) ?>"
                           name="tiendas[]"
                           value="<?= e_eprod((string) $tid) ?>"
                           data-store-id="<?= e_eprod((string) $tid) ?>"
                           <?= $asociada ? 'checked' : '' ?>>
                    <label for="ep-tienda-<?= e_eprod((string) $tid) ?>" style="margin:0;cursor:pointer;">
                        <?= e_eprod($tienda['nombre']) ?>
                    </label>
                </div>
                <div class="price-grid">
                    <div class="mf-group">
                        <label>Precio de venta *</label>
                        <input type="number"
                               id="ep-pventa-<?= e_eprod((string) $tid) ?>"
                               name="precio_venta[<?= e_eprod((string) $tid) ?>]"
                               min="0" step="0.01" placeholder="Ej: 15000"
                               value="<?= e_eprod((string) $pventa) ?>"
                               <?= $asociada ? 'required' : '' ?>>
                    </div>
                    <div class="mf-group">
                        <label>Precio de compra</label>
                        <input type="number"
                               name="precio_compra[<?= e_eprod((string) $tid) ?>]"
                               min="0" step="0.01" placeholder="Ej: 10000"
                               value="<?= e_eprod((string) $pcompra) ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="mf-actions">
        <button type="submit" class="btn btn-primary">Actualizar producto</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>

<script>
document.querySelectorAll('.ep-store-check').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var id = this.dataset.storeId;
        var pv = document.getElementById('ep-pventa-' + id);
        if (pv) pv.required = this.checked;
    });
});
</script>
