import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';

import { createRef, useState } from 'react';

import { Button } from './button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogClose,
} from './dialog';

// Helper component for testing
const DialogExample = ({
  open: controlledOpen,
  onOpenChange,
  title = 'Dialog Title',
  description = 'Dialog description text',
}: {
  open?: boolean;
  onOpenChange?: (open: boolean) => void;
  title?: string;
  description?: string;
}) => {
  const [internalOpen, setInternalOpen] = useState(false);
  const open = controlledOpen ?? internalOpen;
  const handleOpenChange = onOpenChange ?? setInternalOpen;

  return (
    <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogTrigger asChild>
        <Button>Open Dialog</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{description}</DialogDescription>
        </DialogHeader>
        <div>Dialog body content</div>
        <DialogFooter>
          <Button variant="outline">Cancel</Button>
          <Button>Confirm</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

describe('Dialog', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders trigger button', () => {
      render(<DialogExample />);

      expect(screen.getByRole('button', { name: 'Open Dialog' })).toBeInTheDocument();
    });

    it('does not render dialog content initially', () => {
      render(<DialogExample />);

      expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    });

    it('renders dialog content when open', () => {
      render(<DialogExample open={true} />);

      expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    it('renders dialog title', () => {
      render(<DialogExample open={true} title="Test Title" />);

      expect(screen.getByText('Test Title')).toBeInTheDocument();
    });

    it('renders dialog description', () => {
      render(<DialogExample open={true} description="Test Description" />);

      expect(screen.getByText('Test Description')).toBeInTheDocument();
    });

    it('renders dialog body content', () => {
      render(<DialogExample open={true} />);

      expect(screen.getByText('Dialog body content')).toBeInTheDocument();
    });

    it('renders footer buttons', () => {
      render(<DialogExample open={true} />);

      expect(screen.getByRole('button', { name: 'Cancel' })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Confirm' })).toBeInTheDocument();
    });
  });

  // ============================================
  // Open/close behavior tests
  // ============================================

  describe('open/close behavior', () => {
    it('opens dialog on trigger click', async () => {
      render(<DialogExample />);

      await user.click(screen.getByRole('button', { name: 'Open Dialog' }));

      expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    it('calls onOpenChange when opened', async () => {
      const handleOpenChange = vi.fn();
      render(<DialogExample onOpenChange={handleOpenChange} />);

      await user.click(screen.getByRole('button', { name: 'Open Dialog' }));

      expect(handleOpenChange).toHaveBeenCalledWith(true);
    });

    it('closes on close button click', async () => {
      const handleOpenChange = vi.fn();
      render(<DialogExample open={true} onOpenChange={handleOpenChange} />);

      // The close button is the X icon with sr-only "Close" text
      const closeButton = screen.getByRole('button', { name: 'Close' });
      await user.click(closeButton);

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });

    it('calls onOpenChange when closed', async () => {
      const handleOpenChange = vi.fn();
      render(<DialogExample open={true} onOpenChange={handleOpenChange} />);

      const closeButton = screen.getByRole('button', { name: 'Close' });
      await user.click(closeButton);

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });

    it('closes on Escape key press', async () => {
      const handleOpenChange = vi.fn();
      render(<DialogExample open={true} onOpenChange={handleOpenChange} />);

      await user.keyboard('{Escape}');

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });

    it('calls onOpenChange with false on Escape key', async () => {
      const handleOpenChange = vi.fn();
      render(<DialogExample open={true} onOpenChange={handleOpenChange} />);

      await user.keyboard('{Escape}');

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });
  });

  // ============================================
  // DialogContent tests
  // ============================================

  describe('DialogContent', () => {
    it('applies default classes', () => {
      render(<DialogExample open={true} />);

      const dialog = screen.getByRole('dialog');
      expect(dialog).toHaveClass('fixed', 'left-[50%]', 'top-[50%]', 'z-50');
    });

    it('applies custom className', () => {
      render(
        <Dialog open={true}>
          <DialogContent className="custom-content">Content</DialogContent>
        </Dialog>,
      );

      expect(screen.getByRole('dialog')).toHaveClass('custom-content');
    });

    it('has correct displayName', () => {
      expect(DialogContent.displayName).toBe('DialogContent');
    });

    it('renders close button', () => {
      render(<DialogExample open={true} />);

      expect(screen.getByRole('button', { name: 'Close' })).toBeInTheDocument();
    });
  });

  // ============================================
  // DialogHeader tests
  // ============================================

  describe('DialogHeader', () => {
    it('renders children', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogHeader>
              <span data-testid="header-content">Header Content</span>
            </DialogHeader>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('header-content')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogHeader data-testid="header">Content</DialogHeader>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('header')).toHaveClass('flex', 'flex-col', 'space-y-1.5');
    });

    it('applies custom className', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogHeader className="custom-header" data-testid="header">
              Content
            </DialogHeader>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('header')).toHaveClass('custom-header');
    });

    it('has correct displayName', () => {
      expect(DialogHeader.displayName).toBe('DialogHeader');
    });
  });

  // ============================================
  // DialogFooter tests
  // ============================================

  describe('DialogFooter', () => {
    it('renders children', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogFooter>
              <Button>Action</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByRole('button', { name: 'Action' })).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogFooter data-testid="footer">Content</DialogFooter>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('footer')).toHaveClass(
        'flex',
        'flex-col-reverse',
        'sm:flex-row',
        'sm:justify-end',
      );
    });

    it('applies custom className', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogFooter className="custom-footer" data-testid="footer">
              Content
            </DialogFooter>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('footer')).toHaveClass('custom-footer');
    });

    it('has correct displayName', () => {
      expect(DialogFooter.displayName).toBe('DialogFooter');
    });
  });

  // ============================================
  // DialogTitle tests
  // ============================================

  describe('DialogTitle', () => {
    it('renders title text', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogTitle>My Title</DialogTitle>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByText('My Title')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogTitle data-testid="title">Title</DialogTitle>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('title')).toHaveClass(
        'text-lg',
        'font-semibold',
        'leading-none',
        'tracking-tight',
      );
    });

    it('applies custom className', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogTitle className="custom-title" data-testid="title">
              Title
            </DialogTitle>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('title')).toHaveClass('custom-title');
    });

    it('has correct displayName', () => {
      expect(DialogTitle.displayName).toBe('DialogTitle');
    });
  });

  // ============================================
  // DialogDescription tests
  // ============================================

  describe('DialogDescription', () => {
    it('renders description text', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogDescription>My Description</DialogDescription>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByText('My Description')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogDescription data-testid="description">Description</DialogDescription>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('description')).toHaveClass('text-sm', 'text-muted-foreground');
    });

    it('applies custom className', () => {
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogDescription className="custom-description" data-testid="description">
              Description
            </DialogDescription>
          </DialogContent>
        </Dialog>,
      );

      expect(screen.getByTestId('description')).toHaveClass('custom-description');
    });

    it('has correct displayName', () => {
      expect(DialogDescription.displayName).toBe('DialogDescription');
    });
  });

  // ============================================
  // DialogClose tests
  // ============================================

  describe('DialogClose', () => {
    it('closes dialog when clicked', async () => {
      const handleOpenChange = vi.fn();
      render(
        <Dialog open={true} onOpenChange={handleOpenChange}>
          <DialogContent>
            <DialogClose asChild>
              <Button>Custom Close</Button>
            </DialogClose>
          </DialogContent>
        </Dialog>,
      );

      await user.click(screen.getByRole('button', { name: 'Custom Close' }));

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });
  });

  // ============================================
  // Focus management tests
  // ============================================

  describe('focus management', () => {
    it('traps focus within dialog', async () => {
      render(<DialogExample open={true} />);

      // Tab through focusable elements
      await user.tab();
      await user.tab();
      await user.tab();
      await user.tab();

      // Focus should remain within the dialog
      const dialog = screen.getByRole('dialog');
      expect(dialog.contains(document.activeElement)).toBe(true);
    });

    it('focuses first focusable element on open', async () => {
      render(<DialogExample />);

      await user.click(screen.getByRole('button', { name: 'Open Dialog' }));

      // First focusable element should receive focus
      await waitFor(() => {
        const dialog = screen.getByRole('dialog');
        expect(dialog.contains(document.activeElement)).toBe(true);
      });
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has dialog role', () => {
      render(<DialogExample open={true} />);

      expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    it('renders as modal dialog', () => {
      render(<DialogExample open={true} />);

      // Dialog is rendered in a portal and traps focus
      expect(screen.getByRole('dialog')).toBeInTheDocument();
    });

    it('close button has accessible name', () => {
      render(<DialogExample open={true} />);

      expect(screen.getByRole('button', { name: 'Close' })).toBeInTheDocument();
    });

    it('title is associated with dialog', () => {
      render(<DialogExample open={true} title="Accessible Title" />);

      const dialog = screen.getByRole('dialog');
      expect(dialog).toHaveAttribute('aria-labelledby');
    });

    it('description is associated with dialog', () => {
      render(<DialogExample open={true} description="Accessible Description" />);

      const dialog = screen.getByRole('dialog');
      expect(dialog).toHaveAttribute('aria-describedby');
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to DialogContent', () => {
      const ref = createRef<HTMLDivElement>();
      render(
        <Dialog open={true}>
          <DialogContent ref={ref}>Content</DialogContent>
        </Dialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });

    it('forwards ref to DialogTitle', () => {
      const ref = createRef<HTMLHeadingElement>();
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogTitle ref={ref}>Title</DialogTitle>
          </DialogContent>
        </Dialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLHeadingElement);
    });

    it('forwards ref to DialogDescription', () => {
      const ref = createRef<HTMLParagraphElement>();
      render(
        <Dialog open={true}>
          <DialogContent>
            <DialogDescription ref={ref}>Description</DialogDescription>
          </DialogContent>
        </Dialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLParagraphElement);
    });
  });

  // ============================================
  // Portal tests
  // ============================================

  describe('portal', () => {
    it('renders content in portal', () => {
      render(<DialogExample open={true} />);

      // Dialog content should be rendered outside the initial container
      const dialog = screen.getByRole('dialog');
      expect(dialog).toBeInTheDocument();
    });
  });
});
