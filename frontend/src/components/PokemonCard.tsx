'use client';

import React, { useState, useTransition } from 'react';
import Image from 'next/image';
import { Pokemon, toggleFavorite, getTypeColor, formatPokemonName, formatTypeName } from '@/lib/pokemon';
import { useToast } from '@/hooks/useToast';

interface PokemonCardProps {
  pokemon: Pokemon;
  onFavoriteChange?: (pokemonId: number, isFavorite: boolean) => void;
  isLoggedIn?: boolean;
}

export const PokemonCard: React.FC<PokemonCardProps> = ({
  pokemon,
  onFavoriteChange,
  isLoggedIn = false,
}) => {
  const [isFavorite, setIsFavorite] = useState(pokemon.is_favorite || false);
  const [isPending, startTransition] = useTransition();
  const { showToast } = useToast();

  const handleFavoriteClick = async () => {
    if (!isLoggedIn) {
      showToast('Debes estar logueado para guardar favoritos', 'warning');
      return;
    }

    // Optimistic UI: Update state immediately
    const newFavoriteState = !isFavorite;
    setIsFavorite(newFavoriteState);

    // Call API in background
    startTransition(async () => {
      try {
        await toggleFavorite(pokemon.id, isFavorite);

        if (onFavoriteChange) {
          onFavoriteChange(pokemon.id, newFavoriteState);
        }

        showToast(
          newFavoriteState
            ? `${formatPokemonName(pokemon.name)} agregado a favoritos`
            : `${formatPokemonName(pokemon.name)} removido de favoritos`,
          'success'
        );
      } catch (error) {
        console.error('Error updating favorite:', error);
        // Rollback on error
        setIsFavorite(!newFavoriteState);
        showToast('Error al actualizar favoritos', 'error');
      }
    });
  };

  return (
    <div className={`bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 ${
      isFavorite 
        ? 'border-yellow-400 dark:border-yellow-500 ring-2 ring-yellow-200 dark:ring-yellow-900/50' 
        : 'border-gray-200 dark:border-gray-700'
    }`}>
      {/* Image Container */}
      <div className="relative w-full h-48 bg-gradient-to-b from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center overflow-hidden">
        {pokemon.image ? (
          <Image
            src={pokemon.image}
            alt={pokemon.name}
            width={160}
            height={160}
            priority={false}
            className="h-40 w-40 object-contain hover:scale-110 transition-transform duration-300"
          />
        ) : (
          <div className="h-40 w-40 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center">
            <span className="text-gray-500 dark:text-gray-400 text-sm">
              No image
            </span>
          </div>
        )}

        {/* Favorite Star Button */}
        {isLoggedIn && (
          <button
            onClick={handleFavoriteClick}
            disabled={isPending}
            className={`absolute top-2 right-2 p-2 rounded-full transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform ${
              isPending ? 'scale-110' : 'hover:scale-110'
            } ${
              isFavorite
                ? 'bg-yellow-100 dark:bg-yellow-900/30 shadow-lg'
                : 'bg-white/80 dark:bg-gray-800/80 hover:bg-yellow-50 dark:hover:bg-gray-700'
            }`}
            aria-label={isFavorite ? 'Remove from favorites' : 'Add to favorites'}
          >
            <svg
              className={`w-6 h-6 transition-all duration-200 ${
                isFavorite 
                  ? 'fill-yellow-400 dark:fill-yellow-500 text-yellow-400 dark:text-yellow-500 drop-shadow-md' 
                  : 'fill-none text-gray-400 dark:text-gray-500'
              }`}
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              stroke="currentColor"
              strokeWidth={isFavorite ? 0 : 2}
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.734 20.84a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"
              />
            </svg>
          </button>
        )}

        {/* Favorite Indicator Badge */}
        {isFavorite && (
          <div className="absolute top-2 left-2 bg-yellow-100 dark:bg-yellow-900/40 rounded-full p-2 animate-pulse">
            <span className="text-xs font-bold text-yellow-600 dark:text-yellow-400">⭐</span>
          </div>
        )}
      </div>

      {/* Content */}
      <div className={`p-4 ${isFavorite ? 'bg-yellow-50 dark:bg-yellow-900/10' : ''}`}>
        {/* Pokemon ID */}
        <div className="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
          #{String(pokemon.id).padStart(3, '0')}
        </div>

        {/* Pokemon Name */}
        <h3 className={`text-lg font-bold mb-2 truncate transition-colors ${
          isFavorite 
            ? 'text-yellow-700 dark:text-yellow-400' 
            : 'text-gray-900 dark:text-white'
        }`}>
          {formatPokemonName(pokemon.name)}
        </h3>

        {/* Types */}
        <div className="flex gap-2 flex-wrap">
          {pokemon.types && pokemon.types.length > 0 ? (
            pokemon.types.map((type) => (
              <span
                key={type.id}
                className="px-3 py-1 rounded-full text-xs font-semibold text-white"
                style={{
                  backgroundColor: getTypeColor(type.name),
                }}
              >
                {formatTypeName(type.name)}
              </span>
            ))
          ) : (
            <span className="px-3 py-1 rounded-full text-xs font-semibold bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
              Desconocido
            </span>
          )}
        </div>

        {/* Stats (if available) */}
        {(pokemon.height || pokemon.weight) && (
          <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
            <div className="flex justify-between">
              {pokemon.height && (
                <span>Altura: {pokemon.height}m</span>
              )}
              {pokemon.weight && (
                <span>Peso: {pokemon.weight}kg</span>
              )}
            </div>
          </div>
        )}

        {/* Description (if available) */}
        {pokemon.description && (
          <p className="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
            {pokemon.description}
          </p>
        )}

        {/* Favorite Status Footer */}
        {isFavorite && !isLoggedIn && (
          <div className="mt-3 pt-3 border-t border-yellow-200 dark:border-yellow-800 text-xs font-semibold text-yellow-600 dark:text-yellow-400 flex items-center gap-1">
            <svg className="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.734 20.84a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
            Favorito
          </div>
        )}
      </div>
    </div>
  );
};

export default PokemonCard;
