# 🚀 Fase 4.1 - Frontend Development Guide

## Estado Actual ✅

**Completado:**
- ✅ Next.js 14 + TypeScript + TailwindCSS
- ✅ JWT Authentication (localStorage + Cookies)
- ✅ Login & Register pages con validaciones
- ✅ Layout con detección de autenticación
- ✅ 6 componentes reutilizables
- ✅ Context API para estado global
- ✅ API client con JWT automático
- ✅ Documentación completa
- ✅ 30 archivos + 3,086 líneas de código
- ✅ npm install ejecutado (393 paquetes)

---

## 🎯 Próximos Pasos

### Paso 1: Inicia el Servidor de Desarrollo
```bash
cd frontend
npm run dev
```

Abrirá automáticamente: `http://localhost:3000`

### Paso 2: Prueba las Páginas

**Dashboard (Home)**
- URL: `http://localhost:3000`
- Muestra diferente contenido si logueado o no
- Links a Login y Register

**Login**
- URL: `http://localhost:3000/login`
- Email: user@example.com
- Password: cualquiera (se validará en backend)
- Link a Register

**Register**
- URL: `http://localhost:3000/register`
- Crea nueva cuenta
- Validaciones cliente (email, password strength)
- Link a Login

**Favorites (Protected)**
- URL: `http://localhost:3000/favorites`
- Solo accesible si logueado
- Redirecciona a /login si no está autenticado

### Paso 3: Verifica el Backend

Asegúrate que el backend esté corriendo:
```bash
cd app
php artisan serve
# Debe estar en http://localhost:8000/api/v1
```

### Paso 4: Prueba Autenticación Completa

1. Ve a `http://localhost:3000/register`
2. Crea una cuenta (name, email, password)
3. Verifica que el JWT se guarde en localStorage:
   ```javascript
   // En DevTools Console (F12)
   localStorage.getItem('pokemon_bff_token')
   JSON.parse(localStorage.getItem('pokemon_bff_user'))
   ```
4. La Navbar debe mostrar "Bienvenido, [nombre]"
5. Accede a `/favorites` (debe funcionar)
6. Click en Logout
7. Intenta acceder a `/favorites` (debe redirigir a login)

---

## 📡 Endpoints Backend Usados (Fase 4.1)

```bash
# Login
POST /api/v1/auth/login
Body: { email: string, password: string }
Response: { access_token: string, user: { id, name, email } }

# Register
POST /api/v1/auth/register
Body: { name: string, email: string, password: string }
Response: { access_token: string, user: { id, name, email } }
```

---

## 🔧 Comandos Disponibles

```bash
# Desarrollo
npm run dev              # Inicia servidor (localhost:3000)

# Build & Production
npm run build           # Compila para producción
npm start              # Inicia servidor producción

# Quality
npm run lint           # ESLint check
npm run type-check     # TypeScript check

# Utilities
npm run clean          # Limpia build
npm run format         # Formatea código (si configurado)
```

---

## 📁 Estructura de Carpetas

```
frontend/
├── public/                     # Assets estáticos
├── src/
│   ├── app/                   # Páginas (App Router)
│   │   ├── layout.tsx         # Root layout con Auth provider
│   │   ├── page.tsx           # Dashboard (/)
│   │   ├── login/page.tsx     # Login (/login)
│   │   ├── register/page.tsx  # Register (/register)
│   │   ├── favorites/page.tsx # Favorites (/favorites) [Protected]
│   │   └── globals.css        # Estilos globales + Tailwind
│   │
│   ├── components/            # Componentes reutilizables
│   │   ├── Navbar.tsx         # Navegación (dinámico por auth)
│   │   ├── LoginForm.tsx      # Formulario de login
│   │   ├── RegisterForm.tsx   # Formulario de registro
│   │   ├── ProtectedRoute.tsx # Wrapper para rutas protegidas
│   │   ├── Loading.tsx        # Spinner de carga
│   │   └── Toast.tsx          # Sistema de notificaciones
│   │
│   ├── context/
│   │   └── AuthContext.tsx    # Estado global de autenticación
│   │
│   ├── hooks/                 # Hooks personalizados
│   │   ├── useAuth.ts         # Hook para acceder a auth
│   │   └── useToast.ts        # Hook para notificaciones
│   │
│   └── lib/                   # Utilidades y funciones
│       ├── api.ts             # Cliente HTTP Axios con JWT
│       ├── auth.ts            # Funciones de auth (login, register)
│       └── storage.ts         # Manejo de localStorage/Cookies
│
├── .env.local                  # Variables de entorno local
├── .env.example               # Template de env
├── package.json               # Dependencias
├── tsconfig.json              # Configuración TypeScript
├── next.config.js             # Configuración Next.js
├── tailwind.config.ts         # Configuración TailwindCSS
├── postcss.config.js          # Configuración PostCSS
├── .eslintrc.json             # Configuración ESLint
└── README.md                  # Documentación del frontend
```

