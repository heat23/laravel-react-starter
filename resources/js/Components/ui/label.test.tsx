import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';

import { createRef } from 'react';

import { Label } from './label';

describe('Label', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders label component', () => {
      render(<Label>Label Text</Label>);

      expect(screen.getByText('Label Text')).toBeInTheDocument();
    });

    it('renders as label element', () => {
      render(<Label>Test</Label>);

      expect(screen.getByText('Test').tagName).toBe('LABEL');
    });

    it('renders children content', () => {
      render(<Label>Email Address</Label>);

      expect(screen.getByText('Email Address')).toBeInTheDocument();
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default label styling', () => {
      render(<Label>Label</Label>);

      const label = screen.getByText('Label');
      expect(label).toHaveClass('text-sm');
      expect(label).toHaveClass('font-medium');
      expect(label).toHaveClass('leading-none');
    });

    it('applies custom className', () => {
      render(<Label className="custom-class">Label</Label>);

      expect(screen.getByText('Label')).toHaveClass('custom-class');
    });

    it('merges custom className with defaults', () => {
      render(<Label className="mb-2">Label</Label>);

      const label = screen.getByText('Label');
      expect(label).toHaveClass('text-sm');
      expect(label).toHaveClass('mb-2');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to label element', () => {
      const ref = createRef<HTMLLabelElement>();
      render(<Label ref={ref}>Label</Label>);

      expect(ref.current).toBeInstanceOf(HTMLLabelElement);
    });
  });

  // ============================================
  // HTML attributes tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes htmlFor attribute', () => {
      render(<Label htmlFor="email-input">Email</Label>);

      expect(screen.getByText('Email')).toHaveAttribute('for', 'email-input');
    });

    it('passes id attribute', () => {
      render(<Label id="email-label">Email</Label>);

      expect(screen.getByText('Email')).toHaveAttribute('id', 'email-label');
    });

    it('passes data attributes', () => {
      render(<Label data-testid="test-label">Email</Label>);

      expect(screen.getByTestId('test-label')).toBeInTheDocument();
    });

    it('passes aria attributes', () => {
      render(<Label aria-describedby="help-text">Email</Label>);

      expect(screen.getByText('Email')).toHaveAttribute('aria-describedby', 'help-text');
    });
  });

  // ============================================
  // Form integration tests
  // ============================================

  describe('form integration', () => {
    it('associates with input via htmlFor', () => {
      render(
        <>
          <Label htmlFor="username">Username</Label>
          <input id="username" />
        </>,
      );

      const label = screen.getByText('Username');
      const input = screen.getByRole('textbox');

      expect(label).toHaveAttribute('for', 'username');
      expect(input).toHaveAttribute('id', 'username');
    });

    it('works with multiple inputs', () => {
      render(
        <>
          <Label htmlFor="email">Email</Label>
          <input id="email" type="email" />
          <Label htmlFor="password">Password</Label>
          <input id="password" type="password" />
        </>,
      );

      expect(screen.getByText('Email')).toHaveAttribute('for', 'email');
      expect(screen.getByText('Password')).toHaveAttribute('for', 'password');
    });
  });

  // ============================================
  // Different content tests
  // ============================================

  describe('different content', () => {
    it('renders with required indicator', () => {
      render(
        <Label>
          Email <span className="text-red-500">*</span>
        </Label>,
      );

      expect(screen.getByText('Email')).toBeInTheDocument();
      expect(screen.getByText('*')).toBeInTheDocument();
    });

    it('renders long label text', () => {
      const longLabel = 'This is a very long label text that might wrap';
      render(<Label>{longLabel}</Label>);

      expect(screen.getByText(longLabel)).toBeInTheDocument();
    });

    it('renders with icon', () => {
      render(
        <Label>
          <svg data-testid="icon" />
          Email
        </Label>,
      );

      expect(screen.getByTestId('icon')).toBeInTheDocument();
      expect(screen.getByText(/Email/)).toBeInTheDocument();
    });
  });

  // ============================================
  // Peer disabled styling tests
  // ============================================

  describe('peer disabled styling', () => {
    it('has peer-disabled classes', () => {
      render(<Label>Label</Label>);

      const label = screen.getByText('Label');
      expect(label).toHaveClass('peer-disabled:cursor-not-allowed');
      expect(label).toHaveClass('peer-disabled:opacity-70');
    });

    it('renders with disabled peer input', () => {
      render(
        <>
          <input id="disabled-input" disabled className="peer" />
          <Label htmlFor="disabled-input">Disabled Field</Label>
        </>,
      );

      expect(screen.getByText('Disabled Field')).toBeInTheDocument();
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('uses semantic label element', () => {
      render(<Label>Accessible Label</Label>);

      expect(screen.getByText('Accessible Label').tagName).toBe('LABEL');
    });

    it('label text is readable', () => {
      render(<Label>Form Field Label</Label>);

      const label = screen.getByText('Form Field Label');
      expect(label).toBeVisible();
    });
  });

  // ============================================
  // Multiple labels tests
  // ============================================

  describe('multiple labels', () => {
    it('renders multiple labels independently', () => {
      render(
        <>
          <Label htmlFor="field1">Field 1</Label>
          <Label htmlFor="field2">Field 2</Label>
          <Label htmlFor="field3">Field 3</Label>
        </>,
      );

      expect(screen.getByText('Field 1')).toBeInTheDocument();
      expect(screen.getByText('Field 2')).toBeInTheDocument();
      expect(screen.getByText('Field 3')).toBeInTheDocument();
    });

    it('each label has unique htmlFor', () => {
      render(
        <>
          <Label htmlFor="name">Name</Label>
          <Label htmlFor="email">Email</Label>
        </>,
      );

      expect(screen.getByText('Name')).toHaveAttribute('for', 'name');
      expect(screen.getByText('Email')).toHaveAttribute('for', 'email');
    });
  });
});
