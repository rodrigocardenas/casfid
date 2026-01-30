# 📚 BACKEND POKEMON - Fase 3.2

**Implementación del dominio de Pokémon con PokeAPI v2**

Generado: 2026-01-30 | Fase: 3.2 PokeAPI Integration

---

## 🎯 Visión General

Esta fase implementa la integración con **PokeAPI v2** para consumir datos de los primeros 150 Pokémon (Generación 1). El BFF actúa como intermediario entre el frontend y PokeAPI, proporcionando:

- ✅ Listado paginado de 150 Pokémon
- ✅ Búsqueda por nombre
- ✅ Filtrado por tipo
- ✅ Detalles completos por Pokémon
- ✅ Caché en Redis (24 horas)
- ✅ Manejo de errores graceful si PokeAPI falla

**Beneficios de esta arquitectura:**
- Frontend NO depende de PokeAPI directamente
- Control centralizado de datos
- Caché reduce carga en PokeAPI
- Posibilidad de agregar información personalizada (favoritos, puntuaciones)
- Mejor rendimiento con requests comprimidas

---

## 🏗️ Arquitectura

### Flujo de Datos

```
Frontend (Next.js)
    ↓
GET /api/v1/pokemon (con filtros)
    ↓
PokemonController::index()
    ↓ (valida parámetros)
    ↓
PokemonService::getPokemonList()
    ↓
    ├─→ Cache::get('pokemon:generation:1') ✓ Retorna
    ↓ (no encontrado en caché)
    │
    ├─→ PokemonService::fetchAllPokemonFromApi()
    │   ├─→ Http::get('https://pokeapi.co/api/v2/pokemon?offset=0&limit=50')
    │   ├─→ Itera en bloques de 50 (primeros 150)
    │   └─→ Normaliza datos: {id, name, image, types}
    │
    ├─→ Cache::put('pokemon:generation:1', $data, 86400) ← 24 horas
    ↓
    └─→ Paginar y retornar datos
        ↓
Response 200 JSON con data + pagination
```

### Capas de la Aplicación

```
┌─────────────────────────────────┐
│    PokemonController            │ ← Valida requests, maneja errores
├─────────────────────────────────┤
│    PokemonService               │ ← Lógica de negocio
├─────────────────────────────────┤
│    Redis Cache (24h)            │ ← Almacena respuestas
├─────────────────────────────────┤
│    Http Client (Guzzle)         │ ← Conecta con PokeAPI
├─────────────────────────────────┤
│    PokeAPI v2                   │ ← Fuente de datos
└─────────────────────────────────┘
```

---

## 📡 Endpoints Implementados

### 1. GET /api/v1/pokemon

**Obtiene lista paginada de Pokémon con filtros**

#### Request

```bash
curl -X GET "http://localhost:8000/api/v1/pokemon?page=1&per_page=20&type=water&search=squir" \
  -H "Accept: application/json"
```

#### Query Parameters

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `page` | int | 1 | Número de página |
| `per_page` | int | 20 | Items por página (máx 50) |
| `type` | string | null | Filtrar por tipo (fire, water, grass, etc) |
| `search` | string | null | Buscar por nombre (case-insensitive) |

#### Response 200 OK

```json
{
  "success": true,
  "data": [
    {
      "id": 7,
      "name": "Squirtle",
      "image": "https://raw.githubusercontent.com/.../7.png",
      "types": ["water"]
    },
    {
      "id": 8,
      "name": "Wartortle",
      "image": "https://raw.githubusercontent.com/.../8.png",
      "types": ["water"]
    },
    {
      "id": 9,
      "name": "Blastoise",
      "image": "https://raw.githubusercontent.com/.../9.png",
      "types": ["water"]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 3,
    "total_pages": 1,
    "has_next": false,
    "has_prev": false
  },
  "timestamp": "2026-01-30T16:29:00Z"
}
```

#### Response 503 Service Unavailable (PokeAPI down)

```json
{
  "success": false,
  "error": "Pokémon service temporarily unavailable",
  "message": "Failed to fetch pokemon from PokeAPI",
  "timestamp": "2026-01-30T16:29:00Z"
}
```

#### Ejemplos de Uso

