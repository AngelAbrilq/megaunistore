<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-secondary{background:#e0e7ff;color:#1e3a8a;}.btn-sm{padding:6px 12px;font-size:13px;}
.alert{padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;}.alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 4px 24px rgba(15,23,42,.07);overflow:hidden;}
table{width:100%;border-collapse:collapse;}th,td{padding:12px 15px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle;}
th{background:#eff6ff;color:#172554;font-size:12px;text-transform:uppercase;letter-spacing:.04em;font-weight:700;}tr:last-child td{border-bottom:none;}tr:hover td{background:#f9fafb;}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.topbar h2{margin:0 0 4px;color:#172554;font-size:22px;font-weight:800;}.topbar p{margin:0;color:#6b7280;font-size:14px;}
.empty{padding:48px;text-align:center;color:#6b7280;}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;}
</style>

<div class="topbar">
    <div><h2>💼 Nóminas</h2><p>Períodos de pago — procesamiento y liquidaciones</p></div>
    <button class="btn btn-primary" onclick="loadContent('nomina.create')">+ Nueva Nómina</button>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
<?php if (empty($nominas)): ?>
    <div class="empty">
        <p style="font-size:16px;margin-bottom:12px;">No hay períodos de nómina registrados.</p>
        <button class="btn btn-primary" onclick="loadContent('nomina.create')">Crear primer período</button>
    </div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Tienda</th><th>Período</th><th>Tipo</th><th>Empleados</th><th>Total Neto</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php
        $colores = ['borrador'=>['#6b7280','#f3f4f6'],'calculada'=>['#2563eb','#eff6ff'],'aprobada'=>['#16a34a','#f0fdf4'],'pagada'=>['#7c3aed','#f5f3ff']];
        foreach ($nominas as $n):
            [$tc,$bg] = $colores[$n['estado']] ?? ['#6b7280','#f3f4f6'];
        ?>
        <tr>
            <td style="font-weight:700;">#<?= $n['id'] ?></td>
            <td><?= htmlspecialchars($n['tienda_nombre']) ?></td>
            <td style="font-size:13px;">
                <?= date('d/m/Y', strtotime($n['periodo_inicio'])) ?> — <?= date('d/m/Y', strtotime($n['periodo_fin'])) ?>
            </td>
            <td><?= ucfirst($n['tipo']) ?></td>
            <td style="text-align:center;">—</td>
            <td style="font-weight:700;color:#16a34a;">$<?= number_format((float)$n['total_neto'], 2) ?></td>
            <td><span class="badge" style="background:<?= $bg ?>;color:<?= $tc ?>;"><?= ucfirst($n['estado']) ?></span></td>
            <td><button class="btn btn-secondary btn-sm" onclick="loadContent('nomina.show&id=<?= $n['id'] ?>')">Ver detalle</button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
