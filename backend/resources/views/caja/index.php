<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_caja(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_caja(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}
?>

<style>
.caja-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.topbar-caja{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap}
h2.caja-title{margin:0 0 4px;color:#172554;font-size:22px}
p.caja-sub{margin:0;color:#6b7280;font-size:14px}
.top-actions{display:flex;flex-wrap:wrap;gap:10px}
.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;font-family:inherit;transition:opacity .15s;white-space:nowrap}
.btn:hover{opacity:.85}
.btn-primary{background:#1e3a8a;color:#fff}
.btn-secondary{background:#e0e7ff;color:#1e3a8a}
.btn-success{background:#dcfce7;color:#166534}
.btn-warning{background:#fef3c7;color:#92400e}
.btn-sm{padding:7px 12px;font-size:13px;border-radius:10px}
.alert{padding:13px 16px;border-radius:14px;margin-bottom:16px;border:1px solid transparent;font-size:14px}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:14px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:top;font-size:14px}
th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.muted{color:#6b7280;font-size:13px;line-height:1.5}
.money{font-weight:900;color:#172554}
.status{display:inline-flex;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:800}
.status-open{background:#dcfce7;color:#166534}
.status-closed{background:#fee2e2;color:#991b1b}
.status-active{background:#eef2ff;color:#1e3a8a}
.status-inactive{background:#f3f4f6;color:#374151}
.actions-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.empty{padding:34px;text-align:center;color:#6b7280}
@media(max-width:900px){table,thead,tbody,th,td,tr{display:block}thead{display:none}tr{border-bottom:1px solid #e5e7eb;padding:14px}td{border:0;padding:7px 0}td::before{content:attr(data-label);display:block;font-weight:800;color:#172554;margin-bottom:3px}}
</style>

<div class="caja-wrap">
    <div class="topbar-caja">
        <div>
            <h2 class="caja-title">Caja</h2>
            <p class="caja-sub">Administra cajas por tienda, apertura, cierre y movimientos manuales.</p>
        </div>
        <div class="top-actions">
            <button class="btn btn-primary" onclick="openModal('index.php?route=caja.create&ajax=1')">+ Nueva caja</button>
            <button class="btn btn-secondary" onclick="loadContent('caja.movimientos', true)">Ver movimientos</button>
        </div>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_caja($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_caja($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <?php if (empty($cajas)): ?>
            <div class="empty">No hay cajas registradas todavía.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Caja</th>
                        <th>Tienda</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Saldo actual</th>
                        <th>Último movimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cajas as $caja): ?>
                        <tr>
                            <td data-label="ID">#<?= e_caja((string)$caja['id']) ?></td>
                            <td data-label="Caja">
                                <strong><?= e_caja($caja['nombre']) ?></strong><br>
                                <span class="muted"><?= e_caja($caja['descripcion'] ?? 'Sin descripción') ?></span>
                            </td>
                            <td data-label="Tienda"><?= e_caja($caja['tienda_nombre']) ?></td>
                            <td data-label="Estado">
                                <?php if ((int)$caja['estado'] === 1): ?>
                                    <span class="status status-active">Activa</span>
                                <?php else: ?>
                                    <span class="status status-inactive">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Apertura">
                                <?php if ((bool)$caja['abierta']): ?>
                                    <span class="status status-open">Abierta</span>
                                <?php else: ?>
                                    <span class="status status-closed">Cerrada</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Saldo">
                                <span class="money">$<?= e_caja(dinero_caja($caja['saldo_actual'])) ?></span>
                            </td>
                            <td data-label="Último movimiento">
                                <?php if (!empty($caja['ultimo_movimiento'])): ?>
                                    <?= e_caja(ucfirst($caja['ultimo_movimiento']['tipo'])) ?><br>
                                    <span class="muted"><?= e_caja($caja['ultimo_movimiento']['created_at']) ?></span>
                                <?php else: ?>
                                    <span class="muted">Sin movimientos</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Acciones">
                                <div class="actions-row">
                                    <?php if ((bool)$caja['abierta']): ?>
                                        <button class="btn btn-warning btn-sm"
                                            onclick="openModal('index.php?route=caja.cierre&id=<?= (int)$caja['id'] ?>&ajax=1')">
                                            Cerrar
                                        </button>
                                        <button class="btn btn-secondary btn-sm"
                                            onclick="openModal('index.php?route=caja.movimiento&id=<?= (int)$caja['id'] ?>&ajax=1')">
                                            Movimiento
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-sm"
                                            onclick="openModal('index.php?route=caja.apertura&id=<?= (int)$caja['id'] ?>&ajax=1')">
                                            Abrir
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
