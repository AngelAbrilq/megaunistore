# 🎟️ Cupones — Administrador de Tienda

> **Permisos:** `cupones.view · cupones.manage` (heredados de ventas/caja en la matriz base)
> **Referencia base:** `Superadministrador/14_Cupones.md`

---

## Listado: propios + globales

```sql
-- Cupon::listar($tiendaId) con tiendaId != null:
WHERE c.deleted_at IS NULL
  AND (c.tienda_id = :tienda_id OR c.tienda_id IS NULL)
```

El Admin de Tienda ve **dos tipos** de cupones en su listado:

| Tipo | `tienda_id` | Visibilidad |
|---|---|---|
| Cupón propio | `= su_tienda` | ✅ Solo él lo ve |
| Cupón global | `IS NULL` | ✅ Todos los roles lo ven |

---

## Creación

Al crear un cupón, el select de tiendas muestra **solo su tienda**:

```php
// CuponController::create():
$tiendas = $tiendaIdPermitida === null
    ? $this->tiendaModel->listar()
    : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];
```

Además, `store()` llama a `validarAccesoATienda($tiendaId)` si se envía un `tienda_id`, bloqueando cualquier intento de crear cupones para otra tienda. No puede crear cupones globales (`tienda_id = null`) desde la interfaz estándar.

---

## Edición y eliminación: ⚠️ cupones globales sin restricción

`edit()`, `update()` y `destroy()` aplican `validarAccesoATienda()` **solo si el cupón tiene tienda asignada**:

```php
if ($cupon['tienda_id'] !== null) {
    $this->validarAccesoATienda((int) $cupon['tienda_id']);
}
// → Si tienda_id IS NULL (global): NO valida acceso
```

**Implicación:** el Admin de Tienda puede editar y eliminar cupones globales. Esto es probablemente un oversight — si se quiere restringir, habría que bloquear la edición de cupones globales para roles distintos al Superadmin.

---

## Endpoint `validar` (uso en POS)

El endpoint `cupones.validar` no requiere CSRF ni autenticación de tienda. Retorna JSON con el descuento calculado. La cadena de validación es la misma para todos los roles:

```
código existe → fecha_inicio → fecha_fin → usos_maximos → monto_minimo → calcula descuento
```

---

## Integración con Ventas

Igual que para el Superadmin: al aplicar un cupón en una venta, `incrementarUsos()` se llama dentro de `crearVenta()`. Si se anula la venta, `decrementarUsos()` revierte el contador usando `GREATEST(0, usos_actuales - 1)`.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
