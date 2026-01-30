# 🧪 Guía Completa de Testing - CASFID

**Estado:** 📋 Planning & Implementation  
**Fecha:** January 2026  
**Cobertura:** Backend (Laravel/Pest) + Frontend (Vitest + React Testing Library)

---

## 📑 Tabla de Contenidos

1. [Backend Testing con Pest](#backend-testing-con-pest)
2. [Frontend Testing con Vitest](#frontend-testing-con-vitest)
3. [Estrategia de Testing](#estrategia-de-testing)
4. [Ejecutar Tests](#ejecutar-tests)
5. [CI/CD Integration](#cicd-integration)

---

## 🔥 Backend Testing con Pest

### 1. Instalación de Pest

```bash
cd /c/laragon/www/casfid
composer require pestphp/pest pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

### 2. Estructura de Tests Backend

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── RefreshTokenTest.php
│   ├── Pokemon/
│   │   ├── ListPokemonTest.php
│   │   ├── GetPokemonTest.php
│   │   └── SearchPokemonTest.php
│   └── Favorites/
│       ├── AddFavoriteTest.php
│       ├── RemoveFavoriteTest.php
│       ├── ListFavoritesTest.php
│       └── FavoriteAuthTest.php
├── Unit/
│   ├── Services/
│   │   ├── PokemonServiceTest.php
│   │   ├── FavoriteServiceTest.php
│   │   └── AuthServiceTest.php
│   └── Models/
│       ├── UserTest.php
│       ├── PokemonTest.php
│       └── FavoriteTest.php
├── Pest.php                 # Configuración global
└── TestCase.php            # Base TestCase

```

### 3. Ejemplos de Tests Pest

#### Feature: Login Test
```php
<?php

// tests/Feature/Auth/LoginTest.php
use App\Models\User;

describe('Authentication', function () {
    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    });

    it('fails with invalid credentials', function () {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Invalid credentials']);
    });

    it('fails without email', function () {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});
```

#### Feature: Pokemon Favorites Test
```php
<?php

// tests/Feature/Favorites/AddFavoriteTest.php
use App\Models\User;
use Database\Factories\PokemonFactory;

describe('Add Favorite', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('can add pokemon to favorites', function () {
        $pokemonId = 1; // Pikachu

        $response = $this->actingAs($this->user)
            ->postJson('/api/favorites', [
                'pokemon_id' => $pokemonId,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['is_favorite' => true]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemonId,
        ]);
    });

    it('cannot add same pokemon twice', function () {
        $pokemonId = 1;

        // Add first time
        $this->actingAs($this->user)
            ->postJson('/api/favorites', ['pokemon_id' => $pokemonId])
            ->assertStatus(201);

        // Try add second time
        $response = $this->actingAs($this->user)
            ->postJson('/api/favorites', ['pokemon_id' => $pokemonId]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'Pokemon already in favorites']);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/favorites', [
            'pokemon_id' => 1,
        ]);

        $response->assertStatus(401);
    });
});
```

#### Unit: Pokemon Service Test
```php
<?php

// tests/Unit/Services/PokemonServiceTest.php
use App\Services\PokemonService;
use App\Models\Pokemon;

describe('Pokemon Service', function () {
    it('can search pokemon by name', function () {
        Pokemon::factory(5)->create([
            'name' => 'pikachu',
        ]);
        
        Pokemon::factory(3)->create([
            'name' => 'charmander',
        ]);

        $service = new PokemonService();
        $results = $service->search('pika');

        expect($results)->toHaveCount(5);
    });

    it('can filter by type', function () {
        Pokemon::factory(3)->create([
            'type' => 'electric',
        ]);
        
        Pokemon::factory(2)->create([
            'type' => 'fire',
        ]);

        $service = new PokemonService();
        $results = $service->filterByType('electric');

        expect($results)->toHaveCount(3);
    });

    it('can paginate results', function () {
        Pokemon::factory(25)->create();

        $service = new PokemonService();
        $page1 = $service->paginate(page: 1, perPage: 12);

        expect($page1)->toHaveCount(12);
    });
});
```

---

## 🧪 Frontend Testing con Vitest

### 1. Instalación

```bash
cd /c/laragon/www/casfid/frontend

npm install -D vitest @vitest/ui
npm install -D @testing-library/react @testing-library/jest-dom
npm install -D @testing-library/user-event
npm install -D jsdom
npm install -D happy-dom
```

### 2. Configuración Vitest

#### vitest.config.ts
```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.ts'],
    css: true,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html', 'lcov'],
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.config.ts',
      ],
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
});
```

#### tests/setup.ts
```typescript
import { expect, afterEach, vi } from 'vitest';
import { cleanup } from '@testing-library/react';
import '@testing-library/jest-dom';

// Cleanup after each test
afterEach(() => {
  cleanup();
});

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
};

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
});

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});
```

### 3. Estructura de Tests Frontend

```
tests/
├── components/
│   ├── PokemonCard.test.tsx
│   ├── PokemonGrid.test.tsx
│   ├── PokemonFilters.test.tsx
│   ├── Navbar.test.tsx
│   └── AuthForm.test.tsx
├── hooks/
│   ├── useAuth.test.ts
│   ├── useToast.test.ts
│   └── usePokemon.test.ts
├── lib/
│   ├── pokemon.test.ts
│   ├── auth.test.ts
│   └── api.test.ts
├── pages/
│   ├── pokemon.test.tsx
│   ├── login.test.tsx
│   └── register.test.tsx
├── mocks/
│   ├── pokemonMocks.ts
│   ├── authMocks.ts
│   └── handlers.ts
├── setup.ts
└── utils.tsx      # Custom render with providers

```

### 4. Ejemplos de Tests Frontend

#### Component: PokemonCard Test
```typescript
// tests/components/PokemonCard.test.tsx
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { PokemonCard } from '@/components/PokemonCard';
import { describe, it, expect, vi, beforeEach } from 'vitest';

const mockPokemon = {
  id: 25,
  name: 'pikachu',
  image: 'https://example.com/pikachu.png',
  types: [{ id: 1, name: 'electric' }],
  is_favorite: false,
  height: 0.4,
  weight: 6,
  description: 'Electric mouse pokemon',
};

describe('PokemonCard', () => {
  it('renders pokemon card correctly', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    expect(screen.getByText('pikachu')).toBeInTheDocument();
    expect(screen.getByText('#025')).toBeInTheDocument();
    expect(screen.getByRole('img', { name: /pikachu/i })).toBeInTheDocument();
  });

  it('shows favorite button when logged in', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={true} />);

    const favoriteButton = screen.getByRole('button', {
      name: /add to favorites/i,
    });
    
    expect(favoriteButton).toBeInTheDocument();
  });

  it('hides favorite button when not logged in', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    const favoriteButton = screen.queryByRole('button', {
      name: /add to favorites/i,
    });
    
    expect(favoriteButton).not.toBeInTheDocument();
  });

  it('toggles favorite with yellow styling', async () => {
    const mockToggle = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonCard
        pokemon={mockPokemon}
        isLoggedIn={true}
        onToggleFavorite={mockToggle}
      />
    );

    const favoriteButton = screen.getByRole('button', {
      name: /add to favorites/i,
    });

    await user.click(favoriteButton);

    // Check that star becomes yellow
    expect(favoriteButton.querySelector('svg')).toHaveClass('fill-yellow-400');

    // Check that card gets yellow border
    const card = screen.getByText('pikachu').closest('div');
    expect(card).toHaveClass('border-yellow-400');
  });

  it('handles favorite toggle error', async () => {
    const mockToggle = vi.fn().mockRejectedValue(new Error('API Error'));
    const user = userEvent.setup();

    render(
      <PokemonCard
        pokemon={mockPokemon}
        isLoggedIn={true}
        onToggleFavorite={mockToggle}
      />
    );

    const favoriteButton = screen.getByRole('button');
    await user.click(favoriteButton);

    await waitFor(() => {
      expect(mockToggle).toHaveBeenCalled();
    });

    // Star should revert to gray after error
    expect(favoriteButton.querySelector('svg')).not.toHaveClass('fill-yellow-400');
  });
});
```

#### Hook: useAuth Test
```typescript
// tests/hooks/useAuth.test.ts
import { renderHook, act } from '@testing-library/react';
import { useAuth } from '@/hooks/useAuth';
import { describe, it, expect, vi, beforeEach } from 'vitest';

describe('useAuth', () => {
  beforeEach(() => {
    localStorage.clear();
    vi.clearAllMocks();
  });

  it('returns initial state as unauthenticated', () => {
    const { result } = renderHook(() => useAuth());

    expect(result.current.isLoggedIn).toBe(false);
    expect(result.current.user).toBeNull();
  });

  it('can login user', async () => {
    const { result } = renderHook(() => useAuth());

    await act(async () => {
      await result.current.login('test@example.com', 'password123');
    });

    expect(result.current.isLoggedIn).toBe(true);
    expect(result.current.user?.email).toBe('test@example.com');
    expect(localStorage.getItem('token')).toBeTruthy();
  });

  it('can logout user', async () => {
    const { result } = renderHook(() => useAuth());

    await act(async () => {
      await result.current.login('test@example.com', 'password123');
    });

    expect(result.current.isLoggedIn).toBe(true);

    act(() => {
      result.current.logout();
    });

    expect(result.current.isLoggedIn).toBe(false);
    expect(result.current.user).toBeNull();
  });
});
```

#### Integration: Pokemon Page Test
```typescript
// tests/pages/pokemon.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import PokemonPage from '@/app/pokemon/page';
import { describe, it, expect, beforeEach } from 'vitest';
import * as pokemonLib from '@/lib/pokemon';

vi.mock('@/lib/pokemon');

const mockPokemonList = [
  { id: 1, name: 'pikachu', types: [{ name: 'electric' }], is_favorite: false },
  { id: 2, name: 'charmander', types: [{ name: 'fire' }], is_favorite: false },
];

describe('Pokemon Page', () => {
  beforeEach(() => {
    vi.mocked(pokemonLib.getPokemonList).mockResolvedValue(mockPokemonList);
  });

  it('displays pokemon list', async () => {
    render(await PokemonPage());

    await waitFor(() => {
      expect(screen.getByText('pikachu')).toBeInTheDocument();
      expect(screen.getByText('charmander')).toBeInTheDocument();
    });
  });

  it('can search pokemon', async () => {
    const user = userEvent.setup();
    render(await PokemonPage());

    const searchInput = screen.getByPlaceholderText(/search/i);
    await user.type(searchInput, 'pika');

    await waitFor(() => {
      expect(screen.getByText('pikachu')).toBeInTheDocument();
      expect(screen.queryByText('charmander')).not.toBeInTheDocument();
    });
  });

  it('can filter by type', async () => {
    const user = userEvent.setup();
    render(await PokemonPage());

    const typeFilter = screen.getByRole('button', { name: /electric/i });
    await user.click(typeFilter);

    await waitFor(() => {
      expect(screen.getByText('pikachu')).toBeInTheDocument();
      expect(screen.queryByText('charmander')).not.toBeInTheDocument();
    });
  });

  it('can toggle favorite with optimistic UI', async () => {
    const user = userEvent.setup();
    vi.mocked(pokemonLib.toggleFavorite).mockResolvedValue(true);

    render(await PokemonPage());

    const favoriteButtons = screen.getAllByRole('button', {
      name: /add to favorites/i,
    });

    await user.click(favoriteButtons[0]);

    // Star should be yellow immediately (optimistic)
    expect(favoriteButtons[0].querySelector('svg')).toHaveClass('fill-yellow-400');

    await waitFor(() => {
      expect(pokemonLib.toggleFavorite).toHaveBeenCalled();
    });
  });
});
```

---

## 📊 Estrategia de Testing

### Coverage Goals

| Layer | Target Coverage | Priority |
|-------|-----------------|----------|
| **Unit Tests** | 80% | 🔴 High |
| **Integration Tests** | 70% | 🟠 High |
| **E2E Tests** | 50% | 🟡 Medium |
| **Overall** | 75% | 🟢 Target |

### Test Categories

#### 1. Backend (Pest)
```
Feature Tests (Endpoints)
├── Authentication
│   ├── Login ✅
│   ├── Register ✅
│   └── Token Refresh ✅
├── Pokemon
│   ├── List with pagination ✅
│   ├── Search by name ✅
│   ├── Filter by type ✅
│   └── Get single pokemon ✅
└── Favorites
    ├── Add favorite ✅
    ├── Remove favorite ✅
    ├── List user favorites ✅
    └── Auth requirement ✅

Unit Tests (Services)
├── PokemonService
│   ├── Search functionality ✅
│   ├── Filter functionality ✅
│   └── Pagination ✅
├── FavoriteService
│   ├── Add/Remove logic ✅
│   ├── Duplication prevention ✅
│   └── Authorization ✅
└── AuthService
    ├── Token generation ✅
    ├── Token validation ✅
    └── User retrieval ✅
```

#### 2. Frontend (Vitest)
```
Component Tests
├── PokemonCard
│   ├── Render pokemon info ✅
│   ├── Show favorite button (logged in) ✅
│   ├── Favorite button styling (yellow) ✅
│   ├── Optimistic UI on toggle ✅
│   └── Rollback on error ✅
├── PokemonGrid
│   ├── Render multiple cards ✅
│   ├── Pagination controls ✅
│   └── Refresh after favorite toggle ✅
├── PokemonFilters
│   ├── Search input ✅
│   ├── Type filters ✅
│   └── Filter application ✅
└── Navbar
    ├── Show when logged in ✅
    ├── Hide when logged out ✅
    └── Logout functionality ✅

Hook Tests
├── useAuth
│   ├── Initial state ✅
│   ├── Login flow ✅
│   ├── Logout flow ✅
│   └── Token persistence ✅
├── useToast
│   ├── Show toast ✅
│   ├── Auto dismiss ✅
│   └── Multiple toasts ✅
└── usePokemon
    ├── Fetch pokemon ✅
    ├── Filter/search ✅
    └── Pagination ✅

Integration Tests
├── Login flow (complete) ✅
├── Pokemon list + filters ✅
├── Add/remove favorites ✅
└── Dark mode toggle ✅
```

---

## 🚀 Ejecutar Tests

### Backend Tests

```bash
# Run all tests
php artisan pest

# Run specific test class
php artisan pest tests/Feature/Auth/LoginTest.php

# Run with coverage
php artisan pest --coverage

# Run specific test
php artisan pest --filter="can login with valid credentials"

# Watch mode
php artisan pest --watch
```

### Frontend Tests

```bash
# Run all tests
npm run test

# Run specific test file
npm run test -- PokemonCard.test.tsx

# Watch mode
npm run test -- --watch

# UI mode
npm run test -- --ui

# Coverage
npm run test -- --coverage
```

### Package.json Scripts
```json
{
  "scripts": {
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:coverage": "vitest --coverage",
    "test:watch": "vitest --watch"
  }
}
```

---

## 🔄 CI/CD Integration

### GitHub Actions Workflow

#### .github/workflows/tests.yml
```yaml
name: Run Tests

on: [push, pull_request]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: casfid_test
          MYSQL_ROOT_PASSWORD: root
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mysql, pdo, pdo_mysql
          tools: composer:v2

      - name: Install dependencies
        run: composer install

      - name: Run migrations
        run: php artisan migrate

      - name: Run tests
        run: php artisan pest --coverage

  frontend-tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'

      - name: Install dependencies
        working-directory: ./frontend
        run: npm install

      - name: Run tests
        working-directory: ./frontend
        run: npm run test -- --coverage

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./frontend/coverage/lcov.info
```

---

## ✅ Test Checklist

### Before Merge
- [ ] All backend tests pass (`php artisan pest`)
- [ ] All frontend tests pass (`npm run test`)
- [ ] Coverage meets 75% target
- [ ] No console errors/warnings
- [ ] Manual testing in dev environment

### Before Deploy
- [ ] Tests passing on CI/CD
- [ ] Coverage reports reviewed
- [ ] Performance tests passed
- [ ] Security tests passed
- [ ] Accessibility audit passed

---

## 📚 Recursos

- **Pest Documentation:** https://pestphp.com/docs
- **Laravel Testing:** https://laravel.com/docs/11.x/testing
- **Vitest Documentation:** https://vitest.dev/
- **React Testing Library:** https://testing-library.com/react

---

## 🎯 Próximos Pasos

1. ✅ Create test structure
2. ⏳ Write backend tests (50+ tests)
3. ⏳ Write frontend tests (40+ tests)
4. ⏳ Setup CI/CD pipeline
5. ⏳ Achieve 75%+ coverage

**Status:** 🟡 In Progress
