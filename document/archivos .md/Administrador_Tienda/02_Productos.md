# 📦 Productos — Administrador de Tienda

> **Permisos:** `productos.view · productos.create · productos.update · productos.delete`
> **Referencia base:** `Superadministrador/07_Productos.md`

---

## Comportamiento igual al Superadmin

El Admin de Tienda tiene acceso **completo** al CRUD de productos con los mismos permisos. Puede crear, editar, activar/desactivar y eliminar productos.

La tabla `productos` es **global** (no tiene `tienda_id`), por lo que `productos.index` lista **todos** los productos del sistema — igual que el Superadmin. La restricción de tienda opera a nivel de **precios**, no de visibilidad del catálogo.

---

## Diferencias clave

### Al crear un producto (`store`)

El formulario muestra el bloque de precio por tienda para **todas** las tiendas activas, igual que al Superadmin. Sin embargo, en la práctica el Admin de Tienda solo debería configurar precios para su tienda.

> **No hay restricción técnica** que le impida configurar precios para otras tiendas — depende de la disciplina operativa. Si se desea restringir esto, sería necesario agregar `validarAccesoATienda()` en `ProductoController::validarTiendasProducto()`.

### Al editar un producto (`update`)

Mismo comportamiento: puede modificar precios de cualquier tienda en el formulario. Los datos de producto base (nombre, código de barras, categoría, unidad, impuestos) son globales.

### Eliminación lógica

La eliminación desactiva el producto globalmente (afecta a todas las tiendas), ya que `eliminarLogico()` no filtra por tienda:
```php
// Dentro de la transacción:
UPDATE tiendas_productos SET estado = 0 WHERE producto_id = :id  // ← todas las tiendas
```

---

## Flujo típico del Admin de Tienda

```
1. Admin entra a productos.index → ve TODOS los productos del sistema
2. Clic "Nuevo Producto" → abre modal
3. Configura: nombre, categoría, impuestos, precio para SU tienda
   (puede también configurar otras tiendas, pero no es su responsabilidad)
4. Guarda → crearCompleto() en transacción
5. El producto aparece en el catálogo global y en su inventario si se hace entrada
```

---

## Módulos relacionados que SÍ tienen restricción de tienda

- **Inventario** — Solo puede ver/mover stock de su tienda
- **Ventas** — Solo puede vender productos con precio en `tiendas_productos` para su tienda
- **Reportes de productos** — Filtrados por su tienda

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
