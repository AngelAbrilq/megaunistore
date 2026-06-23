<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $contratos
 * @var string $csrfToken
 */

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { require __DIR__ . '/../layout/dashboard_layout.php'; return; }
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
function e_c(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;text-decoration:none;}
.btn:hover{opacity:.85;}.btn-primary{background:#1e3a8a;color:#fff;}.btn-secondary{background:#e0e7ff;color:#1e3a8a;}.btn-danger{background:#fee2e2;color:#991b1b;}.btn-sm{padding:6px 12px;font-size:13px;}
.alert{padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;}.alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:18px;box-shadow:0 4px 24px rgba(15,23,42,.07);overflow:hidden;}
table{width:100%;border-collapse:collapse;}th,td{padding:12px 15px;text-align:left;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle;}
th{background:#eff6ff;color:#172554;font-size:12px;text-transform:uppercase;letter-spacing:.04em;font-weight:700;}tr:last-child td{border-bottom:none;}tr:hover td{background:#f9fafb;}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap;}
.topbar h2{margin:0 0 4px;color:#172554;font-size:22px;font-weight:800;}.topbar p{margin:0;color:#6b7280;font-size:14px;}
.empty{padding:48px;text-align:center;color:#6b7280;}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;}
.actions{display:flex;gap:6px;flex-wrap:nowrap;}
</style>

<div class="topbar">
    <div><h2>📄 Contratos</h2><p>Gestión de contratos laborales</p></div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-secondary btn-sm" onclick="loadContent('contratos.cargos')">⚙️ Cargos</button>
        <button class="btn btn-primary" onclick="openModal('index.php?route=contratos.create&ajax=1')">+ Nuevo Contrato</button>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e_c($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
<?php if (empty($contratos)): ?>
    <div class="empty">
        <p style="font-size:16px;margin-bottom:8px;">No hay contratos registrados.</p>
        <button class="btn btn-primary" onclick="openModal('index.php?route=contratos.create&ajax=1')">Crear primer contrato</button>
    </div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Empleado</th><th>Tipo</th><th>Vigencia</th><th>Salario</th><th>Jornada</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php
        $ec = ['activo'=>['#16a34a','#f0fdf4'],'terminado'=>['#dc2626','#fef2f2'],'suspendido'=>['#d97706','#fefce8']];
        foreach ($contratos as $c):
            [$tc,$bg] = $ec[$c['estado']] ?? ['#6b7280','#f3f4f6'];
        ?>
        <tr>
            <td style="font-weight:700;">#<?= $c['id'] ?></td>
            <td>
                <div style="font-weight:600;"><?= e_c($c['empleado_nombre'] . ' ' . $c['empleado_apellido']) ?></div>
                <div style="font-size:11px;color:#9ca3af;"><?= e_c($c['codigo_empleado']) ?></div>
            </td>
            <td style="font-size:13px;"><?= ucfirst(str_replace('_',' ',$c['tipo_contrato'])) ?></td>
            <td style="font-size:13px;">
                <?= date('d/m/Y', strtotime($c['fecha_inicio'])) ?>
                <?= $c['fecha_fin'] ? '— ' . date('d/m/Y', strtotime($c['fecha_fin'])) : '<span style="color:#9ca3af">Indefinido</span>' ?>
            </td>
            <td style="font-weight:700;">$<?= number_format((float)$c['salario_base'], 0) ?></td>
            <td style="font-size:13px;"><?= ucfirst($c['jornada'] ?? 'completa') ?></td>
            <td><span class="badge" style="background:<?= $bg ?>;color:<?= $tc ?>;"><?= ucfirst($c['estado']) ?></span></td>
            <td>
                <div class="actions">
                <?php if ($c['estado'] === 'activo'): ?>
                    <button class="btn btn-secondary btn-sm" onclick="openModal('index.php?route=contratos.edit&id=<?= $c['id'] ?>&ajax=1')">Editar</button>
                    <form method="post" action="index.php?route=contratos.terminar" style="margin:0;">
                        <input type="hidden" name="csrf_token" value="<?= e_c($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Terminar este contrato?')">Terminar</button>
                    </form>
                <?php else: ?>
                    <span style="font-size:12px;color:#9ca3af;">—</span>
                <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
