# Frontend Next.js - Fase 4.1: Auth & Layout

> Implementación completa del frontend con Next.js, TypeScript, TailwindCSS y autenticación JWT

**Status:** ✅ **COMPLETADO**  
**Framework:** Next.js 14 + TypeScript + TailwindCSS  
**Autenticación:** JWT (localStorage + Cookies)  
**Archivos:** 20+ archivos de código  

---

## 📋 Estructura de Archivos

### Configuración Base
```
frontend/
├── package.json              (Dependencias y scripts)
├── tsconfig.json            (TypeScript strict)
├── next.config.js           (Configuración Next.js)
├── tailwind.config.ts       (TailwindCSS)
├── postcss.config.js        (PostCSS)
├── .eslintrc.json           (ESLint)
├── .gitignore               (Git)
├── .env.local               (Variables de entorno)
├── .env.example             (Ejemplo de env)
└── README.md                (Documentación)
```

### Estructura de Código
```
src/
├── app/                     (Páginas - App Router)
│   ├── layout.tsx
│   ├── page.tsx             (Dashboard)
│   ├── login/page.tsx
│   ├── register/page.tsx
│   └── favorites/page.tsx   (Protegida)
│
├── components/              (Componentes reutilizables)
│   ├── Navbar.tsx
│   ├── LoginForm.tsx
│   ├── RegisterForm.tsx
│   ├── ProtectedRoute.tsx
│   ├── Loading.tsx
│   └── Toast.tsx
│
├── context/                 (Context API)
│   └── AuthContext.tsx
│
├── hooks/                   (Hooks personalizados)
│   ├── useAuth.ts
│   └── useToast.ts
│
└── lib/                     (Utilidades)
    ├── api.ts               (Cliente HTTP)
    ├── auth.ts              (Autenticación)
    └── storage.ts           (localStorage/Cookies)
```

---

## 🔧 Configuración

### TypeScript (`tsconfig.json`)
- ✅ Strict mode habilitado
- ✅ Strict null checks
- ✅ No implicit any
- ✅ Strict bind call apply
- ✅ Alias de paths: `@/*`

### TailwindCSS (`tailwind.config.ts`)
- ✅ Modo oscuro automático
- ✅ Extensiones de colores
- ✅ Tipografía personalizada
- ✅ Dark mode support

### Next.js (`next.config.js`)
- ✅ React strict mode
- ✅ Image optimization
- ✅ Compresión automática

---

## 🔐 Autenticación

### Storage (`src/lib/storage.ts`)
```typescript
// Funciones disponibles:
- getToken(): string | null          // Obtiene JWT del storage
- setToken(token: string)            // Guarda JWT
- removeToken()                      // Elimina JWT
- getUser(): User | null             // Obtiene datos del usuario
- setUser(user: User)                // Guarda datos del usuario
- removeUser()                       // Elimina datos del usuario
```

### Autenticación (`src/lib/auth.ts`)
```typescript
// Funciones disponibles:
- login(email, password)             // Login en API
- register(name, email, password)    // Registro en API
- logout()                           // Logout local
- isAuthenticated()                  // Verifica si hay sesión
- getToken()                         // Obtiene token
- getUser()                          // Obtiene usuario
```

### Context (`src/context/AuthContext.tsx`)
```typescript
interface AuthContextType {
  user: User | null;                 // Usuario actual
  isLoading: boolean;                // Cargando estado
  isAuthenticated: boolean;          // ¿Autenticado?
  logout: () => void;                // Función logout
  setUser: (user: User | null) => void;
}
```

### Hook (`src/hooks/useAuth.ts`)
```typescript
const { user, isLoading, isAuthenticated, logout, setUser } = useAuth();
```

---

## 📱 Componentes

### Navbar (`src/components/Navbar.tsx`)
- ✅ Navegación responsiva
- ✅ Menú dinámico (logueado/no logueado)
- ✅ Logo de Pokémon
- ✅ Dropdown para usuario
- ✅ Botón logout
- ✅ Links protegidos

### LoginForm (`src/components/LoginForm.tsx`)
- ✅ Email y password
- ✅ Validaciones cliente
- ✅ Manejo de errores
- ✅ Loading state
- ✅ Link a registro
- ✅ Remember me (preparado)

