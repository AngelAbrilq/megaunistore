<?php

$pageTitle = 'Panel Cliente';
$pageSubtitle = 'Experiencia de compra: catálogo, pedidos, pagos, historial, fidelización y calificaciones.';

$cards = [
    ['label' => 'Rol activo', 'value' => 'Cliente'],
    ['label' => 'Alcance', 'value' => 'Compra y autoservicio'],
    ['label' => 'Módulo inicial', 'value' => 'Catálogo'],
];

$actions = [
    [
        'title' => 'Ver catálogo',
        'description' => 'Explorar productos disponibles por tienda y categoría.',
        'url' => 'index.php?route=catalogo.index',
    ],
    [
        'title' => 'Mis pedidos',
        'description' => 'Consultar historial, estado de pedidos y comprobantes.',
        'url' => 'index.php?route=cliente.pedidos',
    ],
    [
        'title' => 'Mi perfil',
        'description' => 'Actualizar datos personales y preferencias.',
        'url' => 'index.php?route=cliente.perfil',
    ],
];

require __DIR__ . '/layout.php';