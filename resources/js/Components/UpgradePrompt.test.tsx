import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { UpgradePrompt } from './UpgradePrompt';

vi.mock('@inertiajs/react', () => ({
  Link: ({ href, children }: { href: string; children: React.ReactNode }) => (
    <a href={href}>{children}</a>
  ),
}));

describe('UpgradePrompt', () => {
  const basePrompt = {
    limit: 'api_tokens',
    plan: 'pro',
    cta_url: '/pricing',
  };

  it('renders the upgrade prompt with limit-specific messaging', () => {
    render(<UpgradePrompt prompt={basePrompt} />);

    expect(
      screen.getByText(/you've reached your api token limit/i)
    ).toBeInTheDocument();
  });

  it('uses current plan copy, not hardcoded free plan', () => {
    render(<UpgradePrompt prompt={basePrompt} />);

    expect(screen.getByText(/on your current plan/i)).toBeInTheDocument();
    expect(screen.queryByText(/free plan/i)).not.toBeInTheDocument();
  });

  it('renders upgrade CTA linking to the provided url', () => {
    render(<UpgradePrompt prompt={basePrompt} />);

    const link = screen.getByRole('link', { name: /upgrade to pro/i });
    expect(link).toHaveAttribute('href', '/pricing');
  });

  it('renders with a known limit label for webhook_endpoints', () => {
    render(
      <UpgradePrompt prompt={{ ...basePrompt, limit: 'webhook_endpoints' }} />
    );

    expect(screen.getByText(/webhook endpoint limit/i)).toBeInTheDocument();
  });

  it('formats unknown limit keys by replacing underscores with spaces', () => {
    render(
      <UpgradePrompt prompt={{ ...basePrompt, limit: 'custom_resource' }} />
    );

    expect(screen.getByText(/custom resource limit/i)).toBeInTheDocument();
  });

  it('can be dismissed by clicking the close button', async () => {
    const user = userEvent.setup();
    render(<UpgradePrompt prompt={basePrompt} />);

    expect(screen.getByRole('alert')).toBeInTheDocument();

    await user.click(
      screen.getByRole('button', { name: /dismiss upgrade prompt/i })
    );

    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('has correct aria attributes for accessibility', () => {
    render(<UpgradePrompt prompt={basePrompt} />);

    const alert = screen.getByRole('alert');
    expect(alert).toHaveAttribute('aria-live', 'polite');
  });
});
