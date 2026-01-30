# PLANNING.md - Arquitectura Pokémon BFF + Frontend

**Documento de Planificación Técnica - Prueba Técnica Full-Stack**

---

## 📋 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Estructura de Monorepo](#estructura-de-monorepo)
3. [Decisiones Arquitectónicas](#decisiones-arquitectónicas)
4. [Diseño de Base de Datos](#diseño-de-base-de-datos)
5. [Contratos de API](#contratos-de-api)
6. [Estrategia de Dockerización](#estrategia-de-dockerización)
7. [Stack Tecnológico](#stack-tecnológico)
8. [Plan de Implementación](#plan-de-implementación)

---

## 🎯 Visión General

### Objetivo
Desarrollar una aplicación full-stack que permita a usuarios registrarse, autenticarse y explorar 150 Pokémon desde PokeAPI con capacidad de filtrado y gestión de favoritos con persistencia en BD.

### Requisitos Funcionales
- ✅ Autenticación: Registro, Login, JWT
- ✅ Consumo de PokeAPI: 150 Pokémon
- ✅ Filtros: Nombre, Tipo, Favoritos
- ✅ Persistencia: Usuarios y Favoritos en BD
- ✅ UI Responsiva: Next.js + React 18 + TypeScript

### Requisitos No Funcionales
- Performance: Cache de Pokémon (Redis/Memory)
- Seguridad: JWT, CORS, Rate Limiting
- Escalabilidad: Arquitectura de microservicios preparada
- Observabilidad: Logging estructurado

---

## 📁 Estructura de Monorepo

```
pokemon-bff/
│
├── backend/                          # BFF en PHP/Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── PokemonController.php
│   │   │   │   └── FavoriteController.php
│   │   │   ├── Middleware/
│   │   │   │   ├── Authenticate.php
│   │   │   │   └── RateLimiting.php
│   │   │   └── Requests/
│   │   │       ├── LoginRequest.php
│   │   │       ├── RegisterRequest.php
│   │   │       └── AddFavoriteRequest.php
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   └── Favorite.php
│   │   ├── Services/
│   │   │   ├── PokemonService.php (Consumo PokeAPI)
│   │   │   ├── AuthService.php
│   │   │   └── CacheService.php
│   │   ├── Repositories/
│   │   │   ├── UserRepository.php
│   │   │   └── FavoriteRepository.php
│   │   ├── Exceptions/
│   │   │   └── ApiExceptions.php
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   │
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── 2026_01_30_000000_create_users_table.php
│   │   │   ├── 2026_01_30_000001_create_favorites_table.php
│   │   │   └── 2026_01_30_000002_add_indexes.php
│   │   └── seeders/
│   │       └── DatabaseSeeder.php
│   │
│   ├── routes/
│   │   └── api.php               # Rutas API (v1)
│   │
│   ├── config/
│   │   ├── app.php
│   │   ├── database.php
│   │   ├── jwt.php              # Configuración JWT
│   │   └── pokeapi.php          # Configuración PokeAPI
│   │
│   ├── storage/
│   ├── tests/
│   ├── .env.example
│   ├── .dockerignore
│   ├── Dockerfile
│   ├── docker-compose.yml
│   ├── composer.json
│   └── artisan
│
├── frontend/                         # Frontend Next.js + React 18
│   ├── src/
│   │   ├── app/
│   │   │   ├── layout.tsx
│   │   │   ├── page.tsx
│   │   │   ├── auth/
│   │   │   │   ├── register/
│   │   │   │   │   └── page.tsx
│   │   │   │   └── login/
│   │   │   │       └── page.tsx
│   │   │   ├── pokemon/
│   │   │   │   ├── page.tsx
│   │   │   │   └── [id]/
│   │   │   │       └── page.tsx
│   │   │   └── dashboard/
│   │   │       └── page.tsx
│   │   │
│   │   ├── components/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginForm.tsx
│   │   │   │   ├── RegisterForm.tsx
│   │   │   │   └── ProtectedRoute.tsx
│   │   │   ├── Pokemon/
│   │   │   │   ├── PokemonCard.tsx
│   │   │   │   ├── PokemonList.tsx
│   │   │   │   ├── PokemonFilters.tsx
│   │   │   │   └── FavoriteButton.tsx
│   │   │   └── Common/
│   │   │       ├── Header.tsx
│   │   │       ├── Footer.tsx
│   │   │       └── LoadingSpinner.tsx
│   │   │
│   │   ├── services/
│   │   │   ├── api.ts              # Cliente HTTP
│   │   │   ├── authService.ts
│   │   │   ├── pokemonService.ts
│   │   │   └── favoriteService.ts
│   │   │
│   │   ├── hooks/
│   │   │   ├── useAuth.ts
│   │   │   ├── usePokemon.ts
│   │   │   └── useFavorites.ts
│   │   │
│   │   ├── context/
│   │   │   └── AuthContext.tsx
│   │   │
│   │   ├── types/
│   │   │   ├── pokemon.ts
│   │   │   ├── user.ts
│   │   │   └── api.ts
│   │   │
│   │   ├── styles/
│   │   │   └── globals.css
│   │   │
│   │   └── utils/
│   │       ├── localStorage.ts
│   │       ├── tokenManager.ts
│   │       └── validators.ts
│   │
│   ├── public/
│   ├── .env.example
│   ├── .dockerignore
│   ├── Dockerfile
│   ├── package.json
│   ├── tsconfig.json
│   ├── next.config.js
│   └── jest.config.js
│
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf
│   │   └── Dockerfile
│   ├── php/
│   │   └── Dockerfile
│   └── postgres/
│       └── init.sql
│
├── docker-compose.yml              # Orquestación de servicios
├── .gitignore
├── README.md
└── PLANNING.md                      # Este documento

```

---

## 🏗️ Decisiones Arquitectónicas

### 1. Selección del Framework PHP: **LARAVEL 11**

#### Justificación

| Aspecto | Laravel | Symfony |
|--------|---------|---------|
| **Curva de Aprendizaje** | Baja/Media ✅ | Media/Alta |
| **Documentación** | Excelente ✅ | Buena |
| **Ecosistema** | Vasto ✅ | Robusto |
| **JWT Nativo** | Sanctum ✅ | Bundle |
| **Migrations** | Elegante ✅ | Verbose |
| **API Resources** | Integradas ✅ | Manual |
| **Testing** | PEST/PHPUnit ✅ | PHPUnit |
| **TTM (Time to Market)** | Rápido ✅ | Más lento |

**Decisión: Laravel 11** por su:
- Excelente DSL para APIs REST
- Sanctum/JWT integrado y maduro
- Migraciones y seeders elegantes
- Comunidad activa
- Perfecto para MVP y escalable

### 2. Arquitectura BFF

```
┌─────────────────────────────────────────────────────────────┐
│                   FRONTEND (Next.js)                        │
│                                                             │
│  - React 18 + TypeScript                                   │
│  - Context API para estado                                 │
│  - SWR/React Query para caching                            │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTP/REST
                         │ JWT en Headers
┌────────────────────────▼────────────────────────────────────┐
│              BFF (Backend for Frontend)                     │
│                    Laravel 11                              │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  API Controllers                                     │  │
│  │  - AuthController (Login, Register, Refresh)        │  │
│  │  - PokemonController (List, Filter, Details)        │  │
│  │  - FavoriteController (Add, Remove, List)           │  │
│  └──────────────────────────────────────────────────────┘  │
│                         │                                  │
│  ┌──────────────────────▼──────────────────────────────┐  │
│  │  Service Layer                                      │  │
│  │  - AuthService (JWT, Tokens)                        │  │
│  │  - PokemonService (PokeAPI Integration)             │  │
│  │  - CacheService (Redis/Memory Cache)                │  │
│  │  - FavoriteService (DB Persistence)                 │  │
│  └──────────────────────────────────────────────────────┘  │
│                         │                                  │
│  ┌──────────────────────▼──────────────────────────────┐  │
│  │  External APIs & Persistence                        │  │
│  │  - PokeAPI (REST)                                   │  │
│  │  - PostgreSQL (Users, Favorites)                    │  │
│  │  - Redis (Cache Layer)                              │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### 3. Patrones de Diseño

- **Repository Pattern**: Abstracción de acceso a datos
- **Service Layer**: Lógica de negocio centralizada
- **Dependency Injection**: IoC Container de Laravel
- **Resource Pattern**: Transformación de responses
- **Middleware**: Autenticación, Rate Limiting, CORS

---

## 🗄️ Diseño de Base de Datos

### Diagrama ER

```
┌──────────────────┐         ┌──────────────────┐
│     users        │         │    favorites     │
├──────────────────┤         ├──────────────────┤
│ id (PK)          │◄────────│ id (PK)          │
│ name (VARCHAR)   │  1    ∞ │ user_id (FK)     │
│ email (VARCHAR)  │         │ pokemon_id (INT) │
│ password (HASH)  │         │ pokemon_name     │
│ created_at       │         │ created_at       │
│ updated_at       │         │ updated_at       │
│ deleted_at       │         └──────────────────┘
└──────────────────┘
```

### Tabla: `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);
```

### Tabla: `favorites`

```sql
CREATE TABLE favorites (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    pokemon_id INT NOT NULL,
    pokemon_name VARCHAR(255) NOT NULL,
    pokemon_type VARCHAR(50) NOT NULL,
    pokemon_image_url VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_pokemon (user_id, pokemon_id),
    INDEX idx_user_id (user_id),
    INDEX idx_pokemon_id (pokemon_id),
    INDEX idx_created_at (created_at)
);
```

### Tabla: `personal_access_tokens` (Sanctum JWT)

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(80) NOT NULL UNIQUE,
    abilities LONGTEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id)
);
```

### Índices de Rendimiento

```sql
-- Para búsquedas frecuentes
CREATE INDEX idx_favorites_user_id ON favorites(user_id);
CREATE INDEX idx_users_email ON users(email);

-- Para ordenamiento
CREATE INDEX idx_favorites_created_at ON favorites(created_at DESC);
CREATE INDEX idx_users_created_at ON users(created_at DESC);
```

### Notas sobre Diseño

1. **Soft Deletes**: `deleted_at` en `users` para auditoría
2. **Unicidad Compuesta**: Un usuario no puede favoritar el mismo Pokémon 2 veces
3. **Desnormalización**: `pokemon_name`, `pokemon_type` en `favorites` para consultas rápidas
4. **Sin Tabla de Pokémon**: Se cacheará desde PokeAPI (read-only, sin cambios)

---

## 📡 Contratos de API

### Base URL
- **Desarrollo**: `http://localhost:8000/api/v1`
- **Producción**: `https://api.pokemon-bff.com/api/v1`

### Headers Requeridos

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {jwt_token}  # En endpoints protegidos
```

---

### 🔐 Autenticación

#### 1. POST `/auth/register`

**Descripción**: Registrar nuevo usuario

**Request**:
```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "created_at": "2026-01-30T10:30:00Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Validaciones**:
- Email único (pattern: RFC 5322)
- Password: min 8 caracteres, debe incluir mayúscula, número
- Name: min 2, max 255 caracteres

---

#### 2. POST `/auth/login`

**Descripción**: Autenticar usuario

**Request**:
```json
{
  "email": "juan@example.com",
  "password": "SecurePassword123!"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Autenticación exitosa",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 3600
}
```

**Códigos de Error**:
- 401: Credenciales inválidas
- 429: Demasiados intentos (Rate Limiting: 5 intentos/15min)

---

#### 3. POST `/auth/logout` ⚡

**Descripción**: Cerrar sesión

**Headers**: Requiere JWT

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

#### 4. POST `/auth/refresh` ⚡

**Descripción**: Renovar token JWT

**Headers**: Requiere JWT actual

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 3600
  }
}
```

---

### 🐉 Pokémon

#### 5. GET `/pokemon` ⚡

**Descripción**: Listar Pokémon (150) con filtros

**Query Parameters**:
```
GET /pokemon?page=1&per_page=20&search=pikachu&type=electric&favorites=false
```

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `page` | INT | 1 | Número de página |
| `per_page` | INT | 20 | Items por página (max 50) |
| `search` | STRING | null | Buscar por nombre |
| `type` | STRING | null | Filtrar por tipo (e.g., electric, water) |
| `favorites` | BOOLEAN | false | Solo favoritos del usuario |

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 25,
      "name": "pikachu",
      "type": ["electric"],
      "image": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png",
      "is_favorite": true,
      "stats": {
        "hp": 35,
        "attack": 55,
        "defense": 40,
        "speed": 90
      }
    },
    {
      "id": 26,
      "name": "raichu",
      "type": ["electric"],
      "image": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/26.png",
      "is_favorite": false,
      "stats": {
        "hp": 60,
        "attack": 90,
        "defense": 55,
        "speed": 100
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8,
    "from": 1,
    "to": 20,
    "links": {
      "first": "/api/v1/pokemon?page=1",
      "last": "/api/v1/pokemon?page=8",
      "next": "/api/v1/pokemon?page=2"
    }
  }
}
```

**Cache**: 24 horas (Redis o Memory)

---

#### 6. GET `/pokemon/{id}` ⚡

**Descripción**: Obtener detalles de un Pokémon

**Path Parameters**:
```
GET /pokemon/25
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 25,
    "name": "pikachu",
    "type": ["electric"],
    "description": "When several of these Pokémon...",
    "height": 4,
    "weight": 60,
    "image": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png",
    "is_favorite": true,
    "stats": {
      "hp": 35,
      "attack": 55,
      "defense": 40,
      "sp_attack": 50,
      "sp_defense": 50,
      "speed": 90
    },
    "abilities": ["static", "lightning-rod"],
    "evolution_chain": [
      {
        "id": 25,
        "name": "pikachu",
        "trigger": "Level 25"
      },
      {
        "id": 26,
        "name": "raichu",
        "trigger": "Electric Stone"
      }
    ]
  }
}
```

---

### ⭐ Favoritos

#### 7. POST `/favorites` ⚡

**Descripción**: Agregar Pokémon a favoritos

**Request**:
```json
{
  "pokemon_id": 25,
  "pokemon_name": "pikachu",
  "pokemon_type": "electric"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Pokémon agregado a favoritos",
  "data": {
    "id": 1,
    "user_id": 1,
    "pokemon_id": 25,
    "pokemon_name": "pikachu",
    "pokemon_type": "electric",
    "created_at": "2026-01-30T10:30:00Z"
  }
}
```

**Códigos de Error**:
- 409: Ya está en favoritos
- 422: Validación fallida

---

#### 8. DELETE `/favorites/{pokemon_id}` ⚡

**Descripción**: Remover Pokémon de favoritos

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Pokémon removido de favoritos"
}
```

---

#### 9. GET `/favorites` ⚡

**Descripción**: Listar favoritos del usuario

**Query Parameters**:
```
GET /favorites?page=1&per_page=20&type=electric
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "pokemon_id": 25,
      "pokemon_name": "pikachu",
      "pokemon_type": "electric",
      "image": "https://...",
      "created_at": "2026-01-30T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 5
  }
}
```

---

### Códigos de Error Globales

| Código | Error | Causa |
|--------|-------|-------|
| 200 | OK | Éxito |
| 201 | Created | Recurso creado |
| 400 | Bad Request | Parámetros inválidos |
| 401 | Unauthorized | JWT inválido/expirado |
| 403 | Forbidden | Permisos insuficientes |
| 404 | Not Found | Recurso no existe |
| 409 | Conflict | Recurso duplicado |
| 422 | Unprocessable Entity | Validación fallida |
| 429 | Too Many Requests | Rate limit excedido |
| 500 | Server Error | Error interno |

**Response Error Estándar**:
```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {
    "field": ["Validación específica"]
  }
}
```

---

## 🐳 Estrategia de Dockerización

### Arquitectura de Contenedores

```
┌─────────────────────────────────────────────────────────┐
│                   Docker Network                        │
│                                                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Frontend   │  │   Backend    │  │   Database   │  │
│  │  Next.js     │  │   Laravel    │  │  PostgreSQL  │  │
│  │ :3000        │  │   :8000      │  │  :5432       │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│         │                │                                 │
│         └────────────────┼─────────────────────────┐     │
│                          │                         │     │
│         ┌────────────────┴──────────────┬──────────┼───┐ │
│         │                              │          │   │ │
│  ┌──────▼────────┐            ┌────────▼──┐ ┌────▼──┐│ │
│  │    Redis      │            │  Nginx    │ │ Adminer││ │
│  │  Cache        │            │  Reverse  │ │ (DB)   ││ │
│  │  :6379        │            │  Proxy    │ │        ││ │
│  │               │            │  :80/:443 │ │        ││ │
│  └───────────────┘            └───────────┘ └────────┘│ │
│                                                        │ │
└────────────────────────────────────────────────────────┘ │
```

### Docker Compose Configuration

**Servicios**:

1. **frontend**: Next.js (Node 18)
2. **backend**: PHP 8.2 + Laravel (Apache/FPM)
3. **postgres**: PostgreSQL 15
4. **redis**: Redis 7 (Cache)
5. **nginx**: Nginx (Reverse Proxy)
6. **adminer**: Web UI para DB (desarrollo)

### Dockerfile - Backend (Laravel)

```dockerfile
# Dockerfile backend/Dockerfile
FROM php:8.2-fpm

