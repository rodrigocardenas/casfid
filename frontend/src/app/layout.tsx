import type { Metadata } from 'next';
import { AuthProvider } from '@/context/AuthContext';
import { Navbar } from '@/components/Navbar';
import { Toast } from '@/components/Toast';
import './globals.css';

export const metadata: Metadata = {
  title: 'Pokemon BFF - Gestor de Favoritos',
  description: 'Aplicación web para gestionar tus Pokémon favoritos',
  keywords: ['pokemon', 'favoritos', 'bff'],
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
          <Navbar />
          <main className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
            {children}
          </main>
          <Toast />
        </AuthProvider>
      </body>
    </html>
  );
}