```bash
# Listado básico
curl "http://localhost:8000/api/v1/pokemon"

# Página 2 con 10 items
curl "http://localhost:8000/api/v1/pokemon?page=2&per_page=10"

# Filtrar por tipo
curl "http://localhost:8000/api/v1/pokemon?type=fire"

# Buscar por nombre
curl "http://localhost:8000/api/v1/pokemon?search=charmander"

# Combinar filtros
curl "http://localhost:8000/api/v1/pokemon?type=grass&search=bulba&page=1"
```

---

### 2. GET /api/v1/pokemon/{id}

**Obtiene detalles completos de un Pokémon**

#### Request

```bash
curl -X GET "http://localhost:8000/api/v1/pokemon/1" \
  -H "Accept: application/json"
```

#### Path Parameters

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `id` | int | ID del Pokémon (1-150) |

#### Response 200 OK

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Bulbasaur",
    "image": "https://raw.githubusercontent.com/.../1.png",
    "types": ["grass", "poison"],
    "height": 0.7,
    "weight": 6.9,
    "base_experience": 64,
    "abilities": ["Overgrow", "Chlorophyll"],
    "stats": {
      "HP": 45,
      "Attack": 49,
      "Defense": 49,
      "Sp. Attack": 65,
      "Sp. Defense": 65,
      "Speed": 45
    }
  },
  "timestamp": "2026-01-30T16:29:00Z"
}
```

#### Response 404 Not Found

```json
{
  "success": false,
  "error": "Pokemon not found",
  "timestamp": "2026-01-30T16:29:00Z"
}
```

#### Response 400 Bad Request (ID inválido)

```json
{
  "success": false,
  "error": "Invalid pokemon ID. Must be between 1 and 150",
  "timestamp": "2026-01-30T16:29:00Z"
}
```

#### Ejemplos de Uso

```bash
# Obtener detalles de Bulbasaur
curl "http://localhost:8000/api/v1/pokemon/1"

# Obtener detalles de Charizard
curl "http://localhost:8000/api/v1/pokemon/6"

# ID inválido (retorna 400)
curl "http://localhost:8000/api/v1/pokemon/999"
```

---

### 3. GET /api/v1/pokemon/filters

**Obtiene lista de tipos disponibles para filtros**

#### Request

```bash
curl -X GET "http://localhost:8000/api/v1/pokemon/filters" \
  -H "Accept: application/json"
```

#### Response 200 OK

```json
{
  "success": true,
  "data": {
    "types": [
      "normal", "fighting", "flying", "poison", "ground", "rock",
      "bug", "ghost", "steel", "fire", "water", "grass",
      "electric", "psychic", "ice", "dragon", "dark", "fairy"
    ]
  },
  "timestamp": "2026-01-30T16:29:00Z"
}
```

---

## 🔧 Componentes Implementados

### PokemonService

**Ubicación:** `app/Services/PokemonService.php` (400+ líneas)

#### Métodos Públicos

```php
// Obtiene lista paginada con filtros
public function getPokemonList(
    int $page = 1,
    int $perPage = 20,
    ?string $type = null,
    ?string $search = null
): array

// Obtiene detalles completos de un pokémon
public function getPokemonDetail(int $pokemonId): array
```

#### Características

- **Caché Redis:** 24 horas (86400 segundos)
- **Rate Limiting:** PokeAPI tiene límite de 100 requests/minuto
- **Normalización:** Convierte respuesta de PokeAPI al formato del BFF
- **Error Handling:** Excepción específica si PokeAPI falla
- **Logging:** Registra todas las operaciones
- **Timeout:** 10 segundos máximo por request

#### Flujo de Caché

```
Primera solicitud (caché vacío):
  getPokemonList() → fetchGeneration1Pokemon()
    → MISS en caché
    → Http::get() a PokeAPI (50 + 50 + 51 = 3 requests)
    → Cache::put() por 24h
    → Paginar y retornar

Solicitudes siguientes (caché válido):
  getPokemonList() → fetchGeneration1Pokemon()
    → HIT en caché
    → Retornar inmediatamente (ms)
```

### PokemonController

**Ubicación:** `app/Http/Controllers/PokemonController.php` (250+ líneas)

#### Métodos

```php
// GET /api/v1/pokemon
public function index(Request $request): JsonResponse

