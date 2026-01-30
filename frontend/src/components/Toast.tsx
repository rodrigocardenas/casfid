'use client';

import { useToast } from '@/hooks/useToast';

export const Toast = () => {
  const { toasts, removeToast } = useToast();

  const getBackgroundColor = (type: string) => {
    switch (type) {
      case 'success':
        return 'bg-green-500';
      case 'error':
        return 'bg-red-500';
      case 'warning':
        return 'bg-yellow-500';
      case 'info':
      default:
        return 'bg-blue-500';
    }
  };

  return (
    <div className="fixed top-4 right-4 z-50 space-y-2">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          className={`${getBackgroundColor(
            toast.type
          )} text-white px-6 py-3 rounded-lg shadow-lg flex items-center justify-between min-w-max animate-in`}
        >
          <span>{toast.message}</span>
          <button
            onClick={() => removeToast(toast.id)}
            className="ml-4 font-bold hover:opacity-80"
          >
            ✕
          </button>
        </div>
      ))}
    </div>
  );
};
