# 🧪 TESTING QUICK START

## ⚡ Instalación Rápida

### Backend (Laravel + Pest)
```bash
cd /c/laragon/www/casfid

# Instalar Pest
composer require pestphp/pest pestphp/pest-plugin-laravel --dev

# Inicializar Pest
php artisan pest:install
```

### Frontend (Vitest + React Testing Library)
```bash
cd /c/laragon/www/casfid/frontend

# Instalar dependencias de testing
npm install

# (Las dependencias ya están en package.json)
```

---

## 🚀 Ejecutar Tests

### Backend
```bash
# Todos los tests
php artisan pest

# Con coverage
php artisan pest --coverage

# Watch mode
php artisan pest --watch

# Test específico
php artisan pest tests/Feature/Auth/LoginTest.php
```

### Frontend
```bash
# Todos los tests
npm run test

# Con UI interactiva
npm run test:ui

# Con coverage
npm run test:coverage

# Watch mode
npm run test:watch

# Test específico
npm run test -- PokemonCard.test.tsx
```

---

## 📋 Archivos Creados

### Backend Tests
```
tests/
├── Feature/
│   ├── Auth/
│   │   └── LoginTest.php                ✅ 5 tests
│   ├── Pokemon/
│   │   └── ListPokemonTest.php          ✅ 7 tests
│   └── Favorites/
│       ├── AddFavoriteTest.php          ✅ 6 tests
│       └── RemoveFavoriteTest.php       ✅ 5 tests
├── Pest.php                             ✅ Configuration
└── [Unit tests pending]
```

**Total Backend Tests:** 23 tests (Feature)

### Frontend Tests
```
frontend/tests/
├── components/
│   ├── PokemonCard.test.tsx             ✅ 12 tests
│   ├── PokemonFilters.test.tsx          ✅ 11 tests
│   ├── PokemonGrid.test.tsx             ✅ 11 tests
│   └── [Additional tests pending]
├── setup.ts                             ✅ Setup
├── test-utils.tsx                       ✅ Utilities
└── vitest.config.ts                     ✅ Config
```

**Total Frontend Tests:** 34 tests (Components)

---

## ✅ Test Coverage

### Backend (Planned)
| Layer | Tests | Status |
|-------|-------|--------|
| Auth | 5 | ✅ Ready |
| Pokemon | 7 | ✅ Ready |
| Favorites | 11 | ✅ Ready |
| **Total** | **23** | ✅ |

### Frontend (Planned)
| Component | Tests | Status |
|-----------|-------|--------|
| PokemonCard | 12 | ✅ Ready |
| PokemonFilters | 11 | ✅ Ready |
| PokemonGrid | 11 | ✅ Ready |
| **Total** | **34** | ✅ |

---

## 🎯 Próximos Pasos

1. **Instalar Pest:**
   ```bash
   cd /c/laragon/www/casfid
   composer require pestphp/pest pestphp/pest-plugin-laravel --dev
   php artisan pest:install
   ```

2. **Instalar dependencias Frontend:**
   ```bash
   cd frontend
   npm install
   ```

3. **Ejecutar tests:**
   ```bash
   # Backend
   php artisan pest
   
   # Frontend
   npm run test
   ```

4. **Ver cobertura:**
   ```bash
   # Backend
   php artisan pest --coverage
   
   # Frontend
   npm run test:coverage
   ```

---

## 📊 Estrategia de Testing

### Por Nivel
- **Unit Tests:** Funciones aisladas, servicios (70% coverage)
- **Integration Tests:** Endpoints API, flujos (75% coverage)
- **Component Tests:** React components (80% coverage)
- **Overall Goal:** 75% coverage

### Por Tipo
- ✅ Authentication (Login, Register, Refresh)
- ✅ Pokemon CRUD (List, Get, Search, Filter)
- ✅ Favorites (Add, Remove, Toggle, Sync)
- ✅ Component Rendering
- ✅ User Interactions
- ✅ Optimistic UI Behavior

---

## 🔗 Referencias

- [Pest Documentation](https://pestphp.com/docs)
- [Laravel Testing](https://laravel.com/docs/11.x/testing)
- [Vitest](https://vitest.dev/)
- [React Testing Library](https://testing-library.com/react)

---

## 📝 Notas

- Tests están listos para ejecutar después de instalar dependencias
- Backend tests requieren base de datos de test (en .env.testing)
- Frontend tests usan mocks para APIs externas
- CI/CD workflow incluido en TESTING_GUIDE.md

**Status:** 🟡 Ready for Setup
