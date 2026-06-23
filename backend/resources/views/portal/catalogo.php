<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var string $busqueda
 * @var int|null $categoriaId
 * @var array $categorias
 * @var string $csrfToken
 * @var string $orden
 * @var array $productos
 * @var array $wishlistIds
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<div style="display:flex;gap:24px;align-items:flex-start;">

    <!-- Sidebar filtros -->
    <aside style="width:230px;flex-shrink:0;display:flex;flex-direction:column;gap:16px;">

        <!-- Categorías -->
        <div class="card" style="padding:18px;">
            <h3 style="font-size:13px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Categorías</h3>
            <nav style="display:flex;flex-direction:column;gap:2px;">
                <a href="index.php?route=portal.catalogo<?= $busqueda ? '&q='.urlencode($busqueda) : '' ?>"
                   style="display:block;padding:7px 10px;border-radius:8px;text-decoration:none;font-size:14px;color:<?= $categoriaId===null?'var(--primary)':'var(--gray-800)' ?>;background:<?= $categoriaId===null?'#eff6ff':'transparent' ?>;font-weight:<?= $categoriaId===null?'700':'400' ?>;">🏷️ Todas</a>
                <?php foreach ($categorias as $cat): ?>
                    <a href="index.php?route=portal.catalogo&categoria=<?= $cat['id'] ?><?= $busqueda?'&q='.urlencode($busqueda):'' ?>"
                       style="display:block;padding:7px 10px;border-radius:8px;text-decoration:none;font-size:14px;color:<?= $categoriaId===(int)$cat['id']?'var(--primary)':'var(--gray-800)' ?>;background:<?= $categoriaId===(int)$cat['id']?'#eff6ff':'transparent' ?>;font-weight:<?= $categoriaId===(int)$cat['id']?'700':'400' ?>;">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Filtro precio -->
        <div class="card" style="padding:18px;">
            <h3 style="font-size:13px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Precio</h3>
            <form method="get" action="index.php" id="precio-form">
                <input type="hidden" name="route" value="portal.catalogo">
                <?php if ($categoriaId): ?><input type="hidden" name="categoria" value="<?= $categoriaId ?>"> <?php endif; ?>
                <?php if ($busqueda): ?><input type="hidden" name="q" value="<?= htmlspecialchars($busqueda) ?>"> <?php endif; ?>
                <input type="hidden" name="orden" value="<?= htmlspecialchars($orden) ?>">
                <div style="display:flex;gap:8px;margin-bottom:10px;">
                    <div style="flex:1;">
                        <label style="font-size:11px;color:var(--gray-400);">Mínimo</label>
                        <input type="number" name="precio_min" value="<?= htmlspecialchars($_GET['precio_min'] ?? '') ?>" min="0" step="100"
                               placeholder="$0" style="width:100%;border:1.5px solid var(--gray-200);border-radius:8px;padding:7px;font-size:13px;box-sizing:border-box;">
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:11px;color:var(--gray-400);">Máximo</label>
                        <input type="number" name="precio_max" value="<?= htmlspecialchars($_GET['precio_max'] ?? '') ?>" min="0" step="100"
                               placeholder="Sin límite" style="width:100%;border:1.5px solid var(--gray-200);border-radius:8px;padding:7px;font-size:13px;box-sizing:border-box;">
                    </div>
                </div>
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Aplicar</button>
                <?php if (isset($_GET['precio_min']) || isset($_GET['precio_max'])): ?>
                    <a href="index.php?route=portal.catalogo<?= $categoriaId?'&categoria='.$categoriaId:'' ?><?= $busqueda?'&q='.urlencode($busqueda):'' ?>"
                       style="display:block;text-align:center;font-size:12px;color:var(--gray-400);margin-top:6px;text-decoration:none;">✕ Quitar filtro precio</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Wishlist link -->
        <a href="index.php?route=portal.wishlist" class="btn btn-outline" style="justify-content:center;gap:8px;">❤️ Mis favoritos</a>
    </aside>

    <!-- Contenido principal -->
    <div style="flex:1;min-width:0;">

        <!-- Barra superior: resultados + ordenar -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--gray-400);">
                <?= count($productos) ?> producto(s)<?= $busqueda ? ' para "<strong>'.htmlspecialchars($busqueda).'</strong>"' : '' ?>
            </span>
            <form method="get" action="index.php" style="display:flex;align-items:center;gap:8px;">
                <input type="hidden" name="route" value="portal.catalogo">
                <?php if ($categoriaId): ?><input type="hidden" name="categoria" value="<?= $categoriaId ?>"> <?php endif; ?>
                <?php if ($busqueda): ?><input type="hidden" name="q" value="<?= htmlspecialchars($busqueda) ?>"> <?php endif; ?>
                <?php if (isset($_GET['precio_min'])): ?><input type="hidden" name="precio_min" value="<?= $_GET['precio_min'] ?>"> <?php endif; ?>
                <?php if (isset($_GET['precio_max'])): ?><input type="hidden" name="precio_max" value="<?= $_GET['precio_max'] ?>"> <?php endif; ?>
                <label style="font-size:13px;color:var(--gray-600);">Ordenar:</label>
                <select name="orden" onchange="this.form.submit()" style="border:1.5px solid var(--gray-200);border-radius:8px;padding:6px 10px;font-size:13px;outline:none;">
                    <option value="nombre"      <?= $orden==='nombre'      ?'selected':'' ?>>Nombre A-Z</option>
                    <option value="precio_asc"  <?= $orden==='precio_asc'  ?'selected':'' ?>>Precio: menor a mayor</option>
                    <option value="precio_desc" <?= $orden==='precio_desc' ?'selected':'' ?>>Precio: mayor a menor</option>
                    <option value="rating"      <?= $orden==='rating'      ?'selected':'' ?>>Mejor valorados</option>
                    <option value="nuevo"       <?= $orden==='nuevo'       ?'selected':'' ?>>Más nuevos</option>
                </select>
            </form>
        </div>

        <?php if (empty($productos)): ?>
            <div class="empty-state"><div class="icon">📦</div><h3>Sin productos</h3><p>No hay productos que coincidan con los filtros.</p>
                <a href="index.php?route=portal.catalogo" class="btn btn-outline">Ver todo</a></div>
        <?php else: ?>
            <div class="product-grid">
            <?php foreach ($productos as $p):
                $enWish = in_array($p['id'], $wishlistIds);
                $stock  = (int)$p['stock'];
                $rating = (float)($p['rating_promedio'] ?? 0);
                $nVal   = (int)($p['total_valoraciones'] ?? 0);
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
                            <?php if ($p['categoria_nombre']): ?>
                                <span class="product-category"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
                            <?php endif; ?>
                            <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
                            <?php if ($rating > 0): ?>
                                <div style="display:flex;align-items:center;gap:4px;font-size:13px;">
                                    <span style="color:#f59e0b;"><?= str_repeat('★', (int)round($rating)) ?><?= str_repeat('☆', 5-(int)round($rating)) ?></span>
                                    <span style="color:var(--gray-400);"><?= $rating ?> (<?= $nVal ?>)</span>
                                </div>
                            <?php endif; ?>
                            <div class="product-price">$<?= number_format((float)$p['precio_venta'], 2) ?></div>
                            <div class="product-stock <?= $stock>10?'ok':($stock>0?'low':'out') ?>">
                                <?= $stock>10?'✅ Disponible':($stock>0?"⚠️ Últimas $stock":"❌ Agotado") ?>
                            </div>
                        </div>
                    </a>
                    <div class="product-actions" style="gap:6px;">
                        <?php if ($stock > 0): ?>
                            <form method="post" action="index.php?route=portal.carrito.agregar" style="flex:1;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="cantidad" value="1">
                                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">🛒 Agregar</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm" disabled style="flex:1;opacity:.5;cursor:not-allowed;">Agotado</button>
                        <?php endif; ?>
                        <!-- Wishlist -->
                        <form method="post" action="index.php?route=portal.wishlist.toggle">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="referer" value="portal.catalogo">
                            <button type="submit" title="<?= $enWish?'Quitar de favoritos':'Agregar a favoritos' ?>"
                                    style="width:36px;height:36px;border-radius:8px;border:1.5px solid var(--gray-200);background:<?= $enWish?'#fee2e2':'var(--white)' ?>;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">
                                <?= $enWish?'❤️':'🤍' ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