// GET /api/v1/pokemon/{id}
public function show(int $id): JsonResponse

// GET /api/v1/pokemon/filters
public function filters(): JsonResponse
```

#### Validaciones

- `page`: integer, min 1
- `per_page`: integer, min 1, max 50
- `type`: string, max 20 caracteres
- `search`: string, max 100 caracteres
- `id`: integer, rango 1-150

#### Manejo de Errores

| Escenario | HTTP Code | Error |
|-----------|-----------|-------|
| Página inválida | 404 | "Page X not found" |
| Parámetros inválidos | 400 | Mensajes de validación |
| Pokémon ID fuera de rango | 400 | "Invalid pokemon ID" |
| Pokémon no encontrado | 404 | "Pokemon not found" |
| PokeAPI no responde | 503 | "Service temporarily unavailable" |

### Routes

**Ubicación:** `routes/api.php` (actualizado)

```php
// Públicos (sin autenticación)
Route::get('/pokemon', [PokemonController::class, 'index']);
Route::get('/pokemon/{id}', [PokemonController::class, 'show']);
Route::get('/pokemon/filters', [PokemonController::class, 'filters']);
```

---

## 🛡️ Manejo de Errores

### Estrategia 1: Caché como Fallback

Si PokeAPI falla pero tenemos datos en caché, los retornamos:

```php
// Si el caché no ha expirado (24h), aunque PokeAPI falle
// los datos antiguos se retornan automáticamente
```

### Estrategia 2: Error Descriptivo

Si PokeAPI falla sin caché:

```json
{
  "success": false,
  "error": "Pokémon service temporarily unavailable",
  "message": "Failed to fetch pokemon from PokeAPI",
  "timestamp": "2026-01-30T16:29:00Z"
}
```

### Estrategia 3: Logging

Todas las operaciones se registran:

```php
Log::info('Generation 1 pokemon fetched from API', ['count' => 150]);
Log::warning('Pokemon detail from cache', ['id' => 1]);
Log::error('Error fetching pokemon list', ['error' => '...']);
```

---

## 🚀 Integración Frontend

### Cliente Axios

```typescript
// src/services/pokemonApi.ts
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  timeout: 10000,
});

export const pokemonService = {
  // Obtener lista paginada
  list(page = 1, perPage = 20, type?: string, search?: string) {
    return api.get('/pokemon', {
      params: { page, per_page: perPage, type, search }
    });
  },

  // Obtener detalles
  getDetail(id: number) {
    return api.get(`/pokemon/${id}`);
  },

  // Obtener filtros
  getFilters() {
    return api.get('/pokemon/filters');
  }
};
```

### Componente React

```typescript
// src/components/PokemonList.tsx
import { useEffect, useState } from 'react';
import { pokemonService } from '@/services/pokemonApi';

export function PokemonList() {
  const [pokemon, setPokemon] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  useEffect(() => {
    setLoading(true);
    pokemonService.list(page)
      .then(res => setPokemon(res.data.data))
      .catch(err => console.error(err))
      .finally(() => setLoading(false));
  }, [page]);

  if (loading) return <div>Cargando...</div>;

  return (
    <div>
      {pokemon.map(p => (
        <div key={p.id}>
          <img src={p.image} alt={p.name} />
          <h3>{p.name}</h3>
          <p>{p.types.join(', ')}</p>
        </div>
      ))}
      <button onClick={() => setPage(p => p + 1)}>Siguiente</button>
    </div>
  );
}
```

---

## 🔍 Testing

### Test Scripts

```bash
# Listado básico
curl "http://localhost:8000/api/v1/pokemon"

# Página 2
curl "http://localhost:8000/api/v1/pokemon?page=2"

# Filtrar por tipo agua
curl "http://localhost:8000/api/v1/pokemon?type=water"

# Buscar Charizard
curl "http://localhost:8000/api/v1/pokemon?search=charmander"

# Detalles de Charizard
curl "http://localhost:8000/api/v1/pokemon/6"

# Filtros disponibles
curl "http://localhost:8000/api/v1/pokemon/filters"
```

### Verificar Caché

```bash
# Primera solicitud (va a PokeAPI)
curl "http://localhost:8000/api/v1/pokemon?page=1"
# En logs: "Generation 1 pokemon fetched from API"

