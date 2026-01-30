# FRONTEND_AUTH_INTEGRATION.md - Guía de Integración Frontend

**Cómo conectar el Frontend Next.js con la API de Autenticación**

---

## 📋 Tabla de Contenidos

1. [Setup Inicial](#setup-inicial)
2. [Servicio de Autenticación](#servicio-de-autenticación)
3. [Context API](#context-api)
4. [Componentes](#componentes)
5. [Hooks Personalizados](#hooks-personalizados)
6. [Local Storage](#local-storage)
7. [Protección de Rutas](#protección-de-rutas)
8. [Ejemplos Completos](#ejemplos-completos)

---

## 🚀 Setup Inicial

### 1. Variables de Entorno

Crear `frontend/.env.local`:

```bash
# API Base URL
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

### 2. Cliente HTTP (Axios)

Crear `frontend/src/services/api.ts`:

```typescript
import axios, { AxiosInstance, InternalAxiosRequestConfig } from 'axios';

const api: AxiosInstance = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para agregar token a cada request
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor para manejar errores 401
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expirado - intentar refresh
      try {
        const { data } = await api.post('/auth/refresh');
        localStorage.setItem('auth_token', data.data.token);
        // Reintentar request original
        return api.request(error.config);
      } catch (refreshError) {
        // Refresh falló - limpiar y redirigir a login
        localStorage.removeItem('auth_token');
        window.location.href = '/auth/login';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

## 🔐 Servicio de Autenticación

Crear `frontend/src/services/authService.ts`:

```typescript
import api from './api';

export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data?: User;
  token?: string;
  expires_in?: number;
}

const authService = {
  // Registrar usuario
  async register(
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ): Promise<AuthResponse> {
    try {
      const { data } = await api.post<AuthResponse>('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });

      // Guardar token
      if (data.token) {
        localStorage.setItem('auth_token', data.token);
      }

      return data;
    } catch (error: any) {
      throw error.response?.data || error;
    }
  },

  // Iniciar sesión
  async login(email: string, password: string): Promise<AuthResponse> {
    try {
      const { data } = await api.post<AuthResponse>('/auth/login', {
        email,
        password,
      });

      // Guardar token
      if (data.token) {
        localStorage.setItem('auth_token', data.token);
      }

      return data;
    } catch (error: any) {
      throw error.response?.data || error;
    }
  },

  // Obtener usuario actual
  async getMe(): Promise<AuthResponse> {
    try {
      const { data } = await api.get<AuthResponse>('/auth/me');
      return data;
    } catch (error: any) {
      throw error.response?.data || error;
    }
  },

  // Renovar token
  async refreshToken(): Promise<AuthResponse> {
    try {
      const { data } = await api.post<AuthResponse>('/auth/refresh');

      // Guardar nuevo token
      if (data.data?.token) {
        localStorage.setItem('auth_token', data.data.token);
      }

      return data;
    } catch (error: any) {
      throw error.response?.data || error;
    }
  },

  // Cerrar sesión
  async logout(): Promise<AuthResponse> {
    try {
      const { data } = await api.post<AuthResponse>('/auth/logout');
      localStorage.removeItem('auth_token');
      return data;
    } catch (error: any) {
      // Limpiar token de todas formas
      localStorage.removeItem('auth_token');
      throw error.response?.data || error;
    }
  },

  // Obtener token guardado
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  },

  // Verificar si está autenticado
  isAuthenticated(): boolean {
    return !!this.getToken();
  },
};

export default authService;
```

---

## 🎯 Context API

Crear `frontend/src/context/AuthContext.tsx`:

```typescript
'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import authService, { User } from '@/services/authService';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  error: string | null;
  isAuthenticated: boolean;
  
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshToken: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Cargar usuario al iniciar
  useEffect(() => {
    const loadUser = async () => {
      try {
        if (authService.isAuthenticated()) {
          const response = await authService.getMe();
          if (response.data) {
            setUser(response.data);
          }
        }
      } catch (err: any) {
        console.error('Error loading user:', err);
        setError('Error cargando usuario');
      } finally {
        setLoading(false);
      }
    };

    loadUser();
  }, []);

  // Refresh automático cada 50 minutos (token expira en 60)
  useEffect(() => {
    if (!isAuthenticated) return;

    const interval = setInterval(async () => {
      try {
        await refreshToken();
      } catch (err) {
        console.error('Error refreshing token:', err);
      }
    }, 50 * 60 * 1000);

    return () => clearInterval(interval);
  }, [user]);

  const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
    try {
      setError(null);
      setLoading(true);
      const response = await authService.register(name, email, password, passwordConfirmation);
      if (response.data) {
        setUser(response.data);
      }
    } catch (err: any) {
      const errorMsg = err.message || 'Error en registro';
      setError(errorMsg);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const login = async (email: string, password: string) => {
    try {
      setError(null);
      setLoading(true);
      const response = await authService.login(email, password);
      if (response.data) {
        setUser(response.data);
      }
    } catch (err: any) {
      const errorMsg = err.message || 'Error en login';
      setError(errorMsg);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      setError(null);
      await authService.logout();
      setUser(null);
    } catch (err: any) {
      console.error('Error logging out:', err);
    } finally {
      setLoading(false);
    }
  };

  const refreshToken = async () => {
    try {
      setError(null);
      await authService.refreshToken();
    } catch (err: any) {
      console.error('Error refreshing token:', err);
      // Si refresh falla, desautenticar
      setUser(null);
      localStorage.removeItem('auth_token');
    }
  };

  const isAuthenticated = !!user;

  const value: AuthContextType = {
    user,
    loading,
    error,
    isAuthenticated,
    register,
    login,
    logout,
    refreshToken,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }
  return context;
};
```

---

## 🧩 Componentes

### LoginForm

Crear `frontend/src/components/Auth/LoginForm.tsx`:

```typescript
'use client';

import { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';

export const LoginForm: React.FC = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const { login } = useAuth();
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await login(email, password);
      router.push('/pokemon');
    } catch (err: any) {
      setError(err.message || 'Error al iniciar sesión');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-md mx-auto">
      <h2 className="text-2xl font-bold mb-6">Iniciar Sesión</h2>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      <div className="mb-4">
        <label htmlFor="email" className="block text-sm font-medium mb-1">
          Email
        </label>
        <input
          type="email"
          id="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          disabled={loading}
        />
      </div>

      <div className="mb-6">
        <label htmlFor="password" className="block text-sm font-medium mb-1">
          Contraseña
        </label>
        <input
          type="password"
          id="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          disabled={loading}
        />
      </div>

      <button
        type="submit"
        disabled={loading}
        className="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded disabled:bg-gray-400"
      >
        {loading ? 'Cargando...' : 'Iniciar Sesión'}
      </button>

      <p className="text-sm text-gray-600 mt-4 text-center">
        ¿No tienes cuenta? <a href="/auth/register" className="text-blue-500 hover:underline">Registrate</a>
      </p>
    </form>
  );
};
```

---

## 🎣 Hooks Personalizados

Crear `frontend/src/hooks/useAuth.ts`:

```typescript
'use client';

import { useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';

export const useAuthHook = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuthHook debe usarse dentro de AuthProvider');
  }
  return context;
};
```

---

## 💾 Local Storage

Implementar en `frontend/src/services/storage.ts`:

```typescript
const STORAGE_KEYS = {
  AUTH_TOKEN: 'auth_token',
  USER: 'user',
};

export const storage = {
  // Token
  setToken(token: string) {
    localStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token);
  },

  getToken(): string | null {
    return localStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
  },

  removeToken() {
    localStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
  },

  // Usuario
  setUser(user: any) {
    localStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(user));
  },

  getUser() {
    const user = localStorage.getItem(STORAGE_KEYS.USER);
    return user ? JSON.parse(user) : null;
  },

  removeUser() {
    localStorage.removeItem(STORAGE_KEYS.USER);
  },

  // Limpiar todo
  clear() {
    localStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
    localStorage.removeItem(STORAGE_KEYS.USER);
  },
};
```

---

## 🔐 Protección de Rutas

Crear `frontend/src/middleware.ts`:

```typescript
import { NextRequest, NextResponse } from 'next/server';

export function middleware(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value;

  // Rutas que requieren autenticación
  const protectedRoutes = ['/pokemon', '/dashboard'];
  
  // Rutas públicas
  const publicRoutes = ['/auth/login', '/auth/register', '/'];

  const pathname = request.nextUrl.pathname;

  // Si intenta acceder a ruta protegida sin token
  if (protectedRoutes.some(route => pathname.startsWith(route)) && !token) {
    return NextResponse.redirect(new URL('/auth/login', request.url));
  }

  // Si intenta acceder a login/register con token
  if (publicRoutes.some(route => pathname === route) && token) {
    return NextResponse.redirect(new URL('/pokemon', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
```

---

## 📝 Ejemplos Completos

### Layout con Provider

`frontend/src/app/layout.tsx`:

```typescript
import type { Metadata } from 'next';
import { AuthProvider } from '@/context/AuthContext';
import './globals.css';

export const metadata: Metadata = {
  title: 'Pokémon BFF',
  description: 'Backend for Frontend - Pokémon API',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="es">
      <body>
        <AuthProvider>
          {children}
        </AuthProvider>
      </body>
    </html>
  );
}
```

### Página de Pokémon Protegida

`frontend/src/app/pokemon/page.tsx`:

```typescript
'use client';

import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';

export default function PokemonPage() {
  const { user, isAuthenticated, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading && !isAuthenticated) {
      router.push('/auth/login');
    }
  }, [loading, isAuthenticated, router]);

  if (loading) {
    return <div>Cargando...</div>;
  }

  if (!isAuthenticated) {
    return <div>No autenticado</div>;
  }

  return (
    <div className="p-8">
      <h1 className="text-3xl font-bold mb-4">
        Bienvenido, {user?.name}!
      </h1>
      <p className="text-gray-600">Email: {user?.email}</p>
      
      <div className="mt-8">
        <h2 className="text-2xl font-bold mb-4">Pokémon List</h2>
        {/* Listar pokémon aquí */}
      </div>
    </div>
  );
}
```

---

## 🧪 Testing en Frontend

```bash
# Instalar dependencias
npm install

# Tests
npm run test

# Dev server
npm run dev

# Build
npm run build
```

---

## 🔗 Flujo de Autenticación Completo

```
Usuario → Login Form
         ↓
      API POST /auth/login
         ↓
      Backend verifica credenciales
         ↓
      JWT Token generado
         ↓
      Token guardado en localStorage
         ↓
      User Context actualizado
         ↓
      Redirigir a /pokemon
         ↓
      Requests incluyen Authorization: Bearer {token}
         ↓
      Middleware de Nginx permite acceso
         ↓
      AuthController verifica JWT
         ↓
      Ejecutar lógica (GET /pokemon)
         ↓
      Respuesta enviada con datos
```

---

**Documentación:** 2026-01-30
**Versión:** 1.0
