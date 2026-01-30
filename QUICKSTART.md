# 🚀 QUICK START - Guía Rápida

**Inicio rápido de Pokémon BFF en Docker (5 minutos)**

---

## 1️⃣ Requisitos

- ✅ Docker Desktop instalado
- ✅ Al menos 4GB RAM libres
- ✅ Conexión a internet

---

## 2️⃣ Obtener el Código

```bash
# Clonar repositorio
git clone <repository-url>
cd pokemon-bff
```

---

## 3️⃣ Configurar Ambiente

```bash
# Copiar variables de entorno
cp .env.example .env

# Opcional: editar valores (los defaults funcionan)
# nano .env
```

---

## 4️⃣ Iniciar Contenedores

```bash
# Construir e iniciar
docker-compose up -d --build

# Esperar ~2-3 minutos mientras construye imágenes
# Verificar estado
docker-compose ps
```

**Esperado**:
```
NAME                    STATUS          PORTS
postgres                Up (healthy)    5432
redis                   Up (healthy)    6379
backend                 Up              9000
frontend                Up              3000
nginx                   Up              80
adminer                 Up              8080
mailhog                 Up              1025, 8025
```

---

## 5️⃣ Configurar Backend

```bash
# Instalar dependencias PHP (si no se instaló)
docker-compose exec backend composer install

# Generar APP_KEY
docker-compose exec backend php artisan key:generate

# Generar JWT_SECRET (editar .env después)
docker-compose exec backend php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

# Ejecutar migraciones
docker-compose exec backend php artisan migrate

# Seed base de datos (opcional)
docker-compose exec backend php artisan db:seed
```

---

## 6️⃣ Configurar Frontend

```bash
# Instalar dependencias
docker-compose exec frontend npm install

# Build Next.js (si no se buildea automáticamente)
docker-compose exec frontend npm run build
```

---

## 7️⃣ Verificar que Funciona

### Backend API
```bash
# Verificar health check
curl http://localhost:80/health
# Debe retornar: "healthy"

# Probar endpoint Pokemon
curl http://localhost:8000/api/v1/pokemon
# Debe retornar listado (después de implementar)
```

### Frontend
```bash
# Abrir navegador
open http://localhost:3000

# O desde línea de comandos
curl http://localhost:3000 | head -50
```

---

## 📲 Accesos Rápidos

| Servicio | URL | Usuario | Password |
|----------|-----|---------|----------|
| Frontend | http://localhost:3000 | - | - |
| Backend API | http://localhost:8000/api/v1 | - | - |
| Adminer | http://localhost:8080 | pokemon_user | pokemon_secure_pwd_123 |
| MailHog | http://localhost:8025 | - | - |

---

## ⚙️ Comandos Comunes

```bash
# Ver logs
docker-compose logs -f

# Logs de un servicio
docker-compose logs -f backend

# Ejecutar bash en backend
docker-compose exec backend bash

# Ejecutar artisan
docker-compose exec backend php artisan <comando>

# Reiniciar servicios
docker-compose restart

# Detener todo
docker-compose down

# Detener y limpiar (borrar BD)
docker-compose down -v
```

---

## 🔧 Generar Keys/Secrets

```bash
# APP_KEY (Laravel)
docker-compose exec backend php artisan key:generate

# JWT_SECRET (generar y copiar a .env)
docker-compose exec backend php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"

# Después editar .env:
# JWT_SECRET=<el_valor_generado>
```

---

## ✅ Checklist Inicial

- [ ] Docker Desktop corriendo
- [ ] `.env` copiado de `.env.example`
- [ ] `docker-compose up -d --build` ejecutado
- [ ] `docker-compose ps` muestra 7 servicios healthy/running
- [ ] `docker-compose exec backend php artisan migrate` completó
- [ ] `docker-compose exec frontend npm install` completó
- [ ] Frontend carga en `http://localhost:3000`
- [ ] Backend responde en `http://localhost:80/health`

---

## 🐛 Problemas Rápidos

### "Permission denied"
```bash
sudo chown -R $(id -u):$(id -g) backend/storage frontend
```

### "Port 80 in use"
```bash
# Cambiar en .env
NGINX_HTTP_PORT=8001
```

### "Cannot connect postgres"
```bash
docker-compose logs postgres
docker-compose restart postgres
```

### "npm install falla"
```bash
docker-compose exec frontend rm -rf node_modules
docker-compose exec frontend npm install
```

---

## 📚 Documentación Completa

Ver [DOCKER_SETUP.md](DOCKER_SETUP.md) para guía detallada.

---

**¡Listo! 🎉 Tu entorno está corriendo.**

Ahora puedes empezar a desarrollar.
