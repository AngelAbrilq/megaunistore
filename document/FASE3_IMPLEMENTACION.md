# FASE 3 - POS Completo: Implementación

## ✅ Archivos Creados

### Modelos
- ✅ `backend/app/models/Cupon.php` - Gestión de cupones de descuento
- ✅ `backend/app/models/Devolucion.php` - Gestión de devoluciones
- ✅ `backend/app/models/Reporte.php` - Generación de reportes

### Controladores
- ✅ `backend/app/controllers/CuponController.php` - CRUD de cupones
- ✅ `backend/app/controllers/DevolucionController.php` - Gestión de devoluciones
- ✅ `backend/app/controllers/ReporteController.php` - Visualización de reportes

### Base de Datos
- ✅ `backend/database/fase3_migracion.sql` - Script SQL con las nuevas tablas

### Vistas Creadas
- ✅ `backend/resources/views/cupones/index.php` - Listado de cupones
- ✅ `backend/resources/views/cupones/create.php` - Crear cupón

## 📋 Tareas Pendientes

### 1. Ejecutar Migración de Base de Datos

```bash
# Ejecutar el script SQL en tu base de datos MySQL
mysql -u root -p mega_uni_store < backend/database/fase3_migracion.sql
```

O desde phpMyAdmin/Adminer, importar el archivo `backend/database/fase3_migracion.sql`

### 2. Agregar Rutas al Sistema

Editar `backend/routes/web.php` y agregar las siguientes rutas:

```php
// ========== CUPONES ==========
'cupones.index' => ['controller' => 'CuponController', 'method' => 'index'],
'cupones.create' => ['controller' => 'CuponController', 'method' => 'create'],
'cupones.store' => ['controller' => 'CuponController', 'method' => 'store'],
'cupones.edit' => ['controller' => 'CuponController', 'method' => 'edit'],
'cupones.update' => ['controller' => 'CuponController', 'method' => 'update'],
'cupones.destroy' => ['controller' => 'CuponController', 'method' => 'destroy'],
'cupones.validar' => ['controller' => 'CuponController', 'method' => 'validar'],

// ========== DEVOLUCIONES ==========
'devoluciones.index' => ['controller' => 'DevolucionController', 'method' => 'index'],
'devoluciones.create' => ['controller' => 'DevolucionController', 'method' => 'create'],
'devoluciones.store' => ['controller' => 'DevolucionController', 'method' => 'store'],
'devoluciones.show' => ['controller' => 'DevolucionController', 'method' => 'show'],

// ========== REPORTES ==========
'reportes.index' => ['controller' => 'ReporteController', 'method' => 'index'],
'reportes.ventas' => ['controller' => 'ReporteController', 'method' => 'ventas'],
'reportes.ventas_por_tienda' => ['controller' => 'ReporteController', 'method' => 'ventasPorTienda'],
'reportes.productos_mas_vendidos' => ['controller' => 'ReporteController', 'method' => 'productosMasVendidos'],
'reportes.ventas_por_metodo_pago' => ['controller' => 'ReporteController', 'method' => 'ventasPorMetodoPago'],
'reportes.inventario' => ['controller' => 'ReporteController', 'method' => 'inventario'],
'reportes.stock_bajo' => ['controller' => 'ReporteController', 'method' => 'stockBajo'],
'reportes.movimientos_inventario' => ['controller' => 'ReporteController', 'method' => 'movimientosInventario'],
'reportes.movimientos_caja' => ['controller' => 'ReporteController', 'method' => 'movimientosCaja'],
```

### 3. Vistas Adicionales a Crear

#### Cupones
- `backend/resources/views/cupones/edit.php` - Similar a create.php pero con datos precargados

#### Devoluciones
- `backend/resources/views/devoluciones/index.php` - Listado de devoluciones
- `backend/resources/views/devoluciones/create.php` - Formulario de devolución
- `backend/resources/views/devoluciones/show.php` - Detalle de devolución

#### Reportes
- `backend/resources/views/reportes/index.php` - Menú principal de reportes
- `backend/resources/views/reportes/ventas.php` - Reporte de ventas por período
- `backend/resources/views/reportes/ventas_por_tienda.php` - Ventas por tienda
- `backend/resources/views/reportes/productos_mas_vendidos.php` - Top productos
- `backend/resources/views/reportes/ventas_por_metodo_pago.php` - Ventas por método de pago
- `backend/resources/views/reportes/inventario.php` - Estado del inventario
- `backend/resources/views/reportes/stock_bajo.php` - Productos con stock bajo
- `backend/resources/views/reportes/movimientos_inventario.php` - Historial de movimientos
- `backend/resources/views/reportes/movimientos_caja.php` - Movimientos de caja

### 4. Integrar Cupones en el Proceso de Venta

Modificar `backend/resources/views/ventas/create.php` para:
1. Agregar campo de entrada para código de cupón
2. Botón "Aplicar cupón"
3. Mostrar descuento aplicado
4. Enviar `cupon_id` al crear la venta

