# Pokemon BFF Frontend - README

## Descripción

Frontend Next.js para Pokemon BFF, una aplicación web para gestionar Pokémon favoritos con autenticación JWT segura.

## Características

- ✅ Autenticación con JWT
- ✅ TypeScript strict
- ✅ TailwindCSS para estilos
- ✅ Context API para estado global
- ✅ Protección de rutas
- ✅ Validaciones cliente y servidor
- ✅ Componentes reutilizables
- ✅ Manejo de errores y notificaciones
- ✅ Interfaz responsiva

## Estructura

```
src/
├── app/                      # App Router pages
│   ├── layout.tsx           # Layout principal
│   ├── page.tsx             # Página de inicio
│   ├── login/page.tsx       # Login
│   ├── register/page.tsx    # Registro
│   ├── favorites/page.tsx   # Favoritos (protegida)
│   └── globals.css          # Estilos globales
├── components/              # Componentes React
│   ├── Navbar.tsx          # Barra de navegación
│   ├── LoginForm.tsx       # Formulario de login
│   ├── RegisterForm.tsx    # Formulario de registro
│   ├── ProtectedRoute.tsx  # Protección de rutas
│   ├── Loading.tsx         # Componente loading
│   ├── Toast.tsx           # Notificaciones
├── context/                # Context API
│   └── AuthContext.tsx     # Contexto de autenticación
├── hooks/                  # Hooks personalizados
│   ├── useAuth.ts         # Hook de autenticación
│   └── useToast.ts        # Hook de notificaciones
└── lib/                    # Utilidades
    ├── api.ts             # Cliente HTTP
    ├── auth.ts            # Funciones de autenticación
    └── storage.ts         # Manejo de storage
```

## Configuración

### 1. Instalar dependencias

```bash
npm install
# o
yarn install
```

### 2. Variables de entorno

Copia `.env.example` a `.env.local` y configura:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_AUTH_TOKEN_KEY=pokemon_bff_token
NEXT_PUBLIC_AUTH_USER_KEY=pokemon_bff_user
```

### 3. Ejecutar en desarrollo

```bash
npm run dev
```

Abre [http://localhost:3000](http://localhost:3000)

## Comandos disponibles

- `npm run dev` - Iniciar servidor de desarrollo
- `npm run build` - Compilar para producción
- `npm run start` - Iniciar servidor de producción
- `npm run lint` - Ejecutar linter
- `npm run type-check` - Verificar tipos TypeScript

## Flujo de autenticación

1. **Login/Register** → Envía credenciales al backend
2. **Backend** → Valida y retorna JWT + usuario
3. **Frontend** → Guarda token en localStorage
4. **Requests posteriores** → Se incluye token en headers
5. **Protected routes** → Verifican autenticación

## Componentes principales

### AuthContext
Provee estado global de autenticación.

```tsx
const { user, isAuthenticated, logout } = useAuthContext();
```

### useAuth hook
Hook simplificado para acceder al contexto.

```tsx
const { user, isAuthenticated, isLoading, logout } = useAuth();
```

### ProtectedRoute
Envuelve componentes para protegerlos.

```tsx
<ProtectedRoute>
  <YourComponent />
</ProtectedRoute>
```

## Validaciones

### Cliente
- Email válido
- Contraseña mínimo 6 caracteres
- Confirmación de contraseña
- Campos requeridos

### Servidor
Las validaciones se realizan en el backend (API)

## Manejo de errores

```tsx
try {
  await login(credentials);
} catch (err) {
  showError(err.message);
}
```

## Toast/Notificaciones

```tsx
const { success, error, info, warning } = useToast();

success('Acción completada');
error('Error occurred');
```

## Desarrollo

### Crear un nuevo componente

```tsx
'use client';

import { FC } from 'react';

interface Props {
  // Props aquí
}

export const MyComponent: FC<Props> = ({ /* desestructurar props */ }) => {
  return <div>Mi componente</div>;
};
```

### Llamar API

```tsx
import { apiClient } from '@/lib/api';

const data = await apiClient.get('/endpoint');
const response = await apiClient.post('/endpoint', { data });
```

## Deployment

### Vercel (recomendado)

```bash
vercel deploy
```

### Docker

```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY . .
RUN npm install
RUN npm run build
EXPOSE 3000
CMD ["npm", "start"]
```

## Troubleshooting

### Token no se persiste
- Verifica que `NEXT_PUBLIC_AUTH_TOKEN_KEY` esté configurado
- Asegúrate de que localStorage no esté bloqueado

### Error 401 en requests
- El token ha expirado
- El token es inválido
- El usuario no tiene permisos

### CORS errors
- Verifica que `NEXT_PUBLIC_API_URL` sea correcto
- Asegúrate que el backend permite CORS

## Soporte

Para problemas o preguntas, consulta la documentación del backend.

## Licencia

Todos los derechos reservados © 2024
