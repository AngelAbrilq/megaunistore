<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $carrito
 * @var string $csrfToken
 * @var float $subtotal
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<h1 style="font-size:26px; font-weight:800; margin-bottom:24px;">🛒 Mi carrito</h1>

<?php if (empty($carrito)): ?>
    <div class="empty-state">
        <div class="icon">🛒</div>
        <h3>Tu carrito está vacío</h3>
        <p>Agrega productos desde el catálogo para empezar tu compra.</p>
        <a href="index.php?route=portal.catalogo" class="btn btn-primary">Ir al catálogo</a>
    </div>
<?php else: ?>
    <form method="post" action="index.php?route=portal.carrito.actualizar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">

            <!-- Items -->
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrito as $productoId => $item): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:14px;">
                                            <div style="width:52px; height:52px; border-radius:8px; background:var(--gray-100); display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0; overflow:hidden;">
                                                <?php if ($item['imagen_url']): ?>
                                                    <img src="<?= htmlspecialchars($item['imagen_url']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                                <?php else: ?>
                                                    🛍️
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div style="font-weight:600;"><?= htmlspecialchars($item['nombre']) ?></div>
                                                <a href="index.php?route=portal.producto&id=<?= $productoId ?>" style="font-size:12px; color:var(--primary); text-decoration:none;">Ver producto</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight:600;">$<?= number_format($item['precio'], 2) ?></td>
                                    <td>
                                        <div class="qty-control">
                                            <button type="button" class="qty-btn" onclick="adjQty(this,-1)">−</button>
                                            <input type="number" name="cantidades[<?= $productoId ?>]"
                                                   value="<?= $item['cantidad'] ?>" min="0" class="qty-input"
                                                   onchange="updateRow(this)">
                                            <button type="button" class="qty-btn" onclick="adjQty(this,1)">+</button>
                                        </div>
                                    </td>
                                    <td style="font-weight:700; color:var(--primary);" class="sub-<?= $productoId ?>">
                                        $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
                                    </td>
                                    <td>
                                        <button type="button" onclick="removeItem(<?= $productoId ?>)"
                                                style="background:none; border:none; cursor:pointer; color:var(--danger); font-size:18px;" title="Eliminar">🗑️</button>
                                        <input type="hidden" name="cantidades[<?= $productoId ?>]" value="<?= $item['cantidad'] ?>" id="qty-<?= $productoId ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px; display:flex; gap:10px;">
                    <button type="submit" class="btn btn-outline btn-sm">↻ Actualizar carrito</button>
                    <a href="index.php?route=portal.catalogo" class="btn btn-outline btn-sm">← Seguir comprando</a>
                </div>
            </div>

            <!-- Resumen -->
            <div class="card" style="padding:24px; display:flex; flex-direction:column; gap:16px;">
                <h3 style="font-size:18px; font-weight:700;">Resumen del pedido</h3>
                <div style="display:flex; justify-content:space-between; font-size:15px;">
                    <span style="color:var(--gray-600);">Subtotal</span>
                    <span id="total-display" style="font-weight:700;">$<?= number_format($subtotal, 2) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:15px;">
                    <span style="color:var(--gray-600);">Envío</span>
                    <span style="color:var(--success); font-weight:600;">Gratis</span>
                </div>
                <hr style="border:none; border-top:1px solid var(--gray-200);">
                <div style="display:flex; justify-content:space-between; font-size:20px; font-weight:800;">
                    <span>Total</span>
                    <span id="total-display2" style="color:var(--primary);">$<?= number_format($subtotal, 2) ?></span>
                </div>
                <a href="index.php?route=portal.checkout" class="btn btn-primary" style="justify-content:center; font-size:16px; padding:14px;">
                    ✅ Finalizar compra
                </a>
                <form method="post" action="index.php?route=portal.carrito.vaciar" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <button type="submit" class="btn btn-sm" style="background:none; color:var(--danger); width:100%; justify-content:center;" onclick="return confirm('¿Vaciar el carrito?')">
                        🗑️ Vaciar carrito
                    </button>
                </form>
            </div>
        </div>
    </form>
<?php endif; ?>

<script>
const precios = <?= json_encode(array_map(fn($id,$i) => [$id, $i['precio']], array_keys($carrito), $carrito)) ?>;
const precioMap = {};
<?php foreach ($carrito as $pid => $item): ?>
precioMap[<?= $pid ?>] = <?= $item['precio'] ?>;
<?php endforeach; ?>

function adjQty(btn, d) {
    const input = btn.parentElement.querySelector('input');
    input.value = Math.max(0, parseInt(input.value) + d);
    input.dispatchEvent(new Event('change'));
}
function updateRow(input) {
    const pid = input.name.match(/\[(\d+)\]/)[1];
    const qty = parseInt(input.value) || 0;
    const sub = document.querySelector('.sub-' + pid);
    if (sub) sub.textContent = '$' + (precioMap[pid] * qty).toFixed(2);
}
function removeItem(pid) {
    const inputs = document.querySelectorAll('input[name="cantidades[' + pid + ']"]');
    inputs.forEach(i => i.value = 0);
    document.querySelector('tr:has(.sub-' + pid + ')').style.opacity = '.3';
}
</script>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
