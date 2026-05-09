<?php

$pageTitle = 'Panel Bodeguero';
$pageSubtitle = 'Control físico y digital del inventario, entradas, salidas, ajustes y preparación de pedidos.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Bodeguero'],
    ['label' => 'Alcance', 'value' => 'Inventario y bodega'],
    ['label' => 'Módulo inicial', 'value' => 'Movimientos de stock'],
];

$actions = [
    [
        'title' => 'Inventario',
        'description' => 'Ver cantidades disponibles y niveles mínimos por producto.',
        'url' => 'index.php?route=inventario.index',
    ],
    [
        'title' => 'Registrar inventario',
        'description' => 'Crear o actualizar inventario inicial de productos por tienda.',
        'url' => 'index.php?route=inventario.create',
    ],
    [
        'title' => 'Alertas de stock',
        'description' => 'Revisar productos por debajo del stock mínimo.',
        'url' => 'index.php?route=inventario.alertas',
    ],
];


require __DIR__ . '/layout.php';