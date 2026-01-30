# ⚡ QUICKSTART - Fase 3.2 (Pokemon API)

**Setup en 5 minutos**

---

## 🚀 Quick Start

### 1️⃣ Verificar que el código está en su lugar (30 seg)

```bash
# Verificar archivos creados
ls -la app/Services/PokemonService.php
ls -la app/Http/Controllers/PokemonController.php
ls -la routes/api.php

# Verificar las rutas están registradas
docker-compose exec backend php artisan route:list | grep pokemon
```

### 2️⃣ Ejecutar tests (2 min)

```bash
# Dar permisos
chmod +x test-pokemon.sh

# Ejecutar tests
bash test-pokemon.sh
```

**Output esperado:**
```
✓ Status: 200 (expected 200)
✓ Valid JSON response
✓ success: true
```

### 3️⃣ Probar endpoints principales (1 min)

```bash
# Listado básico
curl "http://localhost:8000/api/v1/pokemon"

# Búsqueda
curl "http://localhost:8000/api/v1/pokemon?search=pikachu"

# Filtro por tipo
curl "http://localhost:8000/api/v1/pokemon?type=water"

# Detalle de pokémon
curl "http://localhost:8000/api/v1/pokemon/25"

# Filtros disponibles
curl "http://localhost:8000/api/v1/pokemon/filters"
```

### 4️⃣ Verificar caché (1 min)

```bash
# Primera solicitud (va a PokeAPI)
curl "http://localhost:8000/api/v1/pokemon?page=1"
# En logs: "Generation 1 pokemon fetched from API"

# Segunda solicitud (del caché)
curl "http://localhost:8000/api/v1/pokemon?page=1"
# En logs: "Generation 1 pokemon from cache"
```

---

## 📡 Endpoints

| Endpoint | Método | Autenticación | Descripción |
|----------|--------|---------------|-------------|
| `/api/v1/pokemon` | GET | ❌ No | Listado paginado (150 Pokémon Gen 1) |
| `/api/v1/pokemon/{id}` | GET | ❌ No | Detalles completos |
| `/api/v1/pokemon/filters` | GET | ❌ No | Tipos disponibles |

---

## 🔧 Query Parameters

### GET /api/v1/pokemon

```bash
# Todas las combinaciones válidas:

# Paginación
?page=2                          # Página 2
?per_page=10                     # 10 items por página

# Filtros
?type=fire                       # Solo tipo fuego
?search=charmander               # Búsqueda por nombre

# Combinados
?page=1&per_page=20&type=water&search=squir

# Límites
# - page: mínimo 1
# - per_page: 1-50 (máximo)
# - type: string válido
# - search: máximo 100 caracteres
```

---

## 📊 Ejemplos de Respuesta

### Listado

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Bulbasaur",
      "image": "https://raw.githubusercontent.com/.../1.png",
      "types": ["grass", "poison"]
    },
    {
      "id": 2,
      "name": "Ivysaur",
      "image": "https://raw.githubusercontent.com/.../2.png",
      "types": ["grass", "poison"]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "total_pages": 8,
    "has_next": true,
    "has_prev": false
  },
  "timestamp": "2026-01-30T16:29:00Z"
}
```

### Detalle

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

---

## 🛡️ Códigos de Error

| Status | Significado | Ejemplo |
|--------|------------|---------|
| 200 | OK - Solicitud exitosa | Listado obtenido |
| 400 | Bad Request - Parámetros inválidos | `?per_page=100` o ID fuera de rango |
| 404 | Not Found - Pokémon no existe | ID > 150 |
| 503 | Service Unavailable - PokeAPI falla | Timeout en PokeAPI |

---

## 📝 Archivos Generados

| Archivo | Líneas | Descripción |
|---------|--------|-------------|
| `app/Services/PokemonService.php` | 400+ | Consumo de PokeAPI + caché |
| `app/Http/Controllers/PokemonController.php` | 250+ | 3 endpoints públicos |
| `app/Http/Requests/PokemonIndexRequest.php` | 50+ | Validaciones |
| `routes/api.php` | +8 | Nuevas rutas |
| `BACKEND_POKEMON.md` | 500+ | Documentación completa |
| `test-pokemon.sh` | 300+ | 15 tests automáticos |

---

## 🔄 Caché

- **TTL:** 24 horas (86400 segundos)
- **Key:** `pokemon:generation:1` para listado
- **Key:** `pokemon:detail:{id}` para detalles
- **Backend:** Redis

### Cómo verificar caché

```bash
# Dentro del contenedor
docker-compose exec backend php artisan tinker

