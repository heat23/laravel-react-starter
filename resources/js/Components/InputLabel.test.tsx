import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';

import InputLabel from './InputLabel';

describe('InputLabel', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders label with value prop', () => {
      render(<InputLabel value="Email Address" />);

      expect(screen.getByText('Email Address')).toBeInTheDocument();
    });

    it('renders label with children', () => {
      render(<InputLabel>Password</InputLabel>);

      expect(screen.getByText('Password')).toBeInTheDocument();
    });

    it('renders as label element', () => {
      render(<InputLabel value="Username" />);

      const label = screen.getByText('Username');
      expect(label.tagName).toBe('LABEL');
    });

    it('prioritizes value prop over children', () => {
      render(<InputLabel value="Value Prop">Children Text</InputLabel>);

      expect(screen.getByText('Value Prop')).toBeInTheDocument();
      expect(screen.queryByText('Children Text')).not.toBeInTheDocument();
    });

    it('renders children when value is not provided', () => {
      render(
        <InputLabel>
          <span>Complex Label</span>
        </InputLabel>,
      );

      expect(screen.getByText('Complex Label')).toBeInTheDocument();
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default styling classes', () => {
      render(<InputLabel value="Label" />);

      const label = screen.getByText('Label');
      expect(label).toHaveClass('block');
      expect(label).toHaveClass('text-sm');
      expect(label).toHaveClass('font-medium');
      expect(label).toHaveClass('text-foreground');
    });

    it('applies custom className', () => {
      render(<InputLabel value="Label" className="custom-class" />);

      expect(screen.getByText('Label')).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<InputLabel value="Label" className="mb-2" />);

      const label = screen.getByText('Label');
      expect(label).toHaveClass('text-sm');
      expect(label).toHaveClass('mb-2');
    });
  });

  // ============================================
  // HTML attributes tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes htmlFor attribute', () => {
      render(<InputLabel value="Email" htmlFor="email-input" />);

      expect(screen.getByText('Email')).toHaveAttribute('for', 'email-input');
    });

    it('passes id attribute', () => {
      render(<InputLabel value="Email" id="email-label" />);

      expect(screen.getByText('Email')).toHaveAttribute('id', 'email-label');
    });

    it('passes data attributes', () => {
      render(<InputLabel value="Email" data-testid="email-label" />);

      expect(screen.getByTestId('email-label')).toBeInTheDocument();
    });

    it('passes aria attributes', () => {
      render(<InputLabel value="Email" aria-describedby="email-help" />);

      expect(screen.getByText('Email')).toHaveAttribute('aria-describedby', 'email-help');
    });
  });

  // ============================================
  // Different content tests
  // ============================================

  describe('different content', () => {
    it('renders with required indicator as children', () => {
      render(
        <InputLabel>
          Email <span className="text-red-500">*</span>
        </InputLabel>,
      );

      expect(screen.getByText('Email')).toBeInTheDocument();
      expect(screen.getByText('*')).toBeInTheDocument();
    });

    it('renders long label text', () => {
      const longLabel = 'This is a very long label text that might wrap on smaller screens';
      render(<InputLabel value={longLabel} />);

      expect(screen.getByText(longLabel)).toBeInTheDocument();
    });

    it('renders empty string value', () => {
      const { container } = render(<InputLabel value="" />);

      const label = container.querySelector('label');
      expect(label).toBeInTheDocument();
      expect(label?.textContent).toBe('');
    });
  });

  // ============================================
  // Form integration tests
  // ============================================

  describe('form integration', () => {
    it('associates label with input via htmlFor', () => {
      render(
        <>
          <InputLabel value="Username" htmlFor="username" />
          <input id="username" />
        </>,
      );

      const label = screen.getByText('Username');
      const input = screen.getByRole('textbox');

      expect(label).toHaveAttribute('for', 'username');
      expect(input).toHaveAttribute('id', 'username');
    });

    it('associates label with input for programmatic focus', () => {
      render(
        <>
          <InputLabel value="Username" htmlFor="username" />
          <input id="username" />
        </>,
      );

      const label = screen.getByText('Username');
      const input = screen.getByRole('textbox');

      // Verify the label and input are correctly associated
      expect(label).toHaveAttribute('for', 'username');
      expect(input).toHaveAttribute('id', 'username');

      // Programmatically focus the input and verify
      input.focus();
      expect(input).toHaveFocus();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('label text is readable', () => {
      render(<InputLabel value="Form Field Label" />);

      const label = screen.getByText('Form Field Label');
      expect(label).toBeVisible();
    });

    it('uses semantic label element', () => {
      render(<InputLabel value="Semantic Label" />);

      expect(screen.getByText('Semantic Label').tagName).toBe('LABEL');
    });
  });
});
