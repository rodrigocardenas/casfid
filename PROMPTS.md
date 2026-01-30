# 📝 Prompts Principales - Pokémon BFF

> Documento que lista los prompts principales utilizados durante el desarrollo de la aplicación Pokémon BFF (Backend For Frontend)
> IA utilizada: Claude Haiku 4.5 (con VScode y github copilot), ya que dejé de pagar hace unos meses Cursor, y tenía la suscripción a copilot desde antes.


---

## 1. Inicialización del Proyecto

### Prompt 1.1: Setup Inicial
```
Quiero crear una aplicación full-stack que consuma la PokeAPI.
Backend: Laravel 11, PHP 8.2+
Frontend: Next.js 14, React 18, TypeScript
Database: PostgreSQL con usuarios y favoritos
Incluir autenticación JWT custom
Integración con PokeAPI v2
Todo dockerizado con Docker Compose
```

**Resultado**: Estructura base del proyecto, docker-compose.yml, migraciones iniciales

---

## 2. Backend - Autenticación

### Prompt 2.1: Implementar Autenticación Custom
```
Implementar autenticación sin librerías externas:
- Crear tabla users con email, nombre, contraseña (hash)
- Crear modelo User con fillable y casting
- Implementar AuthController con:
  * POST /api/auth/register - validar email único
  * POST /api/auth/login - verificar contraseña con hash
  * POST /api/auth/logout - invalidar token
  * GET /api/auth/me - obtener usuario autenticado
- Token format: userid.random_string.timestamp
- Incluir middleware AuthToken para validar
```

**Resultado**: AuthController completo, AuthToken middleware, rutas de autenticación

---

## 3. Backend - Pokémon API

### Prompt 3.1: Integración PokeAPI
```
Crear servicio para consumir PokeAPI:
- Método getPokemonFromAPI($id): obtener por ID
- Método searchPokemon($type, $generation): filtrar por tipo y generación
- Parsear response PokeAPI para extraer:
  * Nombre, ID (pokedex_id)
  * Imagen (official-artwork front_default)
  * Tipos (múltiples valores separados por coma)
  * Estadísticas (hp, attack, defense, sp-attack, sp-defense, speed)
  * Descripción (desde pokemon-species endpoint)
- Implementar caché Redis por 24 horas
- Manejar errores HTTP 404, 500, timeouts
```

**Resultado**: PokemonService completo con caché y manejo de errores

### Prompt 3.2: Modelo Pokemon y Migraciones
```
Crear tabla pokemon con:
- pokedex_id (int, único, índice)
- name (string)
- types (string - valores separados por coma)
- image_url (string nullable)
- description (text nullable)
- hp, attack, defense, sp_attack, sp_defense, speed (int)
- timestamps

También crear tabla favorites con:
- user_id (FK -> users.id, cascading delete)
- pokemon_id (FK -> pokemon.id, cascading delete)
- pokemon_name, pokemon_type (strings)
- unique constraint (user_id, pokemon_id)
- timestamps

Incluir seeders para 150 Pokémon iniciales
```

**Resultado**: Migraciones, modelos Pokemon y Favorite, seeders

---

## 4. Backend - Favoritos

### Prompt 4.1: Servicio de Favoritos
```
Crear FavoriteService con métodos:

addToFavorites($user, $pokemonId):
- Validar pokemonId (1-1025)
- Consultar PokeAPI
- Crear pokémon en BD si no existe (updateOrCreate con pokedex_id)
- Crear registro en favoritos con pokemon_id de la BD
- Retornar el Favorite con relación pokemon eager loaded
- Manejar errores: conflict (409), not found (404), invalid (422)

removeFromFavorites($user, $pokemonId):
- Buscar pokemon por pokedex_id
- Buscar favorite con el pokemon.id
- Eliminar favorite
- Retornar success

getFavorites($user):
- Eager load pokemon data
- Paginar resultados (10 por página)
- Retornar array con todas las estadísticas del pokémon
- Caché por usuario (1 hora TTL)

Usar logging para auditar acciones
```

**Resultado**: FavoriteService completo con on-demand insertion y caché

