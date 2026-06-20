<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-sm{padding:6px 12px;font-size:13px;}
.form-group{margin-bottom:14px;}.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;}
.form-control{width:100%;padding:9px 13px;border:1.5px solid #d1d5db;border-radius:10px;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#2563eb;}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
</style>

<h3 style="font-size:18px;font-weight:800;color:#172554;margin:0 0 20px;">Editar Contrato #<?= $contrato['id'] ?></h3>

<form method="post" action="index.php?route=contratos.update">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= $contrato['id'] ?>">

    <div class="form-group">
        <label class="form-label">Empleado</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($contrato['empleado_nombre'] . ' ' . $contrato['empleado_apellido']) ?>" readonly style="background:#f9fafb;">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Tipo de contrato</label>
            <select name="tipo_contrato" class="form-control">
                <?php foreach (['indefinido'=>'Indefinido','fijo'=>'Término fijo','obra_labor'=>'Obra o labor','aprendizaje'=>'Aprendizaje','prestacion'=>'Prestación'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $contrato['tipo_contrato'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Jornada</label>
            <select name="jornada" class="form-control">
                <?php foreach (['completa'=>'Completa','media'=>'Media jornada','flexible'=>'Flexible'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($contrato['jornada'] ?? 'completa') === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Fecha inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($contrato['fecha_inicio']) ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Fecha fin <small style="color:#9ca3af;">(vacío = indefinido)</small></label>
            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($contrato['fecha_fin'] ?? '') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Salario base</label>
            <input type="number" name="salario_base" class="form-control" value="<?= $contrato['salario_base'] ?>" step="0.01" min="1">
        </div>
        <div class="form-group">
            <label class="form-label">Cargo</label>
            <select name="cargo_id" class="form-control">
                <?php foreach ($cargos as $cargo): ?>
                    <option value="<?= $cargo['id'] ?>" <?= (int)$contrato['cargo_id'] === (int)$cargo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cargo['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row3">
        <div class="form-group">
            <label class="form-label">EPS</label>
            <input type="text" name="eps_id" class="form-control" value="<?= htmlspecialchars($contrato['eps_id'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">AFP</label>
            <input type="text" name="afp_id" class="form-control" value="<?= htmlspecialchars($contrato['afp_id'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">ARL</label>
            <input type="text" name="arl_id" class="form-control" value="<?= htmlspecialchars($contrato['arl_id'] ?? '') ?>">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
</form>
