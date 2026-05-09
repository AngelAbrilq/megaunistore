<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<?php

// contacto.php
// Ruta: frontend/public/contacto.php

// 1 Head global (fuentes + tailwind + estilos compartidos)
// Crea ../src/layout/head.php si no existe y centraliza aquí fonts, tailwind config y estilos repetidos.

include_once __DIR__ . '/../src/layout/narbar.html';
//head.php
?>

<body>
  
<main class="site-contact page-contact bg-white text-slate-900">
  <!-- HERO -->
  <section class="contact-hero max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
    <h1 class="hero-title text-4xl sm:text-5xl font-extrabold tracking-tight">Hablemos de tu negocio</h1>
    <p class="hero-subtitle mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
      Nuestro equipo está listo para ayudarte a impulsar tu operación multitienda. Respuesta en menos de 24 horas hábiles.
    </p>
  </section>

  <!-- CONTENIDO PRINCIPAL: GRID 2 columnas -->
  <section class="contact-grid max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 pb-20">
    <!-- COL IZQUIERDA: INFO -->
    <aside class="col-span-4 space-y-6">
      <!-- Tarjeta de contacto (gradient) -->
      <div class="contact-info-card rounded-2xl overflow-hidden p-6 bg-gradient-to-br from-[#5b7fff] to-[#9b6bff] text-white shadow-card">
        <h2 class="text-xl font-bold mb-4">Información de Contacto</h2>

        <ul class="space-y-4 text-sm">
          <li class="flex items-start gap-3">
            <div class="icon w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
              <!-- icon mail -->
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8"></path></svg>
            </div>
            <div>
              <div class="text-sm font-semibold">Email</div>
              <div class="text-xs opacity-90">contacto    @megai_unistore.com</div>
            </div>
          </li>

          <li class="flex items-start gap-3">
            <div class="icon w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
              <!-- icon phone -->
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92V21a1 1 0 01-1.09 1 19 19 0 01-8.63-3.21 19 19 0 01-6-6A19 19 0 011 3.09 1 1 0 012 2h4.09a1 1 0 011 .75 11 11 0 00.7 2.6 1 1 0 01-.24 1.05l-1.1 1.1a16 16 0 006 6l1.1-1.1a1 1 0 011.05-.24 11 11 0 002.6.7 1 1 0 01.75 1V22z"/></svg>
            </div>
            <div>
              <div class="text-sm font-semibold">Teléfono</div>
              <div class="text-xs opacity-90">+57 310 285 9780</div>
            </div>
          </li>

          <li class="flex items-start gap-3">
            <div class="icon w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
              <!-- icon whatsapp -->
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-4-.9L3 21l1.9-5.6A8.38 8.38 0 014.8 11.5 8.5 8.5 0 0112 3a8.5 8.5 0 019 8.5z"/></svg>
            </div>
            <div>
              <div class="text-sm font-semibold">WhatsApp</div>
              <div class="text-xs opacity-90">Chatear ahora →</div>
            </div>
          </li>

          <li class="flex items-start gap-3">
            <div class="icon w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
              <!-- icon location -->
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s8-4.5 8-10a8 8 0 10-16 0c0 5.5 8 10 8 10z"/></svg>
            </div>
            <div>
              <div class="text-sm font-semibold">Ubicación</div>
              <div class="text-xs opacity-90">Bogotá, Colombia</div>
            </div>
          </li>
        </ul>
      </div>

      <!-- Horarios de Atención -->
      <div class="contact-hours-card rounded-2xl p-6 bg-white border border-slate-100 shadow-sm">
        <h3 class="text-lg font-semibold">Horarios de Atención</h3>
        <ul class="mt-4 text-sm text-slate-600 space-y-3">
          <li class="flex justify-between"><span>Lunes - Viernes</span><span class="font-medium">8:00 AM – 6:00 PM</span></li>
          <li class="flex justify-between"><span>Sábados</span><span class="font-medium">9:00 AM – 2:00 PM</span></li>
          <li class="flex justify-between"><span>Domingos</span><span class="font-medium text-rose-500">Cerrado</span></li>
        </ul>
        <p class="mt-3 text-xs text-slate-400">Soporte 24/7: Para clientes con plan Omnicanal Total</p>
      </div>

      <!-- CTA WhatsApp destacado -->
      <div class="contact-whatsapp-card rounded-2xl p-6 bg-emerald-50 border border-emerald-100 shadow-sm">
        <h4 class="text-md font-semibold text-emerald-800">¿Prefieres WhatsApp?</h4>
        <p class="mt-2 text-sm text-emerald-700">En Colombia, el 70% de nuestros clientes prefieren resolver dudas por WhatsApp. Respuesta inmediata en horario hábil.</p>
        <a href="https://wa.me/573102859780" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-3 justify-center w-full btn-whatsapp bg-emerald-600 text-white font-semibold py-3 rounded-lg shadow-md hover:brightness-95">Abrir WhatsApp</a>
      </div>
    </aside>

    <!-- COL DERECHA: FORMULARIO -->
    <div class="col-span-8">
      <div class="contact-form-card rounded-2xl p-8 bg-white border border-slate-100 shadow-lg">
        <h2 class="text-2xl font-extrabold mb-2">¿Tienes preguntas?</h2>
        <p class="text-sm text-slate-600 mb-6">Completa el formulario y nuestro equipo te responderá en menos de 24 horas.</p>

        <form id="contactForm" class="space-y-4" method="post" action="/api/contact/send">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700">Nombre Completo *</label>
              <input name="name" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="Juan Pérez">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Correo Electrónico *</label>
              <input type="email" name="email" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-3 text-sm" placeholder="juan@ejemplo.com">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700">Teléfono *</label>
              <input name="phone" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-3 text-sm" placeholder="3001234567">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Empresa</label>
              <input name="company" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-3 text-sm" placeholder="Nombre de tu empresa">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700">Mensaje *</label>
            <textarea name="message" required rows="6" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-3 text-sm" placeholder="Cuéntanos cómo podemos ayudarte..."></textarea>
          </div>

          <div class="mt-6">
            <button id="sendBtn" type="submit" class="w-full inline-flex items-center justify-center gap-3 py-4 rounded-xl bg-blue-600 text-white font-semibold shadow-xl hover:brightness-105">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 2L11 13"></path></svg>
              Enviar Mensaje
            </button>
          </div>

          <p id="formMessage" class="text-sm mt-3 text-slate-500 hidden"></p>
        </form>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="contact-faq max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
    <h3 class="text-center text-2xl font-extrabold mb-8">Preguntas Frecuentes</h3>

    <div class="space-y-3">
      <details class="faq-item bg-white rounded-lg border border-slate-100 p-4">
        <summary class="flex justify-between items-center cursor-pointer font-medium">¿Cuánto tiempo toma la implementación?</summary>
        <div class="mt-3 text-sm text-slate-600">Dependiendo del plan y número de sucursales, entre 48h (onboarding básico) y 4 semanas para integraciones completas.</div>
      </details>

      <details class="faq-item bg-white rounded-lg border border-slate-100 p-4">
        <summary class="flex justify-between items-center cursor-pointer font-medium">¿Ofrecen capacitación para el equipo?</summary>
        <div class="mt-3 text-sm text-slate-600">Sí, ofrecemos capacitación remota y opcional presencial según el plan contratado.</div>
      </details>

      <details class="faq-item bg-white rounded-lg border border-slate-100 p-4">
        <summary class="flex justify-between items-center cursor-pointer font-medium">¿Puedo migrar mis datos desde otro sistema?</summary>
        <div class="mt-3 text-sm text-slate-600">Sí, realizamos migraciones y contamos con herramientas de importación CSV y conectores a ERPs comunes.</div>
      </details>
    </div>
  </section>
