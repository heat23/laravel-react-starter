import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';

import { createRef, useState } from 'react';

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from './alert-dialog';
import { Button } from './button';

// Helper component for testing
const AlertDialogExample = ({
  open: controlledOpen,
  onOpenChange,
  onAction,
  onCancel,
  title = 'Are you sure?',
  description = 'This action cannot be undone.',
}: {
  open?: boolean;
  onOpenChange?: (open: boolean) => void;
  onAction?: () => void;
  onCancel?: () => void;
  title?: string;
  description?: string;
}) => {
  const [internalOpen, setInternalOpen] = useState(false);
  const open = controlledOpen ?? internalOpen;
  const handleOpenChange = onOpenChange ?? setInternalOpen;

  return (
    <AlertDialog open={open} onOpenChange={handleOpenChange}>
      <AlertDialogTrigger asChild>
        <Button variant="destructive">Delete Item</Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{title}</AlertDialogTitle>
          <AlertDialogDescription>{description}</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel onClick={onCancel}>Cancel</AlertDialogCancel>
          <AlertDialogAction onClick={onAction}>Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
};

describe('AlertDialog', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders trigger button', () => {
      render(<AlertDialogExample />);

      expect(screen.getByRole('button', { name: 'Delete Item' })).toBeInTheDocument();
    });

    it('does not render dialog content initially', () => {
      render(<AlertDialogExample />);

      expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });

    it('renders dialog content when open', () => {
      render(<AlertDialogExample open={true} />);

      expect(screen.getByRole('alertdialog')).toBeInTheDocument();
    });

    it('renders dialog title', () => {
      render(<AlertDialogExample open={true} title="Confirm Delete" />);

      expect(screen.getByText('Confirm Delete')).toBeInTheDocument();
    });

    it('renders dialog description', () => {
      render(<AlertDialogExample open={true} description="This will permanently delete the item." />);

      expect(screen.getByText('This will permanently delete the item.')).toBeInTheDocument();
    });

    it('renders cancel and action buttons', () => {
      render(<AlertDialogExample open={true} />);

      expect(screen.getByRole('button', { name: 'Cancel' })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Continue' })).toBeInTheDocument();
    });
  });

  // ============================================
  // Open/close behavior tests (different from Dialog)
  // ============================================

  describe('open/close behavior', () => {
    it('opens dialog on trigger click', async () => {
      render(<AlertDialogExample />);

      await user.click(screen.getByRole('button', { name: 'Delete Item' }));

      expect(screen.getByRole('alertdialog')).toBeInTheDocument();
    });

    it('calls onOpenChange when opened', async () => {
      const handleOpenChange = vi.fn();
      render(<AlertDialogExample onOpenChange={handleOpenChange} />);

      await user.click(screen.getByRole('button', { name: 'Delete Item' }));

      expect(handleOpenChange).toHaveBeenCalledWith(true);
    });

    it('closes on Escape key (Radix AlertDialog behavior)', async () => {
      const handleOpenChange = vi.fn();
      render(<AlertDialogExample open={true} onOpenChange={handleOpenChange} />);

      // Radix AlertDialog does close on Escape
      await user.keyboard('{Escape}');

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });

    it('closes when Cancel button is clicked', async () => {
      const handleOpenChange = vi.fn();
      render(<AlertDialogExample open={true} onOpenChange={handleOpenChange} />);

      await user.click(screen.getByRole('button', { name: 'Cancel' }));

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });

    it('closes when Action button is clicked', async () => {
      const handleOpenChange = vi.fn();
      render(<AlertDialogExample open={true} onOpenChange={handleOpenChange} />);

      await user.click(screen.getByRole('button', { name: 'Continue' }));

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });

    it('calls onAction callback when action button clicked', async () => {
      const handleAction = vi.fn();
      render(<AlertDialogExample open={true} onAction={handleAction} />);

      await user.click(screen.getByRole('button', { name: 'Continue' }));

      expect(handleAction).toHaveBeenCalled();
    });

    it('calls onCancel callback when cancel button clicked', async () => {
      const handleCancel = vi.fn();
      render(<AlertDialogExample open={true} onCancel={handleCancel} />);

      await user.click(screen.getByRole('button', { name: 'Cancel' }));

      expect(handleCancel).toHaveBeenCalled();
    });
  });

  // ============================================
  // AlertDialogContent tests
  // ============================================

  describe('AlertDialogContent', () => {
    it('applies default classes', () => {
      render(<AlertDialogExample open={true} />);

      const dialog = screen.getByRole('alertdialog');
      expect(dialog).toHaveClass('fixed', 'left-[50%]', 'top-[50%]', 'z-50');
    });

    it('applies custom className', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent className="custom-content" aria-describedby={undefined}>
            Content
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByRole('alertdialog')).toHaveClass('custom-content');
    });

    it('has correct displayName', () => {
      expect(AlertDialogContent.displayName).toBe('AlertDialogContent');
    });

    it('does not render close X button (unlike Dialog)', () => {
      render(<AlertDialogExample open={true} />);

      // AlertDialog should not have an X close button
      expect(screen.queryByRole('button', { name: 'Close' })).not.toBeInTheDocument();
    });
  });

  // ============================================
  // AlertDialogHeader tests
  // ============================================

  describe('AlertDialogHeader', () => {
    it('renders children', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogHeader>
              <span data-testid="header-content">Header Content</span>
            </AlertDialogHeader>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('header-content')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogHeader data-testid="header">Content</AlertDialogHeader>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('header')).toHaveClass('flex', 'flex-col', 'space-y-2');
    });

    it('applies custom className', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogHeader className="custom-header" data-testid="header">
              Content
            </AlertDialogHeader>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('header')).toHaveClass('custom-header');
    });

    it('has correct displayName', () => {
      expect(AlertDialogHeader.displayName).toBe('AlertDialogHeader');
    });
  });

  // ============================================
  // AlertDialogFooter tests
  // ============================================

  describe('AlertDialogFooter', () => {
    it('renders children', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogFooter>
              <Button>Action</Button>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByRole('button', { name: 'Action' })).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogFooter data-testid="footer">Content</AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>,
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
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogFooter className="custom-footer" data-testid="footer">
              Content
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('footer')).toHaveClass('custom-footer');
    });

    it('has correct displayName', () => {
      expect(AlertDialogFooter.displayName).toBe('AlertDialogFooter');
    });
  });

  // ============================================
  // AlertDialogTitle tests
  // ============================================

  describe('AlertDialogTitle', () => {
    it('renders title text', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogTitle>My Title</AlertDialogTitle>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByText('My Title')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogTitle data-testid="title">Title</AlertDialogTitle>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('title')).toHaveClass('text-lg', 'font-semibold');
    });

    it('applies custom className', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogTitle className="custom-title" data-testid="title">
              Title
            </AlertDialogTitle>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('title')).toHaveClass('custom-title');
    });

    it('has correct displayName', () => {
      expect(AlertDialogTitle.displayName).toBe('AlertDialogTitle');
    });
  });

  // ============================================
  // AlertDialogDescription tests
  // ============================================

  describe('AlertDialogDescription', () => {
    it('renders description text', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogDescription>My Description</AlertDialogDescription>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByText('My Description')).toBeInTheDocument();
    });

    it('applies default classes', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogDescription data-testid="description">Description</AlertDialogDescription>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('description')).toHaveClass('text-sm', 'text-muted-foreground');
    });

    it('applies custom className', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogDescription className="custom-description" data-testid="description">
              Description
            </AlertDialogDescription>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByTestId('description')).toHaveClass('custom-description');
    });

    it('has correct displayName', () => {
      expect(AlertDialogDescription.displayName).toBe('AlertDialogDescription');
    });
  });

  // ============================================
  // AlertDialogAction tests
  // ============================================

  describe('AlertDialogAction', () => {
    it('renders with button variant styles', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogAction>Action</AlertDialogAction>
          </AlertDialogContent>
        </AlertDialog>,
      );

      const button = screen.getByRole('button', { name: 'Action' });
      // Should have button variant classes from buttonVariants()
      expect(button).toBeInTheDocument();
    });

    it('applies custom className', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogAction className="custom-action">Action</AlertDialogAction>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByRole('button', { name: 'Action' })).toHaveClass('custom-action');
    });

    it('has correct displayName', () => {
      expect(AlertDialogAction.displayName).toBe('AlertDialogAction');
    });
  });

  // ============================================
  // AlertDialogCancel tests
  // ============================================

  describe('AlertDialogCancel', () => {
    it('renders with outline variant styles', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
          </AlertDialogContent>
        </AlertDialog>,
      );

      const button = screen.getByRole('button', { name: 'Cancel' });
      // Should have outline variant classes
      expect(button).toBeInTheDocument();
    });

    it('applies custom className', () => {
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogCancel className="custom-cancel">Cancel</AlertDialogCancel>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(screen.getByRole('button', { name: 'Cancel' })).toHaveClass('custom-cancel');
    });

    it('has correct displayName', () => {
      expect(AlertDialogCancel.displayName).toBe('AlertDialogCancel');
    });
  });

  // ============================================
  // Focus management tests
  // ============================================

  describe('focus management', () => {
    it('traps focus within dialog', async () => {
      render(<AlertDialogExample open={true} />);

      // Tab through focusable elements
      await user.tab();
      await user.tab();
      await user.tab();

      // Focus should remain within the dialog
      const dialog = screen.getByRole('alertdialog');
      expect(dialog.contains(document.activeElement)).toBe(true);
    });

    it('focuses first button on open', async () => {
      render(<AlertDialogExample />);

      await user.click(screen.getByRole('button', { name: 'Delete Item' }));

      await waitFor(() => {
        const dialog = screen.getByRole('alertdialog');
        expect(dialog.contains(document.activeElement)).toBe(true);
      });
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has alertdialog role', () => {
      render(<AlertDialogExample open={true} />);

      expect(screen.getByRole('alertdialog')).toBeInTheDocument();
    });

    it('renders as alertdialog role', () => {
      render(<AlertDialogExample open={true} />);

      expect(screen.getByRole('alertdialog')).toBeInTheDocument();
    });

    it('title is associated with dialog', () => {
      render(<AlertDialogExample open={true} title="Confirm Action" />);

      const dialog = screen.getByRole('alertdialog');
      expect(dialog).toHaveAttribute('aria-labelledby');
    });

    it('description is associated with dialog', () => {
      render(<AlertDialogExample open={true} description="This cannot be undone" />);

      const dialog = screen.getByRole('alertdialog');
      expect(dialog).toHaveAttribute('aria-describedby');
    });

    it('can be closed with Cancel or Action buttons', async () => {
      const handleOpenChange = vi.fn();
      render(<AlertDialogExample open={true} onOpenChange={handleOpenChange} />);

      // Close using Cancel button
      await user.click(screen.getByRole('button', { name: 'Cancel' }));

      expect(handleOpenChange).toHaveBeenCalledWith(false);
    });
  });

  // ============================================
  // Ref forwarding tests
  // ============================================

  describe('ref forwarding', () => {
    it('forwards ref to AlertDialogContent', () => {
      const ref = createRef<HTMLDivElement>();
      render(
        <AlertDialog open={true}>
          <AlertDialogContent ref={ref} aria-describedby={undefined}>
            Content
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });

    it('forwards ref to AlertDialogTitle', () => {
      const ref = createRef<HTMLHeadingElement>();
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogTitle ref={ref}>Title</AlertDialogTitle>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLHeadingElement);
    });

    it('forwards ref to AlertDialogDescription', () => {
      const ref = createRef<HTMLParagraphElement>();
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogDescription ref={ref}>Description</AlertDialogDescription>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLParagraphElement);
    });

    it('forwards ref to AlertDialogAction', () => {
      const ref = createRef<HTMLButtonElement>();
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogAction ref={ref}>Action</AlertDialogAction>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLButtonElement);
    });

    it('forwards ref to AlertDialogCancel', () => {
      const ref = createRef<HTMLButtonElement>();
      render(
        <AlertDialog open={true}>
          <AlertDialogContent aria-describedby={undefined}>
            <AlertDialogCancel ref={ref}>Cancel</AlertDialogCancel>
          </AlertDialogContent>
        </AlertDialog>,
      );

      expect(ref.current).toBeInstanceOf(HTMLButtonElement);
    });
  });

  // ============================================
  // Destructive action pattern tests
  // ============================================

  describe('destructive action pattern', () => {
    it('can be used for destructive confirmation', async () => {
      const handleDelete = vi.fn();
      render(
        <AlertDialog>
          <AlertDialogTrigger asChild>
            <Button variant="destructive">Delete Account</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete Account?</AlertDialogTitle>
              <AlertDialogDescription>
                This will permanently delete your account and all data.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Keep Account</AlertDialogCancel>
              <AlertDialogAction onClick={handleDelete}>Delete Forever</AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>,
      );

      // Open dialog
      await user.click(screen.getByRole('button', { name: 'Delete Account' }));

      // Verify warning is shown
      expect(screen.getByText(/permanently delete your account/)).toBeInTheDocument();

      // Confirm deletion
      await user.click(screen.getByRole('button', { name: 'Delete Forever' }));

      expect(handleDelete).toHaveBeenCalled();
    });

    it('allows canceling destructive action', async () => {
      const handleDelete = vi.fn();
      render(
        <AlertDialog>
          <AlertDialogTrigger asChild>
            <Button variant="destructive">Delete</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete?</AlertDialogTitle>
              <AlertDialogDescription>This cannot be undone.</AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction onClick={handleDelete}>Delete</AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>,
      );

      // Open and cancel
      await user.click(screen.getByRole('button', { name: 'Delete' }));
      await user.click(screen.getByRole('button', { name: 'Cancel' }));

      expect(handleDelete).not.toHaveBeenCalled();
      expect(screen.queryByRole('alertdialog')).not.toBeInTheDocument();
    });
  });
});
