'use client';

import React, { useState } from 'react';
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
  const [isLoading, setIsLoading] = useState(false);
  const { showToast } = useToast();

  const handleFavoriteClick = async () => {
    if (!isLoggedIn) {
      showToast('Debes estar logueado para guardar favoritos', 'warning');
      return;
    }

    try {
      setIsLoading(true);
      await toggleFavorite(pokemon.id, isFavorite);
      const newFavoriteState = !isFavorite;
      setIsFavorite(newFavoriteState);

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
      showToast('Error al actualizar favoritos', 'error');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
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

        {/* Favorite Button */}
        {isLoggedIn && (
          <button
            onClick={handleFavoriteClick}
            disabled={isLoading}
            className="absolute top-2 right-2 p-2 bg-white/80 dark:bg-gray-800/80 rounded-full hover:bg-white dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label={isFavorite ? 'Remove from favorites' : 'Add to favorites'}
          >
            <svg
              className={`w-5 h-5 transition-colors ${
                isFavorite ? 'fill-red-500 text-red-500' : 'text-gray-400 dark:text-gray-500'
              }`}
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
          </button>
        )}
      </div>

      {/* Content */}
      <div className="p-4">
        {/* Pokemon ID */}
        <div className="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
          #{String(pokemon.id).padStart(3, '0')}
        </div>

        {/* Pokemon Name */}
        <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-2 truncate">
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
      </div>

      {/* Favorite indicator */}
      {isFavorite && !isLoggedIn && (
        <div className="px-4 pb-3 text-xs font-semibold text-red-500 flex items-center gap-1">
          <svg className="w-4 h-4 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
          </svg>
          Favorito
        </div>
      )}
    </div>
  );
};

export default PokemonCard;