### Prompt 4.2: Controlador de Favoritos
```
Crear FavoriteController con endpoints:

POST /api/v1/favorites
- Body: { pokedex_id }
- Validaciones: auth (401), pokemonId 1-1025 (422), duplicate (409)
- Retornar: { success: true, data: favorite, message }

DELETE /api/v1/favorites/{pokedex_id}
- Path param: pokedex_id
- Validaciones: auth (401), not found (404)
- Retornar: { success: true, message }

GET /api/v1/favorites?page=1&limit=10
- Query params: page, limit, sort
- Validaciones: auth (401)
- Retornar: { success: true, data: [...], pagination: {...} }

Middleware CORS, rate limiting
Formato respuestas JSON consistente
```

**Resultado**: FavoriteController con endpoints REST completos

---

## 5. Backend - Testing

### Prompt 5.1: Tests de Integración
```
Crear 15 tests de Feature con Pest:
- Usar RefreshDatabase trait para aislamiento
- Crear Pokémon manualmente en BD (no mocks HTTP)
- Tests POST /favorites: success, 401, 404, 409, 422
- Tests DELETE /favorites: success, 401, 404
- Tests GET /favorites: success, pagination, user isolation
- Tests completos: agregar, listar, eliminar
- Usar factory para usuarios
- Validar responses con estructura { success, data, pagination }

Verificar: 15 tests, 63+ assertions, cobertura completa
```

**Resultado**: 15 tests Feature pasando con 63 assertions

### Prompt 5.2: Tests Unitarios
```
Crear tests unitarios para:

PokemonServiceTest:
- getPokemonFromAPI(): success, caché, 404, 500, parsing stats, tipos múltiples
- searchPokemon(): con tipo y generación
- Manejo de errores HTTP

FavoriteServiceTest:
- addToFavorites(): success, conflict, invalid, PokeAPI errors
- removeFromFavorites(): success, not found
- getFavorites(): con paginación, data completa

Usar Http::fake() para mockear PokeAPI
Verificar caché funciona correctamente
Min. 10 tests por servicio
```

**Resultado**: Unit tests para Services con mocks HTTP

---

## 6. Frontend - Autenticación

### Prompt 6.1: Setup Next.js + Context
```
Crear estructura frontend:
- app/ folder structure (app router)
- Context para autenticación (user, token, login, logout, register)
- Hook useAuth() para acceder contexto
- Variables de entorno: NEXT_PUBLIC_API_URL, NEXT_PUBLIC_API_TIMEOUT

Tipos TypeScript:
- User: { id, email, name, created_at }
- AuthResponse: { success, message, data, token, expires_in }
```

**Resultado**: Context setup, useAuth hook, tipos TypeScript

### Prompt 6.2: Autenticación UI
```
Crear componentes:
- app/register/page.tsx: form con email, nombre, contraseña
- app/login/page.tsx: form con email, contraseña
- app/layout.tsx: navbar con usuario y logout
- Validaciones: email formato, contraseña 8+ chars
- Guardar token en localStorage
- Redireccionar a /favorites después login
- Mostrar errores con toast notifications
```

**Resultado**: Componentes de autenticación funcionales

---

## 7. Frontend - Pokémon y Favoritos

### Prompt 7.1: Listado y Búsqueda
```
Crear página /pokemon/page.tsx:
- Listar pokémon con filtros por tipo
- Búsqueda por nombre
- Cards con imagen, nombre, tipos, botón agregar a favoritos
- Paginación
- Usar apiClient para llamadas HTTP
- Mostrar loading state
- Manejar errores con toast
```

**Resultado**: Página de listado de Pokémon con filtros

### Prompt 7.2: Favoritos Interactivo
```
Crear página /favorites/page.tsx:
- Listar favoritos del usuario autenticado
- Mostrar para cada pokémon:
  * Imagen (image_url)
  * Nombre y tipos
  * Descripción (line-clamp-2)
  * Estadísticas (hp, attack, defense, sp-attack, sp-defense, speed)
- Botón eliminar de favoritos
- Llamar DELETE /api/v1/favorites/{pokedex_id}
- Paginación
- Validar auth: redireccionar a /login si no autenticado
- Toast notifications para acciones
- Estado: loading, empty, error
```

**Resultado**: Página de favoritos interactiva con datos completos

---

## 8. Infrastructure & Deployment

