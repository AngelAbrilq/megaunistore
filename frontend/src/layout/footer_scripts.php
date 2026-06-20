<?php
// Scripts globales del frontend
?>
  <script>
    // Script del botón de menú (extraído de index.php)
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

    // Smooth entrance emphasis for first render (extraído de index.php)
    window.addEventListener("load", () => {
      document.querySelectorAll(".animate-slideUp").forEach((el, i) => {
        el.style.animationDelay = `${i * 80}ms`;
      });
    });
  </script>
</body>
</html>