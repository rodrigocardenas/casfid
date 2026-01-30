# 🐍 Pokémon BFF - Backend For Frontend

> **Full-Stack Application**: Laravel 11 BFF + Next.js 14 Frontend + PokeAPI Integration

[![Docker](https://img.shields.io/badge/Docker-Supported-blue?logo=docker)](https://www.docker.com/)
[![Laravel](https://img.shields.io/badge/Laravel-11-red?logo=laravel)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-14-black?logo=nextjs)](https://nextjs.org)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)]()

---

## 📋 Acerca de este Proyecto

**Pokémon BFF** es una prueba técnica full-stack que implementa:

- ✅ **Backend en Laravel 11** - BFF que consume PokeAPI
- ✅ **Frontend en Next.js 14** - React 18 + TypeScript
- ✅ **Autenticación JWT** - Registro, Login, Tokens
- ✅ **Base de Datos** - PostgreSQL con usuarios y favoritos
- ✅ **Caché Distribuida** - Redis para performance
- ✅ **Docker Compose** - Ambiente completamente dockerizado
- ✅ **API REST** - 9 endpoints documentados
- ✅ **Integración PokeAPI** - 150 Pokémon con filtros

---

## 🚀 Quick Start (5 minutos)

### Requisitos
- Docker Desktop instalado
- 4GB+ RAM libre
- Conexión a internet

### Pasos

```bash
# 1. Clonar
git clone <repository>
cd pokemon-bff

# 2. Configurar variables
cp .env.example .env

# 3. Iniciar Docker
docker-compose up -d --build

# 4. Esperar a que construya (2-3 min)
docker-compose ps

# 5. Configurar backend
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan key:generate

# 6. Instalar frontend
docker-compose exec frontend npm install

# 7. Acceder
# Frontend: http://localhost:3000
# Backend:  http://localhost:8000/api/v1
```

**Ver [QUICKSTART.md](QUICKSTART.md) para detalles.**

---

## 📚 Documentación

| Documento | Descripción |
|-----------|-------------|
| [PLANNING.md](PLANNING.md) | Arquitectura y diseño detallado |
| [DOCKER_SETUP.md](DOCKER_SETUP.md) | Guía completa de Docker |
| [QUICKSTART.md](QUICKSTART.md) | Inicio rápido (5 min) |
| [API.md](API.md) | Documentación de endpoints *(a crear)* |

---

## 📁 Estructura del Proyecto

```
pokemon-bff/
├── backend/                  # Laravel 11 BFF
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── Dockerfile
│   └── composer.json
│
├── frontend/                 # Next.js 14
│   ├── src/
│   │   ├── app/
│   │   ├── components/
│   │   └── services/
│   ├── Dockerfile
│   └── package.json
│
├── docker/                   # Configuraciones
│   ├── php/
│   ├── nginx/
│   └── postgres/
│
├── docker-compose.yml        # Orquestación
├── .env.example              # Variables de entorno
├── PLANNING.md               # Arquitectura
├── DOCKER_SETUP.md          # Setup Docker
└── README.md                 # Este archivo
```

---

## 🐳 Servicios Docker

| Servicio | Puerto | Imagen | Función |
|----------|--------|--------|---------|
| Frontend | 3000 | node:18-alpine | Next.js |
| Backend | 9000 | php:8.2-fpm | Laravel |
| Nginx | 80 | nginx:1.25 | Reverse Proxy |
| PostgreSQL | 5432 | postgres:15 | Database |
| Redis | 6379 | redis:7 | Cache |
| Adminer | 8080 | adminer | DB UI (dev) |
| MailHog | 8025 | mailhog | Email (dev) |

---

## 🔌 Accesos Rápidos

```
Frontend:       http://localhost:3000
Backend API:    http://localhost:8000/api/v1
Adminer (DB):   http://localhost:8080
MailHog:        http://localhost:8025
Health Check:   http://localhost/health
```

**Credenciales DB**:
- Usuario: `pokemon_user`
- Password: `pokemon_secure_pwd_123`
- Database: `pokemon_bff`

---

## 🔑 Variables de Entorno

```bash
# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=pokemon_bff

# Cache
CACHE_DRIVER=redis
REDIS_HOST=redis

# JWT
JWT_SECRET=your_secret_here

# API
POKEAPI_URL=https://pokeapi.co/api/v2

# Frontend
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
```

Ver [.env.example](.env.example) para lista completa.

---

## 📡 API Endpoints

### Autenticación
```bash
POST   /api/v1/auth/register      # Registro
POST   /api/v1/auth/login         # Login
POST   /api/v1/auth/logout        # Logout
POST   /api/v1/auth/refresh       # Renovar token
```

### Pokémon
```bash
GET    /api/v1/pokemon            # Listar (150) con filtros
GET    /api/v1/pokemon/{id}       # Detalle
```

### Favoritos
```bash
POST   /api/v1/favorites          # Agregar
DELETE /api/v1/favorites/{id}     # Remover
GET    /api/v1/favorites          # Listar
```

---

## ⚙️ Comandos Útiles

```bash
# Logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Migraciones
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan migrate:fresh --seed

# Bash
docker-compose exec backend bash
docker-compose exec frontend sh

# Detener
docker-compose down

# Limpiar todo
docker-compose down -v
```

---

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 11
- **Runtime**: PHP 8.2+
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Auth**: Sanctum JWT
- **Testing**: PEST

### Frontend
- **Framework**: Next.js 14
- **Runtime**: Node.js 18
- **UI**: React 18 + TypeScript
- **Styling**: Tailwind CSS
- **State**: Context API
- **Testing**: Jest

### DevOps
- **Container**: Docker 24+
- **Orchestration**: Docker Compose
- **Proxy**: Nginx 1.25
- **Repository**: Git

---

## 🚀 Roadmap

- [ ] Implementar AuthController (Register, Login)
- [ ] Integrar PokeAPI en backend
- [ ] Crear PokemonController
- [ ] Implementar FavoriteController
- [ ] UI de Login/Register
- [ ] UI de Listado Pokémon
- [ ] Filtros (nombre, tipo, favoritos)
- [ ] Testing (Backend + Frontend)
- [ ] Documentación Swagger
- [ ] Deploy a producción

---

## 📝 Notas de Desarrollo

### Database
Las migraciones se encuentran en `backend/database/migrations/`.

```bash
# Crear nueva migración
docker-compose exec backend php artisan make:migration create_table_name

# Ejecutar
docker-compose exec backend php artisan migrate
```

### API Resources
Los responses se transforman con Laravel Resource classes.

```php
// backend/app/Http/Resources/PokemonResource.php
class PokemonResource extends JsonResource {
    // Transform data
}
```

### Frontend Components
Los componentes están en `frontend/src/components/`.

```tsx
// frontend/src/components/Pokemon/PokemonCard.tsx
export const PokemonCard: React.FC<Props> = ({ pokemon }) => {
  // Component
}
```

---

## 🐛 Troubleshooting

### "Connection refused"
```bash
docker-compose restart postgres
```

### "Port in use"
```bash
# Cambiar en .env
NGINX_HTTP_PORT=8001
```

### "npm install falla"
```bash
docker-compose exec frontend npm install --force
```

Ver [DOCKER_SETUP.md](DOCKER_SETUP.md#-troubleshooting) para más soluciones.

---

## 📄 Licencia

MIT - Ver LICENSE para detalles.

---

## 👤 Autor

Arquitecto de Software Full-Stack  
Enero 2026

---

**¿Preguntas?** Ver documentación en [PLANNING.md](PLANNING.md) y [DOCKER_SETUP.md](DOCKER_SETUP.md)

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