### Prompt 8.1: Docker Compose
```
Crear docker-compose.yml con servicios:
- PostgreSQL 15: puerto 5432, volume persistente
- Redis 7: puerto 6379, volume persistente
- Laravel app: puerto 8000, volume del código
- Next.js: puerto 3000, volume del código

Variables de entorno desde .env
Health checks para cada servicio
Networks para comunicación inter-servicios
Init scripts para DB
Restart policies
```

**Resultado**: docker-compose.yml completo y funcional

### Prompt 8.2: .env.example y Documentación
```
Crear archivos:
- .env.example con todas variables necesarias comentadas
- README.md con:
  * Descripción del proyecto
  * Tecnologías usadas
  * Quick start (5 minutos con Docker)
  * Variables de entorno
  * Cómo ejecutar tests
  * Endpoints API
  * Estructura de carpetas
  * Contribuciones

Incluir badges, screenshots, links a documentación
```

**Resultado**: .env.example completo y README detallado

---

## 9. Git & Code Organization

### Prompt 9.1: Organización en Commits
```
Organizar cambios en 15 commits atómicos:
1. Backend core: models, migrations, base controllers
2. Backend: services (Pokemon, Favorite)
3. Tests: Feature tests (15 tests)
4. Frontend: setup, context, hooks
5. Frontend: auth components
6. Frontend: API client, utils
7. Eliminar archivos innecesarios
8. Docker setup y .env.example
9. README y documentación
10-15. Pequeños fixes y optimizaciones

Usar conventional commits: feat(), fix(), test(), chore(), docs()
```

**Resultado**: Git history limpio con 15 commits atómicos

---

## 10. Evaluación y Refinamiento

### Prompt 10.1: Evaluación contra Criterios
```
Evaluar aplicación contra:
1. Patrón BFF: ¿Backend expone data correctamente formatted?
2. Arquitectura: ¿Capas bien separadas? ¿Responsabilidades claras?
3. Modelado: ¿Entidades correctas? ¿Relaciones bien definidas?
4. Código: ¿Limpio, legible, mantenible?
5. Manejo errores: ¿Excepciones capturadas? ¿Mensajes claros?
6. Tests: ¿Cobertura completa? ¿Casos edge contemplados?
7. Git: ¿Commits atómicos? ¿Mensajes claros?

Dar puntaje 0-10 para cada criterio con justificación
Sugerir mejoras para llegar a 9.5+
```

**Resultado**: Evaluación 8.7/10 con recomendaciones de mejora

---

## 11. Final Deliverables

### Prompt 11.1: Entregables Finales
```
Verificar entrega:
1. ✅ Código Backend (PHP) - app/, config/, routes/
2. ✅ Código Frontend (React + Next.js + TypeScript) - frontend/
3. ✅ README.md con instalación, Docker, tests
4. ✅ Migraciones SQL - database/migrations/
5. ✅ Tests unitarios pasando - tests/Unit/
6. ✅ Tests Feature pasando - tests/Feature/
7. ✅ Documento Prompts - PROMPTS.md
8. ✅ Git organizado - 15 commits atómicos
9. ✅ .env.example - variables documentadas
10. ✅ docker-compose.yml - servicios funcionando
```

**Resultado**: Entrega completa lista para producción

---

## Resumen de Técnicas Utilizadas

| Técnica | Uso |
|---------|-----|
| **BFF Pattern** | Backend solo expone data necesaria para frontend |
| **On-demand Insertion** | Pokémon se crea en BD al agregar a favoritos |
| **Eager Loading** | Eager load de pokemon en favoritos |
| **Dual Caching** | User favorites (1h) + Global pokemon (24h) |
| **Custom Auth** | Token format userid.random.timestamp |
| **RefreshDatabase** | Aislamiento de tests |
| **Http::fake()** | Mocks HTTP para tests unitarios |
| **Docker Compose** | Ambiente reproducible |
| **Atomic Commits** | Historia Git limpia |

---

## Estadísticas Finales

- **15 Prompts principales** utilizados
- **Tiempo estimado**: 40-50 horas de desarrollo
- **Líneas de código**: ~2500+ (backend + frontend + tests)
- **Tests**: 15 Feature + 20+ Unit = 35+ tests totales
- **Cobertura**: ~85% rutas críticas
- **Arquitectura Score**: 8.7/10

---

**Última actualización**: 30 Enero 2026
**Versión**: 1.0 - Release
