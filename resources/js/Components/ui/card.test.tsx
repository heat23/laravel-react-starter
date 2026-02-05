import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import { createRef } from 'react';

import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from './card';

describe('Card', () => {
  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders card component', () => {
      const { container } = render(<Card>Content</Card>);

      expect(container.querySelector('div')).toBeInTheDocument();
    });

    it('renders children content', () => {
      render(<Card>Card Content</Card>);

      expect(screen.getByText('Card Content')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(Card.displayName).toBe('Card');
    });
  });

  // ============================================
  // Styling tests
  // ============================================

  describe('styling', () => {
    it('has default card styling', () => {
      const { container } = render(<Card />);

      const card = container.firstChild as HTMLElement;
      expect(card).toHaveClass('rounded-lg');
      expect(card).toHaveClass('border');
      expect(card).toHaveClass('bg-card');
      expect(card).toHaveClass('shadow-sm');
    });

    it('applies custom className', () => {
      const { container } = render(<Card className="custom-class" />);

      expect(container.firstChild).toHaveClass('custom-class');
    });

    it('merges custom className with defaults', () => {
      const { container } = render(<Card className="p-4" />);

      const card = container.firstChild as HTMLElement;
      expect(card).toHaveClass('rounded-lg');
      expect(card).toHaveClass('p-4');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to div element', () => {
      const ref = createRef<HTMLDivElement>();
      render(<Card ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  // ============================================
  // HTML attributes tests
  // ============================================

  describe('HTML attributes', () => {
    it('passes data attributes', () => {
      render(<Card data-testid="test-card" />);

      expect(screen.getByTestId('test-card')).toBeInTheDocument();
    });

    it('passes id attribute', () => {
      const { container } = render(<Card id="my-card" />);

      expect(container.firstChild).toHaveAttribute('id', 'my-card');
    });
  });
});

describe('CardHeader', () => {
  describe('rendering', () => {
    it('renders card header', () => {
      render(<CardHeader>Header Content</CardHeader>);

      expect(screen.getByText('Header Content')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(CardHeader.displayName).toBe('CardHeader');
    });
  });

  describe('styling', () => {
    it('has default header styling', () => {
      const { container } = render(<CardHeader />);

      const header = container.firstChild as HTMLElement;
      expect(header).toHaveClass('flex');
      expect(header).toHaveClass('flex-col');
      expect(header).toHaveClass('p-6');
    });

    it('applies custom className', () => {
      const { container } = render(<CardHeader className="custom" />);

      expect(container.firstChild).toHaveClass('custom');
    });
  });

  describe('ref forwarding', () => {
    it('forwards ref', () => {
      const ref = createRef<HTMLDivElement>();
      render(<CardHeader ref={ref} />);

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });
});

describe('CardTitle', () => {
  describe('rendering', () => {
    it('renders as h3 element', () => {
      render(<CardTitle>Title</CardTitle>);

      expect(screen.getByText('Title').tagName).toBe('H3');
    });

    it('has correct displayName', () => {
      expect(CardTitle.displayName).toBe('CardTitle');
    });
  });

  describe('styling', () => {
    it('has title styling', () => {
      render(<CardTitle>Title</CardTitle>);

      const title = screen.getByText('Title');
      expect(title).toHaveClass('text-2xl');
      expect(title).toHaveClass('font-semibold');
    });

    it('applies custom className', () => {
      render(<CardTitle className="custom">Title</CardTitle>);

      expect(screen.getByText('Title')).toHaveClass('custom');
    });
  });

  describe('ref forwarding', () => {
    it('forwards ref', () => {
      const ref = createRef<HTMLParagraphElement>();
      render(<CardTitle ref={ref}>Title</CardTitle>);

      expect(ref.current).toBeInstanceOf(HTMLHeadingElement);
    });
  });
});

describe('CardDescription', () => {
  describe('rendering', () => {
    it('renders as p element', () => {
      render(<CardDescription>Description</CardDescription>);

      expect(screen.getByText('Description').tagName).toBe('P');
    });

    it('has correct displayName', () => {
      expect(CardDescription.displayName).toBe('CardDescription');
    });
  });

  describe('styling', () => {
    it('has description styling', () => {
      render(<CardDescription>Description</CardDescription>);

      const desc = screen.getByText('Description');
      expect(desc).toHaveClass('text-sm');
      expect(desc).toHaveClass('text-muted-foreground');
    });

    it('applies custom className', () => {
      render(<CardDescription className="custom">Description</CardDescription>);

      expect(screen.getByText('Description')).toHaveClass('custom');
    });
  });
});

describe('CardContent', () => {
  describe('rendering', () => {
    it('renders content', () => {
      render(<CardContent>Content</CardContent>);

      expect(screen.getByText('Content')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(CardContent.displayName).toBe('CardContent');
    });
  });

  describe('styling', () => {
    it('has content styling', () => {
      const { container } = render(<CardContent />);

      const content = container.firstChild as HTMLElement;
      expect(content).toHaveClass('p-6');
      expect(content).toHaveClass('pt-0');
    });

    it('applies custom className', () => {
      const { container } = render(<CardContent className="custom" />);

      expect(container.firstChild).toHaveClass('custom');
    });
  });
});

describe('CardFooter', () => {
  describe('rendering', () => {
    it('renders footer', () => {
      render(<CardFooter>Footer</CardFooter>);

      expect(screen.getByText('Footer')).toBeInTheDocument();
    });

    it('has correct displayName', () => {
      expect(CardFooter.displayName).toBe('CardFooter');
    });
  });

  describe('styling', () => {
    it('has footer styling', () => {
      const { container } = render(<CardFooter />);

      const footer = container.firstChild as HTMLElement;
      expect(footer).toHaveClass('flex');
      expect(footer).toHaveClass('items-center');
      expect(footer).toHaveClass('p-6');
      expect(footer).toHaveClass('pt-0');
    });

    it('applies custom className', () => {
      const { container } = render(<CardFooter className="custom" />);

      expect(container.firstChild).toHaveClass('custom');
    });
  });
});

describe('Card composition', () => {
  it('renders complete card with all parts', () => {
    render(
      <Card data-testid="full-card">
        <CardHeader>
          <CardTitle>Card Title</CardTitle>
          <CardDescription>Card Description</CardDescription>
        </CardHeader>
        <CardContent>Card Content</CardContent>
        <CardFooter>Card Footer</CardFooter>
      </Card>,
    );

    expect(screen.getByTestId('full-card')).toBeInTheDocument();
    expect(screen.getByText('Card Title')).toBeInTheDocument();
    expect(screen.getByText('Card Description')).toBeInTheDocument();
    expect(screen.getByText('Card Content')).toBeInTheDocument();
    expect(screen.getByText('Card Footer')).toBeInTheDocument();
  });

  it('renders card with only title and content', () => {
    render(
      <Card>
        <CardHeader>
          <CardTitle>Simple Card</CardTitle>
        </CardHeader>
        <CardContent>Simple Content</CardContent>
      </Card>,
    );

    expect(screen.getByText('Simple Card')).toBeInTheDocument();
    expect(screen.getByText('Simple Content')).toBeInTheDocument();
  });

  it('renders minimal card with just content', () => {
    render(
      <Card>
        <CardContent>Just Content</CardContent>
      </Card>,
    );

    expect(screen.getByText('Just Content')).toBeInTheDocument();
  });
});
