<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $empleados
 * @var array $nomina
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$cols = ['borrador'=>['#6b7280','#f3f4f6'],'calculada'=>['#2563eb','#eff6ff'],'aprobada'=>['#16a34a','#f0fdf4'],'pagada'=>['#7c3aed','#f5f3ff']];
[$tc,$bg] = $cols[$nomina['estado']] ?? ['#6b7280','#f3f4f6'];
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-secondary{background:#e0e7ff;color:#1e3a8a;}
.btn-success{background:#166534;color:#fff;}.btn-sm{padding:6px 12px;font-size:13px;}
.alert{padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;}.alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 4px 24px rgba(15,23,42,.07);overflow:hidden;}
.kpis{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-bottom:24px;}
.kpi{background:#fff;border:1px solid #dbe3ef;border-radius:14px;padding:18px;text-align:center;}
.kpi-val{font-size:24px;font-weight:800;color:#1e3a8a;margin-bottom:4px;}.kpi-lbl{font-size:12px;color:#6b7280;}
table{width:100%;border-collapse:collapse;}th,td{padding:12px 15px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle;}
th{background:#eff6ff;color:#172554;font-size:12px;text-transform:uppercase;letter-spacing:.04em;font-weight:700;}tr:last-child td{border-bottom:none;}tr:hover td{background:#f9fafb;}
.empty{padding:48px;text-align:center;color:#6b7280;}
</style>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Encabezado -->
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
            <button class="btn btn-secondary btn-sm" onclick="loadContent('nomina.index')">← Volver</button>
            <h2 style="margin:0;font-size:22px;font-weight:800;color:#172554;">Nómina #<?= $nomina['id'] ?></h2>
            <span style="background:<?= $bg ?>;color:<?= $tc ?>;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:700;"><?= ucfirst($nomina['estado']) ?></span>
        </div>
        <p style="margin:0;color:#6b7280;font-size:13px;">
            <?= htmlspecialchars($nomina['tienda_nombre']) ?> ·
            <?= date('d/m/Y', strtotime($nomina['periodo_inicio'])) ?> al <?= date('d/m/Y', strtotime($nomina['periodo_fin'])) ?> ·
            <?= ucfirst($nomina['tipo']) ?>
        </p>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <?php if ($nomina['estado'] === 'borrador'): ?>
            <form method="post" action="index.php?route=nomina.calcular" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="nomina_id" value="<?= $nomina['id'] ?>">
                <button type="submit" class="btn btn-primary" onclick="return confirm('¿Calcular nómina para todos los empleados activos con contrato?')">
                    🧮 Calcular
                </button>
            </form>
        <?php elseif ($nomina['estado'] === 'calculada'): ?>
            <form method="post" action="index.php?route=nomina.aprobar" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="nomina_id" value="<?= $nomina['id'] ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('¿Aprobar esta nómina?')">✅ Aprobar</button>
            </form>
        <?php elseif ($nomina['estado'] === 'aprobada'): ?>
            <form method="post" action="index.php?route=nomina.pagar" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="nomina_id" value="<?= $nomina['id'] ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('¿Confirmar pago? Esta acción no se puede deshacer.')">💳 Pagar</button>
            </form>
        <?php elseif ($nomina['estado'] === 'pagada'): ?>
            <span style="color:#16a34a;font-weight:700;font-size:14px;padding:9px 0;">
                ✅ Pagada <?= $nomina['pagado_at'] ? 'el ' . date('d/m/Y', strtotime($nomina['pagado_at'])) : '' ?>
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- KPIs -->
<div class="kpis">
    <div class="kpi"><div class="kpi-val"><?= count($empleados) ?></div><div class="kpi-lbl">Empleados</div></div>
    <div class="kpi"><div class="kpi-val">$<?= number_format((float)$nomina['total_devengado'], 0) ?></div><div class="kpi-lbl">Devengado</div></div>
    <div class="kpi"><div class="kpi-val" style="color:#dc2626;">$<?= number_format((float)$nomina['total_deducciones'], 0) ?></div><div class="kpi-lbl">Deducciones</div></div>
    <div class="kpi"><div class="kpi-val" style="color:#16a34a;">$<?= number_format((float)$nomina['total_neto'], 0) ?></div><div class="kpi-lbl">Neto a pagar</div></div>
</div>

<!-- Tabla de empleados -->
<div class="card">
<?php if (empty($empleados)): ?>
    <div class="empty">
        <p style="font-size:15px;margin-bottom:8px;">Nómina sin empleados calculados.</p>
        <?php if ($nomina['estado'] === 'borrador'): ?>
            <p style="font-size:13px;color:#9ca3af;">Usa el botón "Calcular" para procesar automáticamente a todos los empleados activos con contratos vigentes.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Empleado</th><th>Código</th><th>Contrato</th><th>Días</th><th>Devengado</th><th>Deducciones</th><th>Neto</th><th>Estado</th></tr>
        </thead>
        <tbody>
        <?php foreach ($empleados as $e): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($e['empleado_nombre'] . ' ' . $e['empleado_apellido']) ?></td>
                <td style="font-size:12px;color:#6b7280;"><?= htmlspecialchars($e['codigo_empleado']) ?></td>
                <td style="font-size:12px;"><?= ucfirst(str_replace('_', ' ', $e['tipo_contrato'])) ?></td>
                <td><?= (int)$e['dias_trabajados'] ?></td>
                <td>$<?= number_format((float)$e['total_devengado'], 2) ?></td>
                <td style="color:#dc2626;">$<?= number_format((float)$e['total_deducciones'], 2) ?></td>
                <td style="font-weight:700;color:#16a34a;">$<?= number_format((float)$e['neto_pagar'], 2) ?></td>
                <td style="font-size:12px;font-weight:600;color:<?= $e['estado'] === 'pagado' ? '#16a34a' : '#6b7280' ?>;"><?= ucfirst($e['estado']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div style="padding:14px 16px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:24px;font-size:14px;">
        <span style="color:#6b7280;">Deducciones totales: <strong style="color:#dc2626;">$<?= number_format((float)$nomina['total_deducciones'], 2) ?></strong></span>
        <span>Neto total: <strong style="color:#16a34a;font-size:16px;">$<?= number_format((float)$nomina['total_neto'], 2) ?></strong></span>
    </div>
<?php endif; ?>
</div>
