<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_caja_mov(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_mov(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimiento de caja | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .container {
            max-width: 820px;
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

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            margin-bottom: 20px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .summary-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px;
        }

        .summary-item small {
            display: block;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.04em;
        }

        .summary-item strong {
            color: #172554;
            font-size: 18px;
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

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #1f2937;
            font-size: 14px;
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        textarea {
            min-height: 105px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        @media (max-width: 720px) {
            .summary {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Movimiento de caja</h1>
        <p>Registra ingresos o egresos manuales de caja.</p>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e_caja_mov($flash['type'] === 'success' ? 'success' : 'error') ?>">
                <?= e_caja_mov($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <div class="summary">
                <div class="summary-item">
                    <small>Caja</small>
                    <strong><?= e_caja_mov($caja['nombre']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Tienda</small>
                    <strong><?= e_caja_mov($caja['tienda_nombre']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Saldo actual</small>
                    <strong>$<?= e_caja_mov(dinero_mov($caja['saldo_actual'])) ?></strong>
                </div>
            </div>
        </section>

        <section class="card">
            <form action="index.php?route=caja.guardar_movimiento" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_caja_mov($csrfToken) ?>">
                <input type="hidden" name="caja_id" value="<?= e_caja_mov((string) $caja['id']) ?>">

                <div class="form-group">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="egreso">Egreso</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="monto">Monto *</label>
                    <input type="number" id="monto" name="monto" required min="0.01" step="0.01">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Ej: Compra de bolsas, ingreso adicional, retiro autorizado."></textarea>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Guardar movimiento</button>
                    <a href="index.php?route=caja.index" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>