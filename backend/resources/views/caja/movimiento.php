<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_caja_mov(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_mov(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}
?>

<style>
.summary-mini{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
.summary-mini-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:14px}
.summary-mini-item small{display:block;color:#6b7280;font-weight:800;text-transform:uppercase;font-size:11px;letter-spacing:.04em;margin-bottom:4px}
.summary-mini-item strong{color:#172554;font-size:16px}
.form-group{margin-bottom:18px}
label{display:block;margin-bottom:8px;font-weight:800;color:#1f2937;font-size:14px}
input,textarea,select{width:100%;border:1px solid #dbe3ef;border-radius:14px;padding:13px 14px;font-size:15px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
textarea{min-height:90px;resize:vertical}
input:focus,textarea:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.btn{display:inline-flex;border:0;border-radius:12px;padding:12px 16px;font-weight:800;cursor:pointer;font-size:14px;font-family:inherit}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.modal-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
.alert{padding:12px 14px;border-radius:12px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
.alert-error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
@media(max-width:600px){.summary-mini{grid-template-columns:1fr}}
</style>

<h3 style="margin:0 0 4px;color:#172554;font-size:17px">Movimiento de caja</h3>
<p style="margin:0 0 18px;color:#6b7280;font-size:13px">Registra ingresos o egresos manuales de caja.</p>

<?php if ($flash !== null): ?>
    <div class="alert alert-<?= e_caja_mov($flash['type'] === 'success' ? 'success' : 'error') ?>">
        <?= e_caja_mov($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="summary-mini">
    <div class="summary-mini-item">
        <small>Caja</small>
        <strong><?= e_caja_mov($caja['nombre']) ?></strong>
    </div>
    <div class="summary-mini-item">
        <small>Tienda</small>
        <strong><?= e_caja_mov($caja['tienda_nombre']) ?></strong>
    </div>
    <div class="summary-mini-item">
        <small>Saldo actual</small>
        <strong>$<?= e_caja_mov(dinero_mov($caja['saldo_actual'])) ?></strong>
    </div>
</div>

<form action="index.php?route=caja.guardar_movimiento" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_caja_mov($csrfToken) ?>">
    <input type="hidden" name="caja_id" value="<?= e_caja_mov((string)$caja['id']) ?>">

    <div class="form-group">
        <label for="mv_tipo">Tipo *</label>
        <select id="mv_tipo" name="tipo" required>
            <option value="">Seleccionar tipo</option>
            <option value="ingreso">Ingreso</option>
            <option value="egreso">Egreso</option>
        </select>
    </div>

    <div class="form-group">
        <label for="mv_monto">Monto *</label>
        <input type="number" id="mv_monto" name="monto" required min="0.01" step="0.01">
    </div>

    <div class="form-group">
        <label for="mv_descripcion">Descripción</label>
        <textarea id="mv_descripcion" name="descripcion" placeholder="Ej: Compra de bolsas, ingreso adicional, retiro autorizado."></textarea>
    </div>

    <div class="modal-actions">
        <button type="submit" class="btn btn-primary">Guardar movimiento</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </div>
</form>
