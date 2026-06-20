<?php require __DIR__ . '/../layout/portal_layout.php'; ?>

<div class="breadcrumb">
    <a href="index.php?route=portal.catalogo">Inicio</a> ›
    <a href="index.php?route=portal.carrito">Carrito</a> ›
    <span>Finalizar compra</span>
</div>

<h1 style="font-size:26px;font-weight:800;margin-bottom:24px;">✅ Finalizar compra</h1>

<form method="post" action="index.php?route=portal.checkout.post" id="checkout-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

        <!-- Datos -->
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="card" style="padding:28px;">
                <h3 style="font-size:17px;font-weight:700;margin-bottom:20px;">📋 Datos de contacto</h3>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($cliente['nombre']??$usuario['nombre']??'') ?>"></div>
                    <div class="form-group"><label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($cliente['apellido']??$usuario['apellido']??'') ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Correo</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['email']??'') ?>" readonly style="background:var(--gray-100);"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono']??'') ?>"></div>
                    <div class="form-group"><label class="form-label">Tipo documento</label>
                        <select name="tipo_documento" class="form-control">
                            <?php foreach(['CC','TI','CE','Pasaporte'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($cliente['tipo_documento']??'')===$t?'selected':'' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select></div>
                </div>
                <div class="form-group"><label class="form-label">N° documento</label>
                    <input type="text" name="numero_documento" class="form-control" value="<?= htmlspecialchars($cliente['numero_documento']??'') ?>"></div>
                <div class="form-group"><label class="form-label">Dirección de entrega *</label>
                    <input type="text" name="direccion" class="form-control" required value="<?= htmlspecialchars($cliente['direccion']??'') ?>" placeholder="Calle, número, barrio"></div>
            </div>
        </div>

        <!-- Resumen + cupón -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Cupón -->
            <div class="card" style="padding:22px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">🏷️ Cupón de descuento</h3>
                <?php if ($cuponActivo): ?>
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <div>
                            <div style="font-weight:700;color:#16a34a;"><?= htmlspecialchars($cuponActivo['codigo']) ?></div>
                            <div style="font-size:13px;color:#16a34a;">−$<?= number_format($descuento,2) ?> de descuento</div>
                        </div>
                        <button type="button" onclick="quitarCupon()" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:18px;">✕</button>
                    </div>
                <?php endif; ?>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="cupon-input" placeholder="Código de cupón" value="<?= htmlspecialchars($cuponActivo['codigo']??'') ?>"
                           style="flex:1;border:1.5px solid var(--gray-200);border-radius:10px;padding:9px 12px;font-size:14px;outline:none;">
                    <button type="button" onclick="aplicarCupon()" class="btn btn-outline btn-sm">Aplicar</button>
                </div>
                <div id="cupon-msg" style="font-size:13px;margin-top:8px;"></div>
            </div>

            <!-- Resumen pedido -->
            <div class="card" style="padding:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;">🧾 Tu pedido</h3>
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                    <?php foreach ($carrito as $pid => $item): ?>
                        <div style="display:flex;justify-content:space-between;font-size:14px;">
                            <span style="color:var(--gray-600);"><?= htmlspecialchars($item['nombre']) ?> ×<?= $item['cantidad'] ?></span>
                            <span style="font-weight:600;">$<?= number_format($item['precio']*$item['cantidad'],2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr style="border:none;border-top:1px solid var(--gray-200);margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;">
                    <span>Subtotal</span><span>$<?= number_format($subtotal,2) ?></span>
                </div>
                <?php if ($descuento > 0): ?>
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;color:#16a34a;">
                    <span>Descuento cupón</span><span>−$<?= number_format($descuento,2) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;font-size:22px;font-weight:800;color:var(--primary);">
                    <span>Total</span>
                    <span id="total-display">$<?= number_format($total,2) ?></span>
                </div>
            </div>

            <div class="card" style="padding:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:10px;">💳 Método de pago</h3>
                <div style="padding:12px;background:var(--gray-50);border-radius:8px;font-size:14px;color:var(--gray-600);">
                    💵 Pago contra entrega
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="font-size:17px;padding:16px;justify-content:center;border-radius:12px;">
                🚀 Confirmar pedido · <span id="btn-total">$<?= number_format($total,2) ?></span>
            </button>
        </div>
    </div>
</form>

<script>
const subtotal = <?= $subtotal ?>;
let descuentoActual = <?= $descuento ?>;

function aplicarCupon() {
    const codigo = document.getElementById('cupon-input').value.trim();
    if (!codigo) return;
    const msg = document.getElementById('cupon-msg');
    msg.textContent = 'Validando...';
    msg.style.color = '#6b7280';

    fetch('index.php?route=portal.cupon.validar&codigo=' + encodeURIComponent(codigo))
        .then(r => r.json())
        .then(data => {
            msg.textContent = data.mensaje;
            msg.style.color = data.ok ? '#16a34a' : '#dc2626';
            if (data.ok) {
                descuentoActual = data.descuento;
                actualizarTotal();
                if (data.descuento > 0) location.reload();
            }
        });
}

function quitarCupon() {
    fetch('index.php?route=portal.cupon.quitar').then(() => location.reload());
}

function actualizarTotal() {
    const total = Math.max(0, subtotal - descuentoActual);
    const fmt = '$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('total-display').textContent = fmt;
    document.getElementById('btn-total').textContent = fmt;
}

document.getElementById('cupon-input').addEventListener('keydown', e => { if(e.key==='Enter'){e.preventDefault();aplicarCupon();} });
</script>

<?php require __DIR__ . '/../layout/portal_footer.php'; ?>
