<?php

$titulo = $errorTitulo ?? 'Acceso denegado';
$mensaje = $errorMensaje ?? 'No tienes permisos suficientes para acceder a este recurso.';

function e_403(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>403 | Mega_Uni_Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
        }

        .card {
            width: min(92%, 540px);
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 24px;
            padding: 34px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.12);
            text-align: center;
        }

        .code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 26px;
            font-weight: 900;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0 0 10px;
            color: #172554;
        }

        p {
            margin: 0 0 24px;
            line-height: 1.6;
            color: #6b7280;
        }

        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <section class="card">
        <div class="code">403</div>

        <h1><?= e_403($titulo) ?></h1>

        <p><?= e_403($mensaje) ?></p>

        <div class="actions">
            <a class="btn btn-primary" href="index.php?route=dashboard">Volver al dashboard</a>
            <a class="btn btn-secondary" href="index.php?route=logout">Cerrar sesión</a>
        </div>
    </section>
</body>
</html>




