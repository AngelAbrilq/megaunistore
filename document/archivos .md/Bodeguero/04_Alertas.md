# 🔔 Alertas de Stock — Bodeguero

> **Permiso:** `inventario.alerts`
> **Referencia base:** `Superadministrador/08_Inventario.md` (sección Alertas)

---

## Descripción

Las alertas de stock son el módulo principal de acción del Bodeguero. Muestra todos los productos de su tienda donde el stock actual está **en o por debajo del mínimo definido**.

---

## Ruta y acceso

| Ruta | Permiso | Descripción |
|---|---|---|
| `inventario.alertas` | `inventario.alerts` | Lista de productos bajo stock mínimo |

```php
// web.php:
case 'inventario.alertas':
    $authController->requerirPermiso('inventario.alerts');
    $inventarioController->alertas();
    break;
```

---

## Lógica del controlador (`InventarioController::alertas()`)

```php
public function alertas(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();
    // → Para Bodeguero: int de su tienda (nunca null)

    $alertas = $this->inventarioModel->obtenerAlertas($tiendaIdPermitida);
    // → SELECT * FROM inventario i
    //   JOIN productos p ON p.id = i.producto_id
    //   WHERE i.tienda_id = $tiendaId
    //     AND i.cantidad <= i.cantidad_minima
    //   ORDER BY (i.cantidad - i.cantidad_minima) ASC  -- más críticos primero

    require .../views/inventario/alertas.php;
}
```

---

## Vista `inventario/alertas.php`

La vista muestra una tabla con:

| Columna | Descripción |
|---|---|
| Producto | Nombre + código |
| Stock actual | Cantidad en bodega actualmente |
| Stock mínimo | Umbral configurado en inventario |
| Diferencia | `cantidad - cantidad_minima` (negativo = déficit) |
| Acceso rápido | Botón → `inventario.movimiento&id=X` (registrar entrada) |

Los productos con déficit más alto aparecen primero (orden por diferencia ASC).

---

## Flujo de acción del Bodeguero

```
1. Bodeguero entra a inventario.alertas
   → Ve la lista de productos críticos de su tienda

2. Identifica un producto con stock bajo
   → Hace clic en "Registrar Entrada"
   → Navega a inventario.movimiento&id=X (SPA loadContent)

3. En inventario/movimiento.php:
   → Selecciona tipo "entrada"
   → Ingresa la cantidad recibida
   → POST inventario.guardar_movimiento
   → El stock se actualiza y el producto desaparece de la lista de alertas
```

---

## Restricción de tienda

`tiendaIdPermitida()` retorna el `int` de su tienda. El Bodeguero **solo ve las alertas de su propia tienda** — nunca puede ver ni reaccionar a alertas de otras tiendas.

---

## Diferencia vs otros roles

| Rol | Puede ver alertas | Puede registrar movimiento (entrada) |
|---|---|---|
| Superadministrador | ✅ Todas las tiendas | ✅ |
| Administrador de Tienda | ✅ Su tienda | ✅ |
| Supervisor | No tiene `inventario.alerts` | ❌ |
| Bodeguero | ✅ Su tienda | ✅ (razón de ser del rol) |
| Vendedor | ❌ | ❌ |
| Reportero | ❌ | ❌ |

---

## Acceso rápido desde Dashboard

El dashboard del Bodeguero incluye un botón directo a alertas:
```javascript
onclick="loadContent('inventario.alertas', true)"
```

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
