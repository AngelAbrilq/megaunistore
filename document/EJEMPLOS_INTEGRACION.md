# 💻 Ejemplos de Código para Integración

## 1. Integrar Cupones en Ventas

### A. Modificar `backend/resources/views/ventas/create.php`

Agregar después de la sección de "Método de pago":

```php
<div class="form-group full">
    <label for="codigo_cupon">Código de cupón (opcional)</label>
    <div style="display: flex; gap: 10px;">
        <input type="text" id="codigo_cupon" placeholder="Ej: VERANO2026" style="flex: 1;">
        <button type="button" class="btn btn-secondary" id="aplicarCuponBtn">Aplicar cupón</button>
    </div>
    <span class="help" id="cuponMensaje">Ingresa un código de cupón para obtener descuento.</span>
    <input type="hidden" id="cupon_id" name="cupon_id" value="">
    <input type="hidden" id="cupon_descuento" value="0">
</div>
```

Agregar en el JavaScript al final del archivo:

```javascript
// Aplicar cupón
document.getElementById('aplicarCuponBtn').addEventListener('click', function() {
    const codigo = document.getElementById('codigo_cupon').value.trim();
    const tiendaId = tiendaSelect.value;
    const subtotal = calcularSubtotalActual();

    if (!codigo) {
        alert('Ingresa un código de cupón');
        return;
    }

    if (!tiendaId) {
        alert('Primero selecciona una tienda');
        return;
    }

    const formData = new FormData();
    formData.append('codigo', codigo);
    formData.append('subtotal', subtotal);
    formData.append('tienda_id', tiendaId);

    fetch('index.php?route=cupones.validar', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const mensaje = document.getElementById('cuponMensaje');
        
        if (data.valido) {
            document.getElementById('cupon_id').value = data.cupon_id;
            document.getElementById('cupon_descuento').value = data.descuento;
            mensaje.textContent = '✓ ' + data.mensaje + ' - Descuento: $' + data.descuento;
            mensaje.style.color = '#166534';
            recalcularConCupon();
        } else {
            document.getElementById('cupon_id').value = '';
            document.getElementById('cupon_descuento').value = '0';
            mensaje.textContent = '✗ ' + data.mensaje;
            mensaje.style.color = '#991b1b';
        }
    })
    .catch(error => {
        alert('Error al validar cupón');
        console.error(error);
    });
});

function calcularSubtotalActual() {
    let subtotal = 0;
    itemsContainer.querySelectorAll('.item-row').forEach(function(row) {
        const select = row.querySelector('.producto-select');
        const selected = select.options[select.selectedIndex];
        const cantidadInput = row.querySelector('.cantidad-input');
        const precio = Number(selected?.dataset?.precio || 0);
        const cantidad = Number(cantidadInput.value || 0);
        subtotal += precio * cantidad;
    });
    return subtotal;
}

function recalcularConCupon() {
    const subtotal = calcularSubtotalActual();
    const descuento = Number(document.getElementById('cupon_descuento').value || 0);
    const totalConDescuento = subtotal - descuento;
    
    subtotalPreview.textContent = money(subtotal);
    
    // Agregar visualización del descuento si existe
    if (descuento > 0) {
        subtotalPreview.textContent = money(subtotal) + ' - $' + money(descuento) + ' = $' + money(totalConDescuento);
    }
}
```

### B. Modificar `backend/app/models/Venta.php`

En el método `crearVenta()`, agregar soporte para cupones:

