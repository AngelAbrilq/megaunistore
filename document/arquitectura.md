### Arquitectura técnica — Mega_Uni_Store

#### Resumen
Mega_Uni_Store es una solución multitienda (multitenant) diseñada para centralizar la operación de múltiples unidades de negocio. El diseño entrega:

- Separación Frontend / Backend.
- Un núcleo de datos relacional (implementación actual en MySQL, volcado: `mega_uni_store.sql`) que soporta gobernanza multitienda (campo `tienda_id` en tablas críticas).
- Módulos transversales: autenticación, catálogo, inventarios, ventas, pagos, reportes, contabilidad, nómina y auditoría.

> Nota sobre la elección de BD: El documento de requerimientos (PMO) recomendó MongoDB Atlas como opción NoSQL; sin embargo, el repositorio contiene un esquema relacional (MySQL) implementado en `mega_uni_store.sql`. La documentación que sigue describe la implementación actual (MySQL) y cómo satisface los requerimientos del PMO.

#### Componentes principales
##### Frontend
- Framework: React.js (componentes reutilizables: botones, tablas, formularios).
- Páginas principales: Auth (login, registro), Dashboard, Catálogo, Tiendas, POS (venta asistida), Reportes.
- Comunicación con backend via REST JSON y WebSockets (Socket.io) para actualizaciones en tiempo real.

##### Backend
- API REST (Node.js + Express sugerido en PMO; la arquitectura permite implementar controladores, servicios y middlewares).
- Autenticación: JWT para proteger rutas (tokens de acceso + refresh tokens).
- Lógica de negocio modular (módulos por dominio: ventas, inventario, nómina, contabilidad, reportes).
- Jobs en segundo plano: tareas de sincronización, envíos de correo, backups.

##### Persistencia y datos
- Implementación actual: MySQL (archivo `mega_uni_store.sql`) con:
  - Tablas core: `tiendas`, `usuarios`, `roles`, `productos`, `inventario`, `ventas`, `nominas`, `cajas`, `reportes`, `audit_log`, etc.
  - Triggers para asegurar integridad transaccional (ej.: ajuste de inventario en ventas, alertas de stock mínimo, auditoría).
  - Procedimientos almacenados para reportes y resúmenes (ej.: `sp_resumen_caja`, `sp_ventas_resumen`).
- Multitenancy: registro `tienda_id` en la mayoría de tablas; aislamiento de datos a nivel lógico.

##### Integraciones y almacenamiento
- Pasarelas de pago (ej. Mercado Pago) vía API REST.
- Almacenamiento de archivos: S3 compatible (AWS S3, Cloudinary) para imágenes y assets.
- Servicios de correo (SMTP / SendGrid) para notificaciones y recuperación de credenciales.

#### Comunicación en tiempo real
- WebSockets (Socket.io) para:
  - Notificaciones por stock bajo.
  - Actualización de dashboard en tiempo real.
  - Mensajes y eventos operativos entre usuarios y sistema.

#### Seguridad y auditoría
- JWT, HTTPS obligatorio y cifrado de datos sensibles (BCrypt para contraseñas).
- Audit log (`audit_log`) con datos antes/después y IP.
- Políticas de soft delete y triggers que impiden deletes físicos en tablas críticas (ej.: trigger `trg_usuario_soft_delete`).

#### Escalabilidad y despliegue
- Diseño modular que facilita escalado horizontal de la capa de aplicación.
- Recomendado: contenedores (Docker) y orquestación (Kubernetes) o despliegue en plataformas cloud (AWS, GCP, Azure).
- Backups diarios de BD y replicación para alta disponibilidad (SLA: 99.5% según PMO).

#### Diagrama general (sugerencia)
- Cliente (navegador / POS) ↔ Nginx (TLS) ↔ Backend API (Node/Express) ↔ MySQL Cluster (Primary + Replicas)
- Background workers (queues) ↔ Servicios externos (Mercado Pago, SMTP) ↔ Almacenamiento S3