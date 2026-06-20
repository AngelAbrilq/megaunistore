# 👤 Clientes — Supervisor

> **Permisos:** `clientes.view · clientes.manage` (scope de su tienda)
> **Referencia base:** `Superadministrador/11_Clientes.md`

---

## Restricción de tienda

`tiendaIdPermitida()` retorna el `int` de su tienda. `ClienteController` pasa ese valor a `Cliente::listar($tiendaId)` y a `Cliente::crearYAsociar()`, limitando todas las operaciones a los clientes de su tienda.

| Acción | Comportamiento |
|---|---|
| `clientes.index` | Solo clientes asociados a su tienda en `tiendas_clientes` |
| `clientes.create` | Formulario pre-fijado a su tienda |
| `clientes.store` | `crearYAsociar($datos, $tiendaId)` — asocia a su tienda |
| `clientes.edit` | Solo puede editar clientes de su tienda |
| `clientes.update` | Valida acceso a tienda antes de actualizar |
| `clientes.destroy` | Solo puede eliminar/desasociar clientes de su tienda |

---

## Deduplicación automática en `store`

```php
// ClienteController::store():
// Si el cliente ya existe (mismo tipo/número de documento):
$existente = $clienteModel->buscarPorDocumento($tipo, $numero);
if ($existente !== null) {
    $clienteModel->asociarATienda($existente['id'], $tiendaId);
    jsonExito('clientes.index', 'Cliente ya registrado. Asociado a la tienda.');
}
// → No crea duplicado, solo asocia el existente a su tienda
```

El mismo comportamiento aplica para el Superadmin. El Supervisor se beneficia de clientes ya registrados en otras tiendas: solo los asocia a la suya.

---

## Formulario de creación

Campos del formulario `clientes/create.php`:

| Campo | Obligatorio | Notas |
|---|---|---|
| Nombre | ✅ | |
| Apellido | ✅ | |
| Tipo de documento | Opcional | CC, NIT, Pasaporte, etc. |
| Número de documento | Opcional | Si se provee, activa deduplicación |
| Email | Opcional | Se valida unicidad global si se ingresa |
| Teléfono | Opcional | |
| Dirección | Opcional | |
| `tienda_id` | Auto | Fijado desde sesión — no es editable por el Supervisor |

---

## Diferencia vs Superadmin

| Capacidad | Superadmin | Supervisor |
|---|---|---|
| Ver todos los clientes del sistema | ✅ (`null` en listar) | ❌ Solo los de su tienda |
| Crear cliente en cualquier tienda | ✅ | ❌ Solo en la suya |
| Asociar cliente a múltiples tiendas | ✅ | ❌ Solo puede asociar a su tienda |

---

## Uso principal para el Supervisor

El Supervisor registra y mantiene la base de clientes de su tienda. Esta lista se usa en el POS (`ventas.create`) para asociar la venta a un cliente específico (y así habilitar la aplicación de cupones personalizados y el historial de compras).

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