</main>

<?php
// FOOTER global
@include_once __DIR__ . '/../src/layout/footer.php';

// SCRIPTS globales
@include_once __DIR__ . '/../src/layout/scripts.php';
?>

<!-- Si no tienes scripts.php creado, añade este bloque (temporal o mover a scripts.php) -->
<script>
  // Manejo de formulario (ejemplo simple)
  (function(){
    const form = document.getElementById('contactForm');
    const sendBtn = document.getElementById('sendBtn');
    const msg = document.getElementById('formMessage');

    if (!form) return;

    form.addEventListener('submit', function(e){
      e.preventDefault();
      sendBtn.disabled = true;
      sendBtn.classList.add('opacity-70');

      // Simulación de envío (reemplaza con fetch() a tu endpoint)
      setTimeout(() => {
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-70');
        msg.classList.remove('hidden');
        msg.classList.add('text-emerald-600');
        msg.textContent = 'Tu mensaje fue enviado correctamente. Te contactaremos pronto.';
        form.reset();
      }, 900);
    });

    // Hacer clic en details para accesibilidad: no requerido, pero mejora UX
    document.querySelectorAll('.faq-item summary').forEach(s => {
      s.addEventListener('click', () => {
        // opción para animaciones o tracking
      });
    });
  })();
</script>

