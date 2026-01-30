'use client';

import { useAuthContext } from '@/context/AuthContext';
import { User } from '@/lib/auth';

interface UseAuthReturn {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  logout: () => void;
}

export const useAuth = (): UseAuthReturn => {
  const context = useAuthContext();

  return {
    user: context.user,
    isAuthenticated: context.isAuthenticated,
    isLoading: context.isLoading,
    logout: context.logout,
  };
};
