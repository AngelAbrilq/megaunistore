<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Módulos · Mega_Uni_Store — ERP completo para tu negocio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{ink:"#060B1A",cyan:"#2EE6FF",violet:"#8B5CFF",gold:"#FFD36A"},keyframes:{fadeUp:{"0%":{opacity:0,transform:"translateY(16px)"},"100%":{opacity:1,transform:"translateY(0)"}}},animation:{fadeUp:"fadeUp .7s ease both"}}}}</script>
  <style>
    html{scroll-behavior:smooth}
    body{margin:0;font-family:"Inter",sans-serif;color:#dbe5ff;background:radial-gradient(circle at 12% 12%,rgba(46,230,255,.09),transparent 22%),radial-gradient(circle at 88% 16%,rgba(139,92,255,.09),transparent 22%),linear-gradient(180deg,#040712 0%,#08112a 40%,#050816 100%);overflow-x:hidden}
    .font-display{font-family:"Poppins",sans-serif}
    .glass{background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.03));backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.08)}
    .btn-primary{background:linear-gradient(90deg,#2ee6ff 0%,#2a72ff 35%,#8b5cff 100%);box-shadow:0 10px 28px rgba(42,114,255,.22)}
    .text-gradient{background:linear-gradient(90deg,#fff 0%,#cfe7ff 20%,#6fdcff 40%,#8b5cff 72%,#ffd36a 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
    .mod-card{border:1px solid rgba(255,255,255,.08);transition:transform .25s,box-shadow .25s}
    .mod-card:hover{transform:translateY(-4px);box-shadow:0 20px 50px rgba(0,0,0,.28)}
  </style>
</head>
<body>
<?php include __DIR__ . '/../src/layout/narbar.html'; ?>
<main class="pt-4 pb-24">

  <section class="max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-14 text-center animate-fadeUp">
    <div class="inline-block px-4 py-1.5 rounded-full glass text-sm font-semibold text-cyan mb-5">Módulos del sistema</div>
    <h1 class="text-gradient font-display text-3xl sm:text-5xl font-extrabold leading-tight">Un ERP completo.<br>Modular y escalable.</h1>
    <p class="mt-5 text-slate-400 max-w-2xl mx-auto text-lg leading-relaxed">Activa solo los módulos que necesitas hoy. A medida que tu negocio crece, activa más sin cambiar de plataforma ni migrar datos.</p>
  </section>

  <section class="max-w-6xl mx-auto px-4 sm:px-6 mb-20">
    <?php
    $modulos = [
      ["icon"=>"M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2 4h12M9 17a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z","color"=>"#2EE6FF","bg"=>"rgba(46,230,255,.1)","name"=>"Ventas & POS","desc"=>"Punto de venta rápido con facturación electrónica DIAN. Múltiples métodos de pago, cupones, devoluciones y cierre de caja automático.","tags"=>["POS Táctil","Factura DIAN","Cupones","Devoluciones","Cierre de caja"]],
      ["icon"=>"M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4","color"=>"#8B5CFF","bg"=>"rgba(139,92,255,.1)","name"=>"Inventario","desc"=>"Stock en tiempo real por tienda y bodega. Alertas de mínimos, traslados entre bodegas y kardex completo de cada movimiento.","tags"=>["Multi-bodega","Alertas","Traslados","Kardex","Variantes"]],
      ["icon"=>"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 17h.01","color"=>"#FFD36A","bg"=>"rgba(255,211,106,.1)","name"=>"Compras","desc"=>"Órdenes de compra a proveedores, recepción de mercancía y cuentas por pagar. Actualiza el inventario automáticamente al recibir.","tags"=>["OC","Proveedores","Recepción","CxP","Precios"]],
      ["icon"=>"M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5M12 11a4 4 0 100-8 4 4 0 000 8z","color"=>"#22c55e","bg"=>"rgba(34,197,94,.1)","name"=>"Clientes & CRM","desc"=>"Base de clientes con historial de compras, control de crédito y segmentación. Fideliza con beneficios personalizados por tienda.","tags"=>["Historial","Crédito","CxC","Segmentación","Fidelización"]],
      ["icon"=>"M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z","color"=>"#fb7185","bg"=>"rgba(251,113,133,.1)","name"=>"RRHH & Nómina","desc"=>"Nómina colombiana automática: prestaciones, horas extra y aportes a seguridad social. Comprobantes individuales en PDF.","tags"=>["Nómina","Prestaciones","Horas extra","SGSSS","Vacaciones"]],
      ["icon"=>"M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z","color"=>"#2EE6FF","bg"=>"rgba(46,230,255,.1)","name"=>"Contabilidad","desc"=>"Asientos automáticos, plan de cuentas colombiano, centros de costo, P&G y balance consolidado o por tienda.","tags"=>["P&G","Balance","IVA/Retefuente","CxC contable","Conciliación"]],
      ["icon"=>"M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z","color"=>"#8B5CFF","bg"=>"rgba(139,92,255,.1)","name"=>"Reportes & Dashboard","desc"=>"KPIs en tiempo real: ventas, márgenes, rotación y flujo de caja. Exporta a Excel o PDF. Programa envíos automáticos.","tags"=>["Dashboard","KPIs","Excel/PDF","Comparativas","Automáticos"]],
      ["icon"=>"M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9","color"=>"#FFD36A","bg"=>"rgba(255,211,106,.1)","name"=>"Notificaciones","desc"=>"Alertas en tiempo real: stock mínimo, pagos pendientes, vencimiento de contratos y actividad inusual por tienda.","tags"=>["Stock mínimo","Pagos","Vencimientos","Contratos","Tiempo real"]],
      ["icon"=>"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4","color"=>"#22c55e","bg"=>"rgba(34,197,94,.1)","name"=>"Auditoría & Seguridad","desc"=>"Log inmutable de cada acción: quién hizo qué, cuándo y desde dónde. Roles y permisos granulares por módulo y por tienda.","tags"=>["Log inmutable","Roles","Permisos","CSRF","Rate limiting"]],
    ];
    ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($modulos as $m): ?>
      <div class="mod-card glass rounded-3xl p-7">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background:<?= $m['bg'] ?>">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:<?= $m['color'] ?>"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $m['icon'] ?>"/></svg>
        </div>
        <h3 class="text-lg font-bold text-white mb-2"><?= $m['name'] ?></h3>
        <p class="text-slate-400 text-sm leading-relaxed mb-4"><?= $m['desc'] ?></p>
        <div class="flex flex-wrap gap-2">
          <?php foreach($m['tags'] as $tag): ?>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>"><?= $tag ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
    <div class="glass rounded-3xl p-10">
      <h2 class="font-display text-2xl font-extrabold text-white mb-3">Activa los módulos que necesitas</h2>
      <p class="text-slate-400 mb-7">Empieza con ventas e inventario y agrega RRHH, contabilidad y más cuando estés listo.</p>
      <div class="flex flex-wrap gap-4 justify-center">
        <a href="../../backend/public/index.php?route=register" class="px-8 py-4 rounded-full btn-primary text-ink font-bold transition">Crear cuenta gratis</a>
        <a href="planes.php" class="px-8 py-4 rounded-full glass font-semibold hover:bg-white/10 transition">Ver planes</a>
      </div>
    </div>
  </section>
</main>
<?php
include __DIR__ . '/../src/layout/footer.html';
include __DIR__ . '/../src/layout/footer_scripts.php';
?>
