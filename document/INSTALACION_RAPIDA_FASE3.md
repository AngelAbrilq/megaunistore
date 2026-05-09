# ⚡ Instalación Rápida - Fase 3

## 🚀 Pasos para Activar (5 minutos)

### 1. Ejecutar SQL (1 min)
```bash
# Opción A: Línea de comandos
mysql -u root -p mega_uni_store < backend/database/fase3_migracion.sql

# Opción B: phpMyAdmin
# Importar archivo: backend/database/fase3_migracion.sql
```

### 2. Verificar Archivos Creados (Ya está ✅)
- ✅ 3 Modelos
- ✅ 3 Controladores  
- ✅ Rutas actualizadas
- ✅ Vistas básicas

### 3. Probar Funcionalidades (3 min)

#### Cupones:
```
http://localhost/Mega_Uni_Store_v3/backend/public/index.php?route=cupones.index
```

#### Devoluciones:
```
http://localhost/Mega_Uni_Store_v3/backend/public/index.php?route=devoluciones.index
```

#### Reportes:
```
http://localhost/Mega_Uni_Store_v3/backend/public/index.php?route=reportes.index
```

### 4. Agregar Enlaces en Dashboard (1 min)

Editar tu dashboard principal y agregar:

```php
<a href="index.php?route=cupones.index">🎫 Cupones</a>
<a href="index.php?route=devoluciones.index">🔄 Devoluciones</a>
<a href="index.php?route=reportes.index">📊 Reportes</a>
```

## ✅ ¡Listo!

Tu sistema ahora tiene:
- ✅ Cupones de descuento
- ✅ Sistema de devoluciones
- ✅ Reportes completos

## 📚 Documentación Completa

- `FASE3_RESUMEN_COMPLETO.md` - Documentación detallada
- `FASE3_IMPLEMENTACION.md` - Guía técnica completa

## 🎯 Datos de Prueba

El SQL incluye 3 cupones de ejemplo:
- `BIENVENIDA10` - 10% de descuento
- `VERANO2026` - $20 de descuento fijo
- `PRIMERACOMPRA` - 15% de descuento

## 🐛 Solución de Problemas

**Error: Tabla no existe**
→ Ejecuta el SQL de migración

**Error: Ruta no encontrada**
→ Verifica que `backend/routes/web.php` tiene las nuevas rutas

**Error: Permiso denegado**
→ Asigna permisos `reportes.view` a los roles necesarios

---

**¿Necesitas ayuda?** Revisa `FASE3_RESUMEN_COMPLETO.md`
