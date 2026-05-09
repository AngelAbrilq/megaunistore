<?php

$pageTitle = 'Panel Sistema';
$pageSubtitle = 'Actor lógico para automatizaciones, validaciones, alertas, respaldos y eventos internos.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Sistema'],
    ['label' => 'Alcance', 'value' => 'Automatización'],
    ['label' => 'Módulo inicial', 'value' => 'Eventos internos'],
];

$actions = [
    [
        'title' => 'Notificaciones',
        'description' => 'Gestionar alertas automáticas y mensajes internos.',
        'url' => 'index.php?route=notificaciones.index',
    ],
    [
        'title' => 'Respaldos',
        'description' => 'Revisar tareas de respaldo y recuperación.',
        'url' => 'index.php?route=backups.index',
    ],
    [
        'title' => 'Eventos',
        'description' => 'Consultar validaciones y procesos automáticos.',
        'url' => 'index.php?route=eventos.index',
    ],
];

require __DIR__ . '/layout.php';