```php
public function crearVenta(array $venta, array $items, array $pago, ?int $cuponId = null): int
{
    $this->db->beginTransaction();

    try {
        // ... código existente ...

        $calculo = $this->calcularTotalesYValidarStock($tiendaId, $items);

        // Aplicar descuento de cupón si existe
        $descuentoCupon = 0.0;
        if ($cuponId !== null) {
            $cupon = $this->validarYAplicarCupon($cuponId, (float) $calculo['subtotal'], $tiendaId);
            $descuentoCupon = $cupon['descuento'];
        }

        $descuentoTotal = (float) $calculo['descuento'] + $descuentoCupon;
        $total = (float) $calculo['subtotal'] - $descuentoTotal + (float) $calculo['impuesto'];

        // ... resto del código ...

        $stmtVenta->execute([
            ':tienda_id'   => $tiendaId,
            ':cliente_id'  => $venta['cliente_id'],
            ':empleado_id' => $empleadoId,
            ':caja_id'     => (int) $cajaAbierta['id'],
            ':cupon_id'    => $cuponId,  // NUEVO
            ':subtotal'    => $calculo['subtotal'],
            ':descuento'   => number_format($descuentoTotal, 2, '.', ''),  // MODIFICADO
            ':impuesto'    => $calculo['impuesto'],
            ':total'       => number_format($total, 2, '.', ''),
            ':created_by'  => $usuarioId,
            ':updated_by'  => $usuarioId,
        ]);

        $ventaId = (int) $this->db->lastInsertId();

        // Incrementar usos del cupón
        if ($cuponId !== null) {
            $this->incrementarUsosCupon($cuponId);
        }

        // ... resto del código ...

        $this->db->commit();
        return $ventaId;
    } catch (Throwable $error) {
        $this->db->rollBack();
        throw $error;
    }
}

private function validarYAplicarCupon(int $cuponId, float $subtotal, int $tiendaId): array
{
    require_once __DIR__ . '/Cupon.php';
    $cuponModel = new Cupon();
    
    $cupon = $cuponModel->buscarPorId($cuponId);
    
    if ($cupon === null) {
        throw new RuntimeException('El cupón no existe.');
    }

    // Validar que el cupón sea aplicable a esta tienda
    if ($cupon['tienda_id'] !== null && (int) $cupon['tienda_id'] !== $tiendaId) {
        throw new RuntimeException('El cupón no es válido para esta tienda.');
    }

    // Calcular descuento
    $descuento = 0.0;
    if ($cupon['tipo_descuento'] === 'porcentaje') {
        $descuento = $subtotal * ((float) $cupon['valor_descuento'] / 100);
        if ($cupon['descuento_maximo'] !== null && $descuento > (float) $cupon['descuento_maximo']) {
            $descuento = (float) $cupon['descuento_maximo'];
        }
    } else {
        $descuento = (float) $cupon['valor_descuento'];
    }

    return [
        'cupon_id' => $cuponId,
        'descuento' => $descuento,
    ];
}

private function incrementarUsosCupon(int $cuponId): void
{
    require_once __DIR__ . '/Cupon.php';
    $cuponModel = new Cupon();
    $cuponModel->incrementarUsos($cuponId);
}
```

En el método `anularVenta()`, agregar:

```php
public function anularVenta(int $ventaId, ?int $usuarioId = null): bool
{
    $this->db->beginTransaction();

    try {
        $venta = $this->buscarPorId($ventaId);

        // ... código existente ...

        // Decrementar usos del cupón si se usó uno
        if ($venta['cupon_id'] !== null) {
            $this->decrementarUsosCupon((int) $venta['cupon_id']);
        }

        // ... resto del código ...

        $this->db->commit();
        return true;
    } catch (Throwable $error) {
        $this->db->rollBack();
        throw $error;
    }
}

private function decrementarUsosCupon(int $cuponId): void
{
    require_once __DIR__ . '/Cupon.php';
    $cuponModel = new Cupon();
    $cuponModel->decrementarUsos($cuponId);
}
```

### C. Modificar `backend/app/controllers/VentaController.php`

En el método `store()`:

```php
public function store(): void
{
    // ... código existente ...

    $cuponId = (int) ($_POST['cupon_id'] ?? 0);
    $cuponId = $cuponId > 0 ? $cuponId : null;

    try {
        $ventaId = $this->ventaModel->crearVenta(
            [
                'tienda_id' => $tiendaId,
                'cliente_id' => null,
                'created_by' => $this->usuarioIdActual(),
                'updated_by' => $this->usuarioIdActual(),
            ],
            $items,
            [
                'metodo_pago_id' => $metodoPagoId,
                'referencia' => $referencia !== '' ? $referencia : null,
            ],
            $cuponId  // NUEVO PARÁMETRO
        );

        // ... resto del código ...
    } catch (Throwable $error) {
        // ... manejo de errores ...
    }
}
```

---

## 2. Agregar Botón de Devolución en Detalle de Venta

### Modificar `backend/resources/views/ventas/show.php`

Agregar en la sección de acciones (después del botón de anular):

```php
<?php if ($venta['estado'] === 'completada'): ?>
    <a href="index.php?route=devoluciones.create&venta_id=<?= e_show_venta((string) $venta['id']) ?>" 
       class="btn btn-warning">
        🔄 Procesar devolución
    </a>
<?php endif; ?>
```

---

## 3. Agregar Enlaces en Dashboards

### Ejemplo para `backend/resources/views/dashboard/superadmin.php`

```php
<div class="menu-section">
    <h3>Ventas y Promociones</h3>
    <ul>
        <li><a href="index.php?route=ventas.index">📊 Ventas</a></li>
        <li><a href="index.php?route=cupones.index">🎫 Cupones</a></li>
        <li><a href="index.php?route=devoluciones.index">🔄 Devoluciones</a></li>
    </ul>
</div>

<div class="menu-section">
    <h3>Reportes y Análisis</h3>
    <ul>
        <li><a href="index.php?route=reportes.index">📈 Reportes</a></li>
        <li><a href="index.php?route=reportes.ventas">💰 Ventas por Período</a></li>
        <li><a href="index.php?route=reportes.stock_bajo">⚠️ Stock Bajo</a></li>
    </ul>
</div>
```

