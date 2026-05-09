<?php

$pageTitle = 'Panel Vendedor';
$pageSubtitle = 'Registro de ventas asistidas, atención al cliente, carrito, pagos y cierre operativo.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Vendedor'],
    ['label' => 'Alcance', 'value' => 'Punto de venta'],
    ['label' => 'Módulo inicial', 'value' => 'Ventas'],
];

$actions = [
    [
        'title' => 'Nueva venta',
        'description' => 'Crear una venta asistida desde el punto de venta.',
        'url' => 'index.php?route=ventas.create',
    ],
    [
        'title' => 'Consultar productos',
        'description' => 'Buscar productos, precios y disponibilidad.',
        'url' => 'index.php?route=productos.buscar',
    ],
    [
        'title' => 'Caja',
        'description' => 'Consultar movimientos, apertura y cierre de caja.',
        'url' => 'index.php?route=caja.index',
    ],
];

require __DIR__ . '/layout.php';