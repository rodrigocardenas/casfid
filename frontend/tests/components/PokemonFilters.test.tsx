import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { PokemonFilters } from '@/components/PokemonFilters';

// Mock Next.js Image
vi.mock('next/image', () => ({
  default: (props: any) => <img {...props} />,
}));

const mockTypes = [
  { id: 1, name: 'electric' },
  { id: 2, name: 'fire' },
  { id: 3, name: 'water' },
];

describe('PokemonFilters Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders search input', () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
      />
    );

    const searchInput = screen.getByPlaceholderText(/search pokemon/i);
    expect(searchInput).toBeInTheDocument();
  });

  it('renders all type filter buttons', () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
      />
    );

    expect(screen.getByText('Electric')).toBeInTheDocument();
    expect(screen.getByText('Fire')).toBeInTheDocument();
    expect(screen.getByText('Water')).toBeInTheDocument();
  });

  it('calls onSearch when typing in search input', async () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
      />
    );

    const searchInput = screen.getByPlaceholderText(/search pokemon/i);
    await user.type(searchInput, 'pika');

    await waitFor(() => {
      expect(mockOnSearch).toHaveBeenCalledWith('pika');
    });
  });

  it('calls onFilterType when clicking type button', async () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
      />
    );

    const electricButton = screen.getByRole('button', { name: /electric/i });
    await user.click(electricButton);

    await waitFor(() => {
      expect(mockOnFilterType).toHaveBeenCalledWith('electric');
    });
  });

  it('highlights selected type filter', async () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
        selectedType="electric"
      />
    );

    const electricButton = screen.getByRole('button', { name: /electric/i });
    expect(electricButton).toHaveClass('bg-blue-600');
  });

  it('clears search when clear button is clicked', async () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();
    const user = userEvent.setup();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
      />
    );

    const searchInput = screen.getByPlaceholderText(/search pokemon/i) as HTMLInputElement;
    await user.type(searchInput, 'pika');

    expect(searchInput.value).toBe('pika');

    const clearButton = screen.getByRole('button', { name: /clear/i });
    await user.click(clearButton);

    expect(searchInput.value).toBe('');
  });

  it('handles empty type list gracefully', () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={[]}
      />
    );

    const searchInput = screen.getByPlaceholderText(/search pokemon/i);
    expect(searchInput).toBeInTheDocument();
  });

  it('supports dark mode classes', () => {
    const mockOnSearch = vi.fn();
    const mockOnFilterType = vi.fn();

    render(
      <PokemonFilters
        onSearch={mockOnSearch}
        onFilterType={mockOnFilterType}
        types={mockTypes}
      />
    );

    const container = screen.getByPlaceholderText(/search pokemon/i).closest('div');
    expect(container).toHaveClass('dark:bg-gray-800');
  });
});
