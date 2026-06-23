<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Testimonios · Mega_Uni_Store — Lo que dicen nuestros clientes</title>
  <meta name="description" content="Descubre cómo Mega_Uni_Store ha transformado tiendas colombianas. Historias reales de empresarios que centralizaron su operación multitienda." />
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
    .test-card{border:1px solid rgba(255,255,255,.08);transition:transform .25s,box-shadow .25s;}
    .test-card:hover{transform:translateY(-4px);box-shadow:0 20px 50px rgba(0,0,0,.28);}
    .stars{color:#FFD36A;letter-spacing:.1em;}
  </style>
</head>
<body>

<?php include __DIR__ . '/../src/layout/narbar.html'; ?>

<main class="pt-4 pb-24">

  <!-- HERO -->
  <section class="max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-14 text-center animate-fadeUp">
    <div class="inline-block px-4 py-1.5 rounded-full glass text-sm font-semibold text-cyan mb-5">Historias de éxito</div>
    <h1 class="text-gradient font-display text-3xl sm:text-5xl font-extrabold leading-tight">
      Negocios colombianos<br>que ya lo usan.
    </h1>
    <p class="mt-5 text-slate-400 max-w-2xl mx-auto text-lg leading-relaxed">
      Más de 120 empresarios confían en Mega_Uni_Store para gestionar sus tiendas, nómina y contabilidad desde un solo lugar.
    </p>
    <!-- Métricas -->
    <div class="mt-10 grid grid-cols-3 gap-4 max-w-lg mx-auto">
      <div class="glass rounded-2xl p-4">
        <div class="text-2xl font-display font-extrabold text-cyan">120+</div>
        <div class="text-xs text-slate-400 mt-1">Empresas activas</div>
      </div>
      <div class="glass rounded-2xl p-4">
        <div class="text-2xl font-display font-extrabold text-violet">4.9★</div>
        <div class="text-xs text-slate-400 mt-1">Calificación promedio</div>
      </div>
      <div class="glass rounded-2xl p-4">
        <div class="text-2xl font-display font-extrabold text-gold">98%</div>
        <div class="text-xs text-slate-400 mt-1">Renovación anual</div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIOS -->
  <section class="max-w-6xl mx-auto px-4 sm:px-6 mb-20">
    <?php
    $testimonios = [
      [
        "nombre" => "María Fernanda Ospina",
        "cargo"  => "Propietaria · Moda Femenina Las Palmas",
        "ciudad" => "Medellín, Antioquia",
        "plan"   => "Plan Pro · 3 tiendas",
        "color"  => "#2EE6FF",
        "avatar" => "MF",
        "stars"  => 5,
        "texto"  => "Antes cerraba caja a mano y siempre cuadraba diferente. Con Mega_Uni_Store el cierre es automático y en 2 minutos tengo el reporte de las 3 tiendas consolidado. La factura electrónica con DIAN me quitó el dolor de cabeza más grande que tenía.",
        "resultado" => "Redujo 3 horas diarias de trabajo administrativo",
      ],
      [
        "nombre" => "Carlos Andrés Reyes",
        "cargo"  => "Gerente General · Distribuidora CR",
        "ciudad" => "Bogotá, Cundinamarca",
        "plan"   => "Plan Enterprise · 8 puntos",
        "color"  => "#8B5CFF",
        "avatar" => "CR",
        "stars"  => 5,
        "texto"  => "Manejamos 8 puntos de distribución y antes necesitábamos un contador en cada uno. Ahora la contabilidad de todos los puntos se consolida sola. Los traslados de inventario entre bodegas se hacen en segundos y siempre tenemos el stock correcto.",
        "resultado" => "+27% en rotación de inventario en 60 días",
      ],
      [
        "nombre" => "Luisa Margarita Torres",
        "cargo"  => "Socia · Farmacia y Droguería Salud Total",
        "ciudad" => "Cali, Valle del Cauca",
        "plan"   => "Plan Pro · 2 tiendas",
        "color"  => "#FFD36A",
        "avatar" => "LT",
        "stars"  => 5,
        "texto"  => "El control de lotes y vencimientos nos salvó de dos posibles sanciones del INVIMA. El sistema nos avisa con 30 días de anticipación y podemos retirar el producto a tiempo. La nómina de los 12 empleados ahora la liquida mi asistente en 20 minutos.",
        "resultado" => "0 productos vencidos en stock desde que usan el sistema",
      ],
      [
        "nombre" => "Jhon Alexander Muñoz",
        "cargo"  => "Dueño · Ferretería El Constructor",
        "ciudad" => "Bucaramanga, Santander",
        "plan"   => "Plan Starter · 1 tienda",
        "color"  => "#22c55e",
        "avatar" => "JM",
        "stars"  => 5,
        "texto"  => "Tengo más de 4.000 referencias en ferretería. Con el sistema anterior me perdía entre planillas de Excel. Ahora busco cualquier producto en segundos, sé exactamente cuánto tengo y el sistema me pide el pedido al proveedor automáticamente cuando baja del mínimo.",
        "resultado" => "4.000+ referencias controladas sin errores de stock",
      ],
      [
        "nombre" => "Paola Ximena Castillo",
        "cargo"  => "Administradora · Salón Belleza Élite",
        "ciudad" => "Barranquilla, Atlántico",
        "plan"   => "Plan Starter · 1 tienda",
        "color"  => "#fb7185",
        "avatar" => "PC",
        "stars"  => 5,
        "texto"  => "Lo que más me gustó fue la nómina. Tengo 8 estilistas con comisiones diferentes y días variables. Antes me tocaba calcularlo todo a mano y siempre había quejas. Ahora el sistema calcula todo solo — prestaciones, horas extra, todo — y nadie reclama.",
        "resultado" => "Liquidación de nómina con comisiones en < 15 minutos",
      ],
      [
        "nombre" => "Roberto Darío Peña",
        "cargo"  => "CEO · Supermercados El Trigal",
        "ciudad" => "Pereira, Risaralda",
        "plan"   => "Plan Enterprise · 5 sucursales",
        "color"  => "#2EE6FF",
        "avatar" => "RP",
        "stars"  => 5,
        "texto"  => "Teníamos 5 sistemas diferentes en 5 sucursales y un caos total a fin de mes. Hoy tenemos todo en Mega_Uni_Store y el reporte mensual lo genera solo. El equipo contable pasó de trabajar 5 días en el cierre a cerrarlo en 1 día. Increíble.",
        "resultado" => "Cierre contable reducido de 5 días a 1 día",
      ],
    ];
    ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($testimonios as $t): ?>
      <div class="test-card glass rounded-3xl p-7 flex flex-col">
        <div class="stars text-sm mb-3"><?= str_repeat("★", $t['stars']) ?></div>
        <blockquote class="text-slate-300 text-sm leading-relaxed flex-1 mb-5">
          "<?= $t['texto'] ?>"
        </blockquote>
        <div class="pt-4 border-t border-white/8">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold text-ink flex-shrink-0" style="background:<?= $t['color'] ?>"><?= $t['avatar'] ?></div>
            <div>
              <div class="text-white font-semibold text-sm"><?= $t['nombre'] ?></div>
              <div class="text-slate-400 text-xs"><?= $t['cargo'] ?></div>
              <div class="text-slate-500 text-xs"><?= $t['ciudad'] ?></div>
            </div>
          </div>
          <div class="text-xs px-3 py-1.5 rounded-full inline-block font-medium" style="background:<?= $t['color'] ?>18;color:<?= $t['color'] ?>">
            ✓ <?= $t['resultado'] ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- LOGOS / EMPRESAS -->
  <section class="max-w-5xl mx-auto px-4 sm:px-6 mb-20 text-center">
    <p class="text-slate-500 text-sm uppercase tracking-widest mb-8">Sectores que ya confían en nosotros</p>
    <div class="flex flex-wrap justify-center gap-4">
      <?php foreach(["Moda & Ropa","Ferreterías","Farmacias","Restaurantes","Supermercados","Distribuidoras","Salones de belleza","Papelerías","Electrodomésticos","Calzado"] as $s): ?>
      <span class="glass text-slate-300 text-sm px-4 py-2 rounded-full"><?= $s ?></span>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CTA -->
  <section class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
    <div class="glass rounded-3xl p-10">
      <h2 class="font-display text-2xl font-extrabold text-white mb-3">Únete a los 120+ negocios que ya crecen con nosotros</h2>
      <p class="text-slate-400 mb-7">Empieza gratis hoy. Sin tarjeta de crédito. Soporte en español.</p>
      <div class="flex flex-wrap gap-4 justify-center">
        <a href="../../backend/public/index.php?route=register" class="px-8 py-4 rounded-full btn-primary text-ink font-bold transition">Crear cuenta gratis</a>
        <a href="contacto.php" class="px-8 py-4 rounded-full glass font-semibold hover:bg-white/10 transition">Hablar con ventas</a>
      </div>
    </div>
  </section>

</main>

<?php
include __DIR__ . '/../src/layout/footer.html';
include __DIR__ . '/../src/layout/footer_scripts.php';
?>
