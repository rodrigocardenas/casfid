# ⚡ REFERENCIA RÁPIDA

**Hoja de trucos - Pokémon BFF Docker Setup**

---

## 🚀 5 Pasos para Empezar

```bash
1. cp .env.example .env
2. docker-compose up -d --build
3. docker-compose ps                 # Verificar todos corriendo
4. docker-compose exec backend php artisan migrate
5. Acceder a http://localhost:3000
```

---

## 📍 Accesos Inmediatos

| Servicio | URL |
|----------|-----|
| Frontend | `http://localhost:3000` |
| Backend API | `http://localhost:8000/api/v1` |
| Database UI | `http://localhost:8080` |
| Email UI | `http://localhost:8025` |
| Health Check | `http://localhost/health` |

---

## 🔑 Credenciales BD

```
Usuario: pokemon_user
Password: pokemon_secure_pwd_123
Database: pokemon_bff
Host: postgres
Puerto: 5432
```

---

## 🧑‍💻 Comandos Comunes

### Ver Logs
```bash
docker-compose logs -f                      # Todos los servicios
docker-compose logs -f backend              # Solo backend
docker-compose logs -f frontend             # Solo frontend
docker-compose logs -f postgres             # Solo BD
docker-compose logs --tail=100 backend      # Últimas 100 líneas
```

### Conectar a Servicios
```bash
docker-compose exec backend bash            # Bash en backend
docker-compose exec frontend sh             # Shell en frontend
docker-compose exec postgres psql -U pokemon_user -d pokemon_bff   # PostgreSQL
docker-compose exec redis redis-cli         # Redis CLI
```

### Laravel Artisan
```bash
docker-compose exec backend php artisan migrate                    # Migraciones
docker-compose exec backend php artisan migrate:fresh --seed       # Fresh + seed
docker-compose exec backend php artisan cache:clear                # Limpiar cache
docker-compose exec backend php artisan key:generate               # Generate key
docker-compose exec backend php artisan tinker                     # Tinker shell
```

### npm/Node
```bash
docker-compose exec frontend npm install                           # Instalar deps
docker-compose exec frontend npm run build                         # Build
docker-compose exec frontend npm run dev                           # Dev mode
docker-compose exec frontend npm test                              # Tests
```

### Control de Docker
```bash
docker-compose up -d                        # Iniciar
docker-compose up -d --build                # Iniciar + rebuild
docker-compose restart                      # Reiniciar
docker-compose stop                         # Parar
docker-compose down                         # Parar + eliminar
docker-compose down -v                      # Parar + eliminar + volumes
docker-compose ps                           # Ver estado
docker-compose exec [service] [command]     # Ejecutar comando
```

---

## 🔧 Variables de Entorno Clave

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=pokemon_bff

# Cache
CACHE_DRIVER=redis
REDIS_HOST=redis

# JWT
JWT_SECRET=your_secret_here

# Frontend API
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

---

## 📁 Estructura de Carpetas

```
backend/               ← Código Laravel
├─ app/               ← Models, Controllers
├─ database/          ← Migrations, Seeds
├─ routes/            ← Routes API
└─ Dockerfile         ← PHP 8.2-FPM

frontend/             ← Código Next.js
├─ src/               ← Components, Pages
├─ package.json       ← Dependencies
└─ Dockerfile         ← Node 18

docker/               ← Configuraciones
├─ php/               ← PHP config
├─ nginx/             ← Nginx config
└─ postgres/          ← DB init

docker-compose.yml    ← Orquestación
.env.example          ← Variables template
```

---

## 📡 API Endpoints (a implementar)

### Auth
```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh
```

### Pokémon
```
GET    /api/v1/pokemon?search=pikachu&type=electric&page=1
GET    /api/v1/pokemon/{id}
```

### Favoritos
```
POST   /api/v1/favorites
DELETE /api/v1/favorites/{id}
GET    /api/v1/favorites
```

---

## 🐛 Problemas Comunes & Soluciones

| Problema | Solución |
|----------|----------|
| Port 80 in use | Cambiar `NGINX_HTTP_PORT=8001` en .env |
| Cannot connect postgres | `docker-compose restart postgres` |
| npm install falla | `docker-compose exec frontend npm install --force` |
| Permission denied storage | `sudo chown -R $(id -u):$(id -g) backend/storage` |
| Out of memory | Aumentar RAM en Docker Desktop a 8GB |
| DB migration error | `docker-compose exec backend php artisan migrate:fresh` |

---

## ✅ Health Checks

```bash
# Frontend corriendo
curl http://localhost:3000

# Backend corriendo
curl http://localhost:8000/api/v1

# Nginx OK
curl http://localhost/health

# PostgreSQL OK
docker-compose exec postgres pg_isready

# Redis OK
docker-compose exec redis redis-cli ping
```

