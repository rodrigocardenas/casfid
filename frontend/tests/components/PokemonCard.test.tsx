import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { PokemonCard } from '@/components/PokemonCard';

// Mock the pokemon library
vi.mock('@/lib/pokemon', () => ({
  toggleFavorite: vi.fn(),
  getTypeColor: vi.fn((type) => '#FF0000'),
  formatPokemonName: vi.fn((name) => name.charAt(0).toUpperCase() + name.slice(1)),
  formatTypeName: vi.fn((type) => type.toUpperCase()),
}));

// Mock toast hook
vi.mock('@/hooks/useToast', () => ({
  useToast: () => ({
    showToast: vi.fn(),
  }),
}));

// Mock Next.js Image component
vi.mock('next/image', () => ({
  default: (props: any) => <img {...props} />,
}));

const mockPokemon = {
  id: 25,
  name: 'pikachu',
  image: 'https://example.com/pikachu.png',
  types: [{ id: 1, name: 'electric' }],
  is_favorite: false,
  height: 0.4,
  weight: 6,
  description: 'Electric mouse pokemon',
};

describe('PokemonCard Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders pokemon card correctly', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    expect(screen.getByText('Pikachu')).toBeInTheDocument();
    expect(screen.getByText('#025')).toBeInTheDocument();
  });

  it('shows pokemon image', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    const img = screen.getByRole('img', { name: /pikachu/i });
    expect(img).toBeInTheDocument();
    expect(img).toHaveAttribute('src', mockPokemon.image);
  });

  it('displays pokemon types', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    expect(screen.getByText('ELECTRIC')).toBeInTheDocument();
  });

  it('shows favorite button when logged in', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={true} />);

    const favoriteButton = screen.getByRole('button', {
      name: /add to favorites/i,
    });
    expect(favoriteButton).toBeInTheDocument();
  });

  it('hides favorite button when not logged in', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    const favoriteButton = screen.queryByRole('button', {
      name: /add to favorites/i,
    });
    expect(favoriteButton).not.toBeInTheDocument();
  });

  it('shows yellow star when pokemon is favorite', () => {
    const favoritePokemon = { ...mockPokemon, is_favorite: true };
    render(<PokemonCard pokemon={favoritePokemon} isLoggedIn={false} />);

    const card = screen.getByText('Pikachu').closest('div');
    expect(card).toHaveClass('border-yellow-400');
  });

  it('displays favorite badge when pokemon is favorite', () => {
    const favoritePokemon = { ...mockPokemon, is_favorite: true };
    render(<PokemonCard pokemon={favoritePokemon} isLoggedIn={false} />);

    expect(screen.getByText('⭐')).toBeInTheDocument();
  });

  it('displays height and weight stats', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    expect(screen.getByText('Altura: 0.4m')).toBeInTheDocument();
    expect(screen.getByText('Peso: 6kg')).toBeInTheDocument();
  });

  it('displays pokemon description', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={false} />);

    expect(screen.getByText('Electric mouse pokemon')).toBeInTheDocument();
  });

  it('has correct aria-label for accessibility', () => {
    render(<PokemonCard pokemon={mockPokemon} isLoggedIn={true} />);

    const button = screen.getByRole('button', {
      name: /add to favorites/i,
    });

    expect(button).toHaveAttribute('aria-label', 'Add to favorites');
  });

  it('handles height and weight missing data gracefully', () => {
    const pokemonWithoutStats = {
      ...mockPokemon,
      height: null,
      weight: null,
    };

    render(<PokemonCard pokemon={pokemonWithoutStats} isLoggedIn={false} />);

    expect(screen.queryByText(/altura/i)).not.toBeInTheDocument();
  });

  it('handles missing description gracefully', () => {
    const pokemonWithoutDescription = {
      ...mockPokemon,
      description: null,
    };

    render(<PokemonCard pokemon={pokemonWithoutDescription} isLoggedIn={false} />);

    // Component should still render without errors
    expect(screen.getByText('Pikachu')).toBeInTheDocument();
  });
});
