<?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | Mega_Uni_Store</title>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #172554;
            --primary-light: #2563eb;
            --accent: #38bdf8;
            --bg: #f3f6fb;
            --text: #111827;
            --muted: #6b7280;
            --danger: #dc2626;
            --success: #16a34a;
            --white: #ffffff;
            --border: #dbe3ef;
            --shadow: 0 20px 60px rgba(15, 23, 42, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.22), transparent 30%),
                linear-gradient(135deg, #eff6ff 0%, #f8fafc 48%, #e0f2fe 100%);
            color: var(--text);
        }

        .auth-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 0.92fr 1.08fr;
        }

        .auth-brand {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px;
            background: linear-gradient(135deg, rgba(23, 37, 84, 0.97), rgba(30, 64, 175, 0.93));
            color: var(--white);
        }

        .brand-badge {
            width: fit-content;
            padding: 8px 14px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            font-size: 13px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .auth-brand h1 {
            font-size: clamp(32px, 4vw, 52px);
            line-height: 1.08;
            margin: 0 0 20px;
        }

        .auth-brand p {
            max-width: 620px;
            font-size: 17px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
        }

        .brand-note {
            margin-top: 32px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.11);
            border: 1px solid rgba(255, 255, 255, 0.17);
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.86);
        }

        .auth-form-section {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 560px;
            background: var(--white);
            border-radius: 26px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(219, 227, 239, 0.9);
            padding: 36px;
        }

        .auth-card-header {
            margin-bottom: 24px;
        }

        .auth-card-header small {
            display: inline-block;
            color: var(--primary-light);
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .auth-card-header h2 {
            margin: 0 0 10px;
            font-size: 28px;
            color: var(--primary-dark);
        }

        .auth-card-header p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            font-size: 14px;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 14px;
            color: #1f2937;
        }

        .form-control {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
            background: #fbfdff;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            background: var(--white);
        }

        .help-text {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4;
        }

        .terms {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: 13px;
            line-height: 1.5;
            color: var(--muted);
            margin: 2px 0 20px;
        }

        .terms input {
            margin-top: 3px;
            flex: 0 0 auto;
        }

        .terms a,
        .auth-link a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 700;
        }

        .terms a:hover,
        .auth-link a:hover {
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            padding: 14px 18px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.16s ease, box-shadow 0.16s ease;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(37, 99, 235, 0.3);
        }

        .auth-link {
            margin-top: 22px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
        }

        .back-home {
            display: inline-flex;
            margin-top: 20px;
            color: var(--muted);
            font-size: 14px;
            text-decoration: none;
        }

        .back-home:hover {
            color: var(--primary-light);
        }

        @media (max-width: 960px) {
            .auth-page {
                grid-template-columns: 1fr;
            }

            .auth-brand {
                min-height: auto;
                padding: 40px 24px;
            }

            .auth-form-section {
                padding: 28px 18px;
            }
        }

        @media (max-width: 620px) {
            .auth-card {
                padding: 26px 20px;
                border-radius: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .brand-note {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-brand">
            <div class="brand-badge">Registro cliente</div>

            <h1>Crea tu cuenta en Mega_Uni_Store</h1>

            <p>
                Regístrate para acceder al catálogo, gestionar tus compras,
                consultar historial, pagos, pedidos y participar en la experiencia multitienda.
            </p>

            <div class="brand-note">
                El registro público se asigna como <strong>Cliente</strong>. Los roles administrativos
                como Superadministrador, Administrador de Tienda, Vendedor, Bodeguero o Reportero
                se asignan desde el panel interno de gestión de usuarios.
            </div>
        </section>

        <section class="auth-form-section">
            <div class="auth-card">
                <div class="auth-card-header">
                    <small>Cuenta nueva</small>
                    <h2>Registro</h2>
                    <p>Completa tus datos para crear una cuenta de cliente.</p>
                </div>

                <?php if ($flash !== null): ?>
                    <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : 'error') ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?route=register.post" method="POST" autocomplete="on">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="form-control"
                                placeholder="Tu nombre"
                                required
                                maxlength="80"
                                autocomplete="given-name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="apellido">Apellido</label>
                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                class="form-control"
                                placeholder="Tu apellido"
                                required
                                maxlength="80"
                                autocomplete="family-name"
                            >
                        </div>

                        <div class="form-group full">
                            <label for="email">Correo electrónico</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="usuario@correo.com"
                                required
                                maxlength="150"
                                autocomplete="email"
                            >
                            <span class="help-text">El correo será usado para iniciar sesión y debe ser único.</span>
                        </div>

                        <div class="form-group full">
                            <label for="telefono">Teléfono</label>
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                class="form-control"
                                placeholder="Ej: 3101234567"
                                maxlength="20"
                                autocomplete="tel"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Mínimo 8 caracteres"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmar contraseña</label>
                            <input
                                type="password"
                                id="password_confirm"
                                name="password_confirm"
                                class="form-control"
                                placeholder="Repite la contraseña"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <label class="terms">
                        <input type="checkbox" name="terms" required>
                        <span>
                            Acepto los términos de uso y la política de privacidad de Mega_Uni_Store.
                        </span>
                    </label>

                    <button type="submit" class="btn-primary">Crear cuenta</button>
                </form>

                <div class="auth-link">
                    ¿Ya tienes cuenta?
                    <a href="index.php?route=login">Iniciar sesión</a>
                </div>

                <a class="back-home" href="../../../frontend/public/index.php">← Volver al inicio</a>
            </div>
        </section>
    </main>
</body>
</html>