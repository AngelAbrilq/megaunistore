<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function e_create_venta(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$productosJson = json_encode(
    $productosPorTienda,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);

$clientesJson = json_encode(
    $clientesPorTienda,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);
?>

<style>
        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 34px 20px;
        }

        h1 {
            margin: 0 0 8px;
            color: #172554;
        }

        p {
            margin: 0 0 24px;
            color: #6b7280;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
            margin-bottom: 20px;
        }

        .card h2 {
            margin: 0 0 16px;
            color: #172554;
            font-size: 20px;
        }

        .alert {
            padding: 13px 14px;
            border-radius: 14px;
            margin-bottom: 18px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #1f2937;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        input:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .help {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
            line-height: 1.4;
        }

        .items-header,
        .item-row {
            display: grid;
            grid-template-columns: 1.5fr 0.45fr 0.55fr 0.55fr 0.35fr;
            gap: 12px;
            align-items: end;
        }

        .items-header {
            color: #172554;
            font-weight: 900;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .item-row {
            border: 1px solid #dbe3ef;
            border-radius: 18px;
            padding: 14px;
            background: #fbfdff;
            margin-bottom: 12px;
        }

        .item-row .form-group {
            margin-bottom: 0;
        }

        .btn {
            display: inline-flex;
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            justify-content: center;
            align-items: center;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e0e7ff;
            color: #1e3a8a;
        }

        .btn-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-light {
            background: #f8fafc;
            color: #172554;
            border: 1px solid #dbe3ef;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .summary-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px;
        }

        .summary-item small {
            display: block;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.04em;
        }

        .summary-item strong {
            color: #172554;
            font-size: 20px;
        }

        /* ── Cupón ─────────────────────────────────────────── */
        .cupon-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .cupon-row input[type="text"] {
            flex: 1;
            min-width: 160px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cupon-resultado {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            display: none;
        }

        .cupon-resultado.ok {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            display: block;
        }

        .cupon-resultado.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            display: block;
        }

        .summary-item.descuento strong {
            color: #166534;
        }

        @media (max-width: 900px) {
            .form-grid,
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .items-header {
                display: none;
            }

            .item-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

<div class="container">
        <h1>Nueva venta</h1>
        <p>Registra una venta y descuenta automáticamente el inventario disponible.</p>

        <?php if ($flash !== null): ?>
            <div class="alert">
                <?= e_create_venta($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?route=ventas.store" method="POST" id="ventaForm">
            <input type="hidden" name="csrf_token" value="<?= e_create_venta($csrfToken) ?>">

            <section class="card">
                <h2>Datos de la venta</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="tienda_id">Tienda *</label>
                        <select id="tienda_id" name="tienda_id" required>
                            <option value="">Seleccionar tienda</option>

                            <?php foreach ($tiendas as $tienda): ?>
                                <?php if ($tienda === null) { continue; } ?>
                                <option value="<?= e_create_venta((string) $tienda['id']) ?>">
                                    <?= e_create_venta($tienda['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help">Los productos disponibles dependen de la tienda seleccionada.</span>
                    </div>

                    <div class="form-group">
                        <label for="metodo_pago_id">Método de pago *</label>
                        <select id="metodo_pago_id" name="metodo_pago_id" required>
                            <option value="">Seleccionar método</option>

                            <?php foreach ($metodosPago as $metodo): ?>
                                <option value="<?= e_create_venta((string) $metodo['id']) ?>">
                                    <?= e_create_venta($metodo['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label for="cliente_id">Cliente</label>
                        <select id="cliente_id" name="cliente_id">
                            <option value="">— Cliente general (sin cuenta) —</option>
                        </select>
                        <span class="help">Opcional. Los clientes disponibles dependen de la tienda seleccionada.</span>
                    </div>

                    <div class="form-group full">
                        <label for="referencia">Referencia de pago</label>
                        <input type="text" id="referencia" name="referencia" maxlength="100" placeholder="Opcional. Ej: comprobante, transferencia, voucher.">
                    </div>
                </div>
            </section>

            <section class="card">
                <h2>Productos</h2>

                <div class="items-header">
                    <div>Producto</div>
                    <div>Cantidad</div>
                    <div>Precio</div>
                    <div>Subtotal</div>
                    <div></div>
                </div>

                <div id="itemsContainer"></div>

                <button type="button" class="btn btn-light" id="addItemBtn">
                    Agregar producto
                </button>
            </section>

            <!-- ── Cupón de descuento ──────────────────────────────────────── -->
            <section class="card">
                <h2>Cupón de descuento</h2>

                <div class="cupon-row">
                    <input type="text" id="cuponCodigo" placeholder="Ej: VERANO10" maxlength="50" autocomplete="off">
                    <button type="button" class="btn btn-secondary" id="cuponAplicarBtn">Validar cupón</button>
                    <button type="button" class="btn btn-danger" id="cuponQuitarBtn" style="display:none">Quitar cupón</button>
                </div>

                <div class="cupon-resultado" id="cuponResultado"></div>

                <!-- Valores que se envían con el formulario -->
                <input type="hidden" name="cupon_id"        id="cuponIdInput"        value="">
                <input type="hidden" name="descuento_cupon" id="descuentoCuponInput" value="0">
            </section>

            <!-- ── Resumen estimado ────────────────────────────────────────── -->
            <section class="card">
                <h2>Resumen estimado</h2>

                <div class="summary-grid">
                    <div class="summary-item">
                        <small>Subtotal</small>
                        <strong id="subtotalPreview">$0.00</strong>
                    </div>

                    <div class="summary-item descuento" id="descuentoItem" style="display:none">
                        <small>Descuento cupón</small>
                        <strong id="descuentoPreview">-$0.00</strong>
                    </div>

                    <div class="summary-item">
                        <small>Impuesto</small>
                        <strong>Calculado al guardar</strong>
                    </div>

                    <div class="summary-item">
                        <small>Total estimado</small>
                        <strong id="totalPreview">$0.00</strong>
                    </div>

                    <div class="summary-item">
                        <small>Stock</small>
                        <strong id="stockPreview">Validado</strong>
                    </div>
                </div>
            </section>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Registrar venta</button>
                <button type="button" class="btn btn-secondary" onclick="loadContent('ventas.index', true)">Cancelar</button>
            </div>
        </form>
    </main>

    <script>
        const productosPorTienda = <?= $productosJson ?: '{}' ?>;
        const clientesPorTienda  = <?= $clientesJson  ?: '{}' ?>;

        const tiendaSelect      = document.getElementById('tienda_id');
        const clienteSelect     = document.getElementById('cliente_id');
        const itemsContainer    = document.getElementById('itemsContainer');
        const addItemBtn        = document.getElementById('addItemBtn');
        const subtotalPreview   = document.getElementById('subtotalPreview');
        const totalPreview      = document.getElementById('totalPreview');
        const stockPreview      = document.getElementById('stockPreview');

        // Cupón
        const cuponCodigo       = document.getElementById('cuponCodigo');
        const cuponAplicarBtn   = document.getElementById('cuponAplicarBtn');
        const cuponQuitarBtn    = document.getElementById('cuponQuitarBtn');
        const cuponResultado    = document.getElementById('cuponResultado');
        const cuponIdInput      = document.getElementById('cuponIdInput');
        const descuentoCuponInput = document.getElementById('descuentoCuponInput');
        const descuentoItem     = document.getElementById('descuentoItem');
        const descuentoPreview  = document.getElementById('descuentoPreview');

        let descuentoCuponActual = 0; // valor numérico del descuento activo

        function money(value) {
            const number = Number(value || 0);
            return '$' + number.toFixed(2);
        }

        function getProductosActuales() {
            const tiendaId = tiendaSelect.value;
            return productosPorTienda[tiendaId] || [];
        }

        function productoOptions() {
            const productos = getProductosActuales();

            let html = '<option value="">Seleccionar producto</option>';

            productos.forEach(function (producto) {
                const stock = producto.stock !== null ? producto.stock : 0;
                const codigo = producto.codigo_barras ? ' - ' + producto.codigo_barras : '';

                html += `
                    <option 
                        value="${producto.id}" 
                        data-precio="${producto.precio_venta}" 
                        data-stock="${stock}"
                        data-unidad="${producto.unidad_simbolo || ''}"
                    >
                        ${producto.nombre}${codigo} | Stock: ${stock}
                    </option>
                `;
            });

            return html;
        }

        function crearFila() {
            const row = document.createElement('div');
            row.className = 'item-row';

            row.innerHTML = `
                <div class="form-group">
                    <label>Producto *</label>
                    <select name="producto_id[]" class="producto-select" required>
                        ${productoOptions()}
                    </select>
                    <span class="help stock-help">Selecciona un producto.</span>
                </div>

                <div class="form-group">
                    <label>Cantidad *</label>
                    <input type="number" name="cantidad[]" class="cantidad-input" min="0.01" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Precio</label>
                    <input type="text" class="precio-preview" readonly value="$0.00">
                </div>

                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="text" class="subtotal-preview" readonly value="$0.00">
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger remove-item">Quitar</button>
                </div>
            `;

            itemsContainer.appendChild(row);
            enlazarFila(row);
            recalcular();
        }

        function enlazarFila(row) {
            const productoSelect = row.querySelector('.producto-select');
            const cantidadInput = row.querySelector('.cantidad-input');
            const removeBtn = row.querySelector('.remove-item');

            productoSelect.addEventListener('change', recalcular);
            cantidadInput.addEventListener('input', recalcular);

            removeBtn.addEventListener('click', function () {
                row.remove();
                recalcular();
            });
        }

        function refrescarClientes() {
            const tiendaId = tiendaSelect.value;
            const clientes = clientesPorTienda[tiendaId] || [];

            clienteSelect.innerHTML = '<option value="">— Cliente general (sin cuenta) —</option>';

            clientes.forEach(function (c) {
                const apellido = c.apellido ? ' ' + c.apellido : '';
                const doc      = c.numero_documento ? ' (' + c.numero_documento + ')' : '';
                const opt      = document.createElement('option');
                opt.value      = c.id;
                opt.textContent = c.nombre + apellido + doc;
                clienteSelect.appendChild(opt);
            });
        }

        function refrescarOpcionesProductos() {
            const rows = itemsContainer.querySelectorAll('.item-row');

            rows.forEach(function (row) {
                const select = row.querySelector('.producto-select');
                select.innerHTML = productoOptions();

                row.querySelector('.cantidad-input').value = '';
                row.querySelector('.precio-preview').value = '$0.00';
                row.querySelector('.subtotal-preview').value = '$0.00';
                row.querySelector('.stock-help').textContent = 'Selecciona un producto.';
            });

            recalcular();
        }

        function recalcular() {
            let subtotalGeneral = 0;
            let stockOk = true;

            itemsContainer.querySelectorAll('.item-row').forEach(function (row) {
                const select = row.querySelector('.producto-select');
                const selected = select.options[select.selectedIndex];
                const cantidadInput = row.querySelector('.cantidad-input');
                const precioPreview = row.querySelector('.precio-preview');
                const subtotalInput = row.querySelector('.subtotal-preview');
                const stockHelp = row.querySelector('.stock-help');

                const precio = Number(selected?.dataset?.precio || 0);
                const stock = Number(selected?.dataset?.stock || 0);
                const unidad = selected?.dataset?.unidad || '';
                const cantidad = Number(cantidadInput.value || 0);

                const subtotal = precio * cantidad;

                precioPreview.value = money(precio);
                subtotalInput.value = money(subtotal);

                if (select.value) {
                    stockHelp.textContent = 'Stock disponible: ' + stock + ' ' + unidad;

                    if (cantidad > stock) {
                        stockHelp.textContent = 'Stock insuficiente. Disponible: ' + stock + ' ' + unidad;
                        stockHelp.style.color = '#991b1b';
                        stockOk = false;
                    } else {
                        stockHelp.style.color = '#6b7280';
                    }
                } else {
                    stockHelp.textContent = 'Selecciona un producto.';
                    stockHelp.style.color = '#6b7280';
                }

                subtotalGeneral += subtotal;
            });

            subtotalPreview.textContent = money(subtotalGeneral);

            const totalEstimado = Math.max(0, subtotalGeneral - descuentoCuponActual);
            totalPreview.textContent = money(totalEstimado);

            stockPreview.textContent = stockOk ? 'Validado' : 'Revisar';
            stockPreview.style.color = stockOk ? '#166534' : '#991b1b';
        }

        tiendaSelect.addEventListener('change', function () {
            refrescarOpcionesProductos();
            refrescarClientes();

            if (itemsContainer.children.length === 0 && tiendaSelect.value) {
                crearFila();
            }
        });

        addItemBtn.addEventListener('click', function () {
            if (!tiendaSelect.value) {
                alert('Primero selecciona una tienda.');
                return;
            }

            crearFila();
        });

        // ── Cupón ──────────────────────────────────────────────────────────────

        function mostrarResultadoCupon(texto, tipo) {
            cuponResultado.textContent = texto;
            cuponResultado.className = 'cupon-resultado ' + tipo;
        }

        function aplicarCupon(cuponIdVal, descuentoVal, codigoVal) {
            descuentoCuponActual     = descuentoVal;
            cuponIdInput.value       = cuponIdVal;
            descuentoCuponInput.value = descuentoVal.toFixed(2);

            descuentoPreview.textContent = '-' + money(descuentoVal);
            descuentoItem.style.display  = '';

            cuponCodigo.disabled    = true;
            cuponAplicarBtn.style.display = 'none';
            cuponQuitarBtn.style.display  = '';

            mostrarResultadoCupon(
                '✅ Cupón "' + codigoVal + '" aplicado — descuento: ' + money(descuentoVal),
                'ok'
            );

            recalcular();
        }

        function quitarCupon() {
            descuentoCuponActual      = 0;
            cuponIdInput.value        = '';
            descuentoCuponInput.value = '0';
            cuponCodigo.value         = '';
            cuponCodigo.disabled      = false;

            descuentoItem.style.display   = 'none';
            cuponResultado.className      = 'cupon-resultado';
            cuponResultado.textContent    = '';

            cuponAplicarBtn.style.display = '';
            cuponQuitarBtn.style.display  = 'none';

            recalcular();
        }

        cuponAplicarBtn.addEventListener('click', function () {
            const codigo = cuponCodigo.value.trim();

            if (codigo === '') {
                mostrarResultadoCupon('Ingresa un código de cupón.', 'error');
                return;
            }

            if (!tiendaSelect.value) {
                mostrarResultadoCupon('Primero selecciona una tienda.', 'error');
                return;
            }

            // Calcular subtotal actual
            let subtotalActual = 0;
            itemsContainer.querySelectorAll('.item-row').forEach(function (row) {
                const select   = row.querySelector('.producto-select');
                const selected = select.options[select.selectedIndex];
                const precio   = Number(selected?.dataset?.precio || 0);
                const cantidad = Number(row.querySelector('.cantidad-input').value || 0);
                subtotalActual += precio * cantidad;
            });

            if (subtotalActual <= 0) {
                mostrarResultadoCupon('Agrega productos antes de aplicar el cupón.', 'error');
                return;
            }

            cuponAplicarBtn.disabled = true;
            cuponAplicarBtn.textContent = 'Validando…';

            const formData = new FormData();
            formData.append('codigo',    codigo.toUpperCase());
            formData.append('subtotal',  subtotalActual.toFixed(2));
            formData.append('tienda_id', tiendaSelect.value);

            fetch('index.php?route=cupones.validar', {
                method: 'POST',
                body: formData,
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.valido) {
                    aplicarCupon(data.cupon_id, Number(data.descuento), codigo.toUpperCase());
                } else {
                    mostrarResultadoCupon('❌ ' + data.mensaje, 'error');
                }
            })
            .catch(function () {
                mostrarResultadoCupon('Error al comunicarse con el servidor.', 'error');
            })
            .finally(function () {
                cuponAplicarBtn.disabled    = false;
                cuponAplicarBtn.textContent = 'Validar cupón';
            });
        });

        cuponQuitarBtn.addEventListener('click', quitarCupon);

        // Si el vendedor cambia la tienda después de aplicar un cupón, lo quitamos
        tiendaSelect.addEventListener('change', function () {
            if (cuponIdInput.value !== '') {
                quitarCupon();
            }
        });
    </script>