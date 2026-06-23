<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $categoria
 * @var array $categoriasPadre
 * @var string $csrfToken
 */

function e_cate(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
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
<h2 class="mf-title">Editar categoría</h2>
<p class="mf-subtitle"><?=e_cate($categoria['nombre']??'')?></p>
<?php if($flash):?><div class="mf-alert"><?=e_cate($flash['message'])?></div><?php endif;?>
<form action="index.php?route=categorias.update" method="POST">
<input type="hidden" name="csrf_token" value="<?=e_cate($csrfToken)?>">
<input type="hidden" name="id" value="<?=e_cate((string)$categoria['id'])?>">
<div class="mf-grid">
  <div class="mf-group span2">
    <label>Nombre *</label>
    <input type="text" name="nombre" required maxlength="100" value="<?=e_cate($categoria['nombre'])?>">
  </div>
  <div class="mf-group">
    <label>Categoría padre</label>
    <select name="categoria_padre_id">
      <option value="">Sin padre</option>
      <?php foreach($categoriasPadre as $cp):?>
        <option value="<?=e_cate((string)$cp['id'])?>"
          <?=(int)($categoria['categoria_padre_id']??0)===(int)$cp['id']?'selected':''?>>
          <?=e_cate($cp['nombre'])?>
        </option>
      <?php endforeach;?>
    </select>
  </div>
  <div class="mf-group">
    <label>Estado</label>
    <select name="activo">
      <option value="1" <?=(int)$categoria['activo']===1?'selected':''?>>Activa</option>
      <option value="0" <?=(int)$categoria['activo']===0?'selected':''?>>Inactiva</option>
    </select>
  </div>
  <div class="mf-group">
    <label>URL de imagen</label>
    <input type="text" name="imagen_url" maxlength="255" value="<?=e_cate($categoria['imagen_url']??'')?>" >
  </div>
  <div class="mf-group span2">
    <label>Descripción</label>
    <textarea name="descripcion"><?=e_cate($categoria['descripcion']??'')?></textarea>
  </div>
</div>
<div class="mf-actions">
  <button type="submit" class="btn btn-primary">Actualizar</button>
  <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
</div>
</form>
