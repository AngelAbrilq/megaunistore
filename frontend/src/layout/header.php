<?php
// backend/resources/views/layouts/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Mega_Uni_Store | Tu Universo de Compras en un Solo Lugar</title>
  <meta name="description" content="Mega_Uni_Store es la multitienda líder que conecta calidad, variedad y velocidad en una sola plataforma." />

  <!-- Google Fonts (solo aquí) -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind CDN (desarrollo) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#3b82f6',
          }
        }
      }
    }
  </script>

  <!-- CSS propio central -->
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body class="selection:bg-cyan/30 selection:text-white">
  <!-- Background decoration (global) -->
  <div class="fixed inset-0 pointer-events-none bg-decor">
    <div class="absolute inset-0 grid-noise"></div>
    <div class="absolute -top-16 left-1/2 -translate-x-1/2 w-[900px] h-[900px] rounded-full bg-cyan/10 blur-3xl animate-pulseSoft"></div>
    <div class="absolute bottom-0 right-0 w-[640px] h-[640px] rounded-full bg-violet/10 blur-3xl animate-float"></div>
  </div>