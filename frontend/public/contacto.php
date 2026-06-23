<?php
// contacto.php — Ruta: frontend/public/contacto.php
include_once __DIR__ . '/../src/layout/header.php';
include_once __DIR__ . '/../src/layout/narbar.html';
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
            <span>contacto@megaunistore.com</span>
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

<script>
  (function () {
    const form = document.getElementById('contactForm');
    const sendBtn = document.getElementById('sendBtn');
    const msg = document.getElementById('formMessage');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      sendBtn.disabled = true;
      sendBtn.classList.add('opacity-70');
      setTimeout(() => {
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-70');
        msg.classList.remove('hidden');
        msg.classList.add('text-emerald-600');
        msg.textContent = 'Tu mensaje fue enviado correctamente. Te contactaremos pronto.';
        form.reset();
      }, 900);
    });
  })();
</script>

<?php
include_once __DIR__ . '/../src/layout/footer.html';
include_once __DIR__ . '/../src/layout/footer_scripts.php';
?>
