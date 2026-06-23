<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Métodos de Pago — <?= date('d/m/Y') ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#fff;color:#1f2937;padding:32px}
header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;padding-bottom:16px;border-bottom:2px solid #1e3a8a}
header h1{color:#1e3a8a;font-size:22px}
header p{color:#6b7280;font-size:13px;margin-top:4px}
.meta{text-align:right;font-size:12px;color:#6b7280}
table{width:100%;border-collapse:collapse;margin-bottom:24px;font-size:13px}
th{background:#1e3a8a;color:#fff;padding:10px 12px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em}
td{padding:10px 12px;border-bottom:1px solid #e5e7eb}
tr:nth-child(even) td{background:#f8fafc}
.badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
.badge-on{background:#dcfce7;color:#166534}
.badge-off{background:#fee2e2;color:#991b1b}
.totals{display:flex;gap:24px;margin-bottom:20px}
.stat{background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 20px;flex:1;text-align:center}
.stat small{display:block;color:#6b7280;font-size:11px;text-transform:uppercase;font-weight:700;margin-bottom:4px}
.stat strong{color:#1e3a8a;font-size:20px}
footer{font-size:11px;color:#9ca3af;text-align:center;padding-top:16px;border-top:1px solid #e5e7eb;margin-top:8px}
@media print{
    .no-print{display:none!important}
    body{padding:16px}
}
</style>
</head>
<body>

<div class="no-print" style="position:fixed;top:16px;right:16px;display:flex;gap:10px">
    <button onclick="window.print()"
            style="background:#1e3a8a;color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:700;cursor:pointer;font-size:14px">
        🖨️ Imprimir / Guardar PDF
    </button>
    <button onclick="window.close()"
            style="background:#e0e7ff;color:#1e3a8a;border:0;border-radius:10px;padding:10px 20px;font-weight:700;cursor:pointer;font-size:14px">
        ✕ Cerrar
    </button>
</div>

<header>
    <div>
        <h1>💳 Métodos de Pago</h1>
        <p>Reporte de métodos de pago registrados en el sistema</p>
    </div>
    <div class="meta">
        <strong>Mega Uni Store</strong><br>
        Generado: <?= date('d/m/Y H:i') ?><br>
        Total registros: <?= count($datos) ?>
    </div>
</header>

<div class="totals">
    <div class="stat">
        <small>Total métodos</small>
        <strong><?= count($datos) ?></strong>
    </div>
    <div class="stat">
        <small>Activos</small>
        <strong><?= count(array_filter($datos, fn($r) => $r['estado'] === 'Activo')) ?></strong>
    </div>
    <div class="stat">
        <small>Total pagos registrados</small>
        <strong><?= number_format($totalPagos) ?></strong>
    </div>
    <div class="stat">
        <small>Monto total procesado</small>
        <strong>$<?= number_format($totalMonto, 2) ?></strong>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th style="text-align:right">Pagos</th>
            <th style="text-align:right">Monto Total</th>
        </tr>
    </thead>
    <tbody>
        <?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $datos
 * @var float $totalMonto
 * @var int $totalPagos
 */

foreach ($datos as $r): ?>
            <tr>
                <td style="color:#9ca3af"><?= (int)$r['id'] ?></td>
                <td><strong><?= htmlspecialchars($r['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td style="color:#6b7280"><?= htmlspecialchars($r['descripcion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge <?= $r['estado'] === 'Activo' ? 'badge-on' : 'badge-off' ?>"><?= $r['estado'] ?></span></td>
                <td style="text-align:right"><?= number_format((int)$r['total_pagos']) ?></td>
                <td style="text-align:right;font-weight:700">$<?= number_format((float)$r['total_monto'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background:#1e3a8a;color:#fff">
            <td colspan="4" style="padding:10px 12px;font-weight:800;color:#fff">TOTAL</td>
            <td style="text-align:right;padding:10px 12px;font-weight:800;color:#fff"><?= number_format($totalPagos) ?></td>
            <td style="text-align:right;padding:10px 12px;font-weight:800;color:#fff">$<?= number_format($totalMonto, 2) ?></td>
        </tr>
    </tfoot>
</table>

<footer>
    Mega Uni Store · Reporte generado automáticamente el <?= date('d/m/Y \a \l\a\s H:i:s') ?>
</footer>

</body>
</html>
