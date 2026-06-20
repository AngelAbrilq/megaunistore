</main>

<footer class="portal-footer">
    <p>© <?= date('Y') ?> Mega Uni Store · Todos los derechos reservados</p>
</footer>

<script>
// Cerrar dropdown al clic afuera
document.addEventListener('click', function(e) {
    document.querySelectorAll('.dropdown.open').forEach(function(d) {
        if (!d.closest('.user-menu').contains(e.target)) {
            d.classList.remove('open');
        }
    });
});
</script>
</body>
</html>
