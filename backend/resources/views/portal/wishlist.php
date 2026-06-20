<?php require __DIR__ . '/../layout/portal_layout.php'; ?>

<h1 style="font-size:26px;font-weight:800;margin-bottom:24px;">❤️ Mis favoritos</h1>

<?php if (empty($productos)): ?>
    <div class="empty-state">
        <div class="icon">❤️</div>
        <h3>Tu lista de favoritos está vacía</h3>
        <p>Usa el botón 🤍 en los productos para guardarlos aquí.</p>
        <a href="index.php?route=portal.catalogo" class="btn btn-primary">Ver catálogo</a>
    </div>
<?php else: ?>
    <div class="product-grid">
    <?php foreach ($productos as $p):
        $stock = (int)$p['stock'];
    ?>
        <div class="product-card">
            <a href="index.php?route=portal.producto&id=<?= $p['id'] ?>" style="text-decoration:none;color:inherit;">
                <div class="product-img">
                    <?php if ($p['imagen_url']): ?>
                        <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <?php else: ?>
                        <div class="product-img-placeholder">🛍️</div>
                    <?php endif; ?>
                </div>
                <div class="product-body">
                    <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
                    <div class="product-price"><?= $p['precio_venta'] ? '$'.number_format((float)$p['precio_venta'],2) : '<span style="color:var(--gray-400)">No disponible</span>' ?></div>
                    <div class="product-stock <?= $stock>0?'ok':'out' ?>"><?= $stock>0?'✅ Disponible':'❌ Agotado' ?></div>
                </div>
            </a>
            <div class="product-actions" style="gap:6px;">
                <?php if ($stock > 0 && $p['precio_venta']): ?>
                    <form method="post" action="index.php?route=portal.carrito.agregar" style="flex:1;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">🛒 Agregar</button>
                    </form>
                <?php else: ?>
                    <div style="flex:1;"></div>
                <?php endif; ?>
                <!-- Quitar de wishlist -->
                <form method="post" action="index.php?route=portal.wishlist.toggle">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="referer" value="portal.wishlist">
                    <button type="submit" title="Quitar de favoritos"
                            style="width:36px;height:36px;border-radius:8px;border:1.5px solid var(--gray-200);background:#fee2e2;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">
                        ❤️
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
