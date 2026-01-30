# Favorites System Documentation

> Documentación técnica del sistema de Favoritos de Pokémon BFF

**Version:** 1.0.0  
**Last Updated:** 2024  
**Status:** Production Ready

## Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Arquitectura](#arquitectura)
3. [Endpoints API](#endpoints-api)
4. [Validaciones](#validaciones)
5. [Casos de Error](#casos-de-error)
6. [Testing](#testing)
7. [Ejemplos de Uso](#ejemplos-de-uso)
8. [Consideraciones de Seguridad](#consideraciones-de-seguridad)

---

## Descripción General

El sistema de Favoritos permite a usuarios autenticados guardar, listar y eliminar sus Pokémon favoritos. 

**Características principales:**
- ✅ Validación en PokeAPI antes de guardar
- ✅ Prevención de duplicados a nivel DB y aplicación
- ✅ Paginación en listados
- ✅ Aislamiento de datos por usuario
- ✅ Logging completo de operaciones
- ✅ Manejo robusto de errores

---

## Arquitectura

### Estructura de Carpetas

```
app/
├── Models/
│   ├── User.php                    (Already exists)
│   └── Favorite.php               (Already exists)
├── Http/
│   ├── Controllers/
│   │   └── FavoriteController.php  (NEW)
│   └── Requests/
│       └── FavoriteRequest.php     (NEW)
├── Services/
│   └── FavoriteService.php         (NEW)
└── Exceptions/
    └── FavoriteServiceException.php (NEW)

tests/
├── Unit/
│   └── Services/
│       └── FavoriteServiceTest.php         (NEW)
└── Feature/
    └── Controllers/
        └── FavoriteControllerTest.php      (NEW)

routes/
└── api.php                          (MODIFIED)
```

### Flujo de Arquitectura

```
HTTP Request
    ↓
FavoriteController (HTTP Layer)
    ↓
FavoriteRequest (Validation)
    ↓
FavoriteService (Business Logic)
    ├── Validate Pokemon ID (1-150)
    ├── Call PokeAPI v2
    ├── Check Uniqueness
    └── Persist to Database
    ↓
HTTP Response (JSON)
```

### Relaciones de Modelos

```
User (1) ──────→ (N) Favorite
  id              id
  name            user_id (FK)
  email           pokemon_id (1-150)
  password        pokemon_name
  ...             pokemon_type
                  created_at
                  updated_at

Constraint: UNIQUE (user_id, pokemon_id)
```

---

## Endpoints API

### 1. POST /api/v1/favorites

**Agregar un Pokémon a favoritos**

```
POST /api/v1/favorites HTTP/1.1
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json

{
    "pokemon_id": 25
}
```

**Parámetros:**
- `pokemon_id` (required, integer): ID del Pokémon (1-150)

**Respuesta Exitosa (201 Created):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 5,
        "pokemon_id": 25,
        "pokemon_name": "Pikachu",
        "pokemon_type": "electric",
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
    },
    "message": "Pokemon added to favorites",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

**Respuestas de Error:**
- `400 Bad Request`: ID de Pokémon inválido
- `401 Unauthorized`: Falta token JWT
- `409 Conflict`: Pokémon ya en favoritos
- `503 Service Unavailable`: PokeAPI no disponible

---

### 2. GET /api/v1/favorites

**Listar favoritos del usuario**

```
GET /api/v1/favorites?page=1&per_page=15 HTTP/1.1
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json
```

**Query Parameters:**
- `page` (optional, integer): Número de página (default: 1)
- `per_page` (optional, integer): Items por página (default: 15, max: 100)

**Respuesta Exitosa (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "pokemon_id": 25,
            "pokemon_name": "Pikachu",
            "pokemon_type": "electric",
            "created_at": "2024-01-15T10:30:00Z",
            "updated_at": "2024-01-15T10:30:00Z"
        },
        {
            "id": 2,
            "user_id": 5,
            "pokemon_id": 39,
            "pokemon_name": "Jigglypuff",
            "pokemon_type": "normal,fairy",
            "created_at": "2024-01-15T10:31:00Z",
            "updated_at": "2024-01-15T10:31:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 42,
        "total_pages": 3,
        "has_more": true
    },
    "timestamp": "2024-01-15T10:30:00Z"
}
```

**Respuestas de Error:**
- `401 Unauthorized`: Falta token JWT
- `404 Not Found`: Página inválida

---

### 3. DELETE /api/v1/favorites/{pokemon_id}

**Eliminar un Pokémon de favoritos**

```
DELETE /api/v1/favorites/25 HTTP/1.1
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json
```

**Parámetros:**
- `pokemon_id` (required, path): ID del Pokémon a eliminar

**Respuesta Exitosa (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 5,
        "pokemon_id": 25,
        "pokemon_name": "Pikachu",
        "pokemon_type": "electric"
    },
    "message": "Pokemon removed from favorites",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

**Respuestas de Error:**
- `400 Bad Request`: ID de Pokémon inválido (< 1 o > 150)
- `401 Unauthorized`: Falta token JWT
- `404 Not Found`: Pokémon no en favoritos

---

## Validaciones

### Validaciones de Entrada

**FavoriteRequest:**

```php
// pokemon_id
- Required
- Type: integer
- Min: 1
- Max: 150
- Messages: Español
```

**Mensajes de Validación:**

| Campo | Regla | Mensaje |
|-------|-------|---------|
| pokemon_id | required | El campo pokemon_id es requerido |
| pokemon_id | integer | El campo pokemon_id debe ser un entero |
| pokemon_id | min | El pokemon_id debe ser mayor o igual a 1 |
| pokemon_id | max | El pokemon_id debe ser menor o igual a 150 |

### Validaciones de Negocio

**FavoriteService::validatePokemonExists()**

```php
// 1. Valida rango 1-150
if ($pokemonId < 1 || $pokemonId > 150) {
    throw new InvalidPokemonIdException();
}

// 2. Llama PokeAPI v2
$response = Http::timeout(10)
    ->get("https://pokeapi.co/api/v2/pokemon/{$pokemonId}");

// 3. Valida respuesta
if ($response->failed()) {
    throw new PokemonNotFoundException();
}

// 4. Extrae datos
$pokemon = $response->json();
$name = $pokemon['name'];
$types = collect($pokemon['types'])
    ->pluck('type.name')
    ->implode(',');
```

### Prevención de Duplicados

**A Nivel DB:**
```sql
UNIQUE (user_id, pokemon_id)
```

**A Nivel Aplicación:**
```php
// En FavoriteService::addToFavorites()
$existingFavorite = $this->favoriteRepository
    ->findByUserAndPokemon($user, $pokemonId);

if ($existingFavorite) {
    throw new PokemonAlreadyFavoritedException();
}
```

---

## Casos de Error

### Error 400 - Bad Request

**Causas:**
- ID de Pokémon fuera del rango 1-150
- ID de Pokémon no es entero
- Parámetro pokemon_id no es enviado

**Respuesta:**
```json
{
    "success": false,
    "error": "Invalid pokemon_id",
    "message": "Pokemon ID must be between 1 and 150",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Error 401 - Unauthorized

**Causas:**
- Falta token JWT
- Token JWT inválido
- Token JWT expirado

**Respuesta:**
```json
{
    "message": "Unauthenticated.",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Error 404 - Not Found

**Causas (GET Favorites):**
- Página solicitada no existe
- Usuario no tiene favoritos en esa página

**Causas (DELETE Favorite):**
- Pokémon no está en favoritos del usuario
- ID de Pokémon inválido

**Respuesta:**
```json
{
    "success": false,
    "error": "Favorite not found",
    "message": "Pokemon not in user favorites",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Error 409 - Conflict

**Causa:**
- Pokémon ya está en favoritos del usuario

**Respuesta:**
```json
{
    "success": false,
    "error": "Pokemon already in favorites",
    "message": "This pokemon is already in your favorites",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Error 422 - Unprocessable Entity

**Causa:**
- Validación de request falló

**Respuesta:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "pokemon_id": [
            "El campo pokemon_id es requerido"
        ]
    },
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Error 503 - Service Unavailable

**Causa:**
- PokeAPI no responde o está en timeout
- Error en PokeAPI (status 500)

**Respuesta:**
```json
{
    "success": false,
    "error": "Service unavailable",
    "message": "Unable to verify pokemon in PokeAPI",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

---

## Testing

### Unit Tests (PHPUnit)

**Archivo:** `tests/Unit/Services/FavoriteServiceTest.php`

**Casos de Prueba:**

| # | Prueba | Propósito | Mock |
|---|--------|----------|------|
| 1 | test_add_to_favorites_success | Agregar favorito exitosamente | PokeAPI 200 |
| 2 | test_add_to_favorites_conflict | Rechazar duplicado | PokeAPI 200 |
| 3 | test_add_to_favorites_invalid_id | Rechazar ID inválido | N/A |
| 4 | test_add_to_favorites_pokeapi_not_found | PokeAPI retorna 404 | PokeAPI 404 |
| 5 | test_add_to_favorites_pokeapi_timeout | Timeout en PokeAPI | PokeAPI timeout |
| 6 | test_remove_from_favorites_success | Eliminar exitosamente | N/A |
| 7 | test_remove_from_favorites_not_found | Rechazar no existente | N/A |
| 8 | test_get_favorites | Listar favoritos | N/A |
| 9 | test_get_favorites_empty | Listar cuando está vacío | N/A |
| 10 | test_is_favorite_true | Validar que es favorito | N/A |
| 11 | test_is_favorite_false | Validar que no es favorito | N/A |
| 12 | test_pokeapi_called_correctly | Verificar llamada a PokeAPI | PokeAPI mock |

**Ejecutar:**
```bash
# Todos los tests
docker-compose exec backend php artisan test tests/Unit/Services/FavoriteServiceTest.php

# Con output detallado
docker-compose exec backend php artisan test tests/Unit/Services/FavoriteServiceTest.php --verbose

# Solo un test
docker-compose exec backend php artisan test tests/Unit/Services/FavoriteServiceTest.php --filter=test_add_to_favorites_success
```

### Feature Tests (Integration)

**Archivo:** `tests/Feature/Controllers/FavoriteControllerTest.php`

**Casos de Prueba:**

| # | Prueba | Propósito |
|---|--------|----------|
| 1 | test_post_favorites_success | POST exitoso |
| 2 | test_post_favorites_unauthorized | POST sin autenticación |
| 3 | test_post_favorites_conflict | POST con duplicado |
| 4 | test_post_favorites_invalid_id | POST con ID inválido |
| 5 | test_post_favorites_missing_pokemon_id | POST sin pokemon_id |
| 6 | test_delete_favorite_success | DELETE exitoso |
| 7 | test_delete_favorite_not_found | DELETE no encontrado |
| 8 | test_delete_favorite_unauthorized | DELETE sin autenticación |
| 9 | test_get_favorites_success | GET exitoso |
| 10 | test_get_favorites_empty | GET vacío |
| 11 | test_get_favorites_unauthorized | GET sin autenticación |
| 12 | test_get_favorites_pagination | GET con paginación |
| 13 | test_get_favorites_invalid_page | GET página inválida |
| 14 | test_favorites_isolated_by_user | Aislamiento por usuario |
| 15 | test_favorites_complete_flow | Flujo completo (add→list→delete) |

**Ejecutar:**
```bash
# Todos los tests de feature
docker-compose exec backend php artisan test tests/Feature/Controllers/FavoriteControllerTest.php

# Con output detallado
docker-compose exec backend php artisan test tests/Feature/Controllers/FavoriteControllerTest.php --verbose

# Un test específico
docker-compose exec backend php artisan test tests/Feature/Controllers/FavoriteControllerTest.php --filter=test_favorites_complete_flow
```

### Bash Test Script

**Archivo:** `test-favorites.sh`

**Características:**
- Tests manuales sin PHPUnit
- Colores en output
- Registro de éxito/fallo
- Requiere Docker corriendo

**Ejecutar:**
```bash
# Hacer ejecutable
chmod +x test-favorites.sh

# Ejecutar
./test-favorites.sh
```

**Output Ejemplo:**
```
╔════════════════════════════════════════════════════════╗
║ Test Script: Favorites Endpoints                       ║
╚════════════════════════════════════════════════════════╝

[SETUP] Obtener JWT Token
✓ Got JWT token: eyJ0eXAiOiJKV1QiLCJhbGc...

[TEST] Add Pokemon 1 to favorites
Request: POST /api/v1/favorites
✓ Pokemon added to favorites (201)

...

╔════════════════════════════════════════════════════════╗
║ Test Summary                                           ║
╚════════════════════════════════════════════════════════╝
Total Tests:  8
Passed:       8
Failed:       0

All tests passed! ✓
```

---

## Ejemplos de Uso

### 1. Workflow Completo

```bash
# 1. Autenticarse
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"Password123!"}' \
  | jq -r '.data.token')

# 2. Agregar favorito
curl -X POST http://localhost:8000/api/v1/favorites \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"pokemon_id": 25}' \
  | jq '.'

# 3. Listar favoritos
curl -X GET "http://localhost:8000/api/v1/favorites?page=1&per_page=10" \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.'

# 4. Eliminar favorito
curl -X DELETE http://localhost:8000/api/v1/favorites/25 \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.'
```

### 2. cURL - Agregar Favorito

```bash
curl -X POST http://localhost:8000/api/v1/favorites \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "pokemon_id": 39
  }'
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "user_id": 3,
        "pokemon_id": 39,
        "pokemon_name": "Jigglypuff",
        "pokemon_type": "normal,fairy",
        "created_at": "2024-01-15T15:45:30Z",
        "updated_at": "2024-01-15T15:45:30Z"
    },
    "message": "Pokemon added to favorites",
    "timestamp": "2024-01-15T15:45:30Z"
}
```

### 3. cURL - Listar Favoritos

```bash
# Página 1 con 10 items
curl -X GET "http://localhost:8000/api/v1/favorites?page=1&per_page=10" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 3,
            "pokemon_id": 1,
            "pokemon_name": "Bulbasaur",
            "pokemon_type": "grass,poison",
            "created_at": "2024-01-15T14:20:00Z",
            "updated_at": "2024-01-15T14:20:00Z"
        },
        {
            "id": 5,
            "user_id": 3,
            "pokemon_id": 39,
            "pokemon_name": "Jigglypuff",
            "pokemon_type": "normal,fairy",
            "created_at": "2024-01-15T15:45:30Z",
            "updated_at": "2024-01-15T15:45:30Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 42,
        "total_pages": 5,
        "has_more": true
    },
    "timestamp": "2024-01-15T15:50:00Z"
}
```

### 4. cURL - Eliminar Favorito

```bash
curl -X DELETE http://localhost:8000/api/v1/favorites/39 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "user_id": 3,
        "pokemon_id": 39,
        "pokemon_name": "Jigglypuff",
        "pokemon_type": "normal,fairy"
    },
    "message": "Pokemon removed from favorites",
    "timestamp": "2024-01-15T15:52:00Z"
}
```

### 5. JavaScript/Fetch API

```javascript
// Agregar favorito
async function addFavorite(pokemonId) {
    const token = localStorage.getItem('jwt_token');
    
    const response = await fetch('/api/v1/favorites', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ pokemon_id: pokemonId })
    });
    
    return await response.json();
}

// Listar favoritos
async function getFavorites(page = 1) {
    const token = localStorage.getItem('jwt_token');
    
    const response = await fetch(`/api/v1/favorites?page=${page}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        }
    });
    
    return await response.json();
}

// Eliminar favorito
async function removeFavorite(pokemonId) {
    const token = localStorage.getItem('jwt_token');
    
    const response = await fetch(`/api/v1/favorites/${pokemonId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        }
    });
    
    return await response.json();
}

// Uso
(async () => {
    // Agregar
    const addResult = await addFavorite(25);
    console.log('Added:', addResult);
    
    // Listar
    const favorites = await getFavorites(1);
    console.log('Favorites:', favorites.data);
    
    // Eliminar
    const removeResult = await removeFavorite(25);
    console.log('Removed:', removeResult);
})();
```

---

## Consideraciones de Seguridad

### 1. Autenticación

✅ **Implementado:**
- JWT en encabezado `Authorization: Bearer <token>`
- Middleware `auth:api` en todas las rutas
- Validación de token en cada request

### 2. Autorización

✅ **Implementado:**
- Usuario solo puede ver sus favoritos
- Usuario solo puede eliminar sus favoritos
- Aislamientode datos por usuario

### 3. Validación de Entrada

✅ **Implementado:**
- FormRequest con reglas de validación
- Rango de Pokemon 1-150
- Tipo de dato verificado
- Mensajes de error localizados

### 4. Rate Limiting

📝 **Recomendado:**
```php
// En routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // Favoritos endpoints
});
```

### 5. Auditoría

✅ **Implementado:**
- Logging de todas las operaciones
- Timestamps en cada registro
- user_id asociado a cada operación

### 6. Timeout PokeAPI

✅ **Implementado:**
- Timeout de 10 segundos
- Manejo de errores en PokeAPI
- Respuesta 503 si falla

### 7. SQL Injection

✅ **Protegido:**
- Eloquent ORM
- Parameterized queries
- No hay SQL crudo

### 8. CORS

📝 **Verificar en config/cors.php:**
```php
'allowed_origins' => ['http://localhost:3000', 'https://frontend.example.com'],
```

---

## Resumen Técnico

| Aspecto | Detalle |
|--------|---------|
| **Versión** | 1.0.0 |
| **Endpoints** | 3 (POST, GET, DELETE) |
| **Tests Unitarios** | 12 casos |
| **Tests Feature** | 15 casos |
| **Líneas de Código** | 1000+ |
| **Validaciones** | 8 reglas |
| **Errores Manejados** | 6 tipos |
| **Rate Limit** | Recomendado 60/min |
| **Timeout PokeAPI** | 10 segundos |
| **DB Constraint** | UNIQUE (user_id, pokemon_id) |

---

## Contacto & Soporte

Para preguntas o problemas:
- Revisar `test-favorites.sh` para validar endpoints
- Ejecutar `php artisan test` para validar lógica
- Consultar logs en `storage/logs/laravel.log`

---

**© 2024 Pokémon BFF - All Rights Reserved**
