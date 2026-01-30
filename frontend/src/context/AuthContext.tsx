'use client';

import {
  createContext,
  useContext,
  useState,
  useEffect,
  ReactNode,
  useCallback,
} from 'react';
import { User, getToken, getUser, isAuthenticated as checkAuth, logout as performLogout } from '@/lib/auth';

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  logout: () => void;
  setUser: (user: User | null) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUserState] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Cargar usuario al montar el componente
  useEffect(() => {
    const initializeAuth = () => {
      try {
        if (checkAuth()) {
          const currentUser = getUser();
          if (currentUser) {
            setUserState(currentUser);
            setIsAuthenticated(true);
          }
        }
      } catch (error) {
        console.error('Error initializing auth:', error);
      } finally {
        setIsLoading(false);
      }
    };

    initializeAuth();
  }, []);

  // Escuchar cambios en localStorage
  useEffect(() => {
    const handleStorageChange = () => {
      try {
        if (checkAuth()) {
          const currentUser = getUser();
          if (currentUser) {
            setUserState(currentUser);
            setIsAuthenticated(true);
          }
        } else {
          setUserState(null);
          setIsAuthenticated(false);
        }
      } catch (error) {
        console.error('Error updating auth from storage:', error);
      }
    };

    // Escuchar evento de storage (cambios en otros tabs/ventanas)
    window.addEventListener('storage', handleStorageChange);

    return () => {
      window.removeEventListener('storage', handleStorageChange);
    };
  }, []);

  const logout = useCallback(() => {
    performLogout();
    setUserState(null);
    setIsAuthenticated(false);
  }, []);

  const handleSetUser = useCallback((newUser: User | null) => {
    setUserState(newUser);
    setIsAuthenticated(!!newUser);
  }, []);

  const value: AuthContextType = {
    user,
    isLoading,
    isAuthenticated,
    logout,
    setUser: handleSetUser,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuthContext = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuthContext must be used within an AuthProvider');
  }
  return context;
};
