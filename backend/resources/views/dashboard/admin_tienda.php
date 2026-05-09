<?php

$pageTitle = 'Panel Administrador de Tienda';
$pageSubtitle = 'Administración operativa de una tienda específica: productos, personal, inventario, ventas y reportes.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Administrador de Tienda'],
    ['label' => 'Alcance', 'value' => 'Tienda asignada'],
    ['label' => 'Módulo inicial', 'value' => 'Catálogo e inventario'],
];

$actions = [
    [
        'title' => 'Productos',
        'description' => 'Registrar, editar y organizar productos de la tienda.',
        'url' => 'index.php?route=productos.index',
    ],
    [
        'title' => 'Inventario',
        'description' => 'Consultar stock, mínimos, máximos y movimientos.',
        'url' => 'index.php?route=inventario.index',
    ],
    [
        'title' => 'Personal',
        'description' => 'Gestionar vendedores, bodegueros, supervisores y reporteros.',
        'url' => 'index.php?route=empleados.index',
    ],
];

require __DIR__ . '/layout.php';