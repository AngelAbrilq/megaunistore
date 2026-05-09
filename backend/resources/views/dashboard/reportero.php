<?php

$pageTitle = 'Panel Reportero';
$pageSubtitle = 'Generación, consulta y exportación de reportes estratégicos de ventas, inventarios y desempeño.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Reportero'],
    ['label' => 'Alcance', 'value' => 'Analítica y reportes'],
    ['label' => 'Módulo inicial', 'value' => 'BI operativo'],
];

$actions = [
    [
        'title' => 'Reporte de ventas',
        'description' => 'Consultar ingresos por tienda, fechas y estado de venta.',
        'url' => 'index.php?route=reportes.ventas',
    ],
    [
        'title' => 'Reporte de inventario',
        'description' => 'Analizar stock, rotación y productos críticos.',
        'url' => 'index.php?route=reportes.inventario',
    ],
    [
        'title' => 'Exportaciones',
        'description' => 'Generar archivos PDF, Excel o CSV.',
        'url' => 'index.php?route=reportes.exportaciones',
    ],
];

require __DIR__ . '/layout.php';