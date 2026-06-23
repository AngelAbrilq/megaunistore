<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $productos
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_inv_create(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.inv-c-wrap{max-width:880px;margin:0 auto;padding:24px 20px}
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;white-space:nowrap;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
label{font-weight:800;color:#1f2937;font-size:14px}
input,select{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:13px 14px;font-size:15px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
input:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.help{color:#6b7280;font-size:12px;line-height:1.4}
.form-actions{display:flex;gap:10px;margin-top:24px;flex-wrap:wrap}
@media(max-width:720px){.form-grid{grid-template-columns:1fr}}
</style>

<div class="inv-c-wrap">
    <div style="margin-bottom:20px">
        <h2 style="margin:0 0 4px;color:#172554;font-size:22px">Registrar inventario</h2>
        <p style="margin:0;color:#6b7280;font-size:14px">Crea o actualiza el inventario inicial de un producto asociado a una tienda.</p>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-error"><?= e_inv_create($flash['message']) ?></div>
    <?php endif; ?>

    <section class="card">
        <form action="index.php?route=inventario.store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e_inv_create($csrfToken) ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="tienda_id">Tienda *</label>
                    <select id="tienda_id" name="tienda_id" required>
                        <option value="">Seleccionar tienda</option>
                        <?php foreach ($tiendas as $tienda): ?>
                            <?php if ($tienda === null) { continue; } ?>
                            <option value="<?= e_inv_create((string) $tienda['id']) ?>">
                                <?= e_inv_create($tienda['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="producto_id">Producto *</label>
                    <select id="producto_id" name="producto_id" required>
                        <option value="">Seleccionar producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?= e_inv_create((string) $producto['id']) ?>">
                                <?= e_inv_create($producto['nombre']) ?>
                                <?= !empty($producto['codigo_barras']) ? ' — ' . e_inv_create($producto['codigo_barras']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="help">Solo aparecen productos activos.</span>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad actual *</label>
                    <input type="number" id="cantidad" name="cantidad" required min="0" step="0.01" value="0">
                </div>

                <div class="form-group">
                    <label for="cantidad_minima">Cantidad mínima *</label>
                    <input type="number" id="cantidad_minima" name="cantidad_minima" required min="0" step="0.01" value="0">
                </div>

                <div class="form-group">
                    <label for="cantidad_maxima">Cantidad máxima</label>
                    <input type="number" id="cantidad_maxima" name="cantidad_maxima" min="0" step="0.01">
                </div>

                <div class="form-group">
                    <label for="ubicacion">Ubicación</label>
                    <input type="text" id="ubicacion" name="ubicacion" maxlength="255" placeholder="Ej: Bodega A – Estante 2">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar inventario</button>
                <button type="button" class="btn btn-secondary" onclick="loadContent('inventario.index', true)">Cancelar</button>
            </div>
        </form>
    </section>
</div>
