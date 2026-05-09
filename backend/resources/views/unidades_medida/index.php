<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_unidad(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Unidades de medida | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 34px 20px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0 0 6px;
            color: #172554;
        }

        p {
            margin: 0;
            color: #6b7280;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 12px;
            padding: 11px 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
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

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            background: #eff6ff;
            color: #172554;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .pill {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 800;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        form {
            margin: 0;
        }

        .empty {
            padding: 34px;
            text-align: center;
            color: #6b7280;
        }

        .back {
            margin-top: 20px;
        }

        @media (max-width: 760px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                border-bottom: 1px solid #e5e7eb;
                padding: 14px;
            }

            td {
                border: 0;
                padding: 7px 0;
            }

            td::before {
                content: attr(data-label);
                display: block;
                font-weight: 800;
                color: #172554;
                margin-bottom: 3px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="topbar">
            <div>
                <h1>Unidades de medida</h1>
                <p>Administra las unidades utilizadas para productos, inventario y ventas.</p>
            </div>

            <a class="btn btn-primary" href="index.php?route=unidades.create">Nueva unidad</a>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_unidad($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_unidad($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <?php if (empty($unidades)): ?>
                <div class="empty">
                    No hay unidades de medida registradas todavía.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Símbolo</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($unidades as $unidad): ?>
                            <tr>
                                <td data-label="ID"><?= e_unidad((string) $unidad['id']) ?></td>

                                <td data-label="Nombre">
                                    <strong><?= e_unidad($unidad['nombre']) ?></strong>
                                </td>

                                <td data-label="Símbolo">
                                    <span class="pill"><?= e_unidad($unidad['simbolo']) ?></span>
                                </td>

                                <td data-label="Tipo">
                                    <?= e_unidad($unidad['tipo'] ?? 'Sin tipo') ?>
                                </td>

                                <td data-label="Acciones">
                                    <div class="actions">
                                        <a class="btn btn-secondary" href="index.php?route=unidades.edit&id=<?= e_unidad((string) $unidad['id']) ?>">
                                            Editar
                                        </a>

                                        <form action="index.php?route=unidades.destroy" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta unidad de medida?');">
                                            <input type="hidden" name="csrf_token" value="<?= e_unidad($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= e_unidad((string) $unidad['id']) ?>">

                                            <button type="submit" class="btn btn-danger">
                                                Eliminar
                                            </button>
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