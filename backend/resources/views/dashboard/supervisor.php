<?php

$pageTitle = 'Panel Supervisor';
$pageSubtitle = 'Supervisión operativa, validación de procesos, control de actividades y seguimiento de cumplimiento.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Supervisor'],
    ['label' => 'Alcance', 'value' => 'Operación de tienda'],
    ['label' => 'Módulo inicial', 'value' => 'Control operativo'],
];

$actions = [
    [
        'title' => 'Monitorear ventas',
        'description' => 'Revisar transacciones, estados y novedades operativas.',
        'url' => 'index.php?route=ventas.index',
    ],
    [
        'title' => 'Revisar devoluciones',
        'description' => 'Validar solicitudes, motivos y trazabilidad de devoluciones.',
        'url' => 'index.php?route=devoluciones.index',
    ],
    [
        'title' => 'Cumplimiento',
        'description' => 'Supervisar horarios, cajas y procesos diarios.',
        'url' => 'index.php?route=cumplimiento.index',
    ],
];

require __DIR__ . '/layout.php';