# Ver todas las keys
Cache::getStore()->getRedis()->keys('pokemon:*')

# Ver una key específica
Cache::get('pokemon:generation:1')

# Limpiar caché
Cache::forget('pokemon:generation:1')
Cache::flush()
```

---

## 🐛 Troubleshooting

### Error: "Service temporarily unavailable"

**Causa:** PokeAPI está caído o no responde

**Solución:**
1. Verificar conexión a internet
2. Probar: `curl https://pokeapi.co/api/v2/pokemon?limit=1`
3. Si está en caché, los datos se retornarán de todas formas
4. Esperar a que PokeAPI se recupere

### Error: "Invalid pokemon ID"

**Causa:** ID fuera del rango 1-150

**Solución:**
```bash
# ❌ Incorrecto
curl "http://localhost:8000/api/v1/pokemon/999"

# ✅ Correcto
curl "http://localhost:8000/api/v1/pokemon/25"  # 1-150
```

### Error: Listado vacío

**Causa:** Filtro muy restrictivo o búsqueda sin resultados

**Solución:**
```bash
# Verificar con listado completo
curl "http://localhost:8000/api/v1/pokemon?page=1"

# Verificar tipos válidos
curl "http://localhost:8000/api/v1/pokemon/filters"

# Intentar búsqueda sin acentos
curl "http://localhost:8000/api/v1/pokemon?search=bulbasaur"
```

### Error: Timeout (10 segundos)

**Causa:** PokeAPI responde lentamente

**Solución:**
1. Esperar un momento y reintentar
2. Los datos se cachearan para futuras solicitudes
3. Después de la primera solicitud, las siguientes serán instantáneas

---

## 💡 Tips

### Búsqueda efectiva

```bash
# ✅ Funciona (minúsculas)
curl "http://localhost:8000/api/v1/pokemon?search=pikachu"

# ✅ También funciona (mayúsculas)
curl "http://localhost:8000/api/v1/pokemon?search=PIKACHU"

# ✅ Búsqueda parcial
curl "http://localhost:8000/api/v1/pokemon?search=char"
# Retorna: Charmander, Charmeleon, Charizard
```

### Paginación efectiva

```bash
# Página 1 (primeros 20)
curl "http://localhost:8000/api/v1/pokemon?page=1"

# Página 2 (items 21-40)
curl "http://localhost:8000/api/v1/pokemon?page=2"

# Página 8 (últimos items)
curl "http://localhost:8000/api/v1/pokemon?page=8"

# Cambiar cantidad por página
curl "http://localhost:8000/api/v1/pokemon?per_page=50&page=1"
# Retorna máximo 50 items
```

### Tipos disponibles

```bash
curl "http://localhost:8000/api/v1/pokemon/filters"

# Respuesta:
# ["normal", "fighting", "flying", "poison", "ground", "rock",
#  "bug", "ghost", "steel", "fire", "water", "grass",
#  "electric", "psychic", "ice", "dragon", "dark", "fairy"]
```

---

## 📖 Documentación Completa

Para información detallada, consultar:

👉 [BACKEND_POKEMON.md](BACKEND_POKEMON.md)

Secciones:
- 🏗️ Arquitectura completa
- 📡 Especificación de endpoints
- 🔧 Componentes implementados
- 🛡️ Manejo de errores
- 🚀 Integración Frontend
- 🔍 Testing
- 📊 Estadísticas

---

## ✅ Checklist

- [x] PokemonService implementado
- [x] PokemonController implementado
- [x] Rutas configuradas
- [x] Caché Redis configurado
- [x] Manejo de errores completo
- [x] Validaciones de entrada
- [x] Tests automáticos
- [x] Documentación completa
- [ ] Tests en PEST framework (próximo)
- [ ] Integración frontend (próximo)

---

**Status:** ✅ FASE 3.2 - POKEMON API COMPLETADA | Fecha: 2026-01-30
