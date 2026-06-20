<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) return;

$pageTitle    = 'Panel Nómina y RRHH';
$pageSubtitle = 'Gestión de contratos, nóminas y personal.';
?>

<div style="margin-bottom:24px;">
    <h2 style="font-size:22px; font-weight:700;">👔 Panel Nómina y RRHH</h2>
    <p style="color:var(--text-secondary); font-size:14px; margin-top:4px;">Bienvenido, <?= htmlspecialchars($_SESSION['auth']['nombre'] ?? '') ?></p>
</div>

<!-- Acciones rápidas -->
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:28px;">
    <button onclick="loadContent('nomina.index')" style="padding:20px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; cursor:pointer; text-align:left; transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
        <div style="font-size:28px; margin-bottom:8px;">💼</div>
        <div style="font-weight:700; font-size:15px;">Nóminas</div>
        <div style="font-size:12px; color:var(--text-secondary); margin-top:4px;">Períodos y liquidaciones</div>
    </button>
    <button onclick="loadContent('contratos.index')" style="padding:20px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; cursor:pointer; text-align:left; transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
        <div style="font-size:28px; margin-bottom:8px;">📄</div>
        <div style="font-weight:700; font-size:15px;">Contratos</div>
        <div style="font-size:12px; color:var(--text-secondary); margin-top:4px;">Gestión contractual</div>
    </button>
    <button onclick="loadContent('empleados.index')" style="padding:20px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; cursor:pointer; text-align:left; transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
        <div style="font-size:28px; margin-bottom:8px;">👥</div>
        <div style="font-weight:700; font-size:15px;">Empleados</div>
        <div style="font-size:12px; color:var(--text-secondary); margin-top:4px;">Personal activo</div>
    </button>
    <button onclick="loadContent('reportes.ventas')" style="padding:20px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; cursor:pointer; text-align:left; transition:all .15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
        <div style="font-size:28px; margin-bottom:8px;">📊</div>
        <div style="font-weight:700; font-size:15px;">Reportes</div>
        <div style="font-size:12px; color:var(--text-secondary); margin-top:4px;">Productividad del equipo</div>
    </button>
</div>

<!-- Indicaciones -->
<div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; padding:20px;">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:12px;">📋 Flujo de nómina</h3>
    <div style="display:flex; gap:8px; align-items:flex-start; flex-wrap:wrap;">
        <?php foreach ([
            ['1', 'Crear período', 'Define el rango de fechas y tipo (mensual/quincenal)'],
            ['2', 'Calcular', 'El sistema procesa automáticamente salarios y deducciones'],
            ['3', 'Aprobar', 'Revisión y aprobación del total a pagar'],
            ['4', 'Pagar', 'Confirmación de pago y registro definitivo'],
        ] as [$num, $titulo, $desc]): ?>
        <div style="flex:1; min-width:150px; background:var(--bg-secondary); border-radius:8px; padding:14px;">
            <div style="width:28px; height:28px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; margin-bottom:8px;"><?= $num ?></div>
            <div style="font-weight:700; font-size:14px; margin-bottom:4px;"><?= $titulo ?></div>
            <div style="font-size:12px; color:var(--text-secondary);"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
