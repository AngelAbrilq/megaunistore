<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var bool $comproProd
 * @var string $csrfToken
 * @var bool $enWishlist
 * @var array $producto
 * @var float $ratingPromedio
 * @var array $valoraciones
 * @var bool $yaValoro
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<div class="breadcrumb">
    <a href="index.php?route=portal.catalogo">Inicio</a> ›
    <?php if ($producto['categoria_nombre']): ?>
        <a href="index.php?route=portal.catalogo&categoria=<?= $producto['categoria_id'] ?>"><?= htmlspecialchars($producto['categoria_nombre']) ?></a> ›
    <?php endif; ?>
    <span><?= htmlspecialchars($producto['nombre']) ?></span>
</div>

<div class="card" style="padding:32px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start;margin-bottom:32px;">
    <!-- Imagen -->
    <div style="background:var(--gray-100);border-radius:var(--radius);aspect-ratio:1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
        <?php if ($producto['imagen_url']): ?>
            <img src="<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
            <span style="font-size:100px;">🛍️</span>
        <?php endif; ?>
    </div>

    <!-- Info -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <?php if ($producto['categoria_nombre']): ?>
            <span style="font-size:12px;color:var(--gray-400);text-transform:uppercase;"><?= htmlspecialchars($producto['categoria_nombre']) ?></span>
        <?php endif; ?>

        <h1 style="font-size:28px;font-weight:800;color:var(--gray-800);margin:0;"><?= htmlspecialchars($producto['nombre']) ?></h1>

        <!-- Rating promedio -->
        <?php if ($ratingPromedio > 0): ?>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:20px;color:#f59e0b;"><?= str_repeat('★',(int)round($ratingPromedio)) ?><?= str_repeat('☆',5-(int)round($ratingPromedio)) ?></span>
                <span style="font-size:14px;color:var(--gray-600);"><?= $ratingPromedio ?>/5 · <?= count($valoraciones) ?> valoración(es)</span>
            </div>
        <?php endif; ?>

        <div style="font-size:36px;font-weight:900;color:var(--primary);">$<?= number_format((float)$producto['precio_venta'],2) ?></div>

        <?php $stock = (int)$producto['stock']; ?>
        <div style="font-size:14px;font-weight:600;color:<?= $stock>10?'var(--success)':($stock>0?'var(--accent)':'var(--danger)') ?>;">
            <?= $stock>10?'✅ En stock':($stock>0?"⚠️ Solo $stock disponibles":'❌ Agotado') ?>
        </div>

        <?php if ($producto['descripcion']): ?>
            <p style="font-size:15px;color:var(--gray-600);line-height:1.7;margin:0;"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
        <?php endif; ?>

        <?php if ($stock > 0): ?>
            <form method="post" action="index.php?route=portal.carrito.agregar" style="display:flex;flex-direction:column;gap:12px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                <div>
                    <label class="form-label">Cantidad</label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="adj(-1)">−</button>
                        <input type="number" name="cantidad" id="qty" value="1" min="1" max="<?= $stock ?>" class="qty-input">
                        <button type="button" class="qty-btn" onclick="adj(1)">+</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="font-size:16px;padding:14px 28px;">🛒 Agregar al carrito</button>
            </form>
        <?php else: ?>
            <button class="btn btn-outline" disabled style="opacity:.5;cursor:not-allowed;font-size:16px;padding:14px 28px;">Agotado</button>
        <?php endif; ?>

        <!-- Wishlist -->
        <form method="post" action="index.php?route=portal.wishlist.toggle" style="margin:0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
            <input type="hidden" name="referer" value="portal.producto&id=<?= $producto['id'] ?>">
            <button type="submit" class="btn btn-outline" style="gap:8px;">
                <?= $enWishlist ? '❤️ En tus favoritos' : '🤍 Agregar a favoritos' ?>
            </button>
        </form>

        <a href="index.php?route=portal.catalogo" style="color:var(--primary);font-size:14px;text-decoration:none;">← Seguir comprando</a>
    </div>
</div>

<!-- Sección valoraciones -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

    <!-- Reseñas existentes -->
    <div>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:16px;">⭐ Valoraciones (<?= count($valoraciones) ?>)</h2>
        <?php if (empty($valoraciones)): ?>
            <div class="card" style="padding:32px;text-align:center;color:var(--gray-400);">
                <div style="font-size:40px;margin-bottom:12px;">💬</div>
                <p>Aún no hay valoraciones para este producto.</p>
                <?php if ($comproProd && !$yaValoro): ?>
                    <a href="index.php?route=portal.valorar&producto_id=<?= $producto['id'] ?>" class="btn btn-primary" style="margin-top:12px;">Sé el primero en valorarlo</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($valoraciones as $v): ?>
                    <div class="card" style="padding:18px;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                            <div>
                                <span style="font-weight:700;font-size:15px;"><?= htmlspecialchars(trim($v['cliente_nombre'])) ?></span>
                                <span style="margin-left:12px;color:#f59e0b;font-size:16px;"><?= str_repeat('★',(int)$v['estrellas']) ?><?= str_repeat('☆',5-(int)$v['estrellas']) ?></span>
                            </div>
                            <span style="font-size:12px;color:var(--gray-400);"><?= date('d/m/Y',strtotime($v['created_at'])) ?></span>
                        </div>
                        <?php if ($v['comentario']): ?>
                            <p style="margin:0;font-size:14px;color:var(--gray-600);line-height:1.6;"><?= htmlspecialchars($v['comentario']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel valorar -->
    <div>
        <?php if ($comproProd && !$yaValoro): ?>
            <div class="card" style="padding:20px;background:linear-gradient(135deg,#eff6ff,#f5f3ff);">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">¿Ya lo tienes? ¡Valóralo!</h3>
                <p style="font-size:13px;color:var(--gray-600);margin-bottom:14px;">Compraste este producto. Cuéntanos qué te pareció.</p>
                <a href="index.php?route=portal.valorar&producto_id=<?= $producto['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center;">⭐ Dejar valoración</a>
            </div>
        <?php elseif ($yaValoro): ?>
            <div class="card" style="padding:20px;background:#f0fdf4;border:1px solid #bbf7d0;">
                <p style="margin:0;font-size:14px;color:var(--success);font-weight:600;">✅ Ya valoraste este producto. ¡Gracias!</p>
            </div>
        <?php elseif (!$comproProd): ?>
            <div class="card" style="padding:20px;background:var(--gray-50);">
                <p style="margin:0;font-size:13px;color:var(--gray-400);">Solo los clientes que han comprado este producto pueden valorarlo.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function adj(d) {
    const i = document.getElementById('qty');
    i.value = Math.min(<?= $stock ?>, Math.max(1, parseInt(i.value) + d));
}
</script>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
