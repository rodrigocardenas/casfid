import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { PokemonGrid } from '@/components/PokemonGrid';

// Mock Next.js Image
vi.mock('next/image', () => ({
  default: (props: any) => <img {...props} />,
}));

// Mock child components
vi.mock('@/components/PokemonCard', () => ({
  PokemonCard: ({ pokemon }: any) => (
    <div data-testid={`pokemon-${pokemon.id}`}>{pokemon.name}</div>
  ),
}));

const mockPokemonList = [
  {
    id: 1,
    name: 'pikachu',
    image: 'https://example.com/1.png',
    types: [{ id: 1, name: 'electric' }],
    is_favorite: false,
  },
  {
    id: 2,
    name: 'charmander',
    image: 'https://example.com/2.png',
    types: [{ id: 2, name: 'fire' }],
    is_favorite: false,
  },
  {
    id: 3,
    name: 'bulbasaur',
    image: 'https://example.com/3.png',
    types: [{ id: 3, name: 'grass' }],
    is_favorite: false,
  },
];

describe('PokemonGrid Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders pokemon cards in grid', () => {
    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={1}
        totalPages={1}
      />
    );

    expect(screen.getByTestId('pokemon-1')).toBeInTheDocument();
    expect(screen.getByTestId('pokemon-2')).toBeInTheDocument();
    expect(screen.getByTestId('pokemon-3')).toBeInTheDocument();
  });

  it('displays empty state when no pokemon', () => {
    render(
      <PokemonGrid
        pokemon={[]}
        isLoggedIn={false}
        currentPage={1}
        totalPages={0}
      />
    );

    expect(screen.getByText(/no pokemon found/i)).toBeInTheDocument();
  });

  it('shows loading skeleton when loading', () => {
    render(
      <PokemonGrid
        pokemon={[]}
        isLoggedIn={false}
        currentPage={1}
        totalPages={1}
        isLoading={true}
      />
    );

    const skeletons = screen.getAllByTestId('skeleton');
    expect(skeletons.length).toBeGreaterThan(0);
  });

  it('displays pagination controls', () => {
    const mockOnPageChange = vi.fn();

    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={1}
        totalPages={5}
        onPageChange={mockOnPageChange}
      />
    );

    expect(screen.getByRole('button', { name: /previous/i })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /next/i })).toBeInTheDocument();
  });

  it('disables previous button on first page', () => {
    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={1}
        totalPages={5}
      />
    );

    const previousButton = screen.getByRole('button', { name: /previous/i });
    expect(previousButton).toBeDisabled();
  });

  it('disables next button on last page', () => {
    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={5}
        totalPages={5}
      />
    );

    const nextButton = screen.getByRole('button', { name: /next/i });
    expect(nextButton).toBeDisabled();
  });

  it('calls onPageChange when next button clicked', async () => {
    const mockOnPageChange = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={1}
        totalPages={5}
        onPageChange={mockOnPageChange}
      />
    );

    const nextButton = screen.getByRole('button', { name: /next/i });
    await user.click(nextButton);

    expect(mockOnPageChange).toHaveBeenCalledWith(2);
  });

  it('calls onPageChange when previous button clicked', async () => {
    const mockOnPageChange = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={2}
        totalPages={5}
        onPageChange={mockOnPageChange}
      />
    );

    const previousButton = screen.getByRole('button', { name: /previous/i });
    await user.click(previousButton);

    expect(mockOnPageChange).toHaveBeenCalledWith(1);
  });

  it('displays current page information', () => {
    render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={2}
        totalPages={5}
      />
    );

    expect(screen.getByText(/page 2 of 5/i)).toBeInTheDocument();
  });

  it('applies responsive grid classes', () => {
    const { container } = render(
      <PokemonGrid
        pokemon={mockPokemonList}
        isLoggedIn={false}
        currentPage={1}
        totalPages={1}
      />
    );

    const grid = container.querySelector('.grid');
    expect(grid).toHaveClass('grid-cols-1');
    expect(grid).toHaveClass('sm:grid-cols-2');
    expect(grid).toHaveClass('lg:grid-cols-3');
  });
});
