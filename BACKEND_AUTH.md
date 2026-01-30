# BACKEND_AUTH.md - Sistema de Autenticación JWT

**Documentación del Sistema de Autenticación - Pokémon BFF**

---

## 📋 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Arquitectura de Autenticación](#arquitectura-de-autenticación)
3. [Configuración JWT](#configuración-jwt)
4. [Endpoints de Autenticación](#endpoints-de-autenticación)
5. [Validaciones](#validaciones)
6. [Modelos](#modelos)
7. [Middleware](#middleware)
8. [Seguridad](#seguridad)
9. [Testing](#testing)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Visión General

### Características Implementadas

✅ **Autenticación JWT**
- Tokens seguros con HS256 (HMAC-SHA256)
- TTL configurable (default: 1 hora)
- Refresh tokens para renovación

✅ **Registro de Usuarios**
- Validación robusta de email y contraseña
- Hashing de contraseñas con bcrypt
- Email único por usuario

✅ **Inicio de Sesión**
- Rate limiting (5 intentos / 15 minutos)
- Respuestas de error descriptivas
- Token incluido en respuesta

✅ **Seguridad**
- Contraseñas no se devuelven en respuestas
- Soft deletes para usuarios
- Rate limiting por IP
- Validación de RFC 5322 para emails

---

## 🏗️ Arquitectura de Autenticación

### Flujo de Autenticación

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENTE FRONTEND                         │
└────────┬────────────────────────────────────────────────────┘
         │
         │ POST /api/v1/auth/register
         │ { name, email, password, password_confirmation }
         ▼
┌─────────────────────────────────────────────────────────────┐
│            MIDDLEWARE: AuthRateLimiter                       │
│  (3 intentos / 60 minutos por IP)                           │
└────────┬────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│           AuthController::register()                         │
│  1. Validación con RegisterRequest                           │
│  2. Hash contraseña con bcrypt                              │
│  3. Crear User en BD                                         │
│  4. Generar JWT con JwtAuth::fromUser()                      │
│  5. Retornar token + user data                               │
└────────┬────────────────────────────────────────────────────┘
         │
         ▼ JWT Token (eyJ0eXAi...)
┌─────────────────────────────────────────────────────────────┐
│            CLIENTE: Almacena Token                          │
│  localStorage.setItem('auth_token', token)                  │
└─────────────────────────────────────────────────────────────┘
```

### Flujo de Requests Autenticados

```
┌─────────────────────────────────────────────────────────────┐
│            CLIENTE: Envía Request                            │
│  GET /api/v1/pokemon                                        │
│  Header: Authorization: Bearer {token}                       │
└────────┬────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│       MIDDLEWARE: auth:api (JwtMiddleware)                   │
│  1. Obtener token del header                                │
│  2. Validar firma JWT                                       │
│  3. Verificar expiración                                    │
│  4. Cargar usuario                                          │
│  5. Inyectar en auth('api')->user()                         │
└────────┬────────────────────────────────────────────────────┘
         │ ✅ Válido
         ▼
┌─────────────────────────────────────────────────────────────┐
│        Controller Action (protegido)                         │
│  Accedible via: auth('api')->user()                         │
│  Accedible via: Auth::user()                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 Configuración JWT

### Archivos Relevantes

| Archivo | Propósito |
|---------|-----------|
| `config/jwt.php` | Configuración JWT completa |
| `config/auth.php` | Guard 'api' con driver JWT |
| `.env.example` | Variables JWT |
| `app/Http/Middleware/JwtMiddleware.php` | Validación JWT |

### Configuración en `config/jwt.php`

```php
return [
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'secret' => env('JWT_SECRET', 'your-secret-key'),
    'ttl' => env('JWT_TTL', 60),  // minutos
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),  // 2 semanas
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
    'leeway' => env('JWT_LEEWAY', 0),
];
```

### Variables en `.env`

```bash
JWT_SECRET=your_jwt_secret_here
JWT_ALGORITHM=HS256
JWT_TTL=60                    # Token expira en 1 hora
JWT_BLACKLIST_ENABLED=true    # Invalidar tokens al logout
```

### Generar JWT_SECRET

```bash
# Generar clave secreta fuerte
openssl rand -hex 32

# O dentro del contenedor Docker
docker-compose exec backend openssl rand -hex 32
```

---

## 📡 Endpoints de Autenticación

### 1. POST `/api/v1/auth/register`

**Registrar nuevo usuario**

#### Request
```json
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!"
}
```

#### Validaciones
```
name:
  - Requerido
  - String
  - Min: 2 caracteres
  - Max: 255 caracteres
  - Solo letras, espacios, guiones, puntos

email:
  - Requerido
  - RFC 5322 válido
  - Único en tabla users
  - DNS válido (si DNS checking está habilitado)

password:
  - Requerido
  - Min: 8 caracteres
  - Max: 255 caracteres
  - Debe contener: mayúscula, minúscula, número
  - Confirmación coincide con password
```

#### Response Success (201 Created)
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
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### Response Error (422 Unprocessable)
```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "email": ["Este email ya está registrado."],
    "password": ["La contraseña debe contener mayúscula, minúscula y número."]
  }
}
```

---

### 2. POST `/api/v1/auth/login`

**Iniciar sesión**

#### Request
```json
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "SecurePassword123!"
}
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Autenticación exitosa",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 3600
}
```

#### Rate Limiting
- **Límite**: 5 intentos por IP
- **Ventana**: 15 minutos
- **Error**: 429 Too Many Requests

#### Response Error - Rate Limited (429)
```json
{
  "success": false,
  "message": "Demasiados intentos de inicio de sesión. Intenta más tarde.",
  "retry_after": 847
}
```

#### Response Error - Credenciales Inválidas (401)
```json
{
  "success": false,
  "message": "Credenciales inválidas"
}
```

---

### 3. POST `/api/v1/auth/logout` ⚡

**Cerrar sesión (requiere autenticación)**

#### Request
```json
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

**Nota**: El token se invalida inmediatamente en la blacklist.

---

### 4. POST `/api/v1/auth/refresh` ⚡

**Renovar JWT (requiere autenticación)**

#### Request
```json
POST /api/v1/auth/refresh
Authorization: Bearer {token}
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600
  }
}
```

**Uso**: Ejecutar antes de que el token expire para obtener uno nuevo sin re-autenticar.

---

### 5. GET `/api/v1/auth/me` ⚡

**Obtener datos del usuario autenticado**

#### Request
```json
GET /api/v1/auth/me
Authorization: Bearer {token}
```

#### Response Success (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "created_at": "2026-01-30T10:30:00Z"
  }
}
```

---

## ✅ Validaciones

### Contraseña - Mejores Prácticas

**Regla implementada:**
```regex
^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&._-]+$
```

**Requisitos:**
- ✅ Mínimo 8 caracteres
- ✅ Máximo 255 caracteres
- ✅ Al menos 1 letra minúscula
- ✅ Al menos 1 letra mayúscula
- ✅ Al menos 1 número
- ✅ Permite caracteres especiales: `@$!%*?&._-`

**Ejemplos Válidos:**
- `SecurePassword123!`
- `MyP@ssw0rd`
- `Password_123.secure`

**Ejemplos Inválidos:**
- `password123` (no tiene mayúscula)
- `PASSWORD123` (no tiene minúscula)
- `SecurePasswd` (no tiene número)
- `Pass123` (menos de 8 caracteres)

### Email - Mejores Prácticas

**Validación:**
```php
'email' => ['required', 'email:rfc,dns', 'unique:users,email', 'max:255']
```

**Características:**
- ✅ RFC 5322 compliant
- ✅ DNS validation (verifica dominio existe)
- ✅ Único en tabla users
- ✅ Máximo 255 caracteres
- ✅ Case-insensitive en BD

### Nombre - Mejores Prácticas

**Validación:**
```php
'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-záéíóúñA-ZÁÉÍÓÚÑ\s\-\.\']+$/']
```

**Características:**
- ✅ 2 a 255 caracteres
- ✅ Solo caracteres alfanuméricos (español incluido)
- ✅ Permite espacios, guiones, puntos, apóstrofos
- ✅ Previene inyección de caracteres especiales

---

## 👥 Modelos

### User Model

**Ubicación:** `app/Models/User.php`

```php
class User extends Authenticatable implements JwtSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password'];
    
    protected $hidden = ['password', 'remember_token'];

    // JWT Claims
    public function getJwtIdentifier() { /* ... */ }
    public function getJwtCustomClaims() { /* ... */ }

    // Relaciones
    public function favorites() { /* ... */ }

    // Scopes
    public function scopeByEmail($query, $email) { /* ... */ }
}
```

**Características:**
- ✅ Implementa `JwtSubject` para JWT
- ✅ SoftDeletes para auditoría
- ✅ Relación con Favorites
- ✅ Timestamp automático

### Favorite Model

**Ubicación:** `app/Models/Favorite.php`

```php
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pokemon_id',
        'pokemon_name',
        'pokemon_type',
    ];

    // Relaciones
    public function user() { /* ... */ }
}
```

**Características:**
- ✅ Pertenece a User
- ✅ Relación one-to-many inversa
- ✅ Desnormalizado (pokemon_name, pokemon_type)

---

## 🛡️ Middleware

### JwtMiddleware

**Ubicación:** `app/Http/Middleware/JwtMiddleware.php`

**Función:** Validar y autenticar JWT en requests protegidos

**Errores Manejados:**
- Token no proporcionado → 401 (Token no proporcionado)
- Token expirado → 401 (Token expirado)
- Token inválido → 401 (Token inválido)

### AuthRateLimiter

**Ubicación:** `app/Http/Middleware/AuthRateLimiter.php`

**Límites:**
| Endpoint | Límite | Ventana |
|----------|--------|---------|
| `POST /auth/register` | 3 intentos | 60 minutos |
| `POST /auth/login` | 5 intentos | 15 minutos |

**Identificador:** IP del cliente

---

## 🔒 Seguridad

### Implementaciones

✅ **Hashing de Contraseñas**
```php
// En registro y actualización de contraseña
$user->password = Hash::make($request->password);
```

✅ **JWT con HS256**
```php
// Firma segura
JWT::encode($payload, env('JWT_SECRET'), 'HS256')
```

✅ **Soft Deletes**
```php
// Auditoría de usuarios eliminados
User::withTrashed()->find($id);
```

✅ **Rate Limiting**
```php
// Prevenir brute force
$this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)
```

✅ **CORS Configurado**
```php
// En docker/nginx/nginx.conf
add_header Access-Control-Allow-Origin $http_origin always;
add_header Access-Control-Allow-Credentials true always;
```

✅ **Validación RFC 5322**
```php
'email' => ['required', 'email:rfc,dns']
```

### No-Store Passwords

Las contraseñas NUNCA se devuelven en respuestas JSON:

```php
protected $hidden = ['password', 'remember_token'];
```

---

## 🧪 Testing

### Test de Registro

```php
// tests/Feature/AuthRegisterTest.php

public function test_register_success()
{
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'message', 'data', 'token'])
        ->assertJsonPath('data.email', 'juan@example.com');

    $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
}
```

### Test de Login

```php
public function test_login_success()
{
    $user = User::factory()->create([
        'email' => 'juan@example.com',
        'password' => Hash::make('SecurePassword123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'juan@example.com',
        'password' => 'SecurePassword123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'message', 'data', 'token'])
        ->assertJsonPath('data.id', $user->id);
}
```

### Ejecutar Tests

```bash
# En el contenedor
docker-compose exec backend php artisan test

# Con cobertura
docker-compose exec backend php artisan test --coverage

# Específicos
docker-compose exec backend php artisan test tests/Feature/Auth
```

---

## 🆘 Troubleshooting

### Error: "Token no proporcionado"

**Síntoma:**
```json
{ "success": false, "message": "Token no proporcionado" }
```

**Solución:**
- Verificar que Authorization header está siendo enviado
- Formato correcto: `Authorization: Bearer {token}`
- Verificar que no hay espacios extras

### Error: "Token expirado"

**Síntoma:**
```json
{ "success": false, "message": "Token expirado" }
```

**Solución:**
```bash
# Renovar el token
POST /api/v1/auth/refresh
Authorization: Bearer {token}

# O re-autenticar
POST /api/v1/auth/login
```

### Error: "Demasiados intentos"

**Síntoma:**
```json
{ "success": false, "message": "Demasiados intentos..." }
```

**Solución:**
- Esperar el tiempo indicado en `retry_after`
- Por IP: cambiar IP o usar proxy
- En desarrollo: limpiar caché
  ```bash
  docker-compose exec backend php artisan cache:clear
  ```

### Error: "Email ya registrado"

**Síntoma:**
```json
{ "success": false, "message": "Este email ya está registrado." }
```

**Solución:**
- Usar otro email
- O usar login con email existente
- Verificar que no hay duplicados en BD:
  ```bash
  docker-compose exec backend php artisan tinker
  >>> \App\Models\User::where('email', 'juan@example.com')->count()
  ```

### Error: "Contraseña inválida"

**Síntoma:**
```json
{ "success": false, "message": "Credenciales inválidas" }
```

**Solución:**
- Verificar email existe en BD
- Verificar contraseña es correcta (case-sensitive)
- Usar endpoint de reset password (por implementar)

### JWT_SECRET no configurado

**Síntoma:**
```
Tymon\JwtAuth\Exceptions\JwtException: could not load key
```

**Solución:**
```bash
# Generar JWT_SECRET
openssl rand -hex 32

# Agregar a .env
JWT_SECRET=<valor_generado>

# Reiniciar contenedor
docker-compose restart backend
```

---

## 📊 Estadísticas

| Métrica | Valor |
|---------|-------|
| Archivos creados | 6 |
| Líneas de código | ~800 |
| Endpoints | 5 |
| Validaciones | 12 |
| Rate limits | 2 |
| Middleware | 2 |

---

## 🚀 Próximos Pasos

**Fase 3.2: Pokemon API**
- [ ] Crear PokemonService
- [ ] Consumir PokeAPI
- [ ] Implementar caching
- [ ] Endpoints: GET /pokemon, GET /pokemon/{id}

**Fase 3.3: Favorites**
- [ ] Crear FavoriteController
- [ ] Endpoints: POST /favorites, DELETE /favorites/{id}
- [ ] Relaciones User-Favorite

**Fase 3.4: Testing & Documentation**
- [ ] Tests unitarios
- [ ] Tests de integración
- [ ] Swagger/OpenAPI
- [ ] Documentación Postman

---

**Generado:** 2026-01-30
**Versión:** 1.0
**Status:** ✅ Implementado
