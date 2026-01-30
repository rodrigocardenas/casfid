'use client';

import { useState, useCallback } from 'react';

interface Toast {
  id: string;
  message: string;
  type: 'success' | 'error' | 'info' | 'warning';
}

let toastId = 0;

export const useToast = () => {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const addToast = useCallback(
    (message: string, type: 'success' | 'error' | 'info' | 'warning' = 'info') => {
      const id = `toast-${toastId++}`;
      const newToast: Toast = { id, message, type };

      setToasts((prev) => [...prev, newToast]);

      // Remover después de 5 segundos
      setTimeout(() => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
      }, 5000);

      return id;
    },
    []
  );

  const removeToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  const success = useCallback((message: string) => addToast(message, 'success'), [addToast]);
  const error = useCallback((message: string) => addToast(message, 'error'), [addToast]);
  const info = useCallback((message: string) => addToast(message, 'info'), [addToast]);
  const warning = useCallback((message: string) => addToast(message, 'warning'), [addToast]);

  // Alias para showToast que por defecto es 'info'
  const showToast = useCallback((message: string, type: 'success' | 'error' | 'info' | 'warning' = 'info') => {
    return addToast(message, type);
  }, [addToast]);

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    info,
    warning,
    showToast,
  };
};