WORKDIR /var/www/html

# Instalación de dependencias
RUN apt-get update && apt-get install -y \
    build-essential \
    libpq-dev \
    libonig-dev \
    curl \
    git \
    zip \
    unzip \
    supervisor

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Código de la aplicación
COPY backend/ .

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]
```

### Dockerfile - Frontend (Next.js)

```dockerfile
# Dockerfile frontend/Dockerfile
FROM node:18-alpine AS builder

WORKDIR /app

COPY frontend/package*.json ./
RUN npm ci

COPY frontend/ .
RUN npm run build

# Production stage
FROM node:18-alpine

WORKDIR /app

COPY --from=builder /app/package*.json ./
RUN npm ci --only=production

COPY --from=builder /app/.next ./.next
COPY --from=builder /app/public ./public

EXPOSE 3000

CMD ["npm", "start"]
```

### Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  # Database
  postgres:
    image: postgres:15-alpine
    container_name: pokemon_db
    environment:
      POSTGRES_DB: pokemon_bff
      POSTGRES_USER: pokemon_user
      POSTGRES_PASSWORD: pokemon_secure_pwd_123
      POSTGRES_INITDB_ARGS: "--encoding=UTF8"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./docker/postgres/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "5432:5432"
    networks:
      - pokemon_network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U pokemon_user"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis Cache
  redis:
    image: redis:7-alpine
    container_name: pokemon_cache
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - pokemon_network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Backend Laravel
  backend:
    build:
      context: .
      dockerfile: backend/Dockerfile
    container_name: pokemon_backend
    working_dir: /var/www/html
    environment:
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: pokemon_bff
      DB_USERNAME: pokemon_user
      DB_PASSWORD: pokemon_secure_pwd_123
      REDIS_HOST: redis
      REDIS_PORT: 6379
      APP_KEY: base64:your_app_key_here
      APP_DEBUG: "false"
      JWT_SECRET: your_jwt_secret_here
      POKEAPI_URL: https://pokeapi.co/api/v2
    volumes:
      - ./backend:/var/www/html
      - ./backend/storage:/var/www/html/storage
    ports:
      - "9000:9000"
    networks:
      - pokemon_network
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    command: php-fpm

  # Frontend Next.js
  frontend:
    build:
      context: .
      dockerfile: frontend/Dockerfile
    container_name: pokemon_frontend
    environment:
      NEXT_PUBLIC_API_URL: http://localhost:8000/api/v1
      NODE_ENV: production
    volumes:
      - ./frontend:/app
      - /app/node_modules
    ports:
      - "3000:3000"
    networks:
      - pokemon_network
    depends_on:
      - backend

  # Nginx Reverse Proxy
  nginx:
    image: nginx:alpine
    container_name: pokemon_nginx
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./backend:/var/www/html
    ports:
      - "80:80"
      - "443:443"
    networks:
      - pokemon_network
    depends_on:
      - backend
      - frontend

  # Adminer (DB Management - Dev Only)
  adminer:
    image: adminer
    container_name: pokemon_adminer
    ports:
      - "8080:8080"
    networks:
      - pokemon_network
    depends_on:
      - postgres

networks:
  pokemon_network:
    driver: bridge

volumes:
  postgres_data:
  redis_data:
```

