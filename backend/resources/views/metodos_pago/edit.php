<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $metodo
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}
function e_mpe(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<style>
.form-card{background:#fff;border:1px solid #dbe3ef;border-radius:20px;padding:28px;box-shadow:0 4px 24px rgba(15,23,42,.08)}
.form-card h3{margin:0 0 20px;color:#172554;font-size:18px}
label{display:block;font-weight:800;color:#1f2937;font-size:13px;margin-bottom:6px}
input,textarea{width:100%;border:1px solid #dbe3ef;border-radius:12px;padding:11px 14px;font-size:14px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit;resize:vertical}
input:focus,textarea:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.field{margin-bottom:18px}
.btn{display:inline-flex;align-items:center;gap:6px;border:0;border-radius:12px;padding:11px 20px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-ghost{background:#e0e7ff;color:#1e3a8a}
.btn:hover{opacity:.85}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;font-size:14px;border:1px solid transparent}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.actions{display:flex;gap:12px;margin-top:8px}
</style>

<?php if ($flash): ?>
    <div class="alert alert-error"><?= e_mpe($flash['message']) ?></div>
<?php endif; ?>

<div class="form-card">
    <h3>✏️ Editar Método de Pago</h3>
    <form action="index.php?route=metodos_pago.update" method="POST" id="mpEditForm">
        <input type="hidden" name="csrf_token" value="<?= e_mpe($csrfToken) ?>">
        <input type="hidden" name="id"          value="<?= (int)$metodo['id'] ?>">

        <div class="field">
            <label for="mp_nombre">Nombre <span style="color:#dc2626">*</span></label>
            <input type="text" id="mp_nombre" name="nombre" maxlength="100" required
                   value="<?= e_mpe($metodo['nombre']) ?>">
        </div>

        <div class="field">
            <label for="mp_desc">Descripción</label>
            <textarea id="mp_desc" name="descripcion" rows="3"><?= e_mpe($metodo['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
            <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
        </div>
    </form>
</div>

<script>
document.getElementById('mpEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch(this.action, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(d => {
            if (d.ok) { closeModal(); loadContent('metodos_pago.index', true); }
            else { document.querySelector('.form-card').insertAdjacentHTML('afterbegin',
                '<div class="alert alert-error">' + d.error + '</div>'); }
        });
});
</script>