### RegisterForm (`src/components/RegisterForm.tsx`)
- ✅ Name, email, password, confirm
- ✅ Validaciones de contraseña
- ✅ Confirmación de contraseña
- ✅ Manejo de errores
- ✅ Loading state
- ✅ Link a login

### ProtectedRoute (`src/components/ProtectedRoute.tsx`)
- ✅ Redirige si no autenticado
- ✅ Loading state
- ✅ Transparente para rutas públicas

### Loading (`src/components/Loading.tsx`)
- ✅ Spinner animado
- ✅ Centrado en pantalla
- ✅ Overlay semitransparente

### Toast (`src/components/Toast.tsx`)
- ✅ Notificaciones emergentes
- ✅ Success, error, warning, info
- ✅ Auto-dismiss (5s)
- ✅ Posición superior derecha

---

## 📄 Páginas

### `page.tsx` (Dashboard)
```typescript
// Página inicio/dashboard
- Mostrar bienvenida si logueado
- Mostrar botones de login/register si no
- Datos del usuario
- Links a secciones
```

### `login/page.tsx`
```typescript
// Página de login
- Formulario de login
- Validaciones
- Redirección a dashboard si logueado
- Link a registro
```

### `register/page.tsx`
```typescript
// Página de registro
- Formulario de registro
- Validaciones
- Redirección a dashboard si logueado
- Link a login
```

### `favorites/page.tsx`
```typescript
// Página de favoritos (PROTEGIDA)
- Verificación de autenticación
- Redirección a login si no autenticado
- Lista de favoritos
- Opciones para eliminar
```

### `layout.tsx`
```typescript
// Layout principal
- Metadatos (title, description, etc.)
- Navbar
- AuthProvider
- ToastProvider
- Children
```

---

## 🎨 Estilos

### `globals.css`
- ✅ Utilidades de TailwindCSS
- ✅ Clases personalizadas
- ✅ Variables CSS
- ✅ Animaciones
- ✅ Dark mode

---

## 🔗 API Client (`src/lib/api.ts`)

```typescript
// Cliente HTTP con Axios
- Interceptor de requests (JWT automático)
- Interceptor de responses
- Manejo de errores
- Retry automático
- Timeout configurable

// Métodos disponibles:
- api.get(url, config)
- api.post(url, data, config)
- api.put(url, data, config)
- api.delete(url, config)
```

---

## 📝 Variables de Entorno

```bash
# .env.local
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_NAME=Pokemon BFF
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

---

## 🚀 Cómo Usar

### Instalación
```bash
cd frontend
npm install
```

### Desarrollo
```bash
npm run dev
# Se abrirá en http://localhost:3000
```

### Build
```bash
npm run build
npm start
```

### Type checking
```bash
npm run type-check
```

### Linting
```bash
npm run lint
```

---

## ✨ Características Implementadas

### Autenticación
- ✅ Login con email/password
- ✅ Registro con name/email/password
- ✅ JWT en localStorage (+ Cookies ready)
- ✅ Token persistent en reload
- ✅ Logout con limpieza

### Validaciones
- ✅ Email válido
- ✅ Contraseña segura (8+ chars)
- ✅ Confirmación de contraseña
- ✅ Errores claros

### Routing
- ✅ App Router de Next.js
- ✅ Rutas protegidas
- ✅ Redirecciones automáticas
- ✅ Metadata dinámico

### UI/UX
- ✅ Responsive design
- ✅ Dark mode support
- ✅ Loading states
- ✅ Error handling
- ✅ Toast notifications
- ✅ Smooth animations

### Developer Experience
- ✅ TypeScript strict
- ✅ ESLint configurado
- ✅ Code organization
- ✅ Hooks personalizados
- ✅ Comments útiles

---

## 🔄 Flow de Autenticación

### Login Flow
```
1. Usuario ingresa email/password
2. LoginForm valida localmente
3. Envía al backend (/api/v1/auth/login)
4. Backend retorna JWT + user data
5. Frontend guarda en localStorage
6. AuthContext actualiza estado
7. Redirección a dashboard
```

### Register Flow
```
1. Usuario ingresa datos
2. RegisterForm valida localmente
3. Envía al backend (/api/v1/auth/register)
4. Backend retorna JWT + user data
5. Frontend guarda en localStorage
6. AuthContext actualiza estado
7. Redirección a dashboard
```

### Logout Flow
```
1. Usuario hace click en logout
2. Frontend limpia localStorage
3. AuthContext actualiza estado
4. Redirección a login
```

### Protected Route Flow
```
1. Usuario intenta acceder /favorites
2. ProtectedRoute verifica autenticación
3. Si no autenticado → Redirecciona a /login
4. Si autenticado → Muestra página
```

---

## 🔌 Integración con Backend

### Endpoints usados

**POST /api/v1/auth/login**
```json
Request:
{
  "email": "user@example.com",
  "password": "Password123!"
}