### Nginx Configuration

```nginx
# docker/nginx/nginx.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 20M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=api_limit:10m rate=100r/s;
    limit_req_zone $binary_remote_addr zone=auth_limit:10m rate=5r/m;

    # Backend API Upstream
    upstream backend {
        server backend:9000;
    }

    # Frontend Upstream
    upstream frontend {
        server frontend:3000;
    }

    server {
        listen 80;
        server_name localhost;
        
        # API Backend
        location /api/ {
            limit_req zone=api_limit burst=20 nodelay;
            proxy_pass http://backend;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            proxy_cache_bypass $http_upgrade;
        }

        # Auth endpoints - Stricter rate limiting
        location /api/v1/auth/ {
            limit_req zone=auth_limit burst=5 nodelay;
            proxy_pass http://backend;
            proxy_http_version 1.1;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        }

        # Frontend
        location / {
            proxy_pass http://frontend;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_cache_bypass $http_upgrade;
        }

        # Health check
        location /health {
            access_log off;
            return 200 "healthy\n";
            add_header Content-Type text/plain;
        }
    }
}
```

### Inicialización Base de Datos

```sql
-- docker/postgres/init.sql
CREATE DATABASE pokemon_bff;

\c pokemon_bff;

-- Crear extensiones
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Tablas serán creadas por Laravel migrations
```

