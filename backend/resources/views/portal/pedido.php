<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $detalle
 * @var array $pedido
 * @var array $valoradosIds
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<div class="breadcrumb">
    <a href="index.php?route=portal.pedidos">Mis pedidos</a> ›
    <span>Pedido #<?= $pedido['id'] ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
    <div class="card">
        <div style="padding:24px;border-bottom:1px solid var(--gray-200);">
            <h2 style="font-size:20px;font-weight:800;">Pedido #<?= $pedido['id'] ?></h2>
            <p style="font-size:13px;color:var(--gray-400);margin-top:4px;">Realizado el <?= date('d \d\e F \d\e Y',strtotime($pedido['fecha'])) ?></p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Producto</th><th>Precio unit.</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($detalle as $d):
                    $prodNombre = $d['producto_nombre'] ?? $d['nombre'] ?? '-';
                    $precio = (float)($d['precio_unitario'] ?? 0);
                    $cant   = (int)($d['cantidad'] ?? 0);
                    $sub    = (float)($d['subtotal'] ?? $precio * $cant);
                    $prodId = (int)($d['producto_id'] ?? 0);
                    $yaVal  = in_array($prodId, $valoradosIds);
                ?>
                    <tr>
                        <td style="font-weight:600;"><?= htmlspecialchars($prodNombre) ?></td>
                        <td>$<?= number_format($precio,2) ?></td>
                        <td><?= $cant ?></td>
                        <td style="font-weight:700;">$<?= number_format($sub,2) ?></td>
                        <td>
                            <?php if ($pedido['estado'] === 'completada' && $prodId > 0): ?>
                                <?php if ($yaVal): ?>
                                    <span style="font-size:12px;color:#f59e0b;">⭐ Valorado</span>
                                <?php else: ?>
                                    <a href="index.php?route=portal.valorar&producto_id=<?= $prodId ?>"
                                       style="font-size:12px;color:var(--primary);text-decoration:none;white-space:nowrap;">⭐ Valorar</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="padding:24px;display:flex;flex-direction:column;gap:14px;">
        <h3 style="font-size:17px;font-weight:700;">Resumen</h3>
        <div style="display:flex;justify-content:space-between;font-size:14px;">
            <span style="color:var(--gray-600);">Estado</span>
            <span class="status-badge status-<?= $pedido['estado'] ?>"><?= ucfirst($pedido['estado']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:14px;">
            <span style="color:var(--gray-600);">Tienda</span>
            <span style="font-weight:600;"><?= htmlspecialchars($pedido['tienda_nombre']??'-') ?></span>
        </div>
        <hr style="border:none;border-top:1px solid var(--gray-200);">
        <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:800;color:var(--primary);">
            <span>Total</span><span>$<?= number_format((float)$pedido['total'],2) ?></span>
        </div>
        <a href="index.php?route=portal.pedidos" class="btn btn-outline" style="justify-content:center;">← Volver</a>
    </div>
</div>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