---

## 🧹 Limpieza & Reset

```bash
# Limpiar cache
docker-compose exec backend php artisan cache:clear
docker-compose exec redis redis-cli FLUSHALL

# Resetear BD
docker-compose exec backend php artisan migrate:refresh --seed

# Resetear todo
docker-compose down -v
docker-compose up -d --build

# Limpiar imágenes no usadas
docker image prune -a
```

---

## 📊 Monitoreo

```bash
# Ver uso de recursos
docker stats

# Ver procesos en contenedor
docker top pokemon_backend
docker top pokemon_frontend
docker top pokemon_db

# Ver logs con timestamps
docker-compose logs --timestamps -f backend

# Ver eventos
docker events
```

---

## 🔒 Seguridad - Cambiar Defaults

```bash
# Cambiar password PostgreSQL
# Editar .env:
DB_PASSWORD=tu_password_seguro

# Generar JWT secret
openssl rand -hex 32
# Copiar valor a .env: JWT_SECRET=...

# Generar APP_KEY
docker-compose exec backend php artisan key:generate

# Reiniciar contenedores
docker-compose down
docker-compose up -d
```

---

## 📚 Documentación Rápida

| Necesito | Ver Archivo |
|----------|-------------|
| Empezar | QUICKSTART.md |
| Entender Docker | DOCKER_SETUP.md |
| Ver arquitectura | PLANNING.md |
| Ver estructura | ESTRUCTURA.md |
| Troubleshoot | DOCKER_SETUP.md#troubleshooting |
| API Endpoints | PLANNING.md#contratos-de-api |
| Variables .env | .env.example |

---

## ⚙️ Configuración por Rol

### Backend Developer
```bash
docker-compose logs -f backend
docker-compose exec backend bash
docker-compose exec backend php artisan migrate
```

### Frontend Developer
```bash
docker-compose logs -f frontend
docker-compose exec frontend sh
docker-compose exec frontend npm install
```

### DBA
```bash
docker-compose exec postgres psql -U pokemon_user -d pokemon_bff
# En Adminer: http://localhost:8080
```

### DevOps
```bash
docker-compose ps
docker stats
docker-compose logs
docker volume ls
docker network ls
```

---

## 🆘 Ayuda Rápida

### "¿Cómo veo qué está mal?"
```bash
docker-compose logs backend
docker-compose exec backend php artisan list
```

### "¿Cómo reseteo una tabla?"
```bash
docker-compose exec backend php artisan migrate:refresh --path=database/migrations/create_users_table.php
```

### "¿Cómo agrego una gema/paquete?"
```bash
# Backend
docker-compose exec backend composer require vendor/package

# Frontend
docker-compose exec frontend npm install package-name
```

### "¿Cómo veo si está todo OK?"
```bash
docker-compose ps
# Deberías ver:
# postgres    Up (healthy)
# redis       Up (healthy)
# backend     Up
# frontend    Up
# nginx       Up
```

---

## 🎯 Recordar

- ✅ Usar `docker-compose.yml` siempre en la raíz
- ✅ Copiar `.env.example` a `.env` ANTES de iniciar
- ✅ Ejecutar migraciones DESPUÉS del primer up
- ✅ Usar `docker-compose exec` DENTRO de contenedores
- ✅ No editar archivos de configuración en el contenedor
- ✅ Siempre verificar logs ante problemas

---

## 📞 Contactos de Documentación

```
5 minutos  → QUICKSTART.md
30 minutos → DOCKER_SETUP.md
1 hora     → PLANNING.md
Full       → Todos los .md files
Quick ref  → REFERENCIA_RAPIDA.md (este archivo)
Index      → DOCUMENTACION.md
```

---

## 🚀 Pro Tips

```bash
# Ver múltiples logs
watch -n 2 docker-compose ps

# Ejecutar múltiples comandos
docker-compose exec backend bash -c "php artisan migrate && php artisan seed"

# SSH-like experience
docker-compose exec backend bash
cd storage && ls -la

# Ejecutar PHP directamente
docker-compose exec backend php -r "echo phpinfo();"

# Copiar archivos
docker-compose cp backend:/var/www/html/file.txt ./file.txt

# Backup de BD
docker-compose exec postgres pg_dump -U pokemon_user pokemon_bff > backup.sql

# Restore BD
cat backup.sql | docker-compose exec -T postgres psql -U pokemon_user pokemon_bff
```

---

**Referencia Rápida - Pokémon BFF**  
**Actualizado**: 2026-01-30  
**Versión**: 1.0  

👉 **COMIENZA CON**: `cp .env.example .env && docker-compose up -d --build`