### Comandos de Desarrollo

```bash
# Build y start
docker-compose up -d --build

# Ejecutar migraciones
docker-compose exec backend php artisan migrate --seed

# Ver logs
docker-compose logs -f backend

# Detener
docker-compose down

# Limpiar todo
docker-compose down -v
```

### Producción - Consideraciones

1. **HTTPS**: Certificados Let's Encrypt
2. **Environment**: Usar `.env` securizado (secrets)
3. **Logging**: ELK Stack (Elasticsearch, Logstash, Kibana)
4. **Monitoring**: Prometheus + Grafana
5. **Orchestration**: Kubernetes o Docker Swarm
6. **Registry**: Docker Hub o ECR privado
7. **CI/CD**: GitHub Actions o GitLab CI

---

## 🛠️ Stack Tecnológico

### Backend

```
Framework:        Laravel 11
Runtime:          PHP 8.2+
API Style:        RESTful JSON
Authentication:   Laravel Sanctum + JWT
Database:         PostgreSQL 15
Cache:            Redis 7
HTTP Client:      Guzzle (PokeAPI)
Testing:          PEST + PHPUnit
Documentation:    OpenAPI/Swagger
Validation:       Laravel Validation Rules
```

### Frontend

```
Framework:        Next.js 14 (App Router)
React:            18.x
TypeScript:       5.x
State Management: Context API + useReducer
Data Fetching:    SWR o React Query
Styling:          Tailwind CSS
Form Validation:  React Hook Form + Zod
HTTP Client:      Axios
Testing:          Jest + React Testing Library
UI Components:    Headless UI / shadcn/ui
```

