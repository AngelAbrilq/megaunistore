<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $csrfToken
 * @var array $producto
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<div class="breadcrumb">
    <a href="index.php?route=portal.pedidos">Mis pedidos</a> ›
    <a href="index.php?route=portal.producto&id=<?= $producto['id'] ?>"><?= htmlspecialchars($producto['nombre']) ?></a> ›
    <span>Valorar</span>
</div>

<div style="max-width:560px;margin:0 auto;">
    <h1 style="font-size:24px;font-weight:800;margin-bottom:24px;">⭐ Valorar producto</h1>

    <div class="card" style="padding:28px;">
        <div style="display:flex;gap:16px;align-items:center;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--gray-200);">
            <div style="width:60px;height:60px;border-radius:12px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;overflow:hidden;">
                <?php if ($producto['imagen_url']): ?>
                    <img src="<?= htmlspecialchars($producto['imagen_url']) ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>🛍️<?php endif; ?>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;"><?= htmlspecialchars($producto['nombre']) ?></div>
                <div style="font-size:13px;color:var(--gray-400);">$<?= number_format((float)$producto['precio_venta'],2) ?></div>
            </div>
        </div>

        <form method="post" action="index.php?route=portal.valorar.post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">

            <!-- Selector de estrellas -->
            <div class="form-group">
                <label class="form-label">Tu calificación *</label>
                <div style="display:flex;gap:8px;margin-top:4px;" id="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" data-val="<?= $i ?>" onclick="setStar(<?= $i ?>)"
                                style="font-size:32px;background:none;border:none;cursor:pointer;padding:0;color:#d1d5db;transition:color .1s;" class="star-btn">★</button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="estrellas" id="estrellas-input" value="5">
                <p id="star-label" style="font-size:13px;color:var(--gray-400);margin-top:6px;">Excelente</p>
            </div>

            <div class="form-group">
                <label class="form-label">Comentario <small style="color:var(--gray-400);">(opcional)</small></label>
                <textarea name="comentario" class="form-control" rows="4" placeholder="Cuéntanos qué te pareció el producto..." style="resize:vertical;"></textarea>
            </div>

            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">Publicar valoración</button>
                <a href="index.php?route=portal.producto&id=<?= $producto['id'] ?>" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
const labels = {1:'Muy malo',2:'Malo',3:'Regular',4:'Bueno',5:'Excelente'};
function setStar(n) {
    document.getElementById('estrellas-input').value = n;
    document.getElementById('star-label').textContent = labels[n];
    document.querySelectorAll('.star-btn').forEach((b,i) => {
        b.style.color = i < n ? '#f59e0b' : '#d1d5db';
    });
}
setStar(5);
</script>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