# Segunda solicitud (devuelve caché)
curl "http://localhost:8000/api/v1/pokemon?page=1"
# En logs: "Generation 1 pokemon from cache"
```

### Simular Error de PokeAPI

```bash
# Detener internet/PokeAPI
# Primera solicitud falla con 503
curl "http://localhost:8000/api/v1/pokemon"
# {
#   "success": false,
#   "error": "Pokémon service temporarily unavailable"
# }
```

---

## 📊 Estadísticas

| Métrica | Valor |
|---------|-------|
| Archivos nuevos | 2 |
| Líneas de código | ~650 |
| Endpoints | 3 |
| Métodos en Service | 6 |
| Métodos en Controller | 3 |
| Tipos soportados | 18 |
| Pokémon soportados | 150 (Gen 1) |
| Caché TTL | 24h |
| Request timeout | 10s |

---

## 🎬 Flujo de Implementación

### Paso 1: Verificar PokemonService

```bash
# Revisar app/Services/PokemonService.php
cat app/Services/PokemonService.php

# Verificar endpoints PokeAPI
curl "https://pokeapi.co/api/v2/pokemon?limit=1"
```

### Paso 2: Verificar PokemonController

```bash
# Revisar app/Http/Controllers/PokemonController.php
cat app/Http/Controllers/PokemonController.php

# Verificar que hereda de Controller
grep "extends" app/Http/Controllers/PokemonController.php
```

### Paso 3: Verificar Rutas

```bash
# Listar todas las rutas
docker-compose exec backend php artisan route:list | grep pokemon

# Output esperado:
#   GET /api/v1/pokemon
#   GET /api/v1/pokemon/{id}
#   GET /api/v1/pokemon/filters
```

### Paso 4: Probar Endpoints

```bash
# Dentro del contenedor
docker-compose exec backend php artisan tinker

# Crear instancia del service
$service = app(\App\Services\PokemonService::class);

# Obtener primeros 3 pokémon
$pokemon = $service->getPokemonList(page: 1, perPage: 3);
dd($pokemon);
```

---

## 🔐 Consideraciones de Seguridad

### CORS (Si es necesario)

```php
// config/cors.php
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['GET', 'OPTIONS'],
'max_age' => 86400,
```

### Rate Limiting (Opcional para frontend)

```php
// Limitar requests a /pokemon a 100 por minuto por IP
Route::get('/pokemon', [PokemonController::class, 'index'])
    ->middleware('throttle:100,1');
```

### Validación de Entrada

- Todos los parámetros son validados
- SQL injection protegido (no usamos SQL crudo)
- XSS protegido (respuestas JSON)

---

## 📚 Archivos Generados

| Archivo | Líneas | Descripción |
|---------|--------|-------------|
| `app/Services/PokemonService.php` | 400+ | Servicio de PokeAPI |
| `app/Http/Controllers/PokemonController.php` | 250+ | Endpoints API |
| `app/Http/Requests/PokemonIndexRequest.php` | 50+ | Validaciones |
| `routes/api.php` | +8 | Nuevas rutas |
| `BACKEND_POKEMON.md` | 500+ | Esta documentación |

---

## 🎯 Próximos Pasos

### Fase 3.3: Favoritos
- Endpoints para agregar/eliminar favoritos
- Listar favoritos por usuario
- Integración con User model

### Fase 3.4: Frontend
- Componentes de listado
- Búsqueda y filtros
- Detalles de pokémon
- Sistema de favoritos

### Fase 4: Optimizaciones
- GraphQL opcional
- WebSockets para actualizaciones
- Analytics de consultas
- A/B testing

---

## 📞 Referencias

**PokeAPI v2 Documentación:** https://pokeapi.co/docs/v2

**Generación 1 (151 pokémon):**
- Bulbasaur (#1) a Mewtwo (#150)
- Mew (#151) incluido

**Tipos disponibles:**
```
normal, fighting, flying, poison, ground, rock,
bug, ghost, steel, fire, water, grass,
electric, psychic, ice, dragon, dark, fairy
```

---

**Status:** ✅ COMPLETADO | Fecha: 2026-01-30 | Versión: 1.0
