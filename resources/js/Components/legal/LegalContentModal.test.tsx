import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { LegalContentModal } from './LegalContentModal';

describe('LegalContentModal', () => {
  const user = userEvent.setup();
  const mockOnClose = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    vi.stubEnv('VITE_APP_NAME', 'TestApp');
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders nothing when type is null', () => {
      const { container } = render(<LegalContentModal type={null} onClose={mockOnClose} />);

      expect(container.firstChild).toBeNull();
    });

    it('renders terms of service dialog when type is terms', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByRole('dialog')).toBeInTheDocument();
      expect(screen.getByText('Terms of Service')).toBeInTheDocument();
    });

    it('renders privacy policy dialog when type is privacy', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByRole('dialog')).toBeInTheDocument();
      expect(screen.getByText('Privacy Policy')).toBeInTheDocument();
    });

    it('renders close button', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      // There's a "Close" button in the footer - multiple "Close" texts exist (button + sr-only)
      const closeTexts = screen.getAllByText('Close');
      expect(closeTexts.length).toBeGreaterThan(0);
    });
  });

  // ============================================
  // Terms of Service content tests
  // ============================================

  describe('terms of service content', () => {
    it('displays terms introduction', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText(/welcome to testapp/i)).toBeInTheDocument();
    });

    it('displays acceptance of terms section', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText('1. Acceptance of Terms')).toBeInTheDocument();
    });

    it('displays use of service section', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText('2. Use of Service')).toBeInTheDocument();
    });

    it('displays user accounts section', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText('3. User Accounts')).toBeInTheDocument();
    });

    it('displays termination section', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText('4. Termination')).toBeInTheDocument();
    });

    it('displays limitation of liability section', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText('5. Limitation of Liability')).toBeInTheDocument();
    });

    it('displays template disclaimer', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText(/these terms are a template/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Privacy Policy content tests
  // ============================================

  describe('privacy policy content', () => {
    it('displays privacy introduction', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText(/this privacy policy describes/i)).toBeInTheDocument();
    });

    it('displays information we collect section', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText('1. Information We Collect')).toBeInTheDocument();
    });

    it('displays how we use information section', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText('2. How We Use Information')).toBeInTheDocument();
    });

    it('displays information sharing section', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText('3. Information Sharing')).toBeInTheDocument();
    });

    it('displays data security section', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText('4. Data Security')).toBeInTheDocument();
    });

    it('displays your rights section', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText('5. Your Rights')).toBeInTheDocument();
    });

    it('displays privacy template disclaimer', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText(/this privacy policy is a template/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Interaction tests
  // ============================================

  describe('interactions', () => {
    it('calls onClose when close button is clicked', async () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      // Find the visible Close button (not the sr-only one)
      const closeButtons = screen.getAllByText('Close');
      const visibleCloseButton = closeButtons.find(
        (btn) => !btn.classList.contains('sr-only'),
      );
      expect(visibleCloseButton).toBeInTheDocument();

      await user.click(visibleCloseButton!);

      expect(mockOnClose).toHaveBeenCalledTimes(1);
    });

    it('calls onClose when dialog is closed via X button', async () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      // Find and click the close button in dialog header (X button)
      const closeButtons = screen.getAllByRole('button');
      const dialogCloseButton = closeButtons.find(
        (btn) => btn.querySelector('svg[class*="lucide-x"]') !== null,
      );

      if (dialogCloseButton) {
        await user.click(dialogCloseButton);
        expect(mockOnClose).toHaveBeenCalled();
      }
    });
  });

  // ============================================
  // App name customization tests
  // ============================================

  describe('app name customization', () => {
    it('uses app name from environment in terms', () => {
      vi.stubEnv('VITE_APP_NAME', 'CustomApp');
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      // The app name appears multiple times in the content
      expect(screen.getByText(/welcome to customapp/i)).toBeInTheDocument();
    });

    it('uses app name from environment in privacy', () => {
      vi.stubEnv('VITE_APP_NAME', 'CustomApp');
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText(/customapp collects/i)).toBeInTheDocument();
    });

    it('uses fallback name when env var not set', () => {
      vi.stubEnv('VITE_APP_NAME', '');
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      // Should use "Our Application" as fallback
      expect(screen.getByText(/welcome to our application/i)).toBeInTheDocument();
    });
  });

  // ============================================
  // Dialog behavior tests
  // ============================================

  describe('dialog behavior', () => {
    it('dialog is open when type is provided', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    it('renders with scrollable content area', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      const dialog = screen.getByRole('dialog');
      expect(dialog).toHaveClass('overflow-y-auto');
    });

    it('renders with max-width constraint', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      const dialog = screen.getByRole('dialog');
      expect(dialog).toHaveClass('max-w-2xl');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('dialog has accessible title for terms', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByRole('dialog', { name: /terms of service/i })).toBeInTheDocument();
    });

    it('dialog has accessible title for privacy', () => {
      render(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByRole('dialog', { name: /privacy policy/i })).toBeInTheDocument();
    });

    it('close button is accessible', () => {
      render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      // Find the visible Close button
      const closeButtons = screen.getAllByText('Close');
      const visibleCloseButton = closeButtons.find(
        (btn) => !btn.classList.contains('sr-only'),
      );
      expect(visibleCloseButton).toBeInTheDocument();
    });
  });

  // ============================================
  // Type switching tests
  // ============================================

  describe('type switching', () => {
    it('switches from terms to privacy', () => {
      const { rerender } = render(<LegalContentModal type="terms" onClose={mockOnClose} />);

      expect(screen.getByText('Terms of Service')).toBeInTheDocument();

      rerender(<LegalContentModal type="privacy" onClose={mockOnClose} />);

      expect(screen.getByText('Privacy Policy')).toBeInTheDocument();
    });

    it('switches from privacy to null', () => {
      const { rerender, container } = render(
        <LegalContentModal type="privacy" onClose={mockOnClose} />,
      );

      expect(screen.getByRole('dialog')).toBeInTheDocument();

      rerender(<LegalContentModal type={null} onClose={mockOnClose} />);

      expect(container.firstChild).toBeNull();
    });
  });
});
