<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-secondary{background:#e0e7ff;color:#1e3a8a;}.btn-sm{padding:6px 12px;font-size:13px;}
.alert{padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;}.alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 4px 24px rgba(15,23,42,.07);overflow:hidden;}
table{width:100%;border-collapse:collapse;}th,td{padding:12px 15px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle;}
th{background:#eff6ff;color:#172554;font-size:12px;text-transform:uppercase;letter-spacing:.04em;font-weight:700;}tr:last-child td{border-bottom:none;}tr:hover td{background:#f9fafb;}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.topbar h2{margin:0 0 4px;color:#172554;font-size:22px;font-weight:800;}.topbar p{margin:0;color:#6b7280;font-size:14px;}
.empty{padding:48px;text-align:center;color:#6b7280;}
</style>

<div class="topbar">
    <div>
        <div style="margin-bottom:10px;"><button class="btn btn-secondary btn-sm" onclick="loadContent('contratos.index')">← Contratos</button></div>
        <h2>⚙️ Cargos</h2><p>Posiciones laborales disponibles en la tienda</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('index.php?route=contratos.cargo.create&ajax=1')">+ Nuevo Cargo</button>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
<?php if (empty($cargos)): ?>
    <div class="empty">
        <p style="font-size:16px;margin-bottom:8px;">No hay cargos definidos todavía.</p>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:16px;">Los cargos son obligatorios para crear contratos laborales.</p>
        <button class="btn btn-primary" onclick="openModal('index.php?route=contratos.cargo.create&ajax=1')">Crear primer cargo</button>
    </div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Descripción</th><th>Nivel Jerárquico</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($cargos as $cargo): ?>
            <tr>
                <td style="font-weight:700;"><?= $cargo['id'] ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($cargo['nombre']) ?></td>
                <td style="font-size:13px;color:#6b7280;"><?= htmlspecialchars($cargo['descripcion'] ?? '—') ?></td>
                <td><?= $cargo['nivel_jerarquico'] ?></td>
                <td style="font-size:12px;font-weight:700;color:<?= $cargo['activo'] ? '#16a34a' : '#dc2626' ?>;">
                    <?= $cargo['activo'] ? 'Activo' : 'Inactivo' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
