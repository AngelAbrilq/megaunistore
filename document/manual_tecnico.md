### Manual técnico — Mega_Uni_Store

#### Resumen
Guía de instalación, variables de entorno, despliegue y mantenimiento para el equipo de desarrollo / DevOps.

#### Requisitos mínimos
- Node.js 16+ (o la versión estable LTS recomendada)
- MySQL 8.0+ (o servicio gestionado RDS / Cloud SQL)
- Docker y docker-compose (recomendado para desarrollo)
- Git y herramientas CI/CD (GitHub Actions / GitLab CI / Jenkins)

#### Variables de entorno (ejemplo `.env`)
```env
PORT=3000
NODE_ENV=production
JWT_SECRET=tu_secreto_largo
JWT_EXPIRES_IN=1h
DB_HOST=localhost
DB_PORT=3306
DB_NAME=mega_uni_store
DB_USER=root
DB_PASSWORD=tu_password
S3_BUCKET=
S3_ENDPOINT=
SMTP_HOST=smtp.sendgrid.net
SMTP_USER=
SMTP_PASS=
MP_CLIENT_ID=      # Mercado Pago (si aplica)
MP_CLIENT_SECRET=