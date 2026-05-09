<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function e_prov(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f3f6fb;color:#111827}
        .container{max-width:1280px;margin:0 auto;padding:34px 20px}
        .topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}
        h1{margin:0 0 6px;color:#172554} p{margin:0;color:#6b7280}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:11px 14px;font-weight:700;text-decoration:none;cursor:pointer;font-size:14px;white-space:nowrap}
        .btn-primary{background:#1e3a8a;color:#fff}
        .btn-secondary{background:#e0e7ff;color:#1e3a8a}
        .btn-warning{background:#fef3c7;color:#92400e}
        .btn-danger{background:#fee2e2;color:#991b1b}
        .alert{padding:13px 14px;border-radius:14px;margin-bottom:18px;border:1px solid transparent}
        .alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
        .card{background:#fff;border:1px solid #dbe3ef;border-radius:22px;box-shadow:0 18px 48px rgba(15,23,42,.10);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th,td{padding:15px;text-align:left;border-bottom:1px solid #e5e7eb;vertical-align:top;font-size:14px}
        th{background:#eff6ff;color:#172554;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
        .muted{color:#6b7280;font-size:13px}
        .status{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:800}
        .status-active{background:#dcfce7;color:#166534}
        .status-inactive{background:#fee2e2;color:#991b1b}
        .actions{display:flex;flex-wrap:nowrap;gap:8px}
        form{margin:0}
        .empty{padding:34px;text-align:center;color:#6b7280}
        .back{margin-top:20px}
        @media(max-width:900px){
            .topbar{flex-direction:column;align-items:flex-start}
            table,thead,tbody,th,td,tr{display:block}
            thead{display:none}
            tr{border-bottom:1px solid #e5e7eb;padding:14px}
            td{border:0;padding:7px 0}
            td::before{content:attr(data-label);display:block;font-weight:800;color:#172554;margin-bottom:3px}
        }
    </style>
</head>
<body>
<main class="container">
    <div class="topbar">
        <div>
            <h1>Proveedores</h1>
            <p>Gestiona los proveedores que abastecen las tiendas.</p>
        </div>
        <a class="btn btn-primary" href="index.php?route=proveedores.create">Nuevo proveedor</a>
    </div>

    <?php if ($flash !== null): ?>
        <div class="alert alert-<?= e_prov($flash['type'] === 'success' ? 'success' : 'error') ?>">
            <?= e_prov($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <?php if (empty($proveedores)): ?>
            <div class="empty">No hay proveedores registrados todavia.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Proveedor</th>
                        <th>NIT / RUC</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $prov): ?>
                        <tr>
                            <td data-label="ID"><?= e_prov((string) $prov['id']) ?></td>
                            <td data-label="Proveedor">
                                <strong><?= e_prov($prov['nombre']) ?></strong><br>
                                <?php if (!empty($prov['contacto_nombre'])): ?>
                                    <span class="muted">Contacto: <?= e_prov($prov['contacto_nombre']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="NIT/RUC"><?= e_prov($prov['ruc_nit']) ?></td>
                            <td data-label="Contacto">
                                <?= e_prov($prov['email'] ?? 'Sin email') ?><br>
                                <span class="muted"><?= e_prov($prov['telefono'] ?? 'Sin telefono') ?></span>
                            </td>
                            <td data-label="Estado">
                                <span class="status <?= (int)$prov['estado'] === 1 ? 'status-active' : 'status-inactive' ?>">
                                    <?= (int)$prov['estado'] === 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td data-label="Acciones">
                                <div class="actions">
                                    <a class="btn btn-secondary" href="index.php?route=proveedores.edit&id=<?= e_prov((string) $prov['id']) ?>">Editar</a>

                                    <form action="index.php?route=proveedores.toggle" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= e_prov($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= e_prov((string) $prov['id']) ?>">
                                        <input type="hidden" name="estado_actual" value="<?= e_prov((string) $prov['estado']) ?>">
                                        <button type="submit" class="btn btn-warning">
                                            <?= (int)$prov['estado'] === 1 ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>

                                    <form action="index.php?route=proveedores.destroy" method="POST"
                                          onsubmit="return confirm('Eliminar este proveedor?');">
                                        <input type="hidden" name="csrf_token" value="<?= e_prov($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= e_prov((string) $prov['id']) ?>">
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <div class="back">
        <a class="btn btn-secondary" href="index.php?route=dashboard">Volver al dashboard</a>
    </div>
</main>
</body>
</html>
