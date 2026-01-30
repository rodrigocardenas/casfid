'use client';

import Link from 'next/link';
import { useAuth } from '@/hooks/useAuth';

export default function Home() {
  const { isAuthenticated, user } = useAuth();

  return (
    <div className="container mx-auto px-4 py-12">
      <div className="max-w-4xl mx-auto">
        {/* Hero Section */}
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold mb-4 text-gray-800">
            🎮 Bienvenido a Pokemon BFF
          </h1>
          <p className="text-xl text-gray-600 mb-8">
            Gestiona y guarda tus Pokémon favoritos en un solo lugar
          </p>

          {!isAuthenticated ? (
            <div className="flex gap-4 justify-center">
              <Link href="/login" className="btn btn-primary text-lg px-8">
                Iniciar Sesión
              </Link>
              <Link href="/register" className="btn btn-secondary text-lg px-8">
                Registrarse
              </Link>
            </div>
          ) : (
            <div className="flex gap-4 justify-center">
              <Link href="/favorites" className="btn btn-primary text-lg px-8">
                Ver Mis Favoritos
              </Link>
              <p className="text-lg text-gray-700">
                ¡Hola, <span className="font-bold">{user?.name}</span>!
              </p>
            </div>
          )}
        </div>

        {/* Features Section */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          <div className="card">
            <h3 className="text-xl font-bold mb-3">📋 Organiza</h3>
            <p className="text-gray-600">
              Crea tu lista personalizada de Pokémon favoritos
            </p>
          </div>

          <div className="card">
            <h3 className="text-xl font-bold mb-3">💾 Sincroniza</h3>
            <p className="text-gray-600">
              Accede a tus favoritos desde cualquier dispositivo
            </p>
          </div>

          <div className="card">
            <h3 className="text-xl font-bold mb-3">🔒 Protege</h3>
            <p className="text-gray-600">
              Tu información está segura con autenticación JWT
            </p>
          </div>
        </div>

        {/* Info Section */}
        <div className="card bg-blue-50 border-2 border-blue-200">
          <h2 className="text-2xl font-bold mb-4">¿Qué es Pokemon BFF?</h2>
          <p className="text-gray-700 mb-4">
            Pokemon BFF es una aplicación web que te permite crear y gestionar tu lista personal
            de Pokémon favoritos. Con una interfaz intuitiva y acceso seguro mediante autenticación,
            puedes guardar y organizar tus Pokémon preferidos en cualquier momento.
          </p>
          <div className="bg-white p-4 rounded-lg mt-4">
            <h3 className="font-bold mb-2">Características principales:</h3>
            <ul className="list-disc list-inside text-gray-700 space-y-2">
              <li>Autenticación segura con JWT</li>
              <li>Interfaz responsiva y moderna</li>
              <li>Gestión completa de favoritos</li>
              <li>Sincronización en tiempo real</li>
              <li>Notificaciones inteligentes</li>
            </ul>
          </div>
        </div>

        {isAuthenticated && (
          <div className="mt-8 text-center">
            <p className="text-gray-600 mb-4">
              ¿Listo para empezar? Ve a tu dashboard de favoritos.
            </p>
            <Link href="/favorites" className="btn btn-primary text-lg">
              Ir a Favoritos
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
