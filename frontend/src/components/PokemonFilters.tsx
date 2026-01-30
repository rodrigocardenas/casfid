'use client';

import React, { useState, useEffect } from 'react';
import { getPokemonTypes, PokemonType } from '@/lib/pokemon';

interface PokemonFiltersProps {
  onSearchChange: (query: string) => void;
  onTypeChange: (type: string) => void;
  onFavoritesToggle?: (enabled: boolean) => void;
  isLoggedIn?: boolean;
  loading?: boolean;
}

export const PokemonFilters: React.FC<PokemonFiltersProps> = ({
  onSearchChange,
  onTypeChange,
  onFavoritesToggle,
  isLoggedIn = false,
  loading = false,
}) => {
  const [types, setTypes] = useState<PokemonType[]>([]);
  const [loadingTypes, setLoadingTypes] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedType, setSelectedType] = useState('');
  const [onlyFavorites, setOnlyFavorites] = useState(false);
  const [expandFilters, setExpandFilters] = useState(false);

  // Fetch Pokemon types on mount
  useEffect(() => {
    const fetchTypes = async () => {
      try {
        setLoadingTypes(true);
        const typesData = await getPokemonTypes();
        setTypes(typesData);
      } catch (error) {
        console.error('Error fetching types:', error);
      } finally {
        setLoadingTypes(false);
      }
    };

    fetchTypes();
  }, []);

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const query = e.target.value;
    setSearchQuery(query);
    onSearchChange(query);
  };

  const handleTypeChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const type = e.target.value;
    setSelectedType(type);
    onTypeChange(type);
  };

  const handleFavoritesToggle = (e: React.ChangeEvent<HTMLInputElement>) => {
    const isChecked = e.target.checked;
    setOnlyFavorites(isChecked);
    if (onFavoritesToggle) {
      onFavoritesToggle(isChecked);
    }
  };

  const handleReset = () => {
    setSearchQuery('');
    setSelectedType('');
    setOnlyFavorites(false);
    onSearchChange('');
    onTypeChange('');
    if (onFavoritesToggle) {
      onFavoritesToggle(false);
    }
  };

  return (
    <div className="w-full bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 mb-6 border border-gray-200 dark:border-gray-700">
      {/* Mobile Toggle */}
      <div className="md:hidden mb-4 flex items-center justify-between">
        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
          Filtros
        </h3>
        <button
          onClick={() => setExpandFilters(!expandFilters)}
          className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
        >
          <svg
            className={`w-5 h-5 transition-transform ${
              expandFilters ? 'rotate-180' : ''
            }`}
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M19 14l-7 7m0 0l-7-7m7 7V3"
            />
          </svg>
        </button>
      </div>

      {/* Filters Container */}
      <div
        className={`grid grid-cols-1 md:grid-cols-3 gap-4 ${
          !expandFilters && 'hidden md:grid'
        }`}
      >
        {/* Search */}
        <div>
          <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Buscar Pokémon
          </label>
          <div className="relative">
            <svg
              className="absolute left-3 top-3 w-5 h-5 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <input
              type="text"
              value={searchQuery}
              onChange={handleSearchChange}
              placeholder="Bulbasaur, Pikachu..."
              disabled={loading}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 disabled:opacity-50 disabled:cursor-not-allowed"
            />
          </div>
        </div>

        {/* Type Filter */}
        <div>
          <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tipo
          </label>
          <select
            value={selectedType}
            onChange={handleTypeChange}
            disabled={loading || loadingTypes}
            className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <option value="">Todos los tipos</option>
            {types.map((type) => (
              <option key={type.id} value={type.name}>
                {type.name.charAt(0).toUpperCase() + type.name.slice(1)}
              </option>
            ))}
          </select>
        </div>

        {/* Favorites Toggle */}
        {isLoggedIn && (
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Favoritos
            </label>
            <button
              onClick={() => handleFavoritesToggle({ target: { checked: !onlyFavorites } } as any)}
              disabled={loading}
              className={`w-full px-4 py-2 rounded-lg border-2 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${
                onlyFavorites
                  ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                  : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-red-500 dark:hover:border-red-500'
              }`}
            >
              <svg
                className={`w-5 h-5 inline mr-2 ${
                  onlyFavorites ? 'fill-red-500' : ''
                }`}
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
              </svg>
              {onlyFavorites ? 'Solo Favoritos' : 'Todos'}
            </button>
          </div>
        )}
      </div>

      {/* Additional Controls */}
      <div className={`mt-4 flex justify-end gap-2 ${!expandFilters && 'hidden md:flex'}`}>
        {(searchQuery || selectedType || onlyFavorites) && (
          <button
            onClick={handleReset}
            disabled={loading}
            className="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Limpiar filtros
          </button>
        )}

        {/* Mobile Close */}
        <button
          onClick={() => setExpandFilters(false)}
          className="md:hidden px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Aplicar
        </button>
      </div>

      {/* Active Filters Display */}
      {(searchQuery || selectedType || onlyFavorites) && (
        <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div className="flex flex-wrap gap-2">
            {searchQuery && (
              <span className="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                Búsqueda: {searchQuery}
                <button
                  onClick={() => {
                    setSearchQuery('');
                    onSearchChange('');
                  }}
                  className="hover:opacity-70"
                >
                  ✕
                </button>
              </span>
            )}

            {selectedType && (
              <span className="inline-flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-sm font-medium">
                Tipo: {selectedType.charAt(0).toUpperCase() + selectedType.slice(1)}
                <button
                  onClick={() => {
                    setSelectedType('');
                    onTypeChange('');
                  }}
                  className="hover:opacity-70"
                >
                  ✕
                </button>
              </span>
            )}

            {onlyFavorites && (
              <span className="inline-flex items-center gap-2 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full text-sm font-medium">
                Solo favoritos
                <button
                  onClick={() => {
                    setOnlyFavorites(false);
                    if (onFavoritesToggle) {
                      onFavoritesToggle(false);
                    }
                  }}
                  className="hover:opacity-70"
                >
                  ✕
                </button>
              </span>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default PokemonFilters;
