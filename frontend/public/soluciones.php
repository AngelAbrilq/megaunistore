<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Soluciones · Mega_Uni_Store — Para cada tipo de negocio</title>
  <meta name="description" content="Mega_Uni_Store se adapta a tu tipo de negocio: tienda individual, cadena de tiendas, distribuidora o franquicia. Soluciones a tu medida." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors:{ink:"#060B1A",navy:"#0B1430",cyan:"#2EE6FF",violet:"#8B5CFF",gold:"#FFD36A"},
          keyframes:{fadeUp:{"0%":{opacity:0,transform:"translateY(16px)"},"100%":{opacity:1,transform:"translateY(0)"}}},
          animation:{fadeUp:"fadeUp .7s ease both"}
        }
      }
    }
  </script>
  <style>
    html{scroll-behavior:smooth;}
    body{margin:0;font-family:"Inter",sans-serif;color:#dbe5ff;
      background:radial-gradient(circle at 10% 10%,rgba(46,230,255,.09),transparent 22%),
                 radial-gradient(circle at 88% 18%,rgba(139,92,255,.09),transparent 22%),
                 linear-gradient(180deg,#040712 0%,#08112a 40%,#050816 100%);overflow-x:hidden;}
    .font-display{font-family:"Poppins",sans-serif;}
    .glass{background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.03));backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.08);}
    .btn-primary{background:linear-gradient(90deg,#2ee6ff 0%,#2a72ff 35%,#8b5cff 100%);box-shadow:0 10px 28px rgba(42,114,255,.22);}
    .text-gradient{background:linear-gradient(90deg,#fff 0%,#cfe7ff 20%,#6fdcff 40%,#8b5cff 72%,#ffd36a 100%);-webkit-background-clip:text;background-clip:text;color:transparent;}
    .sol-card{border:1px solid rgba(255,255,255,.08);transition:transform .25s,box-shadow .25s;}
    .sol-card:hover{transform:translateY(-4px);box-shadow:0 20px 50px rgba(0,0,0,.28);}
    .tick{color:#2EE6FF;font-weight:700;flex-shrink:0;}
  </style>
</head>
<body>

<?php include __DIR__ . '/../src/layout/narbar.html'; ?>

<main class="pt-4 pb-24">

  <!-- HERO -->
  <section class="max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-14 text-center animate-fadeUp">
    <div class="inline-block px-4 py-1.5 rounded-full glass text-sm font-semibold text-cyan mb-5">Soluciones por tipo de negocio</div>
    <h1 class="text-gradient font-display text-3xl sm:text-5xl font-extrabold leading-tight">
      Diseñado para cada<br>etapa de tu negocio.
    </h1>
    <p class="mt-5 text-slate-400 max-w-2xl mx-auto text-lg leading-relaxed">
      Desde una tienda local hasta una cadena con múltiples sucursales. Mega_Uni_Store crece contigo sin que tengas que cambiar de sistema.
    </p>
  </section>

  <!-- SOLUCIONES POR TAMAÑO -->
  <section class="max-w-6xl mx-auto px-4 sm:px-6 mb-20">
    <div class="grid lg:grid-cols-3 gap-6">

      <!-- Tienda individual -->
      <div class="sol-card glass rounded-3xl p-8">
        <div class="w-14 h-14 rounded-2xl bg-cyan/10 flex items-center justify-center mb-5">
          <svg class="w-7 h-7 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 22V12h6v10"/>
          </svg>
        </div>
        <div class="text-xs font-bold text-cyan uppercase tracking-widest mb-2">Tienda individual</div>
        <h2 class="text-xl font-bold text-white mb-3">Empieza con lo esencial</h2>
        <p class="text-slate-400 text-sm leading-relaxed mb-5">Ideal para tiendas de ropa, calzado, papelerías, misceláneas y negocios locales que necesitan control real sin complicaciones.</p>
        <ul class="space-y-2.5 mb-6">
          <?php foreach(["POS con factura electrónica DIAN","Control de inventario por 1 bodega","Registro de clientes y ventas a crédito","Cierre de caja diario automático","Reportes de ventas y rentabilidad","1 usuario administrador + cajeros"] as $f): ?>
          <li class="flex items-start gap-2 text-sm text-slate-300"><span class="tick mt-0.5">✓</span><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="planes.php" class="inline-block px-5 py-2.5 rounded-full glass text-sm font-semibold hover:bg-white/10 transition text-cyan">Ver plan Starter →</a>
      </div>

      <!-- Cadena multitienda -->
      <div class="sol-card rounded-3xl p-8 relative overflow-hidden" style="background:linear-gradient(135deg,rgba(46,230,255,.08),rgba(139,92,255,.08));border:1px solid rgba(139,92,255,.3);">
        <div class="absolute top-4 right-4">
          <span class="text-xs font-bold px-3 py-1 rounded-full" style="background:rgba(46,230,255,.15);color:#2EE6FF">MÁS POPULAR</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-violet/10 flex items-center justify-center mb-5">
          <svg class="w-7 h-7 text-violet" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <div class="text-xs font-bold text-violet uppercase tracking-widest mb-2">Cadena multitienda</div>
        <h2 class="text-xl font-bold text-white mb-3">Centraliza toda tu cadena</h2>
        <p class="text-slate-400 text-sm leading-relaxed mb-5">Para negocios con 2 a 10 sucursales. Dashboard unificado, inventario compartido y control de cada tienda desde un solo lugar.</p>
        <ul class="space-y-2.5 mb-6">
          <?php foreach(["Todo lo del plan Starter","Gestión de múltiples tiendas y bodegas","Dashboard consolidado en tiempo real","Traslados de mercancía entre tiendas","Roles diferenciados por tienda","Nómina y RRHH para tu equipo","Contabilidad consolidada multi-entidad"] as $f): ?>
          <li class="flex items-start gap-2 text-sm text-slate-300"><span class="tick mt-0.5">✓</span><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="planes.php" class="inline-block px-5 py-2.5 rounded-full btn-primary text-ink text-sm font-bold transition">Ver plan Pro →</a>
      </div>

      <!-- Enterprise / Distribuidora -->
      <div class="sol-card glass rounded-3xl p-8">
        <div class="w-14 h-14 rounded-2xl bg-gold/10 flex items-center justify-center mb-5">
          <svg class="w-7 h-7 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
          </svg>
        </div>
        <div class="text-xs font-bold text-gold uppercase tracking-widest mb-2">Enterprise / Distribuidora</div>
        <h2 class="text-xl font-bold text-white mb-3">Escala sin límites</h2>
        <p class="text-slate-400 text-sm leading-relaxed mb-5">Para distribuidoras, franquicias y cadenas enterprise con +10 puntos de venta, integraciones avanzadas y soporte dedicado.</p>
        <ul class="space-y-2.5 mb-6">
          <?php foreach(["Todo lo del plan Pro","Tiendas y bodegas ilimitadas","Integraciones con Shopify, MercadoLibre y ERPs","API REST para desarrolladores","Soporte prioritario con SLA garantizado","Auditoría avanzada y reportes personalizados","Onboarding y capacitación dedicada"] as $f): ?>
          <li class="flex items-start gap-2 text-sm text-slate-300"><span class="tick mt-0.5">✓</span><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="contacto.php" class="inline-block px-5 py-2.5 rounded-full glass text-sm font-semibold hover:bg-white/10 transition text-gold">Contactar ventas →</a>
      </div>

    </div>
  </section>

  <!-- POR SECTOR -->
  <section class="max-w-6xl mx-auto px-4 sm:px-6 mb-20">
    <h2 class="text-center font-display text-2xl sm:text-3xl font-extrabold text-white mb-10">También funciona para tu sector</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php
      $sectores = [
        ["🛍️","Moda & Ropa","Tallas, colores, variantes y temporadas. Control de colecciones por tienda."],
        ["🍽️","Restaurantes","Mesas, comandas, menú por sucursal y cierre de caja con propinas."],
        ["💊","Salud & Farmacias","Lote, vencimiento, control de productos regulados y factura DIAN."],
        ["🔧","Ferreterías","Catálogo extenso, referencias, proveedores y pedidos al por mayor."],
        ["💄","Salones & Estética","Agendamiento, servicios, productos y nómina de estilistas."],
        ["🏗️","Distribuidoras","Rutas de entrega, precios por cliente y volumen, cartera en campo."],
        ["📚","Papelerías & Librerías","Inventario variado, temporadas escolares y ventas al por menor."],
        ["🥦","Supermercados","Precios variables, báscula, múltiples cajas y control de perecederos."],
      ];
      foreach($sectores as $s): ?>
      <div class="glass rounded-2xl p-5">
        <div class="text-3xl mb-3"><?= $s[0] ?></div>
        <h3 class="font-bold text-white text-sm mb-1"><?= $s[1] ?></h3>
        <p class="text-slate-400 text-xs leading-relaxed"><?= $s[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CTA -->
  <section class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
    <div class="glass rounded-3xl p-10">
      <h2 class="font-display text-2xl font-extrabold text-white mb-3">¿Tu negocio no está en la lista?</h2>
      <p class="text-slate-400 mb-7">Cuéntanos cómo operas y encontramos la configuración perfecta para ti.</p>
      <div class="flex flex-wrap gap-4 justify-center">
        <a href="contacto.php" class="px-8 py-4 rounded-full btn-primary text-ink font-bold transition">Hablar con un experto</a>
        <a href="planes.php" class="px-8 py-4 rounded-full glass font-semibold hover:bg-white/10 transition">Ver todos los planes</a>
      </div>
    </div>
  </section>

</main>

<?php
include __DIR__ . '/../src/layout/footer.html';
include __DIR__ . '/../src/layout/footer_scripts.php';
?>
