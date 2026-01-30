/**
 * Pokemon API Client Library
 * Handles all Pokemon-related API calls
 */

import { apiClient } from './api';

// Types
export interface PokemonType {
  id: number;
  name: string;
}

export interface Pokemon {
  id: number;
  name: string;
  image: string;
  types: PokemonType[];
  height?: number;
  weight?: number;
  description?: string;
  is_favorite?: boolean;
}

export interface PokemonListResponse {
  data: Pokemon[];
  pagination?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
  meta?: {
    total: number;
    count: number;
  };
}

export interface FilterOptions {
  search?: string;
  type?: string;
  onlyFavorites?: boolean;
  page?: number;
  perPage?: number;
}

/**
 * Fetch Pokemon list from API
 * @param filters - Filter options (search, type, pagination)
 * @returns Pokemon list with pagination
 */
export const getPokemonList = async (
  filters?: FilterOptions
): Promise<PokemonListResponse> => {
  try {
    const params = new URLSearchParams();

    if (filters?.search) {
      params.append('search', filters.search);
    }

    if (filters?.type) {
      params.append('type', filters.type);
    }

    if (filters?.onlyFavorites) {
      params.append('favorites_only', 'true');
    }

    if (filters?.page) {
      params.append('page', filters.page.toString());
    }

    if (filters?.perPage) {
      params.append('per_page', filters.perPage.toString());
    }

    const queryString = params.toString();
    const url = queryString ? `/pokemon?${queryString}` : '/pokemon';

    const response = await apiClient.get<PokemonListResponse>(url);
    return response.data;
  } catch (error) {
    console.error('Error fetching Pokemon list:', error);
    throw error;
  }
};

/**
 * Search Pokemon by name
 * @param query - Search query
 * @returns Filtered Pokemon list
 */
export const searchPokemon = async (
  query: string
): Promise<PokemonListResponse> => {
  return getPokemonList({ search: query });
};

/**
 * Filter Pokemon by type
 * @param type - Pokemon type name
 * @returns Filtered Pokemon list
 */
export const filterByType = async (
  type: string
): Promise<PokemonListResponse> => {
  return getPokemonList({ type });
};

/**
 * Get all Pokemon types available
 * @returns List of types
 */
export const getPokemonTypes = async (): Promise<PokemonType[]> => {
  try {
    const response = await apiClient.get<{ data: PokemonType[] }>(
      '/pokemon/types'
    );
    return response.data.data || [];
  } catch (error) {
    console.error('Error fetching Pokemon types:', error);
    return [];
  }
};

/**
 * Get single Pokemon by ID
 * @param id - Pokemon ID
 * @returns Pokemon details
 */
export const getPokemonById = async (id: number): Promise<Pokemon> => {
  try {
    const response = await apiClient.get<{ data: Pokemon }>(`/pokemon/${id}`);
    return response.data.data;
  } catch (error) {
    console.error(`Error fetching Pokemon ${id}:`, error);
    throw error;
  }
};

/**
 * Get user's favorite Pokemon
 * @param filters - Filter options for pagination
 * @returns User's favorite Pokemon list
 */
export const getFavoritePokemon = async (
  filters?: FilterOptions
): Promise<PokemonListResponse> => {
  try {
    const params = new URLSearchParams();

    if (filters?.page) {
      params.append('page', filters.page.toString());
    }

    if (filters?.perPage) {
      params.append('per_page', filters.perPage.toString());
    }

    const queryString = params.toString();
    const url = queryString ? `/favorites?${queryString}` : '/favorites';

    const response = await apiClient.get<PokemonListResponse>(url);
    return response.data;
  } catch (error) {
    console.error('Error fetching favorite Pokemon:', error);
    throw error;
  }
};

/**
 * Add Pokemon to favorites
 * @param pokemonId - Pokemon ID to add
 * @returns Success response
 */
export const addToFavorites = async (pokemonId: number): Promise<void> => {
  try {
    await apiClient.post('/favorites', { pokemon_id: pokemonId });
  } catch (error) {
    console.error(`Error adding Pokemon ${pokemonId} to favorites:`, error);
    throw error;
  }
};

/**
 * Remove Pokemon from favorites
 * @param pokemonId - Pokemon ID to remove
 * @returns Success response
 */
export const removeFromFavorites = async (pokemonId: number): Promise<void> => {
  try {
    await apiClient.delete(`/favorites/${pokemonId}`);
  } catch (error) {
    console.error(`Error removing Pokemon ${pokemonId} from favorites:`, error);
    throw error;
  }
};

/**
 * Toggle favorite status for a Pokemon
 * @param pokemonId - Pokemon ID
 * @param isFavorite - Current favorite status
 */
export const toggleFavorite = async (
  pokemonId: number,
  isFavorite: boolean
): Promise<void> => {
  if (isFavorite) {
    await removeFromFavorites(pokemonId);
  } else {
    await addToFavorites(pokemonId);
  }
};

/**
 * Search with combined filters (name + type + favorites)
 * @param options - All filter options
 * @returns Filtered Pokemon list
 */
export const searchWithFilters = async (
  options: FilterOptions
): Promise<PokemonListResponse> => {
  return getPokemonList(options);
};

/**
 * Get Pokemon type color (for UI display)
 * @param type - Pokemon type name
 * @returns CSS color or class name
 */
export const getTypeColor = (type: string): string => {
  const typeColors: Record<string, string> = {
    normal: '#A8A878',
    fire: '#F08030',
    water: '#6890F0',
    electric: '#F8D030',
    grass: '#78C850',
    ice: '#98D8D8',
    fighting: '#C03028',
    poison: '#A040A0',
    ground: '#E0C068',
    flying: '#A890F0',
    psychic: '#F85888',
    bug: '#A8B820',
    rock: '#B8A038',
    ghost: '#705898',
    dragon: '#7038F8',
    dark: '#705848',
    steel: '#B8B8D0',
    fairy: '#EE99AC',
  };

  return typeColors[type.toLowerCase()] || '#666666';
};

/**
 * Format Pokemon name (capitalize)
 * @param name - Raw Pokemon name
 * @returns Formatted name
 */
export const formatPokemonName = (name: string): string => {
  return name
    .split('-')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join('-');
};

/**
 * Get type badge text (for display)
 * @param type - Type name
 * @returns Formatted type name
 */
export const formatTypeName = (type: string): string => {
  return type.charAt(0).toUpperCase() + type.slice(1);
};
