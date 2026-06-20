<?php
function e_catc(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
?>
<style>
.mf-title{font-size:20px;font-weight:800;color:#172554;margin:0 0 4px}
.mf-subtitle{font-size:13px;color:#6b7280;margin:0 0 20px}
.mf-alert{padding:11px 14px;border-radius:12px;margin-bottom:14px;font-size:14px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b}
.mf-alert.success{border-color:#bbf7d0;background:#f0fdf4;color:#166534}
.mf-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.mf-group{display:flex;flex-direction:column;gap:6px}
.mf-group.span2{grid-column:1/-1}
label{font-size:13px;font-weight:700;color:#374151}
input,textarea,select{width:100%;border:1px solid #dbe3ef;border-radius:10px;padding:10px 12px;font-size:14px;outline:none;background:#fff;box-sizing:border-box;font-family:inherit}
textarea{min-height:80px;resize:vertical}
input:focus,textarea:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.mf-actions{display:flex;gap:10px;margin-top:18px}
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:11px 18px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
@media(max-width:520px){.mf-grid{grid-template-columns:1fr}}
</style>
<h2 class="mf-title">Nueva categoría</h2>
<p class="mf-subtitle">Organiza el catálogo de productos.</p>
<?php if($flash):?><div class="mf-alert"><?=e_catc($flash['message'])?></div><?php endif;?>
<form action="index.php?route=categorias.store" method="POST">
<input type="hidden" name="csrf_token" value="<?=e_catc($csrfToken)?>">
<div class="mf-grid">
  <div class="mf-group span2">
    <label for="catc-nombre">Nombre *</label>
    <input type="text" id="catc-nombre" name="nombre" required maxlength="100">
  </div>
  <div class="mf-group">
    <label for="catc-padre">Categoría padre</label>
    <select id="catc-padre" name="categoria_padre_id">
      <option value="">Sin padre</option>
      <?php foreach($categoriasPadre as $cp):?>
        <option value="<?=e_catc((string)$cp['id'])?>"><?=e_catc($cp['nombre'])?></option>
      <?php endforeach;?>
    </select>
  </div>
  <div class="mf-group">
    <label for="catc-img">URL de imagen</label>
    <input type="text" id="catc-img" name="imagen_url" maxlength="255">
  </div>
  <div class="mf-group span2">
    <label for="catc-desc">Descripción</label>
    <textarea id="catc-desc" name="descripcion"></textarea>
  </div>
</div>
<div class="mf-actions">
  <button type="submit" class="btn btn-primary">Guardar</button>
  <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
</div>
</form>
