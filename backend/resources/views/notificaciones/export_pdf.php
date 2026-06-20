<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Notificaciones — <?= date('d/m/Y') ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#fff;color:#1f2937;padding:32px}
header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;padding-bottom:16px;border-bottom:2px solid #1e3a8a}
header h1{color:#1e3a8a;font-size:22px}
.meta{text-align:right;font-size:12px;color:#6b7280}
.totals{display:flex;gap:16px;margin-bottom:20px}
.stat{background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 20px;flex:1;text-align:center}
.stat small{display:block;color:#6b7280;font-size:11px;text-transform:uppercase;font-weight:700;margin-bottom:4px}
.stat strong{color:#1e3a8a;font-size:20px}
table{width:100%;border-collapse:collapse;font-size:12px}
th{background:#1e3a8a;color:#fff;padding:9px 10px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.04em}
td{padding:9px 10px;border-bottom:1px solid #e5e7eb;vertical-align:top}
tr:nth-child(even) td{background:#f8fafc}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700}
.badge-warning{background:#fef3c7;color:#92400e}
.badge-error{background:#fee2e2;color:#991b1b}
.badge-info{background:#dbeafe;color:#1e40af}
.badge-success{background:#dcfce7;color:#166534}
.unread{background:#fffbeb}
footer{font-size:11px;color:#9ca3af;text-align:center;margin-top:16px;padding-top:12px;border-top:1px solid #e5e7eb}
@media print{.no-print{display:none!important} body{padding:16px}}
</style>
</head>
<body>

<div class="no-print" style="position:fixed;top:16px;right:16px;display:flex;gap:10px">
    <button onclick="window.print()"
            style="background:#1e3a8a;color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:700;cursor:pointer">
        🖨️ Imprimir / PDF
    </button>
    <button onclick="window.close()"
            style="background:#e0e7ff;color:#1e3a8a;border:0;border-radius:10px;padding:10px 20px;font-weight:700;cursor:pointer">
        ✕ Cerrar
    </button>
</div>

<header>
    <div>
        <h1>🔔 Notificaciones del Sistema</h1>
        <p style="color:#6b7280;font-size:13px;margin-top:4px">Historial de alertas y avisos internos</p>
    </div>
    <div class="meta">
        <strong>Mega Uni Store</strong><br>
        Generado: <?= date('d/m/Y H:i') ?><br>
        Total: <?= count($datos) ?> registros
    </div>
</header>

<?php
$leidas   = count(array_filter($datos, fn($r) => $r['estado'] === 'Leída'));
$noLeidas = count($datos) - $leidas;
$porTipo  = array_count_values(array_column($datos, 'tipo'));
?>

<div class="totals">
    <div class="stat"><small>Total</small><strong><?= count($datos) ?></strong></div>
    <div class="stat"><small>No leídas</small><strong style="color:#f59e0b"><?= $noLeidas ?></strong></div>
    <div class="stat"><small>Leídas</small><strong><?= $leidas ?></strong></div>
    <?php foreach ($porTipo as $tp => $cnt): ?>
        <div class="stat"><small><?= htmlspecialchars($tp, ENT_QUOTES, 'UTF-8') ?></small><strong><?= $cnt ?></strong></div>
    <?php endforeach; ?>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Título</th>
            <th>Mensaje</th>
            <th>Estado</th>
            <th>Usuario</th>
            <th>Tienda</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($datos as $r): ?>
            <tr class="<?= $r['estado'] === 'No leída' ? 'unread' : '' ?>">
                <td style="color:#9ca3af"><?= (int)$r['id'] ?></td>
                <td style="white-space:nowrap"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="badge badge-<?= htmlspecialchars($r['tipo'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($r['tipo'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td><strong><?= htmlspecialchars($r['titulo'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td style="color:#6b7280;max-width:220px"><?= htmlspecialchars(mb_strimwidth($r['mensaje'], 0, 80, '…'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:11px"><?= htmlspecialchars($r['usuario'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:11px;color:#6b7280"><?= htmlspecialchars($r['tienda'] ?? 'Global', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<footer>
    Mega Uni Store · Reporte generado el <?= date('d/m/Y \a \l\a\s H:i:s') ?>
</footer>
</body>
</html>
