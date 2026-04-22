<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Planes · Mega_Uni_Store — Multitienda Premium</title>
  <meta name="description" content="Planes Mega_Uni_Store — Desde local único hasta Enterprise. Precios por tienda, facturación DIAN, sincronización multi-bodega y soporte dedicado." />

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            ink: "#030515",
            navy: "#07102a",
            cyan: "#2EE6FF",
            violet: "#9B6BFF",
            gold: "#FFD36A",
            emerald: "#22c55e"
          },
          boxShadow: {
            glow: "0 12px 40px rgba(46,230,255,.12), 0 18px 60px rgba(155,107,255,.08)",
            luxe: "0 20px 70px rgba(2,6,20,.5)",
            card: "0 10px 30px rgba(2,6,20,.24)"
          },
          keyframes: {
            fadeUp: { "0%": { opacity: 0, transform: "translateY(12px)" }, "100%": { opacity: 1, transform: "translateY(0)" } }
          },
          animation: { fadeUp: "fadeUp .7s ease both" }
        }
      }
    }
  </script>

  <style>
    :root {
      --bg-1: #030515;
      --bg-2: #07102a;
    }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg,var(--bg-1) 0%, var(--bg-2) 60%, #02030a 100%);
      color: #e6eefc;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }
    .glass {
      background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.02));
      border: 1px solid rgba(255,255,255,.04);
      backdrop-filter: blur(8px);
    }
    .btn-primary {
      background: linear-gradient(90deg,#2EE6FF 0%, #2A72FF 45%, #9B6BFF 100%);
      color: #061021;
      font-weight: 700;
    }
    .badge-reco {
      background: linear-gradient(90deg, rgba(46,230,255,.14), rgba(155,107,255,.08));
      color: white;
      font-weight: 700;
    }
    .price-big { font-family: "Poppins", sans-serif; font-weight: 800; }
    .thin-line { height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,.06), transparent); }
    a { text-decoration: none; }
    .accent-text { background: linear-gradient(90deg,#cfe7ff,#58e7ff,#9B6BFF); -webkit-background-clip: text; background-clip: text; color: transparent; font-weight:800; }
    @media (max-width:640px) {
      .desktop-only { display: none; }
      .mobile-only { display: block; }
    }
    @media (min-width:641px) {
      .mobile-only { display: none; }
      .desktop-only { display: block; }
    }
  </style>
</head>
<body>

  <!-- NAV -->
<?php
include __DIR__. '/../src/layout/narbar.html';
?>

  <main class="pt-8 pb-20">
    <!-- HERO -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 text-center animate-fadeUp">
      <div class="glass rounded-2xl p-6 sm:p-10">
        <div class="max-w-3xl mx-auto">
          <div class="inline-block px-3 py-1 rounded-full badge-reco text-sm mb-4">Mejor relación calidad/precio</div>
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-tight">
            Elige el plan perfecto para tu operación multitienda
          </h1>
          <p class="mt-3 text-slate-300 max-w-xl mx-auto">
            Desde comercios locales hasta cadenas y enterprise. Precios y límites claros por tienda, con opciones de soporte y onboarding por sucursal.
          </p>

          <!-- Toggle mensual/anual -->
          <div class="mt-6 flex items-center justify-center gap-4">
            <div class="text-sm text-slate-300">Mensual</div>
            <div class="relative">
              <label for="billing" class="sr-only">Toggle Billing</label>
              <input id="billing" type="checkbox" class="peer sr-only" />
              <div class="w-14 h-8 rounded-full bg-white/6 flex items-center p-1 cursor-pointer" onclick="document.getElementById('billing').click()">
                <div id="toggleDot" class="w-6 h-6 rounded-full bg-white shadow transition-transform" style="transform: translateX(0)"></div>
              </div>
            </div>
            <div class="text-sm text-slate-300">Anual <span class="text-xs text-emerald ml-2">-20% (2 meses gratis)</span></div>
          </div>

          <div class="mt-6 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#planes" class="px-6 py-3 rounded-full btn-primary">Comenzar gratis</a>
            <a href="#comparador" class="px-6 py-3 rounded-full bg-white/6 text-white">Ver comparador</a>
          </div>
        </div>
      </div>
    </section>

    <!-- PRICING CARDS -->
    <section id="planes" class="max-w-6xl mx-auto px-4 sm:px-6 mt-10">
      <div class="grid gap-6 sm:grid-cols-3">
        <!-- Starter -->
        <article class="bg-white/5 glass p-5 rounded-2xl border border-white/6 shadow-card animate-fadeUp">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-slate-300">Starter</div>
              <h3 class="text-xl font-bold mt-1">Ideal para tiendas locales</h3>
            </div>
            <div class="text-sm text-slate-300">Más simple</div>
          </div>

          <div class="mt-5">
            <div class="text-sm text-slate-300">Por tienda / mes</div>
            <div class="mt-2 text-3xl text-white price-big" data-monthly="29" data-yearly="23">$<span class="price">29</span></div>
            <div class="text-xs text-slate-400 mt-1">Hasta 1 tienda · 1 bodegaje · Soporte básico</div>
          </div>

          <ul class="mt-5 space-y-2 text-slate-300 text-sm">
            <li>✅ Punto de Venta</li>
            <li>✅ Inventario básico</li>
            <li>✅ Facturación DIAN (limitado)</li>
            <li>✅ 1 canal de marketplace</li>
          </ul>

          <div class="mt-6">
            <a href="#" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl bg-white/6 text-white font-semibold">Comenzar</a>
          </div>

          <div class="mt-4 text-xs text-slate-400">Añadir tiendas extra: $12/tienda/mes</div>
        </article>

        <!-- Growth (recommended) -->
        <article class="relative bg-gradient-to-br from-[#07102a] to-[#041026] p-6 rounded-2xl border border-white/6 shadow-luxe animate-fadeUp">
          <div class="absolute -top-4 left-4 bg-amber-400 text-ink px-3 py-1 rounded-full text-xs font-bold">Recomendado</div>

          <div class="mt-2">
            <div class="text-sm text-slate-300">Growth</div>
            <h3 class="text-2xl font-extrabold mt-1">Escala tu operación</h3>
          </div>

          <div class="mt-4">
            <div class="text-sm text-slate-300">Por tienda / mes</div>
            <div class="mt-2 text-4xl text-white price-big" data-monthly="79" data-yearly="63">$<span class="price">79</span></div>
            <div class="text-xs text-slate-400 mt-1">Incluye hasta 5 tiendas · sincronización multi-bodega · facturación completa</div>
          </div>

          <ul class="mt-5 space-y-2 text-slate-200 text-sm">
            <li>✅ Multi-bodega y SKUs ilimitados</li>
            <li>✅ Integración MercadoLibre / Shopify / WooCommerce</li>
            <li>✅ Facturación DIAN completa</li>
            <li>✅ Alertas inteligentes y automatizaciones</li>
            <li>✅ Onboarding 48h por tienda (opcional)</li>
          </ul>

          <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="#" class="flex-1 px-4 py-3 rounded-xl btn-primary text-white text-center font-semibold">Probar Growth</a>
            <a href="#contacto" class="flex-1 px-4 py-3 rounded-xl bg-white/6 text-white text-center">Contactar ventas</a>
          </div>

          <div class="mt-4 text-xs text-slate-400">Tiendas extra: $10/tienda/mes · Soporte prioritario</div>
        </article>

        <!-- Enterprise -->
        <article class="bg-white/5 glass p-5 rounded-2xl border border-white/6 shadow-card animate-fadeUp">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm text-slate-300">Enterprise</div>
              <h3 class="text-xl font-bold mt-1">Para cadenas y retailers</h3>
            </div>
            <div class="text-sm text-slate-300">A medida</div>
          </div>

          <div class="mt-5">
            <div class="text-sm text-slate-300">Desde</div>
            <div class="mt-2 text-3xl text-white price-big" data-monthly="299" data-yearly="239">$<span class="price">299</span></div>
            <div class="text-xs text-slate-400 mt-1">Contrata por negociación · Soporte dedicado · SLA</div>
          </div>

          <ul class="mt-5 space-y-2 text-slate-300 text-sm">
            <li>✅ Onboarding dedicado</li>
            <li>✅ SLA personalizado</li>
            <li>✅ Integraciones a medida (ERP, logística)</li>
            <li>✅ Aislamiento por cliente y backups avanzados</li>
          </ul>

          <div class="mt-6">
            <a href="#contacto" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl btn-primary text-white font-semibold">Solicitar cotización</a>
          </div>

          <div class="mt-4 text-xs text-slate-400">Incluye evaluación de integración y plan de migración.</div>
        </article>
      </div>

      <!-- Price note -->
      <div class="mt-6 max-w-3xl mx-auto text-center text-sm text-slate-400">
        Precios en USD por tienda/mes. Facturación anual con descuento automático (20%). Impuestos locales no incluidos.
      </div>
    </section>

    <!-- COMPARADOR -->
    <section id="comparador" class="max-w-6xl mx-auto px-4 sm:px-6 mt-12">
      <div class="glass rounded-2xl p-6">
        <h2 class="text-xl font-bold">Comparador de planes</h2>
        <p class="mt-2 text-slate-300 text-sm">Selecciona las columnas que quieres comparar y ve rápidamente qué plan cubre tus necesidades.</p>

        <div class="mt-6 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-slate-300 text-left">
                <th class="p-3">Característica</th>
                <th class="p-3">Starter</th>
                <th class="p-3">Growth</th>
                <th class="p-3">Enterprise</th>
              </tr>
            </thead>
            <tbody class="text-slate-200">
              <tr class="border-t border-white/6">
                <td class="p-3">Tiendas incluidas</td>
                <td class="p-3">1</td>
                <td class="p-3">5</td>
                <td class="p-3">A convenir</td>
              </tr>
              <tr class="border-t border-white/6">
                <td class="p-3">Multi-bodega</td>
                <td class="p-3">Básico</td>
                <td class="p-3">Avanzado</td>
                <td class="p-3">Sí (SLA)</td>
              </tr>
              <tr class="border-t border-white/6">
                <td class="p-3">Integraciones Marketplaces</td>
                <td class="p-3">1</td>
                <td class="p-3">6+</td>
                <td class="p-3">Todas / a medida</td>
              </tr>
              <tr class="border-t border-white/6">
                <td class="p-3">Facturación DIAN</td>
                <td class="p-3">Limitada</td>
                <td class="p-3">Completa</td>
                <td class="p-3">Completa + Integración ERP</td>
              </tr>
              <tr class="border-t border-white/6">
                <td class="p-3">Soporte</td>
                <td class="p-3">Estándar</td>
                <td class="p-3">Prioritario</td>
                <td class="p-3">Dedicado</td>
              </tr>
              <tr class="border-t border-white/6">
                <td class="p-3">Onboarding por tienda</td>
                <td class="p-3">Opcional</td>
                <td class="p-3">Opcional (48h)</td>
                <td class="p-3">Incluido</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-6 flex gap-3">
          <a href="#planes" class="px-4 py-2 rounded-full btn-primary font-semibold">Volver a planes</a>
          <a href="#contacto" class="px-4 py-2 rounded-full bg-white/6">Solicitar demo por tienda</a>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="max-w-5xl mx-auto px-4 sm:px-6 mt-10">
      <div class="glass rounded-2xl p-6">
        <h3 class="text-lg font-bold">Preguntas frecuentes</h3>
        <div class="mt-4 space-y-3 text-slate-300">
          <details class="p-4 rounded-lg bg-white/3">
            <summary class="font-semibold cursor-pointer">¿Puedo añadir tiendas adicionales a mi plan?</summary>
            <div class="mt-2 text-sm text-slate-300">Sí — cada plan indica el precio por tienda extra. También podemos ofrecer descuentos por volumen para cadenas grandes.</div>
          </details>

          <details class="p-4 rounded-lg bg-white/3">
            <summary class="font-semibold cursor-pointer">¿Cómo funciona la facturación DIAN?</summary>
            <div class="mt-2 text-sm text-slate-300">Facturación electrónica integrada con CUFE / XML. Para planes Growth y Enterprise la integración es completa y automática.</div>
          </details>

          <details class="p-4 rounded-lg bg-white/3">
            <summary class="font-semibold cursor-pointer">¿Qué soporte ofrecen?</summary>
            <div class="mt-2 text-sm text-slate-300">Soporte en español por chat y email. Planes Growth y Enterprise incluyen soporte prioritario y SLA según contrato.</div>
          </details>
        </div>
      </div>
    </section>

    <!-- CTA final -->
    <section id="contacto" class="max-w-6xl mx-auto px-4 sm:px-6 mt-10">
      <div class="relative rounded-2xl overflow-hidden p-6 bg-gradient-to-br from-[#07102a] to-[#041026]">
        <div class="grid md:grid-cols-2 gap-6 items-center">
          <div>
            <h4 class="text-white text-2xl font-extrabold">Listo para escalar?</h4>
            <p class="mt-2 text-slate-300">Prueba gratis, agenda una demo por tienda o solicita cotización para tu cadena.</p>
            <div class="mt-4 flex gap-3">
              <a href="#" class="px-5 py-3 rounded-full btn-primary font-semibold">Comenzar prueba</a>
              <a href="#" class="px-5 py-3 rounded-full bg-white/6">Hablar con ventas</a>
            </div>
          </div>
          <div class="text-sm text-slate-300">
            <div class="mb-2">¿Necesitas cotización personalizada?</div>
            <ul class="space-y-2">
              <li>• Onboarding por tienda en 48h</li>
              <li>• Integración ERP y logística</li>
              <li>• SLA y soporte dedicado</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->

<?php
include __DIR__.'/../src/layout/footer.html';
?>
  <!-- SCRIPTS: toggle pricing (mensual/anual) + pequeñas interacciones -->
  <script>
    const billingCheckbox = document.getElementById('billing');
    const priceElements = document.querySelectorAll('[data-monthly]');

    function updatePrices(isYearly) {
      document.querySelectorAll('.price').forEach((el, idx) => {
        const parent = el.closest('[data-monthly]') || el.closest('[data-yearly]') || el.parentElement.parentElement;
        // find numeric values from cards (stored as attributes on nearest .price-big parent)
        const container = el.closest('[data-monthly]') || el.closest('article') || el.parentElement;
        let monthly = container.querySelector('[data-monthly]')?.getAttribute('data-monthly');
        let yearly = container.querySelector('[data-yearly]')?.getAttribute('data-yearly');
        // fallback: read attributes set on the .price element's nearest .price-big
        const priceBig = el.closest('.price-big') || el.parentElement.querySelector('.price-big');
        if (!monthly) monthly = priceBig?.getAttribute('data-monthly') || priceBig?.dataset?.monthly;
        if (!yearly) yearly = priceBig?.getAttribute('data-yearly') || priceBig?.dataset?.yearly;

        // If the specific price isn't found via attributes, fallback to dataset defined on article
        const article = el.closest('article');
        if (!monthly && article) monthly = article.querySelector('.price-big')?.dataset?.monthly;
        if (!yearly && article) yearly = article.querySelector('.price-big')?.dataset?.yearly;

        // Choose value
        const value = isYearly ? (yearly || monthly) : (monthly || yearly);
        if (!value) return;
        el.textContent = value;
      });
    }

    // Setup manual mapping initially from elements having data-monthly/data-yearly
    document.querySelectorAll('.price-big').forEach(pb => {
      // ensure .price inside has the correct initial monthly
      const monthly = pb.dataset.monthly;
      const priceSpan = pb.querySelector('.price') || pb.nextElementSibling?.querySelector('.price');
      if (monthly && priceSpan) priceSpan.textContent = monthly;
    });

    // Toggle visual dot
    function updateToggleVisual() {
      const dot = document.getElementById('toggleDot');
      if (!dot) return;
      if (billingCheckbox.checked) {
        dot.style.transform = 'translateX(28px)';
      } else {
        dot.style.transform = 'translateX(0px)';
      }
    }

    billingCheckbox.addEventListener('change', (e) => {
      const yearly = e.target.checked;
      // apply 20% discount visually (or use data-yearly if present)
      document.querySelectorAll('.price-big').forEach(pb => {
        const monthly = Number(pb.dataset.monthly);
        const yearlyAttr = Number(pb.dataset.yearly || Math.round(monthly * 0.8));
        const target = pb.querySelector('.price');
        if (target) target.textContent = yearly ? yearlyAttr : monthly;
      });
      updateToggleVisual();
    });

    // init
    updateToggleVisual();
  </script>
</body>
</html>