---

## 🔐 Flujo de Autenticación

```
1. Usuario llena formulario de Login/Register
   ↓
2. JavaScript valida datos cliente
   ↓
3. Envía POST a backend (/api/v1/auth/login o /register)
   ↓
4. Backend retorna JWT + user data
   ↓
5. Frontend guarda JWT en localStorage:
   - Token en: pokemon_bff_token
   - User en: pokemon_bff_user
   ↓
6. AuthContext se actualiza
   ↓
7. Navbar re-renderiza (muestra nombre, logout button)
   ↓
8. ProtectedRoute permite acceso a /favorites
   ↓
9. Todas las llamadas API incluyen JWT automáticamente
   (Axios interceptor lo inyecta en Authorization header)
```

---

## 🎨 Componentes Principales

### AuthContext
```typescript
// Proporciona acceso a auth state en toda la app
const { user, isAuthenticated, isLoading, logout, setUser } = useAuthContext();

// Propiedades:
// - user: { id, name, email } | null
// - isAuthenticated: boolean
// - isLoading: boolean (durante inicialización)
// - logout(): void
// - setUser(): (user) => void
```

### useAuth Hook
```typescript
// Hook personalizado (recomendado usar sobre useAuthContext)
const { user, isAuthenticated, logout } = useAuth();

// Lanza error si no está dentro de AuthProvider
```

### useToast Hook
```typescript
// Sistema de notificaciones
const { showToast } = useToast();

// Tipos: 'success' | 'error' | 'warning' | 'info'
showToast('Mensaje', 'success');
// Auto-desaparece en 5 segundos
```

### ProtectedRoute Component
```typescript
// Protege componentes/páginas
<ProtectedRoute>
  <YourComponent />
</ProtectedRoute>

// Si no está autenticado:
// - Muestra loading
// - Redirecciona a /login
```

---

## 💾 Variables de Entorno

**Archivo: `.env.local`**

```bash
# API Backend
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1

# App Info
NEXT_PUBLIC_APP_NAME=Pokemon BFF
NEXT_PUBLIC_APP_URL=http://localhost:3000

# Storage Keys
NEXT_PUBLIC_AUTH_TOKEN_KEY=pokemon_bff_token
NEXT_PUBLIC_AUTH_USER_KEY=pokemon_bff_user
```

---

## 🧪 Testing Manual

### 1. Verificar JWT Almacenado
```javascript
// En DevTools Console (F12)
localStorage.getItem('pokemon_bff_token')
// Debería mostrar un string JWT largo
```

### 2. Verificar User Data
```javascript
JSON.parse(localStorage.getItem('pokemon_bff_user'))
// Debería mostrar: { id: 1, name: 'John', email: 'john@example.com' }
```

### 3. Verificar API Client
```javascript
// Abrir Network tab (F12) y hacer click en algún botón
// Cada request debería tener header:
// Authorization: Bearer [token_aqui]
```

### 4. Limpiar Datos (Reset)
```javascript
localStorage.removeItem('pokemon_bff_token');
localStorage.removeItem('pokemon_bff_user');
location.reload();
// Volverá a dashboard sin autenticación
```

