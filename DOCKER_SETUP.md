# 🐳 DOCKER SETUP - Guía de Configuración

**Documento de Configuración Docker para Pokémon BFF**  
**Fecha**: 2026-01-30  
**Versión**: 1.0

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Requisitos Previos](#requisitos-previos)
3. [Estructura de Archivos Creados](#estructura-de-archivos-creados)
4. [Configuración Inicial](#configuración-inicial)
5. [Servicios Docker](#servicios-docker)
6. [Comandos Útiles](#comandos-útiles)
7. [Troubleshooting](#troubleshooting)
8. [Resumen de Cambios](#resumen-de-cambios)

---

## 🎯 Introducción

Este documento describe la configuración completa de Docker para el entorno de desarrollo de **Pokémon BFF**.

Se han creado los siguientes archivos de acuerdo a las especificaciones del `PLANNING.md`:

- ✅ `docker-compose.yml` - Orquestación de servicios
- ✅ `backend/Dockerfile` - Imagen PHP 8.2-FPM
- ✅ `frontend/Dockerfile` - Imagen Node.js 18 (Next.js)
- ✅ `.env.example` - Variables de entorno
- ✅ Configuraciones de servicios (PHP, Nginx, PostgreSQL)

---

## 📦 Requisitos Previos

### Instalación de Docker

**Windows**:
```bash
# Descargar e instalar Docker Desktop
https://www.docker.com/products/docker-desktop

# Verificar instalación
docker --version
docker-compose --version
```

**macOS**:
```bash
# Usando Homebrew
brew install docker docker-compose

# O descargar Docker Desktop
https://www.docker.com/products/docker-desktop
```

**Linux (Ubuntu/Debian)**:
```bash
# Instalar Docker
sudo apt-get update
sudo apt-get install docker.io docker-compose

# Agregar usuario al grupo docker (evitar sudo)
sudo usermod -aG docker $USER
newgrp docker
```

### Requisitos Mínimos

| Recurso | Mínimo | Recomendado |
|---------|--------|-------------|
| CPU | 2 cores | 4 cores |
| RAM | 4 GB | 8 GB |
| Disco | 10 GB | 20 GB |
| Docker | 20.10+ | 24.0+ |

---

## 📁 Estructura de Archivos Creados

```
pokemon-bff/
├── docker-compose.yml                 # Orquestación principal
├── .env.example                       # Variables de ejemplo
│
├── backend/
│   ├── Dockerfile                     # PHP 8.2-FPM multi-stage
│   ├── .dockerignore                  # Exclusiones de build
│   ├── composer.json                  # Dependencias PHP (a crear)
│   └── artisan                        # CLI de Laravel (a crear)
│
├── frontend/
│   ├── Dockerfile                     # Node.js 18 multi-stage
│   ├── .dockerignore                  # Exclusiones de build
│   ├── package.json                   # Dependencias Node (a crear)
│   └── next.config.js                 # Config Next.js (a crear)
│
└── docker/
    ├── php/
    │   ├── php.ini                    # Configuración PHP
    │   ├── php-fpm.conf               # Configuración PHP-FPM global
    │   └── www.conf                   # Configuración pool www
    ├── nginx/
    │   ├── nginx.conf                 # Configuración Nginx
    │   └── conf.d/                    # Configs adicionales (a crear)
    └── postgres/
        └── init.sql                   # Script inicialización BD
```

---

## ⚙️ Configuración Inicial

### Paso 1: Clonar/Descargar el Proyecto

```bash
cd /ruta/del/proyecto
git clone <repository-url>
```

### Paso 2: Crear archivo .env

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar con tus valores (opcional - usa defaults si no cambias)
nano .env  # o abre con tu editor favorito
```

### Paso 3: Verificar estructura

```bash
# Asegurarse de que existan estos directorios
mkdir -p backend/storage
mkdir -p frontend
mkdir -p docker/php
mkdir -p docker/nginx
mkdir -p docker/postgres

# Crear archivos composer.json y package.json si no existen
touch backend/composer.json
touch frontend/package.json
```

### Paso 4: Inicializar Docker

```bash
# Build de imágenes y start de contenedores
docker-compose up -d --build

# Verificar que todos los servicios estén corriendo
docker-compose ps

# Debería ver:
# - postgres (healthy)
# - redis (healthy)
# - backend (running)
# - frontend (running)
# - nginx (running)
# - adminer (running)
# - mailhog (running)
```

---

## 🐳 Servicios Docker

### 1. PostgreSQL (postgres:15-alpine)

**Puerto**: 5432  
**Usuario**: `pokemon_user`  
**Password**: `pokemon_secure_pwd_123`  
**Database**: `pokemon_bff`

```bash
# Conectarse a PostgreSQL
docker-compose exec postgres psql -U pokemon_user -d pokemon_bff

# Ver tablas (después de migrations)
\dt

# Salir
\q
```

**Volúmenes**:
- `postgres_data:/var/lib/postgresql/data` - Persistencia de datos
- `docker/postgres/init.sql` - Script de inicialización

---

### 2. Redis (redis:7-alpine)

**Puerto**: 6379  
**Función**: Cache y Queue

```bash
# Conectarse a Redis
docker-compose exec redis redis-cli

# Verificar keys
KEYS *

# Salir
exit
```

**Configuración**:
- **Cache Driver**: Redis
- **Queue Connection**: Redis
- **TTL**: 24 horas (configurable)

---

### 3. Backend (PHP 8.2-FPM)

**Puerto**: 9000  
**Base de Datos**: PostgreSQL  
**Cache**: Redis

```bash
# Ver logs
docker-compose logs -f backend

# Ejecutar comandos Laravel
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan seed
docker-compose exec backend php artisan cache:clear
```

**Características**:
- Multi-stage build (optimizado)
- Extensiones: pdo, pdo_pgsql, mbstring, bcmath, zip, intl
- Composer preinstalado
- Usuario www-data (no root)

**Environment Variables** (en .env):
```
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=pokemon_bff
CACHE_DRIVER=redis
REDIS_HOST=redis
JWT_SECRET=your_jwt_secret_here
```

---

### 4. Frontend (Node.js 18)

**Puerto**: 3000  
**Framework**: Next.js 14  
**TypeScript**: Soportado

```bash
# Ver logs
docker-compose logs -f frontend

# Instalar dependencias
docker-compose exec frontend npm install

# Build
docker-compose exec frontend npm run build

# Desarrollo
docker-compose exec frontend npm run dev
```

**Environment Variables** (en .env):
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NODE_ENV=development
```

---

### 5. Nginx (nginx:1.25-alpine)

**Puerto**: 80 (443 en producción)  
**Función**: Reverse proxy

**Características**:
- Balanceo de carga
- Rate limiting
  - API General: 100 req/s
  - Auth: 5 req/min
- Gzip compression
- Security headers
- Cache de archivos estáticos

**Rutas configuradas**:
- `/api/*` → backend:9000
- `/api/v1/auth/*` → backend:9000 (rate limit estricto)
- `/*` → frontend:3000
- `/adminer` → adminer:8080 (dev)
- `/mailhog` → mailhog:8025 (dev)

```bash
# Ver logs
docker-compose logs -f nginx

# Recargar configuración
docker-compose exec nginx nginx -s reload
```

---

### 6. Adminer (adminer:latest)

**Puerto**: 8080  
**URL**: `http://localhost:8080`

Interfaz web para gestionar PostgreSQL.

**Login**:
- Sistema: PostgreSQL
- Servidor: postgres
- Usuario: pokemon_user
- Password: pokemon_secure_pwd_123
- Base de datos: pokemon_bff

**Nota**: Solo disponible en modo desarrollo (profile: dev)

---

### 7. MailHog (mailhog/mailhog:latest)

**SMTP**: Puerto 1025  
**UI**: Puerto 8025 (`http://localhost:8025`)

Captura emails en desarrollo (no envía realmente).

**Configuración Laravel**:
```env
MAIL_DRIVER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

**Nota**: Solo disponible en modo desarrollo (profile: dev)

---

## 🚀 Comandos Útiles

### Iniciar/Detener Servicios

```bash
# Iniciar todos los servicios
docker-compose up -d

# Iniciar con rebuild de imágenes
docker-compose up -d --build

# Detener servicios sin eliminar
docker-compose stop

# Detener y eliminar contenedores
docker-compose down

# Detener, eliminar y limpiar volúmenes
docker-compose down -v

# Reiniciar servicios
docker-compose restart
```

### Ver Estado

```bash
# Listar todos los contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f postgres

# Últimas 100 líneas
docker-compose logs --tail=100 backend
```

### Ejecutar Comandos en Contenedores

```bash
# Bash en contenedor backend
docker-compose exec backend bash

# Bash en contenedor frontend
docker-compose exec frontend sh

# Comando específico
docker-compose exec backend php artisan list

# MySQL/PostgreSQL CLI
docker-compose exec postgres psql -U pokemon_user -d pokemon_bff
```

### Gestión de Base de Datos

```bash
# Ejecutar migraciones
docker-compose exec backend php artisan migrate

# Rollback migraciones
docker-compose exec backend php artisan migrate:rollback

# Refresh (drop + migrate)
docker-compose exec backend php artisan migrate:refresh

# Seed base de datos
docker-compose exec backend php artisan db:seed

# Ejecutar migrations + seeds
docker-compose exec backend php artisan migrate:fresh --seed
```

### Gestión de Cache

```bash
# Limpiar cache
docker-compose exec backend php artisan cache:clear

# Limpiar config cache
docker-compose exec backend php artisan config:clear

# Limpiar todo
docker-compose exec backend php artisan optimize:clear

# Ver cache Redis
docker-compose exec redis redis-cli KEYS "*"
```

### Build de Imágenes

```bash
# Rebuild una imagen específica
docker-compose build backend
docker-compose build frontend

# Rebuild sin cache
docker-compose build --no-cache backend

# Ver imágenes
docker images | grep pokemon
```

### Network

```bash
# Ver network
docker network ls | grep pokemon

# Inspeccionar network
docker network inspect pokemon_bff_pokemon_network

# Probar conectividad entre servicios
docker-compose exec backend ping postgres
docker-compose exec backend ping redis
docker-compose exec frontend ping backend
```

---

## 🔧 Configuración Avanzada

### Variables de Entorno (.env)

Todas estas variables están documentadas en `.env.example`:

**Database**:
```env
DB_CONNECTION=pgsql          # Driver: pgsql, mysql
DB_HOST=postgres             # Hostname del contenedor
DB_PORT=5432                 # Puerto PostgreSQL
DB_DATABASE=pokemon_bff      # Nombre DB
DB_USERNAME=pokemon_user     # Usuario
DB_PASSWORD=***              # Password
```

**Redis**:
```env
CACHE_DRIVER=redis           # Driver de cache
REDIS_HOST=redis             # Host Redis
REDIS_PORT=6379              # Puerto Redis
QUEUE_CONNECTION=redis       # Queue driver
```

**Application**:
```env
APP_NAME=Pokemon BFF         # Nombre app
APP_ENV=development          # Entorno
APP_DEBUG=true               # Debug mode
APP_URL=http://localhost:8000
APP_KEY=base64:***           # Generar con: php artisan key:generate
JWT_SECRET=***               # JWT secret (generar)
```

**Frontend**:
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NODE_ENV=development
```

### Escalado de Servicios

```bash
# Escalar un servicio (ej: 3 instancias de backend)
docker-compose up -d --scale backend=3

# Solo en producción con load balancer
```

### Volumes Personalizados

```bash
# Listar volúmenes
docker volume ls | grep pokemon

# Inspeccionar volumen
docker volume inspect pokemon_bff_postgres_data

# Limpiar volúmenes huérfanos
docker volume prune
```

---

## 🐛 Troubleshooting

### "Port already in use"

```bash
# Encontrar qué proceso usa el puerto 8000
lsof -i :8000

# Liberar puerto (kill process)
kill -9 <PID>

# O cambiar puerto en .env
NGINX_HTTP_PORT=8001
```

### "Cannot connect to postgres"

```bash
# Verificar que postgres esté healthy
docker-compose ps

# Ver logs
docker-compose logs postgres

# Probar conectividad
docker-compose exec backend ping postgres

# Reiniciar postgres
docker-compose restart postgres
```

### "npm install falla en frontend"

```bash
# Limpiar node_modules
docker-compose exec frontend rm -rf node_modules package-lock.json

# Reinstalar
docker-compose exec frontend npm install

# O rebuild imagen
docker-compose build --no-cache frontend
```

### "Permission denied" en archivos

```bash
# Arreglar permisos (Linux)
sudo chown -R $(id -u):$(id -g) backend/storage
sudo chmod -R 755 backend/storage

# En Docker, el usuario automático es www-data (1000:1000)
```

### "Out of memory"

```bash
# Aumentar memoria en Docker Desktop
Settings → Resources → Memory: 8GB (mínimo)

# O aumentar en contenedor específico
# Editar docker-compose.yml para agregar:
# deploy:
#   resources:
#     limits:
#       memory: 1G
```

### "Migrations no se ejecutan"

```bash
# Ver error completo
docker-compose exec backend php artisan migrate --verbose

# Rollback y reintentar
docker-compose exec backend php artisan migrate:rollback
docker-compose exec backend php artisan migrate

# O usar fresh (destructiva)
docker-compose exec backend php artisan migrate:fresh
```

### Laravel APP_KEY no generado

```bash
# Generar key
docker-compose exec backend php artisan key:generate

# Debe producir: Application key set successfully

# Verificar en .env
grep "APP_KEY=" .env
```

---

## 📊 Resumen de Cambios

### Archivos Creados/Modificados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `docker-compose.yml` | Creado | Orquestación de 7 servicios |
| `backend/Dockerfile` | Creado | PHP 8.2-FPM multi-stage |
| `frontend/Dockerfile` | Creado | Node.js 18 multi-stage |
| `.env.example` | Modificado | Variables de env completas |
| `docker/php/php.ini` | Creado | Config PHP optimizada |
| `docker/php/php-fpm.conf` | Creado | Config PHP-FPM |
| `docker/php/www.conf` | Creado | Pool www configuración |
| `docker/nginx/nginx.conf` | Creado | Reverse proxy con rate limit |
| `docker/postgres/init.sql` | Creado | Schemas y tablas iniciales |
| `backend/.dockerignore` | Creado | Exclusiones de build |
| `frontend/.dockerignore` | Creado | Exclusiones de build |

### Servicios Implementados

| Servicio | Imagen | Puerto | Función |
|----------|--------|--------|---------|
| PostgreSQL | postgres:15-alpine | 5432 | Base de datos |
| Redis | redis:7-alpine | 6379 | Cache + Queue |
| Backend | PHP 8.2-FPM | 9000 | API Laravel |
| Frontend | Node 18 | 3000 | App Next.js |
| Nginx | nginx:1.25 | 80 | Reverse proxy |
| Adminer | adminer | 8080 | DB UI (dev) |
| MailHog | mailhog | 1025/8025 | Email (dev) |

### Características Implementadas

✅ **Comunicación interna**
- Backend se conecta a PostgreSQL
- Backend se conecta a Redis
- Frontend se conecta a Backend a través de Nginx
- Todos en misma red Docker

✅ **Seguridad**
- Usuarios no-root (www-data, nextjs)
- Headers de seguridad en Nginx
- CORS configurado
- Rate limiting

✅ **Performance**
- Multi-stage builds (imágenes optimizadas)
- Gzip compression
- Cache de archivos estáticos
- Connection pooling

✅ **Development**
- Adminer para BD
- MailHog para emails
- Logs en tiempo real
- Hot reload habilitado

---

## 🚀 Próximos Pasos

1. **Crear composer.json** en backend con dependencias Laravel
2. **Crear package.json** en frontend con dependencias Next.js
3. **Generar APP_KEY** con `php artisan key:generate`
4. **Ejecutar migrations** con `php artisan migrate`
5. **Seed base de datos** con `php artisan db:seed`
6. **Instalar dependencias frontend** con `npm install`
7. **Verificar conectividad** visitando endpoints

---

## 📞 Soporte Rápido

**Accesos útiles**:
- Frontend: `http://localhost:3000`
- Backend API: `http://localhost:8000/api/v1`
- Nginx Health: `http://localhost/health`
- Adminer (dev): `http://localhost:8080`
- MailHog UI (dev): `http://localhost:8025`

**Contacto**:
Para problemas específicos, verificar los logs:

```bash
docker-compose logs [service-name]
```

---

**Documento preparado**: 2026-01-30  
**Versión**: 1.0  
**Estado**: Listo para desarrollo
