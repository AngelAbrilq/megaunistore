<?php
// Detectar si es petición AJAX
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

$pageTitle = 'Panel Superadministrador';
$pageSubtitle = 'Gobernanza global de la plataforma, tiendas, usuarios, roles, seguridad y configuración.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Superadministrador'],
    ['label' => 'Alcance', 'value' => 'Global'],
    ['label' => 'Módulo inicial', 'value' => 'Tiendas y usuarios'],
];

$actions = [
    [
        'title' => 'Gestionar tiendas',
        'description' => 'Crear, activar, suspender o configurar tiendas del ecosistema.',
        'url' => 'index.php?route=tiendas.index',
    ],
    [
        'title' => 'Gestionar usuarios',
        'description' => 'Administrar cuentas, roles y asignaciones por tienda.',
        'url' => 'index.php?route=usuarios.index',
    ],
    [
        'title' => 'Auditoría',
        'description' => 'Consultar trazabilidad de acciones críticas del sistema.',
        'url' => 'index.php?route=auditoria.index',
    ],
];

// Si es AJAX, solo mostrar el contenido
if ($isAjax) {
    ?>
    <style>
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .dashboard-card {
            background: white;
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .dashboard-card-label {
            display: block;
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .dashboard-card-value {
            display: block;
            color: #1e3a8a;
            font-size: 24px;
            font-weight: 700;
        }

        .dashboard-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 32px;
        }

        .dashboard-action {
            display: block;
            background: #f8fafc;
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            padding: 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }

        .dashboard-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #2563eb;
        }

        .dashboard-action-title {
            display: block;
            color: #1e3a8a;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .dashboard-action-description {
            display: block;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        .dashboard-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 16px;
        }
    </style>

    <div class="dashboard-cards">
        <?php foreach ($cards as $card): ?>
            <div class="dashboard-card">
                <span class="dashboard-card-label"><?= htmlspecialchars($card['label']) ?></span>
                <span class="dashboard-card-value"><?= htmlspecialchars($card['value']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="dashboard-section-title">Acciones Principales</h2>

    <div class="dashboard-actions">
        <?php foreach ($actions as $action): ?>
            <a href="<?= htmlspecialchars($action['url']) ?>" class="dashboard-action">
                <span class="dashboard-action-title"><?= htmlspecialchars($action['title']) ?></span>
                <span class="dashboard-action-description"><?= htmlspecialchars($action['description']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
} else {
    // Si no es AJAX, usar el layout completo original
    require __DIR__ . '/layout.php';
}
?>