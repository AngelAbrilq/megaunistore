<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($meta['emoji'] . ' ' . $meta['label'], ENT_QUOTES, 'UTF-8') ?> — <?= date('d/m/Y') ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#fff;color:#1f2937;padding:32px}
header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #1e3a8a}
header h1{color:#1e3a8a;font-size:20px}
.meta{text-align:right;font-size:12px;color:#6b7280}
.summary{display:flex;gap:16px;margin-bottom:20px}
.stat{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 18px;flex:1;text-align:center}
.stat small{display:block;color:#6b7280;font-size:10px;text-transform:uppercase;font-weight:700;margin-bottom:4px}
.stat strong{color:#1e3a8a;font-size:18px}
table{width:100%;border-collapse:collapse;font-size:11px}
th{background:#1e3a8a;color:#fff;padding:8px 10px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.04em}
td{padding:8px 10px;border-bottom:1px solid #e5e7eb;word-break:break-word;max-width:180px}
tr:nth-child(even) td{background:#f8fafc}
footer{font-size:11px;color:#9ca3af;text-align:center;margin-top:16px;padding-top:10px;border-top:1px solid #e5e7eb}
@media print{.no-print{display:none!important} body{padding:12px}}
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
        <h1><?= htmlspecialchars($meta['emoji'] . ' Respaldo: ' . $meta['label'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p style="color:#6b7280;font-size:13px;margin-top:4px">Exportación de datos del sistema — máx. 2 000 registros</p>
    </div>
    <div class="meta">
        <strong>Mega Uni Store</strong><br>
        Generado: <?= date('d/m/Y H:i') ?><br>
        Registros: <?= count($datos) ?>
    </div>
</header>

<div class="summary">
    <div class="stat"><small>Total registros</small><strong><?= count($datos) ?></strong></div>
    <div class="stat"><small>Columnas</small><strong><?= count($columnas) ?></strong></div>
    <div class="stat"><small>Fecha generación</small><strong style="font-size:13px"><?= date('d/m/Y') ?></strong></div>
</div>

<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $columnas
 * @var array $datos
 * @var array $meta
 */

if (!empty($datos)): ?>
<table>
    <thead>
        <tr>
            <?php foreach ($columnas as $col): ?>
                <th><?= htmlspecialchars(str_replace('_', ' ', $col), ENT_QUOTES, 'UTF-8') ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($datos as $row): ?>
            <tr>
                <?php foreach ($columnas as $col): ?>
                    <td><?= htmlspecialchars((string)($row[$col] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p style="text-align:center;color:#9ca3af;padding:32px">No hay datos para exportar.</p>
<?php endif; ?>

<footer>
    Mega Uni Store · Exportación <?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?> — <?= date('d/m/Y \a \l\a\s H:i:s') ?>
</footer>
</body>
</html>
