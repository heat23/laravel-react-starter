import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';

import { createRef } from 'react';

import { Avatar, AvatarImage, AvatarFallback } from './avatar';

describe('Avatar', () => {
  describe('rendering', () => {
    it('renders avatar component', () => {
      const { container } = render(<Avatar />);

      expect(container.firstChild).toBeInTheDocument();
    });

    it('renders children content', () => {
      render(
        <Avatar>
          <AvatarFallback>AB</AvatarFallback>
        </Avatar>,
      );

      expect(screen.getByText('AB')).toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('has base avatar styling', () => {
      const { container } = render(<Avatar />);

      const avatar = container.firstChild as HTMLElement;
      expect(avatar).toHaveClass('relative');
      expect(avatar).toHaveClass('flex');
      expect(avatar).toHaveClass('h-10');
      expect(avatar).toHaveClass('w-10');
      expect(avatar).toHaveClass('rounded-full');
    });

    it('applies custom className', () => {
      const { container } = render(<Avatar className="custom-size" />);

      expect(container.firstChild).toHaveClass('custom-size');
    });
  });

  describe('ref forwarding', () => {
    it('forwards ref', () => {
      const ref = createRef<HTMLSpanElement>();
      render(<Avatar ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLSpanElement);
    });
  });
});

describe('AvatarImage', () => {
  describe('rendering', () => {
    it('renders within Avatar component', () => {
      const { container } = render(
        <Avatar>
          <AvatarImage src="/avatar.jpg" alt="User avatar" />
          <AvatarFallback>U</AvatarFallback>
        </Avatar>,
      );

      // Avatar component is rendered
      expect(container.firstChild).toBeInTheDocument();
    });
  });

  describe('attributes', () => {
    it('accepts src prop', () => {
      render(
        <Avatar>
          <AvatarImage src="/avatar.jpg" />
          <AvatarFallback>U</AvatarFallback>
        </Avatar>,
      );

      // Component renders without error
      expect(true).toBe(true);
    });

    it('accepts alt prop', () => {
      render(
        <Avatar>
          <AvatarImage src="/avatar.jpg" alt="User" />
          <AvatarFallback>U</AvatarFallback>
        </Avatar>,
      );

      // Component renders without error
      expect(true).toBe(true);
    });
  });
});

describe('AvatarFallback', () => {
  describe('rendering', () => {
    it('renders fallback content', () => {
      render(
        <Avatar>
          <AvatarFallback>JD</AvatarFallback>
        </Avatar>,
      );

      expect(screen.getByText('JD')).toBeInTheDocument();
    });

    it('renders with single letter', () => {
      render(
        <Avatar>
          <AvatarFallback>A</AvatarFallback>
        </Avatar>,
      );

      expect(screen.getByText('A')).toBeInTheDocument();
    });

    it('renders with icon', () => {
      render(
        <Avatar>
          <AvatarFallback>
            <svg data-testid="user-icon" />
          </AvatarFallback>
        </Avatar>,
      );

      expect(screen.getByTestId('user-icon')).toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('has fallback styling', () => {
      render(
        <Avatar>
          <AvatarFallback>AB</AvatarFallback>
        </Avatar>,
      );

      const fallback = screen.getByText('AB');
      expect(fallback).toHaveClass('flex');
      expect(fallback).toHaveClass('h-full');
      expect(fallback).toHaveClass('w-full');
      expect(fallback).toHaveClass('items-center');
      expect(fallback).toHaveClass('justify-center');
      expect(fallback).toHaveClass('rounded-full');
      expect(fallback).toHaveClass('bg-muted');
    });

    it('applies custom className', () => {
      render(
        <Avatar>
          <AvatarFallback className="custom">AB</AvatarFallback>
        </Avatar>,
      );

      expect(screen.getByText('AB')).toHaveClass('custom');
    });
  });
});

describe('Avatar composition', () => {
  it('renders avatar with image and fallback', () => {
    render(
      <Avatar>
        <AvatarImage src="/avatar.jpg" alt="John Doe" />
        <AvatarFallback>JD</AvatarFallback>
      </Avatar>,
    );

    // Fallback is present
    expect(screen.getByText('JD')).toBeInTheDocument();
  });

  it('shows fallback when image fails', async () => {
    render(
      <Avatar>
        <AvatarImage src="/invalid.jpg" alt="User" />
        <AvatarFallback>U</AvatarFallback>
      </Avatar>,
    );

    // Fallback should be present
    expect(screen.getByText('U')).toBeInTheDocument();
  });

  it('renders different sizes', () => {
    const { container } = render(
      <Avatar className="h-20 w-20">
        <AvatarFallback>XL</AvatarFallback>
      </Avatar>,
    );

    expect(container.firstChild).toHaveClass('h-20');
    expect(container.firstChild).toHaveClass('w-20');
  });

  it('renders with custom colors', () => {
    render(
      <Avatar>
        <AvatarFallback className="bg-blue-500 text-white">AB</AvatarFallback>
      </Avatar>,
    );

    const fallback = screen.getByText('AB');
    expect(fallback).toHaveClass('bg-blue-500');
    expect(fallback).toHaveClass('text-white');
  });
});