Response (200):
{
  "success": true,
  "data": {
    "token": "jwt_token",
    "user": {
      "id": 1,
      "name": "John",
      "email": "user@example.com"
    }
  }
}
```

**POST /api/v1/auth/register**
```json
Request:
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}

Response (201):
{
  "success": true,
  "data": {
    "token": "jwt_token",
    "user": {
      "id": 1,
      "name": "John",
      "email": "user@example.com"
    }
  }
}
```

---

## 📊 Estructura de Tipos

```typescript
// User type
interface User {
  id: number;
  name: string;
  email: string;
  created_at?: string;
}

// Auth response
interface AuthResponse {
  success: boolean;
  data: {
    token: string;
    user: User;
  };
  message?: string;
}

// Error response
interface ErrorResponse {
  success: false;
  error: string;
  errors?: Record<string, string[]>;
}
```

---

## 🎓 Archivos Clave

### Para Entender Autenticación
- [src/lib/auth.ts](src/lib/auth.ts) - Lógica de auth
- [src/context/AuthContext.tsx](src/context/AuthContext.tsx) - Estado global
- [src/hooks/useAuth.ts](src/hooks/useAuth.ts) - Hook de uso

### Para Entender UI
- [src/components/Navbar.tsx](src/components/Navbar.tsx) - Navegación
- [src/components/LoginForm.tsx](src/components/LoginForm.tsx) - Login
- [src/app/layout.tsx](src/app/layout.tsx) - Layout principal

### Para Entender Rutas
- [src/app/page.tsx](src/app/page.tsx) - Home
- [src/app/login/page.tsx](src/app/login/page.tsx) - Login
- [src/app/register/page.tsx](src/app/register/page.tsx) - Register

---

## ✅ Checklist

- ✅ Next.js 14 + TypeScript configurado
- ✅ TailwindCSS integrado
- ✅ JWT authentication en localStorage
- ✅ Context API para estado global
- ✅ Páginas de login y registro
- ✅ Página de dashboard
- ✅ Página de favoritos (protegida)
- ✅ Navbar dinámico
- ✅ Validaciones de formularios
- ✅ Error handling
- ✅ Loading states
- ✅ Toast notifications
- ✅ Responsive design
- ✅ Dark mode support
- ✅ ESLint configurado

---

## 🔍 Próximos Pasos

1. **Instalar dependencias:**
   ```bash
   cd frontend && npm install
   ```

2. **Configurar backend:**
   - Asegurar que `/api/v1/auth/login` funciona
   - Asegurar que `/api/v1/auth/register` funciona

3. **Testing local:**
   ```bash
   npm run dev
   # Visitar http://localhost:3000
   ```

4. **Fase 4.2 (Próximo):**
   - Página de Pokémon
   - Página de favoritos completa
   - Integración con PokeAPI

---

## 📞 Soporte

### Problemas Comunes

**"CORS Error"**
- Verificar que backend tiene CORS habilitado
- Verificar NEXT_PUBLIC_API_URL correcto

**"JWT no persiste"**
- Revisar browser localStorage
- Verificar cookies enabled
- Revisar console para errores

**"Redirect loop"**
- Verificar AuthContext en layout
- Revisar useAuth hook
- Comprobar lógica de protección

---

**Fase 4.1: ✅ COMPLETADA**

Frontend listo para desarrollo. Próximo: Integración con API de Pokémon.

