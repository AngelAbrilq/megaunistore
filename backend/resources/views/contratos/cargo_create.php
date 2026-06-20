<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) return;
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrfToken = $_SESSION['csrf_token'];
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}
.form-group{margin-bottom:14px;}.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;}
.form-control{width:100%;padding:9px 13px;border:1.5px solid #d1d5db;border-radius:10px;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#2563eb;}
</style>

<h3 style="font-size:18px;font-weight:800;color:#172554;margin:0 0 20px;">⚙️ Nuevo Cargo</h3>

<form method="post" action="index.php?route=contratos.cargo.store">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="tienda_id" value="<?= (int)($_SESSION['auth']['tienda_id'] ?? 0) ?>">

    <div class="form-group">
        <label class="form-label">Nombre del cargo *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Vendedor Senior, Supervisor de turno...">
    </div>
    <div class="form-group">
        <label class="form-label">Descripción</label>
        <input type="text" name="descripcion" class="form-control" placeholder="Breve descripción de responsabilidades">
    </div>
    <div class="form-group">
        <label class="form-label">Nivel jerárquico</label>
        <select name="nivel_jerarquico" class="form-control">
            <option value="1">1 — Dirección / Gerencia</option>
            <option value="2">2 — Supervisión</option>
            <option value="3" selected>3 — Operativo</option>
            <option value="4">4 — Apoyo / Auxiliar</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Crear cargo</button>
</form>
