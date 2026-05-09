<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_cupones(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cupones | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 34px 20px;
        }

        h1 {
            margin: 0 0 8px;
            color: #172554;
        }

        p {
            margin: 0 0 24px;
            color: #6b7280;
        }

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            margin-bottom: 18px;
            border: 1px solid;
        }

        .alert.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert.error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            justify-content: center;
            align-items: center;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f8fafc;
            font-weight: 800;
            color: #172554;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.04em;
        }

        tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Cupones de descuento</h1>
        <p>Gestiona cupones promocionales para aplicar descuentos en ventas.</p>

        <?php if ($flash !== null): ?>
            <div class="alert <?= e_cupones($flash['type']) ?>">
                <?= e_cupones($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="index.php?route=cupones.create" class="btn btn-primary">Crear cupón</a>
            <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
        </div>

        <div class="card">
            <?php if (empty($cupones)): ?>
                <p style="text-align: center; color: #6b7280;">No hay cupones registrados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Tienda</th>
                            <th>Usos</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cupones as $cupon): ?>
                            <tr>
                                <td><strong><?= e_cupones($cupon['codigo']) ?></strong></td>
                                <td><?= e_cupones($cupon['descripcion'] ?? 'Sin descripción') ?></td>
                                <td>
                                    <?php if ($cupon['tipo_descuento'] === 'porcentaje'): ?>
                                        <span class="badge badge-info">Porcentaje</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Fijo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($cupon['tipo_descuento'] === 'porcentaje'): ?>
                                        <?= e_cupones($cupon['valor_descuento']) ?>%
                                    <?php else: ?>
                                        $<?= e_cupones(number_format((float) $cupon['valor_descuento'], 2)) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= e_cupones($cupon['tienda_nombre'] ?? 'Todas') ?></td>
                                <td>
                                    <?= e_cupones((string) $cupon['usos_actuales']) ?> / 
                                    <?= $cupon['usos_maximos'] !== null ? e_cupones((string) $cupon['usos_maximos']) : '∞' ?>
                                </td>
                                <td>
                                    <?php if ($cupon['fecha_inicio'] !== null && $cupon['fecha_fin'] !== null): ?>
                                        <?= date('d/m/Y', strtotime($cupon['fecha_inicio'])) ?> - 
                                        <?= date('d/m/Y', strtotime($cupon['fecha_fin'])) ?>
                                    <?php else: ?>
                                        Sin límite
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $cupon['activo'] === 1): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?route=cupones.edit&id=<?= e_cupones((string) $cupon['id']) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Editar</a>
                                    
                                    <form action="index.php?route=cupones.destroy" method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este cupón?');">
                                        <input type="hidden" name="csrf_token" value="<?= e_cupones($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= e_cupones((string) $cupon['id']) ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
