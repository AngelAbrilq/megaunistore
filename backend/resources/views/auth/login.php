<?php
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 */

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
    <title>Iniciar sesión | Mega_Uni_Store</title>

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
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 32%),
                linear-gradient(135deg, #eff6ff 0%, #f8fafc 45%, #e0f2fe 100%);
            color: var(--text);
        }

        .auth-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
        }

        .auth-brand {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px;
            background:
                linear-gradient(135deg, rgba(30, 58, 138, 0.96), rgba(37, 99, 235, 0.92)),
                url("../../../frontend/public/assets/img/fondo.jpg");
            background-size: cover;
            background-position: center;
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
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.05;
            margin: 0 0 20px;
        }

        .auth-brand p {
            max-width: 620px;
            font-size: 17px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
        }

        .brand-list {
            display: grid;
            gap: 14px;
            margin-top: 34px;
            max-width: 560px;
        }

        .brand-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.11);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
        }

        .brand-item span {
            width: 10px;
            height: 10px;
            margin-top: 6px;
            border-radius: 50%;
            background: var(--accent);
            flex: 0 0 auto;
        }

        .brand-item strong {
            display: block;
            margin-bottom: 3px;
            font-size: 15px;
        }

        .brand-item small {
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.5;
        }

        .auth-form-section {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--white);
            border-radius: 26px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(219, 227, 239, 0.9);
            padding: 36px;
        }

        .auth-card-header {
            margin-bottom: 26px;
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

        .form-group {
            margin-bottom: 18px;
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

        .form-meta {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .form-meta a,
        .auth-link a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 700;
        }

        .form-meta a:hover,
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

        @media (max-width: 900px) {
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

        @media (max-width: 520px) {
            .auth-card {
                padding: 26px 20px;
                border-radius: 20px;
            }

            .brand-list {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-brand">
            <div class="brand-badge">Mega_Uni_Store</div>

            <h1>Gestión multitienda segura y centralizada</h1>

            <p>
                Accede al ecosistema de administración para controlar usuarios,
                tiendas, inventarios, ventas, reportes y operaciones según tu rol.
            </p>

            <div class="brand-list">
                <div class="brand-item">
                    <span></span>
                    <div>
                        <strong>Acceso por roles</strong>
                        <small>Superadministrador, administrador de tienda, vendedor, bodeguero, reportero, cliente y más.</small>
                    </div>
                </div>

                <div class="brand-item">
                    <span></span>
                    <div>
                        <strong>Operación trazable</strong>
                        <small>El sistema prepara la base para auditoría, seguridad y control de acciones.</small>
                    </div>
                </div>

                <div class="brand-item">
                    <span></span>
                    <div>
                        <strong>Diseño responsive</strong>
                        <small>Interfaz adaptable para escritorio, tablet y móvil.</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-form-section">
            <div class="auth-card">
                <div class="auth-card-header">
                    <small>Acceso seguro</small>
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa con tu correo y contraseña para continuar al panel correspondiente.</p>
                </div>

                <?php if ($flash !== null): ?>
                    <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : 'error') ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?route=login.post" method="POST" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="usuario@correo.com"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Ingresa tu contraseña"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="form-meta">
                        <a href="index.php?route=password.request">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-primary">Entrar al sistema</button>
                </form>

                <div class="auth-link">
                    ¿No tienes cuenta?
                    <a href="index.php?route=register">Crear cuenta como cliente</a>
                </div>

                <a class="back-home" href="../../../../frontend/public/">← Volver al inicio</a>
            </div>
        </section>
    </main>
</body>
</html>