---

## 🐛 Troubleshooting

### Error: "Cannot GET /api/..."
**Causa:** Backend no está corriendo
**Solución:**
```bash
cd app
php artisan serve
```

### Error: "JWT malformed"
**Causa:** Token guardado incorrecto
**Solución:**
```javascript
localStorage.clear();
location.reload();
// Hacer login nuevamente
```

### Componentes No Se Actualizan Después de Login
**Causa:** AuthContext no se inicializó
**Solución:**
1. Revisar que `<AuthProvider>` envuelve todo en `layout.tsx`
2. Verificar Console para errores
3. Recargar página

### CORS Error
**Causa:** Backend sin CORS configurado
**Solución:** Revisar `config/cors.php` en backend

---

## 📚 Documentación Relacionada

- **[FRONTEND_PHASE_4_1.md](FRONTEND_PHASE_4_1.md)** - Guía técnica detallada
- **[QUICKSTART_FRONTEND.md](QUICKSTART_FRONTEND.md)** - Quick start rápido
- **[FASE_4_1_COMPLETION.md](FASE_4_1_COMPLETION.md)** - Reporte de completitud
- **[frontend/README.md](frontend/README.md)** - README del frontend

---

## ✅ Checklist - Antes de Fase 4.2

- [ ] `npm run dev` ejecuta sin errores
- [ ] Dashboard carga en http://localhost:3000
- [ ] Login page funciona
- [ ] Register page funciona
- [ ] Puedes crear una cuenta
- [ ] JWT se guarda en localStorage
- [ ] Navbar muestra nombre después de login
- [ ] `/favorites` redirecciona a login si no logueado
- [ ] Logout limpia token y redirecciona
- [ ] `npm run lint` sin errores
- [ ] `npm run type-check` sin errores

---

## 🚀 Fase 4.2 - Próxima

Una vez completada Fase 4.1, se procederá con:

**Fase 4.2: Pokemon Pages & Favorites Display**
- Grid de Pokémon
- Búsqueda y filtros
- Detalle de Pokémon
- Agregar/quitar favoritos
- Integración con backend `/pokemon` endpoints

---

## 📊 Git History

```bash
# Ver commits de Fase 4.1
git log --oneline -10

# Ver cambios en frontend
git log -p frontend/
```

**Último commit:**
```
44d9195 - feat(frontend): add Fase 4.1 - Next.js auth & layout complete
```

---

## 💡 Tips

### 1. Hot Reload
- Frontend recompila automáticamente al guardar archivos
- Perfecto para desarrollo rápido

### 2. TypeScript IntelliSense
- VSCode da autocompletado automático
- Úsalo para descubrir APIs disponibles

### 3. Network Inspector
- F12 → Network tab
- Ver todas las requests al backend
- Verificar JWT en headers

### 4. React DevTools
- Instalar extensión: React Developer Tools
- Inspeccionar componentes y estado

### 5. Console Logs
- Úsalos para debuggear
- Todos se ven en `npm run dev` terminal

---

## 📞 Quick Help

**¿No funciona login?**
1. ¿Backend corre? (http://localhost:8000)
2. ¿.env.local correcto?
3. ¿Email existe en DB?
4. Ver console para errores

**¿No persiste JWT?**
1. Verificar localStorage (F12)
2. Revisar AuthContext inicialization
3. Comprobar useEffect en AuthProvider

**¿Componentes no se actualizan?**
1. Usar `useAuth()` en lugar de `useAuthContext()`
2. Verificar que está dentro de `<AuthProvider>`
3. Revisar estado en React DevTools

---

## 🎯 Remember

```
✅ Frontend = Fase 4.1 COMPLETADA
⏳ Tests automatizados = Fase 4.2+
⏳ Pokémon UI = Fase 4.2
⏳ Deploy producción = Fase 4.4
```

**Estás en buen camino! 🚀**

Para empezar:
```bash
cd frontend && npm run dev
```

Abre http://localhost:3000

¡Disfruta! 🎉
