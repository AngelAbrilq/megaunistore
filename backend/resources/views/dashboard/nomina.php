<?php

$pageTitle = 'Panel Nómina y RRHH';
$pageSubtitle = 'Gestión de personal, contratos, novedades laborales, liquidaciones y reportes de productividad.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Nómina y RRHH'],
    ['label' => 'Alcance', 'value' => 'Talento humano'],
    ['label' => 'Módulo inicial', 'value' => 'Empleados y contratos'],
];

$actions = [
    [
        'title' => 'Empleados',
        'description' => 'Gestionar información laboral del personal.',
        'url' => 'index.php?route=empleados.index',
    ],
    [
        'title' => 'Contratos',
        'description' => 'Consultar y administrar contratos laborales.',
        'url' => 'index.php?route=contratos.index',
    ],
    [
        'title' => 'Liquidaciones',
        'description' => 'Procesar nómina, deducciones, devengados y pagos.',
        'url' => 'index.php?route=nomina.index',
    ],
];

require __DIR__ . '/layout.php';