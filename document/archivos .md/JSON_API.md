# 🔌 Respuestas JSON / AJAX — Referencia

> Documentación de los formatos de respuesta usados en el sistema para peticiones AJAX y SPA.

---

## Helpers de respuesta (ControllerHelper trait)

Todos los controllers tienen acceso a estos métodos via el trait `ControllerHelper`:

### `jsonExito(string $redireccion, string $mensaje)`

Respuesta de éxito para operaciones de creación/edición vía AJAX (en formularios de modal).

```json
{
  "success": true,
  "message": "Categoría creada correctamente.",
  "redirect": "index.php?route=categorias.index"
}
```

**HTTP Status:** 200  
**Content-Type:** `application/json`

**Comportamiento del cliente:**
```javascript
// En modales (assets/js/modal.js o inline):
fetch(form.action, { method: 'POST', body: formData })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeModal();
      loadContent(routeFromRedirect, true);  // refresca el panel
    }
  });
```

---

### `jsonError(string $mensaje, int $codigo = 422)`

Respuesta de error de validación para formularios AJAX.

```json
{
  "success": false,
  "message": "El nombre de la categoría es obligatorio."
}
```

**HTTP Status:** 422 (por defecto) o el código pasado  
**Content-Type:** `application/json`

---

## Respuestas de módulos específicos

### `cupones.validar` — Validar cupón en POS

**Request:**
```
POST index.php?route=cupones.validar
Content-Type: application/x-www-form-urlencoded

codigo=DESC10&subtotal=150.00&tienda_id=3
```

**Respuesta exitosa:**
```json
{
  "valido": true,
  "cupon_id": 7,
  "tipo_descuento": "porcentaje",
  "valor_descuento": "10.00",
  "descuento_calculado": 15.00,
  "total_con_descuento": 135.00,
  "mensaje": "Cupón aplicado: 10% de descuento"
}
```

**Respuesta de error:**
```json
{
  "valido": false,
  "mensaje": "El cupón ya alcanzó su límite de usos."
}
```

**HTTP Status:** siempre 200 — el campo `valido` indica el resultado  
**Errores posibles:**
- Código vacío → `"Debes ingresar un código de cupón."`
- Subtotal ≤ 0 → `"El subtotal debe ser mayor a cero."`
- Cupón no existe → `"Cupón no encontrado."`
- Cupón expirado → `"El cupón venció el YYYY-MM-DD."`
- Monto mínimo no alcanzado → `"El monto mínimo es $X.XX"`
- Límite de usos → `"El cupón ya alcanzó su límite de usos."`
- Excepción interna → mensaje del error

---

## Respuestas de redirección (no-AJAX)

Para operaciones que no usan AJAX (formularios clásicos con `POST` y redirección):

```php
// En web.php: redirección con flash en sesión
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Operación exitosa'];
header('Location: index.php?route=modulo.index');
exit;
```

El layout detecta el flash en `$_SESSION['flash']` y lo muestra como alerta en la siguiente carga.

---

## Vistas SPA — guard `$isAjax`

Las vistas que se cargan via `loadContent()` deben tener el guard al inicio:

```php
<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

// ... preparar datos ...

if (!$isAjax) { return; }  // ← sin esto, la vista no renderiza en SPA
?>
<div class="content">
  <!-- contenido del panel -->
</div>
```

**Sin `?ajax=1`:** La vista retorna sin salida (el layout completo maneja la carga inicial).  
**Con `?ajax=1`:** La vista emite solo su HTML parcial, que `loadContent()` inyecta en `#dynamicContent`.

---

## Modal — apertura AJAX

```javascript
// En cualquier vista — abrir modal con contenido AJAX:
openModal('index.php?route=productos.create&ajax=1');

// La función (en ControllerHelper / modal.js):
// 1. Hace GET a la URL con &ajax=1
// 2. El response HTML se inyecta en el contenedor del modal
// 3. El modal se muestra con overlay
```

Los formularios dentro del modal deben enviar `jsonExito()` o `jsonError()` para que el modal sepa si cerrar o mostrar errores inline.

---

## Códigos HTTP usados en el sistema

| Código | Cuándo se usa |
|---|---|
| 200 | Respuesta normal (GET) y respuestas JSON de módulos |
| 403 | `denegarAcceso()` — permiso insuficiente |
| 404 | Vista de error 404 (ruta no encontrada en web.php) |
| 419 | Token CSRF inválido — `exit` con mensaje de texto |
| 422 | `jsonError()` — error de validación |
| 500 | Error interno no manejado (PHP default) |

---

*Documentado: mayo 2026 — Ángel Nicolás Abril*