### DevOps

```
Containerization: Docker & Docker Compose
Reverse Proxy:    Nginx
Database:         PostgreSQL 15
Cache:            Redis 7
Version Control:  Git
CI/CD:            GitHub Actions
Monitoring:       (Prometheus ready)
```

---

## 📅 Plan de Implementación

### Fase 1: Setup Inicial (Semana 1)

- [ ] Configurar monorepo Git
- [ ] Crear Docker Compose base
- [ ] Scaffolding Backend Laravel
- [ ] Scaffolding Frontend Next.js
- [ ] Setup CI/CD básico

**Deliverables**: Ambiente dockerizado funcional

---

### Fase 2: Backend Core (Semana 2)

- [ ] Migrations de BD
- [ ] Modelo User + Favorite
- [ ] AuthController (Register, Login, Logout, Refresh)
- [ ] JWT con Sanctum
- [ ] Autenticación endpoint `POST /auth/register`
- [ ] Autenticación endpoint `POST /auth/login`

**Deliverables**: Autenticación JWT funcional

---

### Fase 3: Integración PokeAPI (Semana 3)

- [ ] PokemonService (consumo PokeAPI)
- [ ] Caching estrategia (Redis)
- [ ] PokemonController
- [ ] Endpoints `/pokemon` (listado + filtros)
- [ ] Endpoint `/pokemon/{id}` (detalle)

