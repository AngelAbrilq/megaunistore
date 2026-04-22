<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Mega_Uni_Store | Tu Universo de Compras en un Solo Lugar</title>
  <meta name="description" content="Mega_Uni_Store es la multitienda líder que conecta calidad, variedad y velocidad en una sola plataforma." />

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
            ink: "#060B1A",
            navy: "#0B1430",
            navy2: "#101D43",
            cyan: "#2EE6FF",
            violet: "#8B5CFF",
            gold: "#FFD36A",
          },
          boxShadow: {
            glow: "0 10px 40px rgba(46,230,255,.18), 0 16px 60px rgba(139,92,255,.12)",
            soft: "0 12px 40px rgba(12, 20, 48, .08)",
            card: "0 16px 40px rgba(10, 16, 40, .08)",
          },
          keyframes: {
            float: {
              "0%,100%": {
                transform: "translateY(0px)"
              },
              "50%": {
                transform: "translateY(-10px)"
              },
            },
            pulseSoft: {
              "0%,100%": {
                opacity: 0.45
              },
              "50%": {
                opacity: 0.9
              },
            },
            slideUp: {
              "0%": {
                opacity: 0,
                transform: "translateY(20px)"
              },
              "100%": {
                opacity: 1,
                transform: "translateY(0)"
              },
            },
            slowSpin: {
              "0%": {
                transform: "rotate(0deg)"
              },
              "100%": {
                transform: "rotate(360deg)"
              },
            },
          },
          animation: {
            float: "float 7s ease-in-out infinite",
            pulseSoft: "pulseSoft 4s ease-in-out infinite",
            slideUp: "slideUp .9s ease both",
            slowSpin: "slowSpin 28s linear infinite",
          },
        },
      },
    };
  </script>

  <style>
    :root {
      --bg-1: #050816;
      --bg-2: #08112a;
      --glass: rgba(255, 255, 255, 0.06);
      --stroke: rgba(255, 255, 255, 0.1);
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      font-family: "Inter", sans-serif;
      color: #dbe5ff;
      background:
        radial-gradient(circle at 10% 10%,
          rgba(46, 230, 255, 0.12),
          transparent 20%),
        radial-gradient(circle at 90% 20%,
          rgba(139, 92, 255, 0.12),
          transparent 22%),
        radial-gradient(circle at 50% 100%,
          rgba(255, 211, 106, 0.08),
          transparent 18%),
        linear-gradient(180deg, #040712 0%, #08112a 36%, #050816 100%);
      overflow-x: hidden;
    }

    .font-display {
      font-family: "Poppins", sans-serif;
    }

    .glass {
      background: linear-gradient(180deg,
          rgba(255, 255, 255, 0.08),
          rgba(255, 255, 255, 0.03));
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .panel {
      background: linear-gradient(180deg,
          rgba(255, 255, 255, 0.98),
          rgba(248, 250, 255, 0.96));
      border: 1px solid rgba(15, 23, 42, 0.08);
      box-shadow: 0 18px 50px rgba(10, 16, 40, 0.08);
    }

    .shadow-premium {
      box-shadow:
        0 18px 60px rgba(9, 17, 42, 0.08),
        0 8px 28px rgba(9, 17, 42, 0.06);
    }

    .text-gradient {
      background: linear-gradient(90deg,
          #ffffff 0%,
          #cfe7ff 20%,
          #6fdcff 40%,
          #8b5cff 72%,
          #ffd36a 100%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .accent-gradient {
      background: linear-gradient(90deg,
          #2ee6ff 0%,
          #8b5cff 50%,
          #ffd36a 100%);
    }

    .soft-gradient {
      background:
        radial-gradient(circle at top left,
          rgba(46, 230, 255, 0.2),
          transparent 28%),
        radial-gradient(circle at bottom right,
          rgba(139, 92, 255, 0.16),
          transparent 28%),
        linear-gradient(180deg,
          rgba(255, 255, 255, 0.03),
          rgba(255, 255, 255, 0));
    }

    .grid-noise {
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
      background-size: 42px 42px;
      opacity: 0.18;
      mask-image: linear-gradient(180deg,
          rgba(0, 0, 0, 0.8),
          transparent 94%);
    }

    .hover-rise {
      transition:
        transform 0.28s ease,
        box-shadow 0.28s ease,
        border-color 0.28s ease,
        background 0.28s ease;
    }

    .hover-rise:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 50px rgba(9, 17, 42, 0.12);
    }

    .glow-ring {
      position: relative;
    }

    .glow-ring::before {
      content: "";
      position: absolute;
      inset: -1px;
      border-radius: inherit;
      padding: 1px;
      background: linear-gradient(135deg,
          rgba(46, 230, 255, 0.6),
          rgba(139, 92, 255, 0.55),
          rgba(255, 211, 106, 0.45));
      -webkit-mask:
        linear-gradient(#000 0 0) content-box,
        linear-gradient(#000 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      pointer-events: none;
      opacity: 0.4;
    }

    .btn-primary {
      background: linear-gradient(90deg,
          #2ee6ff 0%,
          #2a72ff 35%,
          #8b5cff 100%);
      box-shadow: 0 10px 28px rgba(42, 114, 255, 0.22);
    }

    .btn-primary:hover {
      filter: saturate(1.06) brightness(1.03);
      transform: translateY(-1px);
      box-shadow: 0 14px 34px rgba(42, 114, 255, 0.26);
    }

    .btn-outline {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-1px);
    }

    .section-title {
      letter-spacing: -0.04em;
    }

    .thin-divider {
      height: 1px;
      background: linear-gradient(90deg,
          transparent,
          rgba(148, 163, 184, 0.35),
          transparent);
    }
  </style>
</head>

<body class="selection:bg-cyan/30 selection:text-white">
  <!-- Background decoration -->
  <div class="fixed inset-0 pointer-events-none">
    <div class="absolute inset-0 grid-noise"></div>
    <div
      class="absolute -top-16 left-1/2 -translate-x-1/2 w-[900px] h-[900px] rounded-full bg-cyan/10 blur-3xl animate-pulseSoft"></div>
    <div
      class="absolute bottom-0 right-0 w-[640px] h-[640px] rounded-full bg-violet/10 blur-3xl animate-float"></div>
  </div>

  <!-- NAVBAR -->
  <?php include __DIR__ . '/../src/layout/narbar.html'; ?>

  <main>
    <!-- HERO -->
    <section class="relative">
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 lg:pt-20 pb-10 lg:pb-14">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div class="relative z-10 animate-slideUp">
            <div
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 text-sm font-semibold text-white/90 shadow-glow">
              <span
                class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_14px_rgba(52,211,153,.8)]"></span>
              Multitienda escalable para negocios modernos
            </div>

            <h1
              class="mt-6 font-display font-black text-5xl sm:text-6xl lg:text-7xl leading-[1.02] tracking-tight text-gradient">
              Tu Universo de Compras
              <span class="block">en un Solo Lugar</span>
            </h1>

            <p
              class="mt-6 text-lg sm:text-xl text-slate-200/90 max-w-xl leading-relaxed">
              Mega_Uni_Store es la multitienda líder que conecta calidad con
              variedad, centraliza múltiples negocios y crea una experiencia
              de compra rápida, elegante y confiable.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
              <a
                href="#registro"
                class="inline-flex items-center justify-center gap-3 px-7 py-4 rounded-2xl text-white font-bold text-lg btn-primary transition hover-rise">
                <span>Prueba Gratis</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
              <a
                href="#contacto"
                class="inline-flex items-center justify-center gap-3 px-7 py-4 rounded-2xl text-white font-semibold btn-outline transition hover-rise">
                <span>Agenda una Demo</span>
                <span class="text-cyan text-xl">✦</span>
              </a>
            </div>

            <div class="mt-8 flex flex-wrap gap-3 text-sm text-slate-300">
              <div
                class="px-4 py-2 rounded-full bg-white/6 border border-white/10">
                Sin contratos de permanencia
              </div>
              <div
                class="px-4 py-2 rounded-full bg-white/6 border border-white/10">
                Soporte en español
              </div>
              <div
                class="px-4 py-2 rounded-full bg-white/6 border border-white/10">
                Listo para escalar
              </div>
            </div>

            <div class="mt-10 grid grid-cols-3 gap-3 max-w-xl">
              <div class="glass rounded-3xl p-4 border border-white/10">
                <div class="text-2xl font-extrabold text-white">+500</div>
                <div class="text-sm text-slate-300 mt-1">
                  Negocios confiando
                </div>
              </div>
              <div class="glass rounded-3xl p-4 border border-white/10">
                <div class="text-2xl font-extrabold text-white">24/7</div>
                <div class="text-sm text-slate-300 mt-1">Acompañamiento</div>
              </div>
              <div class="glass rounded-3xl p-4 border border-white/10">
                <div class="text-2xl font-extrabold text-white">100%</div>
                <div class="text-sm text-slate-300 mt-1">
                  Diseñado para crecer
                </div>
              </div>
            </div>
          </div>

          <!-- Hero mockup -->
          <div class="relative lg:pl-8 animate-slideUp">
            <div
              class="absolute -top-8 -left-10 w-32 h-32 rounded-full bg-cyan/15 blur-3xl"></div>
            <div
              class="absolute bottom-0 right-0 w-40 h-40 rounded-full bg-violet/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-[640px]">
              <div
                class="absolute inset-0 rounded-[2rem] bg-gradient-to-br from-white/15 via-white/8 to-white/5 blur-xl transform translate-y-6 scale-[.98]"></div>

              <div
                class="relative panel rounded-[2rem] overflow-hidden shadow-[0_24px_80px_rgba(8,12,34,.16)]">
                <div
                  class="p-4 sm:p-5 border-b border-slate-200/80 bg-white/85">
                  <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                      <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                      <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                      <span
                        class="w-3 h-3 rounded-full bg-emerald-400"></span>
                    </div>
                    <div
                      class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-500 text-sm border border-slate-200">
                      <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor">
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M16.5 10.5V7a4.5 4.5 0 10-9 0v3.5M5 10.5h14l1 10H4l1-10z" />
                      </svg>
                      app.megaunistore.com/dashboard
                    </div>
                  </div>
                </div>

                <div
                  class="p-5 sm:p-6 bg-gradient-to-br from-slate-50 via-white to-slate-50">
                  <div class="grid lg:grid-cols-[1.12fr_.88fr] gap-5">
                    <div
                      class="rounded-[1.75rem] bg-gradient-to-br from-[#f9fbff] to-white border border-slate-200 p-5 sm:p-6 shadow-[0_12px_34px_rgba(15,23,42,.06)]">
                      <div class="flex items-start justify-between gap-4">
                        <div>
                          <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan/10 text-slate-700 text-xs font-bold uppercase tracking-[.16em]">
                            Hola, Admin
                          </div>
                          <h3
                            class="mt-4 text-2xl sm:text-3xl font-black text-slate-950 tracking-tight">
                            Tu negocio, sincronizado.
                          </h3>
                          <p class="mt-2 text-slate-500">
                            Operación centralizada con una vista clara de
                            ventas, stock y tiendas.
                          </p>
                        </div>
                        <div
                          class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan to-violet text-white flex items-center justify-center shadow-glow">
                          <svg
                            class="w-6 h-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor">
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 6v12m6-6H6" />
                          </svg>
                        </div>
                      </div>

                      <div class="mt-5 grid sm:grid-cols-2 gap-3">
                        <div
                          class="rounded-2xl bg-white border border-slate-200 p-4">
                          <div
                            class="text-xs uppercase tracking-[.18em] text-slate-400 font-bold">
                            Ventas hoy
                          </div>
                          <div
                            class="mt-2 text-3xl font-black text-slate-950">
                            $4.2M
                          </div>
                          <div
                            class="mt-2 text-sm text-emerald-600 font-semibold">
                            +18% vs ayer
                          </div>
                        </div>
                        <div
                          class="rounded-2xl bg-white border border-slate-200 p-4">
                          <div
                            class="text-xs uppercase tracking-[.18em] text-slate-400 font-bold">
                            Pedidos listos
                          </div>
                          <div
                            class="mt-2 text-3xl font-black text-slate-950">
                            128
                          </div>
                          <div class="mt-2 text-sm text-slate-500">
                            En proceso de despacho
                          </div>
                        </div>
                      </div>

                      <div
                        class="mt-5 rounded-2xl bg-slate-950 p-4 text-white overflow-hidden relative">
                        <div
                          class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(46,230,255,.2),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(139,92,255,.18),transparent_35%)]"></div>
                        <div
                          class="relative flex items-center justify-between">
                          <div>
                            <div
                              class="text-xs text-slate-300 uppercase tracking-[.18em] font-semibold">
                              Monitoreo en tiempo real
                            </div>
                            <div class="mt-1 text-lg font-bold">
                              Inventario Multi-bodega
                            </div>
                          </div>
                          <div
                            class="px-3 py-1 rounded-full bg-emerald-400/15 text-emerald-300 text-xs font-bold border border-emerald-400/20">
                            Sincronizado
                          </div>
                        </div>
                        <div class="relative mt-4 grid grid-cols-3 gap-3">
                          <div
                            class="h-3 rounded-full bg-white/10 overflow-hidden">
                            <div
                              class="h-full w-[84%] rounded-full bg-gradient-to-r from-cyan to-violet"></div>
                          </div>
                          <div
                            class="h-3 rounded-full bg-white/10 overflow-hidden">
                            <div
                              class="h-full w-[68%] rounded-full bg-gradient-to-r from-violet to-gold"></div>
                          </div>
                          <div
                            class="h-3 rounded-full bg-white/10 overflow-hidden">
                            <div
                              class="h-full w-[52%] rounded-full bg-gradient-to-r from-cyan to-gold"></div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="space-y-4">
                      <div
                        class="rounded-[1.65rem] bg-white border border-slate-200 p-5 shadow-[0_12px_34px_rgba(15,23,42,.06)]">
                        <div class="flex items-start gap-3">
                          <div
                            class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                            ✓
                          </div>
                          <div>
                            <div class="font-bold text-slate-950">
                              Factura DIAN emitida
                            </div>
                            <div class="text-sm text-slate-500 mt-1">
                              Documento validado y enviado automáticamente.
                            </div>
                          </div>
                        </div>
                      </div>

                      <div
                        class="rounded-[1.65rem] bg-white border border-slate-200 p-5 shadow-[0_12px_34px_rgba(15,23,42,.06)]">
                        <div class="flex items-start gap-3">
                          <div
                            class="w-11 h-11 rounded-2xl bg-cyan/10 border border-cyan/20 flex items-center justify-center text-cyan font-bold">
                            3
                          </div>
                          <div>
                            <div class="font-bold text-slate-950">
                              3 bodegas sincronizadas
                            </div>
                            <div class="text-sm text-slate-500 mt-1">
                              Stock consolidado para evitar errores y
                              pérdidas.
                            </div>
                          </div>
                        </div>
                      </div>

                      <div
                        class="rounded-[1.65rem] bg-white border border-slate-200 p-5 shadow-[0_12px_34px_rgba(15,23,42,.06)]">
                        <div class="flex items-start gap-3">
                          <div
                            class="w-11 h-11 rounded-2xl bg-violet/10 border border-violet/20 flex items-center justify-center text-violet font-bold">
                            24
                          </div>
                          <div>
                            <div class="font-bold text-slate-950">
                              Alertas inteligentes 24/7
                            </div>
                            <div class="text-sm text-slate-500 mt-1">
                              Reabastecimiento, pedidos y comportamiento de
                              ventas.
                            </div>
                          </div>
                        </div>
                      </div>

                      <div
                        class="rounded-[1.65rem] bg-gradient-to-br from-slate-950 to-[#14214d] text-white p-5 shadow-[0_16px_40px_rgba(10,16,40,.16)]">
                        <div class="flex items-center justify-between">
                          <div>
                            <div class="text-sm text-slate-300">
                              Asistencia premium
                            </div>
                            <div class="mt-1 font-bold text-lg">
                              Te acompañamos en cada paso
                            </div>
                          </div>
                          <div
                            class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center">
                            ✦
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="absolute -right-5 top-16 sm:-right-10 sm:top-20 rounded-2xl glass px-4 py-3 shadow-premium border border-white/15 animate-float">
                <div class="text-xs text- -300">Clientes atendidos</div>
                <div class="text-2xl font-black text-white">+12K</div>
              </div>

              <div
                class="absolute left-0 bottom-8 -translate-x-5 sm:-translate-x-8 rounded-2xl bg-white text-slate-700 px-4 py-3 shadow-premium border border-slate-100">
                <div
                  class="text-xs font-bold uppercase tracking-[.16em] text-slate-400">
                  Tiempo de respuesta
                </div>
                <div class="text-xl font-black text-slate-950">0.8s</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TRUST STRIP -->
    <section class="relative mt-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="panel rounded-[2rem] px-5 py-5 sm:px-8 sm:py-6">
          <div class="grid md:grid-cols-5 gap-4 text-slate-600">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-2xl bg-cyan/10 text-cyan flex items-center justify-center">
                ◉
              </div>
              <div class="font-semibold">Facturación DIAN validada</div>
            </div>
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-2xl bg-violet/10 text-violet flex items-center justify-center">
                ◉
              </div>
              <div class="font-semibold">Datos seguros y aislados</div>
            </div>
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                ◉
              </div>
              <div class="font-semibold">Soporte en español</div>
            </div>
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center">
                ◉
              </div>
              <div class="font-semibold">+500 negocios confían</div>
            </div>
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center">
                ◉
              </div>
              <div class="font-semibold">Sin contratos de permanencia</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- WHO / WHAT -->
    <section id="quienes" class="py-20 lg:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
          <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/6 border border-white/10 text-sm font-semibold text-white/85">
            Qué hacemos y por qué importamos
          </div>
          <h2
            class="mt-5 font-display font-black section-title text-4xl sm:text-5xl text-white">
            Una plataforma diseñada para
            <span class="text-gradient">vender más y operar mejor</span>
          </h2>
          <p class="mt-5 text-lg text-slate-300 leading-relaxed">
            Mega_Uni_Store reúne módulos, tiendas, inventario, facturación,
            compras, finanzas y reportes en una arquitectura elegante y fácil
            de adoptar.
          </p>
        </div>

        <div class="mt-14 grid md:grid-cols-2 xl:grid-cols-4 gap-5">
          <article
            class="glass rounded-[1.8rem] p-6 border border-white/10 hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan/20 to-violet/20 border border-white/10 flex items-center justify-center text-2xl">
              🛒
            </div>
            <h3 class="mt-5 text-xl font-bold text-white">
              Multitienda unificada
            </h3>
            <p class="mt-3 text-slate-300 leading-relaxed">
              Administra múltiples tiendas desde un solo panel con una
              experiencia visual clara y profesional.
            </p>
          </article>

          <article
            class="glass rounded-[1.8rem] p-6 border border-white/10 hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet/20 to-gold/20 border border-white/10 flex items-center justify-center text-2xl">
              📦
            </div>
            <h3 class="mt-5 text-xl font-bold text-white">
              Stock e inventario
            </h3>
            <p class="mt-3 text-slate-300 leading-relaxed">
              Control inteligente de bodegas, sucursales, productos y alertas
              de reposición.
            </p>
          </article>

          <article
            class="glass rounded-[1.8rem] p-6 border border-white/10 hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400/20 to-cyan/20 border border-white/10 flex items-center justify-center text-2xl">
              🧾
            </div>
            <h3 class="mt-5 text-xl font-bold text-white">
              Facturación y cumplimiento
            </h3>
            <p class="mt-3 text-slate-300 leading-relaxed">
              Diseñada para operar con seguridad, trazabilidad y respaldo
              documental.
            </p>
          </article>

          <article
            class="glass rounded-[1.8rem] p-6 border border-white/10 hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-300/20 to-rose-300/20 border border-white/10 flex items-center justify-center text-2xl">
              📊
            </div>
            <h3 class="mt-5 text-xl font-bold text-white">
              Datos que convierten
            </h3>
            <p class="mt-3 text-slate-300 leading-relaxed">
              Reportes, métricas y decisiones con foco comercial real y visual
              de impacto.
            </p>
          </article>
        </div>
      </div>
    </section>

    <!-- INTEGRATIONS -->
    <section id="integraciones" class="py-18 lg:py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
          class="rounded-[2rem] overflow-hidden shadow-[0_24px_70px_rgba(8,12,34,.16)]">
          <div
            class="bg-gradient-to-r from-[#06102b] via-[#0a1436] to-[#09193d] px-6 py-8 sm:px-10 sm:py-10">
            <div
              class="flex flex-col lg:flex-row gap-8 lg:items-center lg:justify-between">
              <div class="max-w-xl">
                <div
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/8 text-white/85 border border-white/10 text-sm font-semibold">
                  Integraciones nativas
                </div>
                <h2
                  class="mt-5 text-3xl sm:text-4xl font-display font-black text-white tracking-tight">
                  Conecta tu negocio con el ecosistema que ya usas
                </h2>
                <p class="mt-4 text-slate-300 text-lg leading-relaxed">
                  Diseñado para integrarse con marketplaces, medios de pago,
                  logística y herramientas clave de operación comercial.
                </p>
              </div>

              <div
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:min-w-[420px]">
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  Factus / DIAN
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  ePayco
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  MercadoLibre
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  Shopify
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  WooCommerce
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  Stripe / Pasarelas
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  WhatsApp
                </div>
                <div
                  class="rounded-2xl bg-white/6 border border-white/10 px-4 py-3 text-white/85 text-center font-semibold">
                  API Abierta
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MODULES -->
    <section id="modulos" class="py-20 lg:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
          <h2
            class="font-display font-black section-title text-4xl sm:text-5xl text-white">
            Una plataforma completa para
            <span class="text-gradient">cada área de tu negocio</span>
          </h2>
          <p class="mt-5 text-lg text-slate-300">
            Módulos diseñados para trabajar juntos y evitar que tu operación
            dependa de herramientas desconectadas.
          </p>
        </div>

        <div class="mt-14 grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
              🛒
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Punto de Venta (POS)
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Cobro rápido, múltiples medios de pago, sesiones de caja y Corte
              Z con una interfaz ultrarrápida.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-2xl">
              📦
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Inventario
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Variantes, bodegas, alertas de stock y control de vencimientos
              desde una sola vista.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
              🧾
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Facturación Electrónica DIAN
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              CUFE, notas crédito y documentos con validación y automatización
              completa.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
              📈
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Ventas y Cotizaciones
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Cotizaciones, devoluciones, anulaciones y trazabilidad comercial
              con gran claridad.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
              🚚
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Compras y Proveedores
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Órdenes de compra, recepción en bodega y proveedores
              centralizados.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-cyan-50 text-cyan-700 flex items-center justify-center text-2xl">
              💰
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Finanzas
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Gastos, ingresos, análisis de cartera y control financiero por
              sesión y por área.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl">
              👥
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              RRHH y Nómina
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Gestión de personal, comisiones, asistencias y comprobantes
              listos para imprimir.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>

          <article
            class="bg-white rounded-[1.75rem] p-6 border border-slate-200 shadow-card hover-rise">
            <div
              class="w-14 h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-2xl">
              🔌
            </div>
            <h3 class="mt-5 text-xl font-extrabold text-slate-950">
              Integraciones y E-commerce
            </h3>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Sincroniza WooCommerce, Shopify, MercadoLibre y más con control
              bidireccional.
            </p>
            <a
              href="#"
              class="inline-flex mt-4 text-blue-600 font-bold hover:underline">Conocer más →</a>
          </article>
        </div>
      </div>
    </section>

    <!-- WHY CHOOSE US / COMPLIANCE DARK SECTION -->
    <section id="beneficios" class="py-24 relative overflow-hidden">
      <div
        class="absolute inset-0 bg-gradient-to-r from-[#0a1432] via-[#13225a] to-[#091126]"></div>
      <div
        class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top_left,rgba(46,230,255,.45),transparent_20%),radial-gradient(circle_at_top_right,rgba(255,211,106,.28),transparent_20%)]"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-[1fr_1.15fr] gap-10 items-center">
          <div class="max-w-xl">
            <div
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/8 border border-white/10 text-white/90 text-sm font-semibold">
              Diseñado para Colombia, conforme con la DIAN
            </div>
            <h2
              class="mt-6 font-display font-black text-4xl sm:text-5xl leading-tight text-white">
              No es un software genérico.
              <span class="block text-gradient">Está pensado para operar en serio.</span>
            </h2>
            <p class="mt-5 text-lg text-slate-200/90 leading-relaxed">
              Cada módulo fue creado para responder a la realidad comercial,
              tributaria y operativa de negocios en crecimiento. Aquí no solo
              vendes: controlas, automatizas y escalas.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
              <div
                class="px-4 py-2 rounded-full bg-white/8 border border-white/10 text-white/90">
                CUFE y XML firmado
              </div>
              <div
                class="px-4 py-2 rounded-full bg-white/8 border border-white/10 text-white/90">
                Rangos DIAN autorizados
              </div>
              <div
                class="px-4 py-2 rounded-full bg-white/8 border border-white/10 text-white/90">
                NIT validado automáticamente
              </div>
              <div
                class="px-4 py-2 rounded-full bg-white/8 border border-white/10 text-white/90">
                PUC y retenciones listas
              </div>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div
              class="glass rounded-[1.6rem] p-5 border border-white/10 hover-rise">
              <div class="flex items-start gap-4">
                <div
                  class="w-10 h-10 rounded-2xl bg-emerald-400/15 text-emerald-300 border border-emerald-400/20 flex items-center justify-center">
                  ✓
                </div>
                <div>
                  <h3 class="text-white font-bold text-lg">
                    Facturación validada
                  </h3>
                  <p class="mt-2 text-slate-300">
                    Documentos alineados con procesos reales y soporte de
                    envío automático.
                  </p>
                </div>
              </div>
            </div>

            <div
              class="glass rounded-[1.6rem] p-5 border border-white/10 hover-rise">
              <div class="flex items-start gap-4">
                <div
                  class="w-10 h-10 rounded-2xl bg-cyan/15 text-cyan border border-cyan/20 flex items-center justify-center">
                  ↻
                </div>
                <div>
                  <h3 class="text-white font-bold text-lg">
                    Inventario sincronizado
                  </h3>
                  <p class="mt-2 text-slate-300">
                    Control multicentro, bodegas, sucursales y stock en tiempo
                    real.
                  </p>
                </div>
              </div>
            </div>

            <div
              class="glass rounded-[1.6rem] p-5 border border-white/10 hover-rise">
              <div class="flex items-start gap-4">
                <div
                  class="w-10 h-10 rounded-2xl bg-violet/15 text-violet border border-violet/20 flex items-center justify-center">
                  ★
                </div>
                <div>
                  <h3 class="text-white font-bold text-lg">
                    Crecimiento comercial
                  </h3>
                  <p class="mt-2 text-slate-300">
                    Más ventas con menos fricción en operación y experiencia
                    del cliente.
                  </p>
                </div>
              </div>
            </div>

            <div
              class="glass rounded-[1.6rem] p-5 border border-white/10 hover-rise">
              <div class="flex items-start gap-4">
                <div
                  class="w-10 h-10 rounded-2xl bg-gold/15 text-gold border border-gold/20 flex items-center justify-center">
                  ⚡
                </div>
                <div>
                  <h3 class="text-white font-bold text-lg">Velocidad real</h3>
                  <p class="mt-2 text-slate-300">
                    Interfaz rápida, clara y precisa para operar sin estorbos.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SOLUTIONS BY INDUSTRY -->
    <section class="py-20 lg:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
          <h2
            class="font-display font-black section-title text-4xl sm:text-5xl text-white">
            Soluciones para cada tipo de
            <span class="text-gradient">negocio</span>
          </h2>
          <p class="mt-5 text-lg text-slate-300">
            Mega_Uni_Store se adapta a múltiples industrias con una
            presentación visual moderna y pensada para conversión.
          </p>
        </div>

        <div
          class="mt-12 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
          <div class="panel rounded-3xl p-5 text-center hover-rise">
            <div class="text-3xl">👗</div>
            <div class="mt-3 font-bold text-slate-950">Ropa</div>
          </div>
          <div class="panel rounded-3xl p-5 text-center hover-rise">
            <div class="text-3xl">🍽️</div>
            <div class="mt-3 font-bold text-slate-950">Restaurantes</div>
          </div>
          <div class="panel rounded-3xl p-5 text-center hover-rise">
            <div class="text-3xl">💇</div>
            <div class="mt-3 font-bold text-slate-950">Salones</div>
          </div>
          <div class="panel rounded-3xl p-5 text-center hover-rise">
            <div class="text-3xl">🏬</div>
            <div class="mt-3 font-bold text-slate-950">Distribución</div>
          </div>
          <div class="panel rounded-3xl p-5 text-center hover-rise">
            <div class="text-3xl">🛍️</div>
            <div class="mt-3 font-bold text-slate-950">Minimarket</div>
          </div>
          <div class="panel rounded-3xl p-5 text-center hover-rise">
            <div class="text-3xl">💼</div>
            <div class="mt-3 font-bold text-slate-950">Independientes</div>
          </div>
        </div>
      </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="testimonios" class="py-20 lg:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
          <h2
            class="font-display font-black section-title text-4xl sm:text-5xl text-white">
            Negocios como el tuyo ya usan
            <span class="text-gradient">Mega_Uni_Store</span>
          </h2>
          <p class="mt-5 text-lg text-slate-300">
            Una experiencia más ordenada, más clara y más rentable para
            tiendas reales.
          </p>
        </div>

        <div class="mt-14 grid lg:grid-cols-3 gap-5">
          <article
            class="bg-white rounded-[2rem] p-7 border border-slate-200 shadow-card hover-rise">
            <div class="text-5xl text-cyan/40">“</div>
            <p class="mt-3 text-slate-700 leading-relaxed text-lg italic">
              Antes gestionaba inventario y ventas por separado. Ahora tengo
              todo conectado, limpio y fácil de operar.
            </p>
            <div class="mt-6 flex items-center gap-4">
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan to-violet text-white flex items-center justify-center font-black">
                MG
              </div>
              <div>
                <div class="font-extrabold text-slate-950">
                  María González
                </div>
                <div class="text-sm text-slate-500">
                  Dueña de tienda de ropa • Bogotá
                </div>
              </div>
            </div>
          </article>

          <article
            class="bg-white rounded-[2rem] p-7 border border-slate-200 shadow-card hover-rise">
            <div class="text-5xl text-violet/40">“</div>
            <p class="mt-3 text-slate-700 leading-relaxed text-lg italic">
              El control comercial y los reportes me ayudaron a tomar
              decisiones con más seguridad y menos improvisación.
            </p>
            <div class="mt-6 flex items-center gap-4">
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-violet to-gold text-white flex items-center justify-center font-black">
                CH
              </div>
              <div>
                <div class="font-extrabold text-slate-950">
                  Carlos Herrera
                </div>
                <div class="text-sm text-slate-500">
                  Administrador • Medellín
                </div>
              </div>
            </div>
          </article>

          <article
            class="bg-white rounded-[2rem] p-7 border border-slate-200 shadow-card hover-rise">
            <div class="text-5xl text-emerald-400/40">“</div>
            <p class="mt-3 text-slate-700 leading-relaxed text-lg italic">
              La plataforma se siente premium. Mis clientes lo notan y mi
              operación quedó mucho más profesional.
            </p>
            <div class="mt-6 flex items-center gap-4">
              <div
                class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 to-cyan text-white flex items-center justify-center font-black">
                AP
              </div>
              <div>
                <div class="font-extrabold text-slate-950">Andrés Patiño</div>
                <div class="text-sm text-slate-500">
                  Dueño de cadena • Cali
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- FINAL CTA -->
    <section id="registro" class="py-20 lg:py-24">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
          class="relative overflow-hidden rounded-[2.25rem] px-6 py-14 sm:px-10 sm:py-16 text-center">
          <div
            class="absolute inset-0 bg-gradient-to-br from-[#091126] via-[#122251] to-[#060816]"></div>
          <div
            class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,rgba(46,230,255,.35),transparent_18%),radial-gradient(circle_at_bottom_right,rgba(139,92,255,.28),transparent_18%)]"></div>

          <div class="relative max-w-3xl mx-auto">
            <div
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/8 border border-white/10 text-white/90 text-sm font-semibold">
              La oportunidad que tu negocio estaba esperando
            </div>
            <h2
              class="mt-6 font-display font-black text-4xl sm:text-5xl lg:text-6xl text-white tracking-tight">
              Empieza hoy.
              <span class="block text-gradient">Tu negocio no puede esperar.</span>
            </h2>
            <p class="mt-6 text-lg sm:text-xl text-slate-300 leading-relaxed">
              Regístrate gratis y construye una operación más sólida, más
              clara y lista para crecer.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
              <a
                href="#registro"
                class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl text-white font-bold text-lg btn-primary transition hover-rise">
                Crear mi cuenta gratis
                <span>→</span>
              </a>
              <a
                href="#contacto"
                class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl text-white font-semibold btn-outline transition hover-rise">
                Agenda una demo personalizada
              </a>
            </div>
            <p class="mt-5 text-sm text-slate-400">
              ¿Tienes preguntas?
              <a
                href="#contacto"
                class="text-cyan font-semibold hover:underline">Habla con un experto →</a>
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <?php
  include __DIR__ . '/../src/layout/footer.html';

  ?>

  <script>
    const menuBtn = document.getElementById("menuBtn");
    const mobileMenu = document.getElementById("mobileMenu");
    const menuIcon = document.getElementById("menuIcon");

    let open = false;
    menuBtn?.addEventListener("click", () => {
      open = !open;
      mobileMenu.classList.toggle("hidden", !open);
      menuIcon.innerHTML = open ?
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />' :
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
    });

    // Smooth entrance emphasis for first render
    window.addEventListener("load", () => {
      document.querySelectorAll(".animate-slideUp").forEach((el, i) => {
        el.style.animationDelay = `${i * 80}ms`;
      });
    });
  </script>
</body>

</html>