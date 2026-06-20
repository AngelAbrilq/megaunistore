<?php
// Soporta modal (ajax=1) y SPA page
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-secondary{background:#e0e7ff;color:#1e3a8a;}.btn-sm{padding:6px 12px;font-size:13px;}
.form-group{margin-bottom:14px;}.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:4px;}
.form-control{width:100%;padding:9px 13px;border:1.5px solid #d1d5db;border-radius:10px;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#2563eb;}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
.warn{background:#fef9c3;border:1px solid #fde047;border-radius:10px;padding:12px;font-size:13px;color:#713f12;margin-bottom:14px;}
</style>

<h3 style="font-size:18px;font-weight:800;color:#172554;margin:0 0 20px;">📄 Nuevo Contrato</h3>

<?php if (empty($cargos)): ?>
<div class="warn">
    ⚠️ No hay cargos definidos para esta tienda.
    <button type="button" class="btn btn-secondary btn-sm" style="margin-left:10px;" onclick="loadContent('contratos.cargos')">Crear un cargo primero →</button>
</div>
<?php endif; ?>

<form method="post" action="index.php?route=contratos.store">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="form-group">
        <label class="form-label">Empleado *</label>
        <select name="empleado_id" class="form-control" required>
            <option value="">Seleccionar empleado...</option>
            <?php foreach ($empleados as $emp): ?>
                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['usuario_nombre'] . ' ' . $emp['usuario_apellido']) ?> (<?= htmlspecialchars($emp['codigo_empleado']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Tipo de contrato *</label>
            <select name="tipo_contrato" class="form-control" required>
                <option value="indefinido">Indefinido</option>
                <option value="fijo">Término fijo</option>
                <option value="obra_labor">Obra o labor</option>
                <option value="aprendizaje">Aprendizaje</option>
                <option value="prestacion">Prestación de servicios</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Jornada</label>
            <select name="jornada" class="form-control">
                <option value="completa">Completa</option>
                <option value="media">Media jornada</option>
                <option value="flexible">Flexible</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Fecha inicio *</label>
            <input type="date" name="fecha_inicio" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Fecha fin <small style="color:#9ca3af;">(vacío = indefinido)</small></label>
            <input type="date" name="fecha_fin" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Salario base *</label>
            <input type="number" name="salario_base" class="form-control" required min="1" step="0.01" placeholder="Ej: 1300000">
        </div>
        <div class="form-group">
            <label class="form-label">Cargo *</label>
            <select name="cargo_id" class="form-control" required <?= empty($cargos) ? 'disabled' : '' ?>>
                <option value="">Seleccionar cargo...</option>
                <?php foreach ($cargos as $cargo): ?>
                    <option value="<?= $cargo['id'] ?>"><?= htmlspecialchars($cargo['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row3">
        <div class="form-group">
            <label class="form-label">EPS</label>
            <input type="text" name="eps_id" class="form-control" placeholder="Ej: Sanitas">
        </div>
        <div class="form-group">
            <label class="form-label">AFP (Pensión)</label>
            <input type="text" name="afp_id" class="form-control" placeholder="Ej: Porvenir">
        </div>
        <div class="form-group">
            <label class="form-label">ARL</label>
            <input type="text" name="arl_id" class="form-control" placeholder="Ej: Positiva">
        </div>
    </div>

    <button type="submit" class="btn btn-primary" <?= empty($cargos) ? 'disabled style="opacity:.5"' : '' ?>>Guardar contrato</button>
</form>