**Deliverables**: API Pokémon funcional + cacheada

---

### Fase 4: Favoritos (Semana 4)

- [ ] Migración tabla favorites
- [ ] FavoriteRepository
- [ ] FavoriteController
- [ ] Endpoints POST/DELETE/GET favoritos

**Deliverables**: Persistencia de favoritos

---

### Fase 5: Frontend Auth (Semana 5)

- [ ] Setup Next.js + TypeScript
- [ ] AuthContext + useAuth hook
- [ ] LoginForm component
- [ ] RegisterForm component
- [ ] Protected routes
- [ ] Token management (localStorage)

**Deliverables**: Autenticación UI funcional

---

### Fase 6: Frontend Pokémon (Semana 6)

- [ ] PokemonList component
- [ ] PokemonCard component
- [ ] PokemonFilters component
- [ ] Paginación
- [ ] Búsqueda y filtrado
- [ ] FavoriteButton

**Deliverables**: Listado Pokémon UI

---

### Fase 7: Integración Frontend-Backend (Semana 7)

- [ ] Conectar servicios API
- [ ] Error handling
- [ ] Loading states
- [ ] Favorites UI
- [ ] Testing E2E básico

**Deliverables**: Full stack funcional

---

### Fase 8: Pulido y Deployment (Semana 8)

