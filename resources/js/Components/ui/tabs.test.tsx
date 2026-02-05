import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect } from 'vitest';

import { Tabs, TabsList, TabsTrigger, TabsContent } from './tabs';

describe('Tabs', () => {
  const user = userEvent.setup();

  // ============================================
  // Rendering tests
  // ============================================

  describe('rendering', () => {
    it('renders tabs component', () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      expect(screen.getByText('Tab 1')).toBeInTheDocument();
    });

    it('renders multiple tabs', () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
            <TabsTrigger value="tab2">Tab 2</TabsTrigger>
            <TabsTrigger value="tab3">Tab 3</TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      expect(screen.getByText('Tab 1')).toBeInTheDocument();
      expect(screen.getByText('Tab 2')).toBeInTheDocument();
      expect(screen.getByText('Tab 3')).toBeInTheDocument();
    });

    it('renders tab content', () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
          <TabsContent value="tab1">Tab 1 Content</TabsContent>
        </Tabs>,
      );

      expect(screen.getByText('Tab 1 Content')).toBeInTheDocument();
    });
  });

  // ============================================
  // TabsList styling tests
  // ============================================

  describe('TabsList styling', () => {
    it('has default list styling', () => {
      const { container } = render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      const list = container.querySelector('[role="tablist"]');
      expect(list).toHaveClass('inline-flex');
      expect(list).toHaveClass('items-center');
      expect(list).toHaveClass('rounded-md');
      expect(list).toHaveClass('bg-muted');
    });

    it('applies custom className to list', () => {
      const { container } = render(
        <Tabs defaultValue="tab1">
          <TabsList className="custom-list">
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      const list = container.querySelector('[role="tablist"]');
      expect(list).toHaveClass('custom-list');
    });
  });

  // ============================================
  // TabsTrigger styling tests
  // ============================================

  describe('TabsTrigger styling', () => {
    it('has default trigger styling', () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      const trigger = screen.getByText('Tab 1');
      expect(trigger).toHaveClass('inline-flex');
      expect(trigger).toHaveClass('items-center');
      expect(trigger).toHaveClass('rounded-sm');
    });

    it('applies custom className to trigger', () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1" className="custom-trigger">
              Tab 1
            </TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      expect(screen.getByText('Tab 1')).toHaveClass('custom-trigger');
    });
  });

  // ============================================
  // TabsContent styling tests
  // ============================================

  describe('TabsContent styling', () => {
    it('has default content styling', () => {
      const { container } = render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
          <TabsContent value="tab1">Content</TabsContent>
        </Tabs>,
      );

      const content = container.querySelector('[role="tabpanel"]');
      expect(content).toHaveClass('mt-2');
      expect(content).toHaveClass('ring-offset-background');
    });

    it('applies custom className to content', () => {
      const { container } = render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
          <TabsContent value="tab1" className="custom-content">
            Content
          </TabsContent>
        </Tabs>,
      );

      const content = container.querySelector('[role="tabpanel"]');
      expect(content).toHaveClass('custom-content');
    });
  });

  // ============================================
  // Interaction tests
  // ============================================

  describe('interactions', () => {
    it('switches between tabs', async () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
            <TabsTrigger value="tab2">Tab 2</TabsTrigger>
          </TabsList>
          <TabsContent value="tab1">Content 1</TabsContent>
          <TabsContent value="tab2">Content 2</TabsContent>
        </Tabs>,
      );

      expect(screen.getByText('Content 1')).toBeInTheDocument();

      await user.click(screen.getByText('Tab 2'));

      expect(screen.getByText('Content 2')).toBeInTheDocument();
    });

    it('shows correct active state', async () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
            <TabsTrigger value="tab2">Tab 2</TabsTrigger>
          </TabsList>
          <TabsContent value="tab1">Content 1</TabsContent>
          <TabsContent value="tab2">Content 2</TabsContent>
        </Tabs>,
      );

      const tab1 = screen.getByText('Tab 1');
      const tab2 = screen.getByText('Tab 2');

      expect(tab1).toHaveAttribute('data-state', 'active');
      expect(tab2).toHaveAttribute('data-state', 'inactive');

      await user.click(tab2);

      expect(tab2).toHaveAttribute('data-state', 'active');
      expect(tab1).toHaveAttribute('data-state', 'inactive');
    });
  });

  // ============================================
  // Accessibility tests
  // ============================================

  describe('accessibility', () => {
    it('has correct ARIA roles', () => {
      const { container } = render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
          </TabsList>
          <TabsContent value="tab1">Content</TabsContent>
        </Tabs>,
      );

      expect(container.querySelector('[role="tablist"]')).toBeInTheDocument();
      expect(container.querySelector('[role="tab"]')).toBeInTheDocument();
      expect(container.querySelector('[role="tabpanel"]')).toBeInTheDocument();
    });

    it('triggers are keyboard accessible', async () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
            <TabsTrigger value="tab2">Tab 2</TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      const tab1 = screen.getByText('Tab 1');
      await user.tab();

      expect(tab1).toHaveFocus();
    });
  });

  // ============================================
  // Disabled state tests
  // ============================================

  describe('disabled state', () => {
    it('renders disabled tab', () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
            <TabsTrigger value="tab2" disabled>
              Tab 2
            </TabsTrigger>
          </TabsList>
        </Tabs>,
      );

      expect(screen.getByText('Tab 2')).toBeDisabled();
    });

    it('cannot click disabled tab', async () => {
      render(
        <Tabs defaultValue="tab1">
          <TabsList>
            <TabsTrigger value="tab1">Tab 1</TabsTrigger>
            <TabsTrigger value="tab2" disabled>
              Tab 2
            </TabsTrigger>
          </TabsList>
          <TabsContent value="tab1">Content 1</TabsContent>
          <TabsContent value="tab2">Content 2</TabsContent>
        </Tabs>,
      );

      await user.click(screen.getByText('Tab 2'));

      // Tab 1 should still be active
      expect(screen.getByText('Tab 1')).toHaveAttribute('data-state', 'active');
      expect(screen.getByText('Content 1')).toBeInTheDocument();
    });
  });
});
