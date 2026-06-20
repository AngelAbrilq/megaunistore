# 👤 Clientes — Administrador de Tienda

> **Permisos:** (heredados de `ventas.view/create`)
> **Referencia base:** `Superadministrador/11_Clientes.md`

---

## Restricción de tienda en el listado

```php
// ClienteController::index():
$tiendaId = $this->tiendaIdPermitida();
$clientes = $this->clienteModel->listar($tiendaId);
```

Con `tiendaId` distinto de `null`, el modelo usa INNER JOIN con `tiendas_clientes`:

```sql
SELECT c.*, tc.puntos_fidelidad, tc.activo AS activo_tienda
FROM clientes c
INNER JOIN tiendas_clientes tc ON tc.cliente_id = c.id
WHERE c.deleted_at IS NULL
  AND tc.tienda_id = :tienda_id
  AND tc.activo    = 1
ORDER BY c.nombre ASC, c.apellido ASC
```

El Admin de Tienda **solo ve clientes que están activamente asociados a su tienda** (activo = 1 en `tiendas_clientes`). El listado también incluye `puntos_fidelidad` de la asociación.

---

## Creación de clientes

### Deduplicación por documento

Si el cliente ya existe (mismo `tipo_documento` + `numero_documento`), el sistema **no crea un duplicado**: simplemente lo asocia a la tienda mediante `asociarATienda()`:

```php
if ($existente !== null) {
    $tiendaId = (int) ($_POST['tienda_id'] ?? 0);
    if ($tiendaId > 0) {
        $this->clienteModel->asociarATienda($existente['id'], $tiendaId);
    }
    // → 'Cliente ya registrado. Asociado a la tienda.'
}
```

`asociarATienda()` usa `ON DUPLICATE KEY UPDATE activo = 1`, de modo que si existía una asociación inactiva la reactiva.

### Formulario `create`

El formulario de creación lista **todas las tiendas activas** (igual que al Superadmin). No hay restricción técnica que fuerce el `tienda_id` a la tienda del Admin en el formulario — la restricción es operativa. Si se quisiera forzarla, habría que aplicar el patrón de select reducido igual que en Caja/Empleados.

---

## Sin validación `validarAccesoATienda` explícita en store

A diferencia de Inventario o Caja, `ClienteController::store()` no llama a `validarAccesoATienda()` antes de asociar el cliente. El filtrado opera solo en el listado. Esto permite al Admin crear clientes y asociarlos a cualquier tienda si manipulara el formulario, aunque en la práctica el flujo estándar siempre usa su tienda.

---

## Puntos de fidelidad

El listado del Admin incluye `puntos_fidelidad` del pivot `tiendas_clientes`. Los puntos son por tienda, no globales: un cliente puede tener distintos puntos en distintas tiendas.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