- [ ] Testing unitario (Backend + Frontend)
- [ ] Documentación API (Swagger)
- [ ] Performance optimization
- [ ] Security audit
- [ ] Build production
- [ ] Deployment a servidor

**Deliverables**: Producción lista

---

## 📊 Checklist Técnico Pre-Inicio

### Backend

- [ ] `composer.json` con dependencias:
  - `laravel/framework: 11.*`
  - `laravel/sanctum: ~3.0`
  - `guzzlehttp/guzzle: ^7.0`
  - `predis/predis: ^2.0`

- [ ] Configuración `.env`:
  ```
  DB_CONNECTION=pgsql
  DB_HOST=postgres
  DB_DATABASE=pokemon_bff
  DB_USERNAME=pokemon_user
  DB_PASSWORD=***
  
  CACHE_DRIVER=redis
  QUEUE_CONNECTION=redis
  SESSION_DRIVER=cookie
  
  JWT_SECRET=***
  POKEAPI_URL=https://pokeapi.co/api/v2
  ```

- [ ] Estructura de carpetas según diagram
- [ ] Archivo `.dockerignore`

### Frontend

- [ ] `package.json` con dependencias:
  - `next: ^14.0`
  - `react: ^18.2`
  - `typescript: ^5.3`
  - `axios: ^1.6`
  - `swr: ^2.2`
  - `tailwindcss: ^3.3`
  - `react-hook-form: ^7.48`

- [ ] Archivo `.env.local`:
  ```
  NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
  ```

- [ ] Estructura de carpetas según diagram
- [ ] `tsconfig.json` configurado
- [ ] `next.config.js` con CORS

### Docker

- [ ] Dockerfiles creados
- [ ] `docker-compose.yml` configurado
- [ ] `docker/nginx/nginx.conf` listo
- [ ] `docker/postgres/init.sql` ready
- [ ] `.gitignore` global

---

## 🔒 Consideraciones de Seguridad

1. **JWT**: Expiración 1 hora, refresh token en httpOnly cookie
2. **CORS**: Solo origen frontend en producción
3. **Rate Limiting**: 100 req/s general, 5 req/min auth
4. **Validación**: Server-side siempre
5. **Hashing**: bcrypt para passwords (Laravel default)
6. **Sanitización**: XSS prevention en responses JSON
7. **SQL Injection**: ORM Laravel + Parameterized queries
8. **HTTPS**: TLS 1.2+ en producción
9. **SECRETS**: Variables de entorno, nunca hardcodeadas
10. **Audit Logging**: Soft deletes + timestamps

---

## 📈 Métricas de Éxito

- ✅ Autenticación: 0 fallos de seguridad (OWASP Top 10)
- ✅ Performance: Listado 150 Pokémon < 200ms
- ✅ Uptime: 99.9% en staging
- ✅ Coverage: 80%+ de cobertura de tests
- ✅ Response: < 50ms promedio en endpoints
- ✅ Favoritos: Persistencia 100% confiable

---

## 📚 Referencias y Recursos

### Documentación Oficial

- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Next.js 14 Docs](https://nextjs.org/docs)
- [PostgreSQL 15 Docs](https://www.postgresql.org/docs/15/)
- [PokeAPI Docs](https://pokeapi.co/docs/v2)
- [Docker Docs](https://docs.docker.com)

### Librerías Recomendadas

**Backend**:
- `laravel/sanctum` - JWT Authentication
- `guzzlehttp/guzzle` - HTTP Client
- `predis/predis` - Redis Client
- `phpunit/phpunit` - Testing

**Frontend**:
- `swr` - Data fetching
- `react-hook-form` - Form management
- `zod` - Schema validation
- `tailwindcss` - Styling
- `vitest` - Testing

---

## 🎯 Próximos Pasos

1. **Crear repositorio Git** y preparar monorepo
2. **Inicializar Docker Compose** y verificar servicios
3. **Scaffolding Laravel** con migraciones
4. **Scaffolding Next.js** con TypeScript
5. **Implementar AuthController** fase por fase
6. **Documentar progreso** en README.md

---

**Documento preparado**: 2026-01-30  
**Versión**: 1.0  
**Autor**: Arquitecto de Software Full-Stack  
**Estado**: Aprobado para implementación
