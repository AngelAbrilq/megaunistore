<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_caja_create(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.form-group{margin-bottom:18px}
label{display:block;margin-bottom:8px;font-weight:800;color:#1f2937;font-size:14px}
input,textarea,select{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:13px 14px;font-size:15px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
textarea{min-height:90px;resize:vertical}
input:focus,textarea:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.btn{display:inline-flex;border:0;border-radius:12px;padding:12px 16px;font-weight:800;cursor:pointer;font-size:14px;font-family:inherit}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.modal-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
.alert{padding:12px 14px;border-radius:12px;margin-bottom:16px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;font-size:14px}
</style>

<h3 style="margin:0 0 18px;color:#172554;font-size:17px">Nueva caja</h3>

<?php if ($flash !== null): ?>
    <div class="alert"><?= e_caja_create($flash['message']) ?></div>
<?php endif; ?>

<form action="index.php?route=caja.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_caja_create($csrfToken) ?>">

    <div class="form-group">
        <label for="cc_tienda_id">Tienda *</label>
        <select id="cc_tienda_id" name="tienda_id" required>
            <option value="">Seleccionar tienda</option>
            <?php foreach ($tiendas as $tienda): ?>
                <?php if ($tienda === null) { continue; } ?>
                <option value="<?= e_caja_create((string)$tienda['id']) ?>">
                    <?= e_caja_create($tienda['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="cc_nombre">Nombre de la caja *</label>
        <input type="text" id="cc_nombre" name="nombre" required maxlength="100" placeholder="Ej: Caja Principal">
    </div>

    <div class="form-group">
        <label for="cc_descripcion">Descripción</label>
        <textarea id="cc_descripcion" name="descripcion" placeholder="Ej: Caja ubicada en punto de venta principal."></textarea>
    </div>

    <div class="modal-actions">
        <button type="submit" class="btn btn-primary">Guardar caja</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
