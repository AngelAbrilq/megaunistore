<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_dev(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.btn{display:inline-flex;align-items:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.btn-sm{padding:6px 11px;font-size:13px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;padding:26px;box-shadow:0 18px 48px rgba(15,23,42,.10)}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:12px;border-bottom:1px solid #e5e7eb;font-size:14px}
th{background:#f8fafc;font-weight:800;color:#172554;text-transform:uppercase;font-size:12px;letter-spacing:.04em}
tr:hover td{background:#f8fafc}
.badge{display:inline-block;padding:4px 10px;border-radius:8px;font-size:12px;font-weight:700}
.badge-success{background:#d1fae5;color:#065f46}
.badge-warning{background:#fef3c7;color:#92400e}
.badge-danger{background:#fee2e2;color:#991b1b}
.spa-link{color:#2563eb;background:none;border:none;padding:0;font:inherit;cursor:pointer;text-decoration:underline}
.spa-link:hover{color:#1d4ed8}
.empty{padding:34px;text-align:center;color:#6b7280}
</style>

<div style="max-width:1280px;margin:0 auto;padding:24px 20px">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div>
            <h2 style="margin:0 0 4px;color:#172554;font-size:22px">Devoluciones</h2>
            <p style="margin:0;color:#6b7280;font-size:14px">Gestiona las devoluciones de productos de ventas realizadas.</p>
        </div>
        <button class="btn btn-primary" onclick="loadContent('ventas.index', true)">Ver ventas</button>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_dev($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_dev($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($devoluciones)): ?>
            <div class="empty">No hay devoluciones registradas.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Venta</th>
                        <th>Tienda</th>
                        <th>Monto Devuelto</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devoluciones as $devolucion): ?>
                        <tr>
                            <td><strong>#<?= e_dev((string) $devolucion['id']) ?></strong></td>
                            <td>
                                <button class="spa-link"
                                    onclick="loadContent('ventas.show&id=<?= (int)$devolucion['venta_id'] ?>', true)">
                                    Venta #<?= e_dev((string) $devolucion['venta_id']) ?>
                                </button>
                            </td>
                            <td><?= e_dev($devolucion['tienda_nombre']) ?></td>
                            <td><strong>$<?= number_format((float) $devolucion['monto_devuelto'], 2) ?></strong></td>
                            <td><?= e_dev(substr($devolucion['motivo'], 0, 50)) ?><?= strlen($devolucion['motivo']) > 50 ? '...' : '' ?></td>
                            <td>
                                <?php if ($devolucion['estado'] === 'completada'): ?>
                                    <span class="badge badge-success">Completada</span>
                                <?php elseif ($devolucion['estado'] === 'pendiente'): ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rechazada</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($devolucion['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-secondary btn-sm"
                                    onclick="loadContent('devoluciones.show&id=<?= (int)$devolucion['id'] ?>', true)">
                                    Ver detalle
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>