Modificar `backend/app/models/Venta.php`:
- Método `crearVenta()` para aceptar y guardar `cupon_id`
- Llamar a `Cupon::incrementarUsos()` después de crear la venta
- En `anularVenta()`, llamar a `Cupon::decrementarUsos()` si había cupón

### 5. Agregar Enlaces en el Menú de Navegación

En los dashboards correspondientes, agregar enlaces a:
- Cupones: `index.php?route=cupones.index`
- Devoluciones: `index.php?route=devoluciones.index`
- Reportes: `index.php?route=reportes.index`

### 6. Agregar Botón de Devolución en Detalle de Venta

En `backend/resources/views/ventas/show.php`, agregar:

```php
<?php if ($venta['estado'] === 'completada'): ?>
    <a href="index.php?route=devoluciones.create&venta_id=<?= $venta['id'] ?>" class="btn btn-warning">
        Procesar devolución
    </a>
<?php endif; ?>
```

## 🎯 Funcionalidades Implementadas

### 1. Cupones de Descuento
- ✅ CRUD completo de cupones
- ✅ Tipos: porcentaje o monto fijo
- ✅ Validación de fechas de vigencia
- ✅ Control de usos máximos
- ✅ Monto mínimo de compra
- ✅ Descuento máximo (para porcentajes)
- ✅ Cupones globales o por tienda
- ✅ API de validación para usar en ventas

### 2. Devoluciones
- ✅ Crear devolución desde una venta
- ✅ Selección de productos y cantidades a devolver
- ✅ Validación de cantidades vendidas
- ✅ Devolución automática al inventario
- ✅ Registro de movimiento de caja (egreso)
- ✅ Historial de devoluciones
- ✅ Detalle completo de cada devolución

### 3. Reportes Básicos

#### Reportes de Ventas
- ✅ Ventas por período (diarias)
- ✅ Ventas por tienda
- ✅ Productos más vendidos
- ✅ Ventas por método de pago

#### Reportes de Inventario
- ✅ Estado del inventario por tienda
- ✅ Productos con stock bajo
- ✅ Movimientos de inventario

#### Reportes de Caja
- ✅ Movimientos de caja por período

#### Dashboard
- ✅ Resumen general (ventas del día, mes, stock bajo, etc.)

## 🔧 Estructura de Tablas Creadas

### Tabla: `cupones`
```sql
- id
- tienda_id (NULL = global)
- codigo (único)
- descripcion
- tipo_descuento (porcentaje/fijo)
- valor_descuento
- descuento_maximo
- monto_minimo
- fecha_inicio
- fecha_fin
- usos_maximos
- usos_actuales
- activo
- timestamps y auditoría
```

### Tabla: `devoluciones`
```sql
- id
- venta_id
- tienda_id
- motivo
- monto_devuelto
- estado
- timestamps y auditoría
```

### Tabla: `devoluciones_detalle`
```sql
- id
- devolucion_id
- producto_id
- cantidad
- precio_unitario
- subtotal
```

### Modificación: `ventas`
```sql
- cupon_id (nuevo campo, nullable)
```

## 📊 Ejemplos de Uso

### Aplicar Cupón en Venta
```javascript
// En el frontend de ventas
fetch('index.php?route=cupones.validar', {
    method: 'POST',
    body: new FormData({
        codigo: 'VERANO2026',
        subtotal: 150.00,
        tienda_id: 1
    })
})
.then(res => res.json())
.then(data => {
    if (data.valido) {
        // Aplicar descuento: data.descuento
        // Guardar cupon_id: data.cupon_id
    }
});
```

### Crear Devolución
1. Ir a detalle de venta
2. Click en "Procesar devolución"
3. Seleccionar productos y cantidades
4. Especificar motivo
5. Confirmar devolución
6. Sistema devuelve productos al inventario
7. Registra egreso en caja

### Ver Reportes
1. Ir a "Reportes"
2. Seleccionar tipo de reporte
3. Filtrar por fechas y/o tienda
4. Ver resultados en tabla
5. Opción de exportar (futuro)

## 🚀 Próximos Pasos (Fase 4)

- Contabilidad básica
- Nómina de empleados
- Reportes avanzados con gráficos
- Exportación de reportes (PDF, Excel)
- Dashboard con estadísticas visuales

## 📝 Notas Importantes

1. **Permisos**: Asegúrate de configurar los permisos adecuados para cada rol
2. **Validaciones**: Todos los formularios tienen validación en backend
3. **Transacciones**: Las operaciones críticas usan transacciones de BD
4. **Auditoría**: Todas las tablas tienen campos de auditoría (created_by, updated_by)
5. **Soft Delete**: Las tablas principales usan soft delete (deleted_at)

## 🐛 Testing Recomendado

1. Crear cupones de diferentes tipos
2. Validar cupones en ventas
3. Procesar devoluciones parciales y totales
4. Verificar que el inventario se actualiza correctamente
5. Revisar movimientos de caja
6. Generar todos los tipos de reportes
7. Probar con diferentes rangos de fechas
8. Verificar filtros por tienda
