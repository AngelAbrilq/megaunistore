<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $tiendas
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-secondary{background:#e0e7ff;color:#1e3a8a;}.btn-sm{padding:6px 12px;font-size:13px;}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 4px 24px rgba(15,23,42,.07);padding:28px;max-width:580px;}
.form-group{margin-bottom:16px;}.form-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:5px;}
.form-control{width:100%;padding:9px 13px;border:1.5px solid #d1d5db;border-radius:10px;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#2563eb;}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <button class="btn btn-secondary btn-sm" onclick="loadContent('nomina.index')">← Volver</button>
    <h2 style="margin:0;font-size:22px;font-weight:800;color:#172554;">+ Nuevo Período de Nómina</h2>
</div>

<div class="card">
    <form method="post" action="index.php?route=nomina.store">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="form-group">
            <label class="form-label">Tienda *</label>
            <select name="tienda_id" class="form-control" required>
                <option value="">Seleccionar tienda...</option>
                <?php foreach ($tiendas as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Fecha inicio *</label>
                <input type="date" name="periodo_inicio" class="form-control" required value="<?= date('Y-m-01') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Fecha fin *</label>
                <input type="date" name="periodo_fin" class="form-control" required value="<?= date('Y-m-t') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Tipo de período</label>
            <select name="tipo" class="form-control">
                <option value="mensual">Mensual</option>
                <option value="quincenal">Quincenal</option>
                <option value="bisemanal">Bisemanal</option>
            </select>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
            <button type="submit" class="btn btn-primary">Crear período</button>
            <button type="button" class="btn btn-secondary" onclick="loadContent('nomina.index')">Cancelar</button>
        </div>
    </form>
</div>
