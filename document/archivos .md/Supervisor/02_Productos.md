# 📦 Productos — Supervisor

> **Permiso:** `productos.view` (solo lectura)
> **Referencia base:** `Vendedor/02_Productos.md`

---

## Solo lectura

El Supervisor puede consultar el catálogo completo para verificar precios, datos y stock al gestionar ventas, devoluciones o revisar reportes. No puede crear, editar ni eliminar productos.

| Ruta | Acceso | Notas |
|---|---|---|
| `productos.index` | ✅ | Listado con buscador y filtros |
| `productos.show` | ✅ | Detalle del producto con precios por tienda |
| `productos.create / store` | ❌ 403 | Requiere `productos.create` |
| `productos.edit / update` | ❌ 403 | Requiere `productos.update` |
| `productos.destroy` | ❌ 403 | Requiere `productos.delete` |
| `productos.precios` | ❌ 403 | Requiere `productos.update` |

---

## Comportamiento del listado

`ProductoController::index()` carga el catálogo global (sin filtro de tienda). La columna de precio muestra el precio configurado para la tienda del Supervisor en `tiendas_productos`. Si un producto no tiene precio en su tienda, aparece como "Sin precio".

---

## Uso principal para el Supervisor

- Verificar precios antes de registrar una venta
- Confirmar el SKU / código de barras de un producto en una devolución
- Consultar unidad de medida e impuesto aplicable al facturar

---

## Cómo solicitar cambios en productos

El Supervisor no puede editar. Si necesita actualizar un precio o crear un nuevo producto, debe pedírselo al **Administrador de Tienda** o al **Superadministrador**.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
