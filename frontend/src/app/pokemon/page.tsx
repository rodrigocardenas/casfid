'use client';

import { useState, useEffect, useCallback } from 'react';
import { PokemonFilters } from '@/components/PokemonFilters';
import { PokemonGrid } from '@/components/PokemonGrid';
import { useAuth } from '@/hooks/useAuth';
import { useToast } from '@/hooks/useToast';
import {
  searchWithFilters,
  getFavoritePokemon,
  Pokemon,
  FilterOptions,
} from '@/lib/pokemon';

const PER_PAGE = 12;

export default function PokemonPage() {
  const { isAuthenticated } = useAuth();
  const { showToast } = useToast();

  // State
  const [pokemon, setPokemon] = useState<Pokemon[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // Filter state
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedType, setSelectedType] = useState('');
  const [onlyFavorites, setOnlyFavorites] = useState(false);

  // Fetch Pokémon with current filters
  const fetchPokemon = useCallback(async () => {
    try {
      setIsLoading(true);

      let response;

      // If only favorites, use different endpoint
      if (onlyFavorites && isAuthenticated) {
        response = await getFavoritePokemon({
          page: currentPage,
          perPage: PER_PAGE,
        });
      } else {
        // Use search with all filters
        const filters: FilterOptions = {
          search: searchQuery || undefined,
          type: selectedType || undefined,
          page: currentPage,
          perPage: PER_PAGE,
        };

        response = await searchWithFilters(filters);
      }

      console.log('Response from searchWithFilters:', response);
      console.log('response.data:', response.data);
      console.log('response.pagination:', response.pagination);
      console.log('Setting pokemon to:', Array.isArray(response.data) ? response.data.length + ' items' : 'NOT AN ARRAY');
      setPokemon(response.data || []);

      // Set pagination
      if (response.pagination) {
        setTotalPages(response.pagination.last_page || 1);
      } else if (response.meta) {
        setTotalPages(
          Math.ceil((response.meta.total || 0) / PER_PAGE)
        );
      } else {
        setTotalPages(1);
      }
    } catch (error) {
      console.error('Error fetching Pokemon:', error);
      showToast('Error al cargar Pokémon', 'error');
      setPokemon([]);
      setTotalPages(1);
    } finally {
      setIsLoading(false);
    }
  }, [currentPage, searchQuery, selectedType, onlyFavorites, isAuthenticated, showToast]);

  // Fetch on mount and filter changes
  useEffect(() => {
    setCurrentPage(1); // Reset to first page when filters change
  }, [searchQuery, selectedType, onlyFavorites]);

  // Fetch when page or filter changes
  useEffect(() => {
    fetchPokemon();
  }, [fetchPokemon]);

  const handleSearchChange = (query: string) => {
    setSearchQuery(query);
  };

  const handleTypeChange = (type: string) => {
    setSelectedType(type);
  };

  const handleFavoritesToggle = (enabled: boolean) => {
    if (enabled && !isAuthenticated) {
      showToast('Debes estar logueado para ver favoritos', 'warning');
      return;
    }
    setOnlyFavorites(enabled);
  };

  const handlePageChange = (newPage: number) => {
    setCurrentPage(newPage);
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleFavoriteChange = (pokemonId: number, isFavorite: boolean) => {
    // Refresh list if we're viewing only favorites
    if (onlyFavorites) {
      fetchPokemon();
    } else {
      // Update the card in current view
      setPokemon((prev) =>
        prev.map((p) =>
          p.id === pokemonId ? { ...p, is_favorite: isFavorite } : p
        )
      );
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
      {/* Header */}
      <div className="max-w-7xl mx-auto mb-8">
        <div className="flex items-center gap-3 mb-2">
          <svg
            className="w-8 h-8 text-blue-600 dark:text-blue-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h1 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
            Pokédex
          </h1>
        </div>
        <p className="text-gray-600 dark:text-gray-400 text-lg">
          Explora y colecciona Pokémon
        </p>
      </div>

      <div className="max-w-7xl mx-auto">
        {/* Filters */}
        <PokemonFilters
          onSearchChange={handleSearchChange}
          onTypeChange={handleTypeChange}
          onFavoritesToggle={handleFavoritesToggle}
          isLoggedIn={isAuthenticated}
          loading={isLoading}
        />

        {/* Results Info */}
        <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
          {onlyFavorites && isAuthenticated ? (
            <span>Mostrando tus Pokémon favoritos</span>
          ) : (
            <span>
              {pokemon.length > 0 && `Mostrando ${pokemon.length} Pokémon`}
            </span>
          )}
        </div>

        {/* Grid */}
        <PokemonGrid
          pokemon={pokemon}
          isLoading={isLoading}
          isEmpty={pokemon.length === 0 && !isLoading}
          onFavoriteChange={handleFavoriteChange}
          isLoggedIn={isAuthenticated}
          currentPage={currentPage}
          totalPages={totalPages}
          onPageChange={handlePageChange}
        />
      </div>

      {/* Additional Info */}
      <div className="max-w-7xl mx-auto mt-16 pt-8 border-t border-gray-200 dark:border-gray-800">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="text-center">
            <div className="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">
              {pokemon.length > 0 ? pokemon.length : '∞'}
            </div>
            <p className="text-gray-600 dark:text-gray-400">
              Pokémon en esta página
            </p>
          </div>

          <div className="text-center">
            <div className="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">
              {selectedType || 'Todos'}
            </div>
            <p className="text-gray-600 dark:text-gray-400">
              Filtro de tipo
            </p>
          </div>

          <div className="text-center">
            <div className="text-3xl font-bold text-red-600 dark:text-red-400 mb-2">
              {onlyFavorites ? '❤️' : '🔍'}
            </div>
            <p className="text-gray-600 dark:text-gray-400">
              {onlyFavorites ? 'Solo favoritos' : 'Todos los Pokémon'}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
