<?php 
/**
 * Variables inyectadas por el controlador (via require/include con scope compartido).
 * @var array $pedidos
 */

require __DIR__ . '/../layout/portal_layout.php'; ?>

<h1 style="font-size:26px; font-weight:800; margin-bottom:24px;">📦 Mis pedidos</h1>

<?php if (empty($pedidos)): ?>
    <div class="empty-state">
        <div class="icon">📦</div>
        <h3>Aún no tienes pedidos</h3>
        <p>Cuando realices una compra, aparecerá aquí el historial de tus pedidos.</p>
        <a href="index.php?route=portal.catalogo" class="btn btn-primary">Ir al catálogo</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pedido #</th>
                        <th>Fecha</th>
                        <th>Tienda</th>
                        <th>Artículos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td style="font-weight:700;">#<?= $p['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                            <td><?= htmlspecialchars($p['tienda_nombre']) ?></td>
                            <td style="text-align:center;"><?= $p['total_items'] ?></td>
                            <td style="font-weight:700; color:var(--primary);">$<?= number_format((float)$p['total'], 2) ?></td>
                            <td>
                                <span class="status-badge status-<?= $p['estado'] ?>">
                                    <?= ucfirst($p['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?route=portal.pedido&id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Ver detalle</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