---

## 4. Crear Vista de Edición de Cupón

### `backend/resources/views/cupones/edit.php`

Copiar `create.php` y modificar:

```php
<?php
// ... mismo código que create.php pero con datos precargados ...

<form action="index.php?route=cupones.update" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_edit_cupon($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= e_edit_cupon((string) $cupon['id']) ?>">

    <!-- Campos con valores precargados -->
    <input type="text" id="codigo" name="codigo" 
           value="<?= e_edit_cupon($cupon['codigo']) ?>" required>

    <select id="tipo_descuento" name="tipo_descuento" required>
        <option value="porcentaje" <?= $cupon['tipo_descuento'] === 'porcentaje' ? 'selected' : '' ?>>
            Porcentaje (%)
        </option>
        <option value="fijo" <?= $cupon['tipo_descuento'] === 'fijo' ? 'selected' : '' ?>>
            Monto fijo ($)
        </option>
    </select>

    <!-- ... resto de campos con valores precargados ... -->

    <button type="submit" class="btn btn-primary">Actualizar cupón</button>
</form>
```

---

## 5. Crear Vista de Creación de Devolución

### `backend/resources/views/devoluciones/create.php`

```php
<form action="index.php?route=devoluciones.store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e_create_devolucion($csrfToken) ?>">
    <input type="hidden" name="venta_id" value="<?= e_create_devolucion((string) $venta['id']) ?>">

    <div class="card">
        <h2>Información de la Venta</h2>
        <p><strong>Venta #<?= e_create_devolucion((string) $venta['id']) ?></strong></p>
        <p>Fecha: <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
        <p>Total: $<?= number_format((float) $venta['total'], 2) ?></p>
    </div>

    <div class="card">
        <h2>Productos a Devolver</h2>
        
        <?php foreach ($detalle as $item): ?>
            <div class="item-row">
                <input type="hidden" name="producto_id[]" value="<?= e_create_devolucion((string) $item['producto_id']) ?>">
                
                <div>
                    <strong><?= e_create_devolucion($item['producto_nombre']) ?></strong>
                    <br>
                    <small>Cantidad vendida: <?= e_create_devolucion($item['cantidad']) ?></small>
                </div>

                <div>
                    <label>Cantidad a devolver</label>
                    <input type="number" name="cantidad[]" 
                           min="0" max="<?= e_create_devolucion($item['cantidad']) ?>" 
                           step="0.01" value="0">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>Motivo de la Devolución</h2>
        <textarea name="motivo" required rows="4" 
                  placeholder="Describe el motivo de la devolución..."></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Procesar Devolución</button>
    <a href="index.php?route=ventas.show&id=<?= e_create_devolucion((string) $venta['id']) ?>" 
       class="btn btn-secondary">Cancelar</a>
</form>
```

---

## 6. Plantilla para Vistas de Reportes

Todas las vistas de reportes siguen este patrón:

```php
<?php
function e_reporte(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nombre del Reporte | Mega_Uni_Store</title>
    <!-- Mismo CSS que ventas.php -->
</head>
<body>
    <main class="container">
        <h1>Nombre del Reporte</h1>
        <p>Descripción del reporte.</p>

        <!-- Filtros -->
        <div class="card">
            <form method="GET" action="index.php">
                <input type="hidden" name="route" value="reportes.nombre">
                <!-- Campos de filtro -->
            </form>
        </div>

        <!-- Resumen (opcional) -->
        <div class="summary-grid">
            <!-- Tarjetas de resumen -->
        </div>

        <!-- Tabla de resultados -->
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <!-- Columnas -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos as $dato): ?>
                        <tr>
                            <!-- Celdas -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
```

---

## 📝 Notas Finales

1. **Todos los ejemplos usan el mismo patrón de diseño** del sistema existente
2. **La validación se hace en backend**, el frontend solo muestra mensajes
3. **Usa transacciones** para operaciones que modifican múltiples tablas
4. **Sanitiza todas las salidas** con `htmlspecialchars()`
5. **Valida CSRF** en todos los formularios

---

**¿Necesitas más ejemplos?** Revisa los archivos existentes en:
- `backend/app/models/Venta.php` - Patrón de modelo
- `backend/app/controllers/VentaController.php` - Patrón de controlador
- `backend/resources/views/ventas/` - Patrón de vistas
