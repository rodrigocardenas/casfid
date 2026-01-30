'use client';

import { apiClient } from './api';

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

const TOKEN_KEY = process.env.NEXT_PUBLIC_AUTH_TOKEN_KEY || 'pokemon_bff_token';
const USER_KEY = process.env.NEXT_PUBLIC_AUTH_USER_KEY || 'pokemon_bff_user';

// Obtener token
export const getToken = (): string | null => {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(TOKEN_KEY) || null;
};

// Obtener usuario actual
export const getUser = (): User | null => {
  if (typeof window === 'undefined') return null;
  const user = localStorage.getItem(USER_KEY);
  return user ? JSON.parse(user) : null;
};

// Verificar si está autenticado
export const isAuthenticated = (): boolean => {
  return !!getToken();
};

// Login
export const login = async (credentials: LoginCredentials): Promise<AuthResponse> => {
  const response = await apiClient.post<AuthResponse>('/auth/login', credentials);
  
  if (response.token && response.user) {
    localStorage.setItem(TOKEN_KEY, response.token);
    localStorage.setItem(USER_KEY, JSON.stringify(response.user));
  }
  
  return response;
};

// Register
export const register = async (
  credentials: RegisterCredentials
): Promise<AuthResponse> => {
  const response = await apiClient.post<AuthResponse>('/auth/register', credentials);
  
  if (response.token && response.user) {
    localStorage.setItem(TOKEN_KEY, response.token);
    localStorage.setItem(USER_KEY, JSON.stringify(response.user));
  }
  
  return response;
};

// Logout
export const logout = (): void => {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
};

// Verificar token en servidor (para middleware)
export const verifyToken = async (): Promise<boolean> => {
  try {
    const token = getToken();
    if (!token) return false;
    
    // Llamar a un endpoint para verificar el token
    await apiClient.get('/auth/verify');
    return true;
  } catch {
    return false;
  }
};
