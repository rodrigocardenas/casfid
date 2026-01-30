'use client';

import axios, { AxiosInstance, AxiosError, AxiosResponse } from 'axios';
import { getToken } from './auth';

interface ApiError {
  message: string;
  status: number;
  data?: Record<string, any>;
}

class ApiClient {
  private client: AxiosInstance;
  private baseURL: string;

  constructor() {
    this.baseURL =
      typeof window !== 'undefined'
        ? process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'
        : 'http://localhost:8000/api';

    console.log('API Client initialized with baseURL:', this.baseURL);

    this.client = axios.create({
      baseURL: this.baseURL,
      timeout: 60000, // 60 segundos para cargar todos los pokémon
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    // Interceptor para agregar token
    this.client.interceptors.request.use((config) => {
      const token = getToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    });

    // Interceptor para manejar errores
    this.client.interceptors.response.use(
      (response) => response,
      (error: AxiosError) => {
        if (error.response?.status === 401) {
          // Token expirado o inválido
          if (typeof window !== 'undefined') {
            localStorage.removeItem(process.env.NEXT_PUBLIC_AUTH_TOKEN_KEY || '');
            localStorage.removeItem(process.env.NEXT_PUBLIC_AUTH_USER_KEY || '');
            window.location.href = '/login';
          }
        }
        return Promise.reject(error);
      }
    );
  }

  async get<T>(url: string, config?: any): Promise<T> {
    try {
      const response: AxiosResponse<T> = await this.client.get(url, config);
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async post<T>(url: string, data?: any, config?: any): Promise<T> {
    try {
      const response: AxiosResponse<T> = await this.client.post(url, data, config);
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async put<T>(url: string, data?: any, config?: any): Promise<T> {
    try {
      const response: AxiosResponse<T> = await this.client.put(url, data, config);
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async delete<T>(url: string, config?: any): Promise<T> {
    try {
      const response: AxiosResponse<T> = await this.client.delete(url, config);
      return response.data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  private handleError(error: any): ApiError {
    if (axios.isAxiosError(error)) {
      const status = error.response?.status || 500;
      const message =
        error.response?.data?.message ||
        error.message ||
        'Error en la solicitud';
      const data = error.response?.data;

      return {
        message,
        status,
        data,
      };
    }

    return {
      message: 'Error desconocido',
      status: 500,
    };
  }
}

export const apiClient = new ApiClient();
export type { ApiError };
