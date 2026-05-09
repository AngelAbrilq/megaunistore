<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_caja_cierre(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dinero_cierre(float|string|null $valor): string
{
    return number_format((float) ($valor ?? 0), 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cerrar caja | Mega_Uni_Store</title>
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
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
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
        textarea {
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
        textarea:focus {
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
        <h1>Cerrar caja</h1>
        <p>Registra el dinero real contado y calcula la diferencia frente al sistema.</p>

        <?php if ($flash !== null): ?>
            <div class="alert">
                <?= e_caja_cierre($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <div class="summary">
                <div class="summary-item">
                    <small>Caja</small>
                    <strong><?= e_caja_cierre($caja['nombre']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Tienda</small>
                    <strong><?= e_caja_cierre($caja['tienda_nombre']) ?></strong>
                </div>

                <div class="summary-item">
                    <small>Saldo sistema</small>
                    <strong>$<?= e_caja_cierre(dinero_cierre($caja['saldo_actual'])) ?></strong>
                </div>
            </div>
        </section>

        <section class="card">
            <form action="index.php?route=caja.cerrar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e_caja_cierre($csrfToken) ?>">
                <input type="hidden" name="caja_id" value="<?= e_caja_cierre((string) $caja['id']) ?>">

                <div class="form-group">
                    <label for="monto_real">Monto real contado *</label>
                    <input
                        type="number"
                        id="monto_real"
                        name="monto_real"
                        required
                        min="0"
                        step="0.01"
                        value="<?= e_caja_cierre((string) $caja['saldo_actual']) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Ej: Cierre de turno sin novedades."></textarea>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Cerrar caja</button>
                    <a href="index.php?route=caja.index" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>