import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';

import { Logo, TextLogo } from './Logo';

describe('Logo', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders SVG element', () => {
      const { container } = render(<Logo />);

      expect(container.querySelector('svg')).toBeInTheDocument();
    });

    it('renders with default size of 24', () => {
      const { container } = render(<Logo />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('width', '24');
      expect(svg).toHaveAttribute('height', '24');
    });

    it('renders with custom size', () => {
      const { container } = render(<Logo size={48} />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('width', '48');
      expect(svg).toHaveAttribute('height', '48');
    });

    it('renders with small size', () => {
      const { container } = render(<Logo size={16} />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('width', '16');
      expect(svg).toHaveAttribute('height', '16');
    });

    it('renders with large size', () => {
      const { container } = render(<Logo size={100} />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('width', '100');
      expect(svg).toHaveAttribute('height', '100');
    });
  });

  // ============================================
  // SVG attributes tests
  // ============================================

  describe('SVG attributes', () => {
    it('has correct viewBox', () => {
      const { container } = render(<Logo />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('viewBox', '0 0 24 24');
    });

    it('has fill set to none', () => {
      const { container } = render(<Logo />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('fill', 'none');
    });

    it('has correct xmlns', () => {
      const { container } = render(<Logo />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveAttribute('xmlns', 'http://www.w3.org/2000/svg');
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default text-primary class', () => {
      const { container } = render(<Logo />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveClass('text-primary');
    });

    it('applies custom className', () => {
      const { container } = render(<Logo className="custom-class" />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveClass('custom-class');
    });

    it('merges custom className with default', () => {
      const { container } = render(<Logo className="w-10 h-10" />);

      const svg = container.querySelector('svg');
      expect(svg).toHaveClass('text-primary');
      expect(svg).toHaveClass('w-10');
      expect(svg).toHaveClass('h-10');
    });
  });

  // ============================================
  // SVG content tests
  // ============================================

  describe('SVG content', () => {
    it('contains rect element for background', () => {
      const { container } = render(<Logo />);

      const rect = container.querySelector('rect');
      expect(rect).toBeInTheDocument();
    });

    it('contains path element for checkmark', () => {
      const { container } = render(<Logo />);

      const path = container.querySelector('path');
      expect(path).toBeInTheDocument();
    });

    it('rect has correct attributes', () => {
      const { container } = render(<Logo />);

      const rect = container.querySelector('rect');
      expect(rect).toHaveAttribute('x', '3');
      expect(rect).toHaveAttribute('y', '3');
      expect(rect).toHaveAttribute('width', '18');
      expect(rect).toHaveAttribute('height', '18');
      expect(rect).toHaveAttribute('rx', '4');
    });

    it('rect uses currentColor', () => {
      const { container } = render(<Logo />);

      const rect = container.querySelector('rect');
      expect(rect).toHaveAttribute('fill', 'currentColor');
      expect(rect).toHaveAttribute('stroke', 'currentColor');
    });

    it('path uses currentColor', () => {
      const { container } = render(<Logo />);

      const path = container.querySelector('path');
      expect(path).toHaveAttribute('stroke', 'currentColor');
    });
  });
});

describe('TextLogo', () => {
  beforeEach(() => {
    vi.stubEnv('VITE_APP_NAME', 'TestApp');
  });

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders span element', () => {
      render(<TextLogo />);

      expect(screen.getByText('TestApp').tagName).toBe('SPAN');
    });

    it('displays app name from environment', () => {
      vi.stubEnv('VITE_APP_NAME', 'MyApplication');
      render(<TextLogo />);

      expect(screen.getByText('MyApplication')).toBeInTheDocument();
    });

    it('uses fallback name when env var not set', () => {
      vi.stubEnv('VITE_APP_NAME', '');
      render(<TextLogo />);

      expect(screen.getByText('App')).toBeInTheDocument();
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default styling classes', () => {
      render(<TextLogo />);

      const span = screen.getByText('TestApp');
      expect(span).toHaveClass('font-bold');
      expect(span).toHaveClass('text-xl');
      expect(span).toHaveClass('tracking-tight');
    });

    it('applies custom className', () => {
      render(<TextLogo className="custom-class" />);

      expect(screen.getByText('TestApp')).toHaveClass('custom-class');
    });

    it('merges custom className with defaults', () => {
      render(<TextLogo className="text-primary" />);

      const span = screen.getByText('TestApp');
      expect(span).toHaveClass('font-bold');
      expect(span).toHaveClass('text-primary');
    });

    it('allows overriding default classes', () => {
      render(<TextLogo className="text-2xl" />);

      const span = screen.getByText('TestApp');
      expect(span).toHaveClass('text-2xl');
    });
  });

  // ============================================
  // Different app names tests
  // ============================================

  describe('different app names', () => {
    it('renders short name', () => {
      vi.stubEnv('VITE_APP_NAME', 'ABC');
      render(<TextLogo />);

      expect(screen.getByText('ABC')).toBeInTheDocument();
    });

    it('renders long name', () => {
      vi.stubEnv('VITE_APP_NAME', 'Very Long Application Name');
      render(<TextLogo />);

      expect(screen.getByText('Very Long Application Name')).toBeInTheDocument();
    });

    it('renders name with special characters', () => {
      vi.stubEnv('VITE_APP_NAME', 'App & Co.');
      render(<TextLogo />);

      expect(screen.getByText('App & Co.')).toBeInTheDocument();
    });

    it('renders numeric name', () => {
      vi.stubEnv('VITE_APP_NAME', '123App');
      render(<TextLogo />);

      expect(screen.getByText('123App')).toBeInTheDocument();
    });
  });
});