</body>
</html>
<?php
// contacto.php
// Ruta: frontend/public/contacto.php

// 1. Configuración y lógica (opcional)
$pageTitle = "Contacto - Megai Unistore";

// 2. Importar el Head (Aquí debe ir el <!DOCTYPE>, <html>, <head> y CSS)
@include_once __DIR__ . '/../src/layout/head.php';

// 3. Importar el Navbar/Header
@include_once __DIR__ . '/../src/layout/header.php';
?>

<main class="site-contact page-contact bg-white text-slate-900">
  <section class="contact-hero max-w-6xl mx-auto px-4 py-16 text-center">
    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">Hablemos de tu negocio</h1>
    <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
      Nuestro equipo está listo para ayudarte a impulsar tu operación multitienda.
    </p>
  </section>

  <section class="contact-grid max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-8 pb-20">
    
    <aside class="col-span-1 lg:col-span-4 space-y-6">
      <div class="rounded-2xl p-6 bg-gradient-to-br from-[#5b7fff] to-[#9b6bff] text-white shadow-lg">
        <h2 class="text-xl font-bold mb-4">Información de Contacto</h2>
        <ul class="space-y-4 text-sm">
          <li class="flex items-center gap-3">
             <span>contacto@megai_unistore.com</span>
          </li>
          <li class="flex items-center gap-3">
             <span>+57 310 285 9780</span>
          </li>
        </ul>
      </div>

      <div class="rounded-2xl p-6 bg-white border border-slate-100 shadow-sm">
        <h3 class="text-lg font-semibold">Horarios de Atención</h3>
        <ul class="mt-4 text-sm text-slate-600 space-y-2">
          <li class="flex justify-between"><span>Lunes - Viernes</span><b>8:00 AM – 6:00 PM</b></li>
          <li class="flex justify-between text-rose-500"><span>Domingos</span><b>Cerrado</b></li>
        </ul>
      </div>
    </aside>

    <div class="col-span-1 lg:col-span-8">
      <div class="rounded-2xl p-8 bg-white border border-slate-100 shadow-xl">
        <h2 class="text-2xl font-extrabold mb-6">¿Tienes preguntas?</h2>
        <form id="contactForm" class="space-y-4" method="POST" action="/api/contact/send">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="name" required placeholder="Nombre Completo" class="w-full border rounded-lg p-3">
            <input type="email" name="email" required placeholder="Correo" class="w-full border rounded-lg p-3">
          </div>
          <textarea name="message" rows="5" placeholder="Mensaje..." class="w-full border rounded-lg p-3"></textarea>
          <button id="sendBtn" type="submit" class="w-full py-4 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition">
            Enviar Mensaje
          </button>
          <p id="formMessage" class="hidden text-center font-medium"></p>
        </form>
      </div>
    </div>
  </section>

  <section class="max-w-4xl mx-auto px-4 pb-24">
    <h3 class="text-center text-2xl font-extrabold mb-8">Preguntas Frecuentes</h3>
    <div class="space-y-3">
      <details class="bg-white border rounded-lg p-4 cursor-pointer">
        <summary class="font-medium">¿Cuánto tiempo toma la implementación?</summary>
        <p class="mt-2 text-slate-600 text-sm">Entre 48h y 4 semanas según el plan.</p>
      </details>
    </div>
  </section>
</main>

<?php
// 4. Footer y Scripts
@include_once __DIR__ . '/../src/layout/footer.html';
@include_once __DIR__ . '/../src/layout/scripts.php';
?>