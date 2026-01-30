'use client';

// Storage utilities para trabajar con localStorage y cookies

export interface StorageOptions {
  path?: string;
  secure?: boolean;
  sameSite?: 'Strict' | 'Lax' | 'None';
  maxAge?: number; // en segundos
}

// LocalStorage
export const storage = {
  getItem: (key: string): string | null => {
    if (typeof window === 'undefined') return null;
    try {
      return localStorage.getItem(key);
    } catch {
      return null;
    }
  },

  setItem: (key: string, value: string): void => {
    if (typeof window === 'undefined') return;
    try {
      localStorage.setItem(key, value);
    } catch {
      console.warn(`Unable to set localStorage item: ${key}`);
    }
  },

  removeItem: (key: string): void => {
    if (typeof window === 'undefined') return;
    try {
      localStorage.removeItem(key);
    } catch {
      console.warn(`Unable to remove localStorage item: ${key}`);
    }
  },

  clear: (): void => {
    if (typeof window === 'undefined') return;
    try {
      localStorage.clear();
    } catch {
      console.warn('Unable to clear localStorage');
    }
  },
};

// Cookies (helpers básicos)
export const cookies = {
  setCookie: (name: string, value: string, options?: StorageOptions): void => {
    if (typeof document === 'undefined') return;

    let cookieString = `${encodeURIComponent(name)}=${encodeURIComponent(value)}`;

    if (options?.maxAge) {
      cookieString += `; max-age=${options.maxAge}`;
    }

    if (options?.path) {
      cookieString += `; path=${options.path}`;
    }

    if (options?.secure) {
      cookieString += '; secure';
    }

    if (options?.sameSite) {
      cookieString += `; samesite=${options.sameSite}`;
    }

    document.cookie = cookieString;
  },

  getCookie: (name: string): string | null => {
    if (typeof document === 'undefined') return null;

    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
      const [key, value] = cookie.trim().split('=');
      if (decodeURIComponent(key) === name) {
        return decodeURIComponent(value);
      }
    }
    return null;
  },

  removeCookie: (name: string): void => {
    cookies.setCookie(name, '', { maxAge: 0, path: '/' });
  },
};
