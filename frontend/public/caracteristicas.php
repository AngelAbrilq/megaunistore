<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Características · Mega_Uni_Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{ink:"#060B1A",cyan:"#2EE6FF",violet:"#8B5CFF",gold:"#FFD36A"},keyframes:{fadeUp:{"0%":{opacity:0,transform:"translateY(16px)"},"100%":{opacity:1,transform:"translateY(0)"}}},animation:{fadeUp:"fadeUp .7s ease both"}}}}</script>
  <style>
    html{scroll-behavior:smooth}
    body{margin:0;font-family:"Inter",sans-serif;color:#dbe5ff;background:radial-gradient(circle at 8% 8%,rgba(46,230,255,.09),transparent 22%),radial-gradient(circle at 90% 20%,rgba(139,92,255,.09),transparent 22%),linear-gradient(180deg,#040712 0%,#08112a 40%,#050816 100%);overflow-x:hidden}
    .font-display{font-family:"Poppins",sans-serif}
    .glass{background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.03));backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.08)}
    .btn-primary{background:linear-gradient(90deg,#2ee6ff 0%,#2a72ff 35%,#8b5cff 100%);box-shadow:0 10px 28px rgba(42,114,255,.22)}
    .text-gradient{background:linear-gradient(90deg,#fff 0%,#cfe7ff 20%,#6fdcff 40%,#8b5cff 72%,#ffd36a 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
    .tab-btn{padding:.45rem 1.1rem;border-radius:9999px;font-size:.875rem;font-weight:600;cursor:pointer;border:1px solid rgba(255,255,255,.08);background:transparent;color:#94a3b8;transition:all .2s}
    .tab-btn.active,.tab-btn:hover{background:rgba(46,230,255,.12);color:#2EE6FF;border-color:rgba(46,230,255,.2)}
    .feat-panel{display:none}.feat-panel.active{display:grid}
    .check{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;flex-shrink:0;font-size:11px;font-weight:700}
  </style>
</head>
<body>
<?php include __DIR__ . '/../src/layout/narbar.html'; ?>
<main class="pt-4 pb-24">

  <section class="max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-12 text-center animate-fadeUp">
    <div class="inline-block px-4 py-1.5 rounded-full glass text-sm font-semibold text-cyan mb-5">Características completas</div>
    <h1 class="text-gradient font-display text-3xl sm:text-5xl font-extrabold leading-tight">Todo lo que necesitas,<br>nada que no uses.</h1>
    <p class="mt-5 text-slate-400 max-w-2xl mx-auto text-lg leading-relaxed">Cada módulo fue diseñado para el comercio colombiano: facturación DIAN, nómina con prestaciones y operación multitienda real.</p>
  </section>

  <section class="max-w-6xl mx-auto px-4 sm:px-6 mb-20">
    <div class="flex flex-wrap gap-2 justify-center mb-10">
      <button class="tab-btn active" data-tab="ventas">Ventas &amp; POS</button>
      <button class="tab-btn" data-tab="inventario">Inventario</button>
      <button class="tab-btn" data-tab="compras">Compras</button>
      <button class="tab-btn" data-tab="clientes">Clientes &amp; CRM</button>
      <button class="tab-btn" data-tab="rrhh">RRHH &amp; Nómina</button>
      <button class="tab-btn" data-tab="contabilidad">Contabilidad</button>
      <button class="tab-btn" data-tab="reportes">Reportes</button>
      <button class="tab-btn" data-tab="seguridad">Seguridad</button>
    </div>

    <div id="panel-ventas" class="feat-panel active grid sm:grid-cols-2 gap-4">
      <?php foreach(["POS táctil optimizado para cajas físicas y tablets","Factura electrónica DIAN en tiempo real (Factus)","Múltiples métodos de pago: efectivo, tarjeta, transferencia, mixto","Cupones y descuentos por producto, categoría o total de venta","Gestión de devoluciones con nota crédito automática","Cierre de caja con cuadre y arqueo automático","Ventas a crédito y control de cartera por cliente","Historial completo de transacciones por tienda"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(46,230,255,.15);color:#2EE6FF">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-inventario" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Control de stock por tienda y bodega de forma independiente","Alertas automáticas de stock mínimo por producto","Traslados de mercancía entre bodegas con trazabilidad completa","Múltiples unidades de medida (und, caja, kg, litro…)","Categorías y subcategorías ilimitadas de productos","Productos con atributos y variantes (talla, color, etc.)","Kardex completo: entradas, salidas y ajustes de inventario","Inventario físico con conteo y conciliación de diferencias"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(139,92,255,.15);color:#8B5CFF">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-compras" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Órdenes de compra a proveedores con seguimiento de estado","Registro de proveedores con NIT, RUT y condiciones comerciales","Recepción parcial o total con actualización automática de inventario","Comparación de precios entre proveedores por producto","Cuentas por pagar con fechas de vencimiento y alertas","Historial de compras y precios negociados por proveedor","Alertas de pedidos pendientes de recepción","Vinculación de compras a centros de costo contable"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(255,211,106,.15);color:#FFD36A">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-clientes" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Base de datos de clientes con historial completo de compras","Segmentación por frecuencia, monto y tienda","Gestión de clientes frecuentes con beneficios diferenciados","Control de créditos otorgados y saldos pendientes","Datos de contacto: teléfono, email, dirección de entrega","Vinculación de clientes a tiendas específicas","Notificaciones automáticas por deuda o promociones vigentes","Exportación de base de clientes para campañas de marketing"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(34,197,94,.15);color:#22c55e">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-rrhh" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Registro de empleados con tipo de contrato y cargo","Liquidación de nómina con todas las variables colombianas","Prestaciones sociales automáticas: prima, cesantías, intereses","Horas extras diurnas, nocturnas, dominicales y festivas","Control de vacaciones acumuladas y disfrutadas","Aportes a seguridad social: salud, pensión, ARL, caja","Historial de contratos y novedades por empleado","Comprobantes de nómina individuales exportables a PDF"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(251,113,133,.15);color:#fb7185">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-contabilidad" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Plan de cuentas adaptado a normativa colombiana","Asientos contables automáticos desde ventas, compras y nómina","Centros de costo por tienda, área o proyecto","Periodos contables con cierre y reapertura controlada","Estado de resultados (P&G) en tiempo real","Balance general consolidado y por tienda","Conciliación bancaria mensual","Liquidación automática de IVA, retefuente e ICA"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(46,230,255,.15);color:#2EE6FF">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-reportes" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Dashboard ejecutivo con KPIs en tiempo real por tienda","Ventas por producto, categoría, tienda y vendedor","Análisis de rentabilidad por tienda y por línea de producto","Reporte de inventario: rotación, antigüedad y valorización","Estado de cartera: cuentas por pagar y por cobrar","Exportación a Excel, PDF y CSV con un clic","Programación de reportes automáticos por correo","Comparativas periodo a periodo y año a año"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(139,92,255,.15);color:#8B5CFF">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
    <div id="panel-seguridad" class="feat-panel grid sm:grid-cols-2 gap-4">
      <?php foreach(["Roles y permisos granulares por módulo y por tienda","Auditoría inmutable de todos los movimientos del sistema","Autenticación con bloqueo automático por intentos fallidos","Sesiones con timeout configurable por perfil","Backup automático de la base de datos","Cabeceras HTTP: CSP, HSTS, X-Frame-Options, Permissions-Policy","Log de accesos y cambios con usuario, fecha y hora","Tokens CSRF en todos los formularios del sistema"] as $f): ?>
      <div class="glass rounded-2xl px-5 py-4 flex items-start gap-3"><span class="check mt-0.5" style="background:rgba(255,211,106,.15);color:#FFD36A">✓</span><span class="text-slate-300 text-sm leading-relaxed"><?= $f ?></span></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
    <div class="glass rounded-3xl p-10">
      <h2 class="font-display text-2xl font-extrabold text-white mb-3">¿Quieres verlo en acción?</h2>
      <p class="text-slate-400 mb-7">Crea tu cuenta gratis y explora todas las características sin límite de tiempo.</p>
      <div class="flex flex-wrap gap-4 justify-center">
        <a href="../../backend/public/index.php?route=register" class="px-8 py-4 rounded-full btn-primary text-ink font-bold transition">Empezar gratis</a>
        <a href="contacto.php" class="px-8 py-4 rounded-full glass font-semibold hover:bg-white/10 transition">Agendar demo</a>
      </div>
    </div>
  </section>
</main>
<script>
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.feat-panel').forEach(p => p.classList.remove('active'));
      document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
  });
</script>
<?php
include __DIR__ . '/../src/layout/footer.html';
include __DIR__ . '/../src/layout/footer_scripts.php';
?>
