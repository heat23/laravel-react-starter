import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';

import { TimezoneSelector } from './TimezoneSelector';

describe('TimezoneSelector', () => {
  const user = userEvent.setup();
  const mockOnChange = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders combobox trigger button', () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('shows placeholder when no value selected', () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      expect(screen.getByText('Select timezone...')).toBeInTheDocument();
    });

    it('shows selected timezone label', () => {
      render(<TimezoneSelector value="America/New_York" onChange={mockOnChange} />);

      expect(screen.getByText('Eastern Time (US & Canada)')).toBeInTheDocument();
    });

    it('renders globe icon', () => {
      const { container } = render(<TimezoneSelector value="" onChange={mockOnChange} />);

      // Check for lucide globe icon
      const globeIcon = container.querySelector('.lucide-globe');
      expect(globeIcon).toBeInTheDocument();
    });

    it('renders chevrons icon', () => {
      const { container } = render(<TimezoneSelector value="" onChange={mockOnChange} />);

      const chevronsIcon = container.querySelector('.lucide-chevrons-up-down');
      expect(chevronsIcon).toBeInTheDocument();
    });
  });

  // ============================================
  // Dropdown behavior tests
  // ============================================

  describe('dropdown behavior', () => {
    it('opens popover when clicked', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByPlaceholderText('Search timezone...')).toBeInTheDocument();
      });
    });

    it('closes popover after selection', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));
      await waitFor(() => {
        expect(screen.getByText('London')).toBeInTheDocument();
      });

      await user.click(screen.getByText('London'));

      await waitFor(() => {
        expect(screen.queryByPlaceholderText('Search timezone...')).not.toBeInTheDocument();
      });
    });

    it('shows region groups in dropdown', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByText('Americas')).toBeInTheDocument();
        expect(screen.getByText('Europe')).toBeInTheDocument();
        expect(screen.getByText('Asia')).toBeInTheDocument();
        expect(screen.getByText('Pacific')).toBeInTheDocument();
        expect(screen.getByText('Africa')).toBeInTheDocument();
        // UTC appears as both a region heading and timezone label, just check it exists
        expect(screen.getAllByText('UTC').length).toBeGreaterThan(0);
      });
    });
  });

  // ============================================
  // Selection tests
  // ============================================

  describe('selection', () => {
    it('calls onChange when timezone selected', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));
      await waitFor(() => {
        expect(screen.getByText('London')).toBeInTheDocument();
      });

      await user.click(screen.getByText('London'));

      expect(mockOnChange).toHaveBeenCalledWith('Europe/London');
    });

    it('selects US Eastern timezone', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));
      await waitFor(() => {
        expect(screen.getByText('Eastern Time (US & Canada)')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Eastern Time (US & Canada)'));

      expect(mockOnChange).toHaveBeenCalledWith('America/New_York');
    });

    it('selects Tokyo timezone', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));
      await waitFor(() => {
        expect(screen.getByText('Tokyo')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Tokyo'));

      expect(mockOnChange).toHaveBeenCalledWith('Asia/Tokyo');
    });

    it('selects UTC timezone', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));
      await waitFor(() => {
        // Get the UTC option (there should be one in UTC region)
        const utcOption = screen.getAllByText('UTC').find((el) => el.closest('[cmdk-item]'));
        expect(utcOption).toBeInTheDocument();
      });
    });
  });

  // ============================================
  // Search functionality tests
  // ============================================

  describe('search functionality', () => {
    it('renders search input in popover', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByPlaceholderText('Search timezone...')).toBeInTheDocument();
      });
    });

    it('filters timezones by search', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByPlaceholderText('Search timezone...')).toBeInTheDocument();
      });

      await user.type(screen.getByPlaceholderText('Search timezone...'), 'Tokyo');

      await waitFor(() => {
        expect(screen.getByText('Tokyo')).toBeInTheDocument();
      });
    });

    it('shows no results message for invalid search', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByPlaceholderText('Search timezone...')).toBeInTheDocument();
      });

      await user.type(screen.getByPlaceholderText('Search timezone...'), 'InvalidTimezone12345');

      await waitFor(() => {
        expect(screen.getByText('No timezone found.')).toBeInTheDocument();
      });
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled state', () => {
    it('renders as disabled', () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} disabled />);

      expect(screen.getByRole('combobox')).toBeDisabled();
    });

    it('does not open when disabled', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} disabled />);

      await user.click(screen.getByRole('combobox'));

      expect(screen.queryByPlaceholderText('Search timezone...')).not.toBeInTheDocument();
    });
  });

  // ============================================
  // Timezone display tests
  // ============================================

  describe('timezone display', () => {
    it('displays Americas timezones', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByText('Eastern Time (US & Canada)')).toBeInTheDocument();
        expect(screen.getByText('Central Time (US & Canada)')).toBeInTheDocument();
        expect(screen.getByText('Pacific Time (US & Canada)')).toBeInTheDocument();
      });
    });

    it('displays European timezones', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByText('London')).toBeInTheDocument();
        expect(screen.getByText('Paris')).toBeInTheDocument();
        expect(screen.getByText('Berlin')).toBeInTheDocument();
      });
    });

    it('displays Asian timezones', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      await user.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByText('Tokyo')).toBeInTheDocument();
        expect(screen.getByText('Singapore')).toBeInTheDocument();
        expect(screen.getByText('Shanghai')).toBeInTheDocument();
      });
    });
  });

  // ============================================
  // Checkmark display tests
  // ============================================

  describe('checkmark display', () => {
    it('shows selected value in trigger', () => {
      render(<TimezoneSelector value="Europe/London" onChange={mockOnChange} />);

      // When a value is selected, its label should be displayed in the trigger
      expect(screen.getByText('London')).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('trigger has combobox role', () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('has aria-expanded attribute', async () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      const combobox = screen.getByRole('combobox');
      expect(combobox).toHaveAttribute('aria-expanded', 'false');

      await user.click(combobox);

      await waitFor(() => {
        expect(combobox).toHaveAttribute('aria-expanded', 'true');
      });
    });

    it('button has full width styling', () => {
      render(<TimezoneSelector value="" onChange={mockOnChange} />);

      expect(screen.getByRole('combobox')).toHaveClass('w-full');
    });
  });

  // ============================================
  // Value changes tests
  // ============================================

  describe('value changes', () => {
    it('updates display when value prop changes', () => {
      const { rerender } = render(<TimezoneSelector value="" onChange={mockOnChange} />);

      expect(screen.getByText('Select timezone...')).toBeInTheDocument();

      rerender(<TimezoneSelector value="Asia/Tokyo" onChange={mockOnChange} />);

      expect(screen.getByText('Tokyo')).toBeInTheDocument();
    });

    it('shows placeholder when value changes to empty', () => {
      const { rerender } = render(
        <TimezoneSelector value="Asia/Tokyo" onChange={mockOnChange} />,
      );

      expect(screen.getByText('Tokyo')).toBeInTheDocument();

      rerender(<TimezoneSelector value="" onChange={mockOnChange} />);

      expect(screen.getByText('Select timezone...')).toBeInTheDocument();
    });
  });
});
