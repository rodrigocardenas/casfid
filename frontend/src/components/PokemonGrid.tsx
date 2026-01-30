'use client';

import React from 'react';
import { Pokemon } from '@/lib/pokemon';
import { PokemonCard } from './PokemonCard';
import { Loading } from './Loading';

interface PokemonGridProps {
  pokemon: Pokemon[];
  isLoading: boolean;
  isEmpty?: boolean;
  onFavoriteChange?: (pokemonId: number, isFavorite: boolean) => void;
  isLoggedIn?: boolean;
  currentPage?: number;
  totalPages?: number;
  onPageChange?: (page: number) => void;
}

export const PokemonGrid: React.FC<PokemonGridProps> = ({
  pokemon,
  isLoading,
  isEmpty = false,
  onFavoriteChange,
  isLoggedIn = false,
  currentPage = 1,
  totalPages = 1,
  onPageChange,
}) => {
  if (isLoading) {
    return (
      <div className="w-full h-screen flex items-center justify-center">
        <Loading />
      </div>
    );
  }

  if (isEmpty || pokemon.length === 0) {
    return (
      <div className="w-full py-16 flex flex-col items-center justify-center">
        <div className="text-center">
          <svg
            className="w-20 h-20 text-gray-400 mx-auto mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h3 className="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">
            No se encontraron Pokémon
          </h3>
          <p className="text-gray-500 dark:text-gray-400">
            Intenta ajustar tus filtros o búsqueda
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full">
      {/* Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 mb-8">
        {pokemon.map((poke) => (
          <PokemonCard
            key={poke.id}
            pokemon={poke}
            onFavoriteChange={onFavoriteChange}
            isLoggedIn={isLoggedIn}
          />
        ))}
      </div>

      {/* Pagination */}
      {totalPages > 1 && onPageChange && (
        <div className="flex justify-center items-center gap-2 py-8">
          {/* Previous Button */}
          <button
            onClick={() => onPageChange(Math.max(1, currentPage - 1))}
            disabled={currentPage === 1}
            className="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            ← Anterior
          </button>

          {/* Page Numbers */}
          <div className="flex gap-1">
            {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
              let pageNum;
              if (totalPages <= 5) {
                pageNum = i + 1;
              } else if (currentPage <= 3) {
                pageNum = i + 1;
              } else if (currentPage >= totalPages - 2) {
                pageNum = totalPages - 4 + i;
              } else {
                pageNum = currentPage - 2 + i;
              }

              return (
                <button
                  key={pageNum}
                  onClick={() => onPageChange(pageNum)}
                  className={`px-3 py-2 rounded-lg transition-colors ${
                    currentPage === pageNum
                      ? 'bg-blue-600 text-white font-semibold'
                      : 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                  }`}
                >
                  {pageNum}
                </button>
              );
            })}
          </div>

          {/* Next Button */}
          <button
            onClick={() => onPageChange(Math.min(totalPages, currentPage + 1))}
            disabled={currentPage === totalPages}
            className="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Siguiente →
          </button>

          {/* Page Info */}
          <span className="ml-4 text-sm text-gray-600 dark:text-gray-400">
            Página {currentPage} de {totalPages}
          </span>
        </div>
      )}
    </div>
  );
};

export default PokemonGrid;
