### Base de datos — Mega_Uni_Store

### Visión general
La implementación actual disponible en el repositorio está en un esquema relacional MySQL (`mega_uni_store.sql`). El modelo contempla multitenancy mediante la columna `tienda_id` en tablas operativas y fue diseñado para garantizar trazabilidad, integridad transaccional y facilidad para reportes.

#### Restaurar la base de datos (instrucciones rápidas)
1. Crear la base de datos y cargar el volcado:
```bash
mysql -u <usuario> -p < mega_uni_store.sql