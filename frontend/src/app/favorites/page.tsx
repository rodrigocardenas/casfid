'use client';

import { useState, useEffect } from 'react';
import { ProtectedRoute } from '@/components/ProtectedRoute';
import { useAuth } from '@/hooks/useAuth';
import { useToast } from '@/hooks/useToast';
import { apiClient } from '@/lib/api';

interface PokemonFavorite {
  id: number;
  user_id: number;
  pokemon_id: number;
  pokemon_name: string;
  pokemon_type: string;
  pokedex_id: number;
  image_url: string | null;
  description: string | null;
  hp: number | null;
  attack: number | null;
  defense: number | null;
  sp_attack: number | null;
  sp_defense: number | null;
  speed: number | null;
  created_at: string;
  updated_at: string;
}

export default function FavoritesPage() {
  const { isLoading: authLoading } = useAuth();
  const { success, error: showError } = useToast();

  const [favorites, setFavorites] = useState<PokemonFavorite[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isEmpty, setIsEmpty] = useState(false);

  useEffect(() => {
    if (!authLoading) {
      fetchFavorites();
    }
  }, [authLoading]);

  const fetchFavorites = async () => {
    setIsLoading(true);
    try {
      const response = await apiClient.get<{ success: boolean; data: PokemonFavorite[] }>('/favorites');
      const favoritesList = response.data || [];
      setFavorites(favoritesList);
      setIsEmpty(favoritesList.length === 0);
    } catch (err: any) {
      showError('Error al cargar los favoritos');
      setIsEmpty(true);
    } finally {
      setIsLoading(false);
    }
  };

  const removeFavorite = async (pokedexId: number) => {
    try {
      await apiClient.delete(`/favorites/${pokedexId}`);
      setFavorites(favorites.filter((fav) => fav.pokedex_id !== pokedexId));
      success('Pokémon removido de favoritos');
    } catch (err: any) {
      showError('Error al remover el favorito');
    }
  };

  const handleLoadMore = () => {
    fetchFavorites();
  };

  return (
    <ProtectedRoute>
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-6xl mx-auto">
          {/* Header */}
          <div className="mb-8">
            <h1 className="text-4xl font-bold mb-2">Mis Pokémon Favoritos</h1>
            <p className="text-gray-600">
              Tienes {favorites.length} Pokémon guardado{favorites.length !== 1 ? 's' : ''}
            </p>
          </div>

          {/* Loading State */}
          {isLoading && (
            <div className="flex justify-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
          )}

          {/* Empty State */}
          {!isLoading && isEmpty && (
            <div className="card text-center py-12">
              <div className="text-6xl mb-4">🎮</div>
              <h2 className="text-2xl font-bold mb-2">No hay favoritos aún</h2>
              <p className="text-gray-600 mb-6">
                Comienza a explorar y añade Pokémon a tu lista de favoritos
              </p>
              <button
                onClick={handleLoadMore}
                className="btn btn-primary"
              >
                Recargar
              </button>
            </div>
          )}

          {/* Favorites Grid */}
          {!isLoading && !isEmpty && (
            <>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
                {favorites.map((pokemon) => (
                  <div key={pokemon.id} className="card hover:shadow-lg transition-shadow">
                    <div className="mb-4 aspect-square bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                      {pokemon.image_url ? (
                        <img
                          src={pokemon.image_url}
                          alt={pokemon.pokemon_name}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        <div className="text-4xl">🎮</div>
                      )}
                    </div>

                    <h3 className="text-lg font-bold mb-2 capitalize">
                      {pokemon.pokemon_name}
                    </h3>

                    <p className="text-sm text-gray-600 mb-2">
                      {pokemon.pokemon_type}
                    </p>

                    <p className="text-sm text-gray-500 mb-4">
                      ID: #{pokemon.pokedex_id}
                    </p>

                    {pokemon.description && (
                      <p className="text-xs text-gray-600 mb-3 line-clamp-2">
                        {pokemon.description}
                      </p>
                    )}

                    {pokemon.hp !== null && (
                      <div className="text-xs text-gray-600 mb-3 grid grid-cols-3 gap-1">
                        <div>HP: {pokemon.hp}</div>
                        <div>ATK: {pokemon.attack}</div>
                        <div>DEF: {pokemon.defense}</div>
                      </div>
                    )}

                    <p className="text-xs text-gray-400 mb-4">
                      Añadido: {new Date(pokemon.created_at).toLocaleDateString()}
                    </p>

                    <button
                      onClick={() => removeFavorite(pokemon.pokedex_id)}
                      className="w-full btn btn-danger"
                    >
                      Remover ✕
                    </button>
                  </div>
                ))}
              </div>

              <div className="text-center">
                <button
                  onClick={handleLoadMore}
                  className="btn btn-secondary"
                >
                  Recargar Favoritos
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </ProtectedRoute>
  );
}
