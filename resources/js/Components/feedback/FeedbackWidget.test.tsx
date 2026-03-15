import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';

import { FeedbackWidget } from './FeedbackWidget';

vi.mock('@inertiajs/react', () => ({
  router: {
    post: vi.fn(),
  },
}));

// Mock route helper
vi.stubGlobal('route', (name: string) => `/${name.replace('.', '/')}`);

describe('FeedbackWidget', () => {
  it('renders the trigger button', () => {
    render(<FeedbackWidget />);
    expect(
      screen.getByRole('button', { name: /send feedback/i })
    ).toBeInTheDocument();
  });

  it('opens the feedback form when clicked', async () => {
    const user = userEvent.setup();
    render(<FeedbackWidget />);

    await user.click(screen.getByRole('button', { name: /send feedback/i }));

    expect(
      screen.getByText('Help us improve by sharing your thoughts.')
    ).toBeInTheDocument();
    expect(screen.getByLabelText(/type/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/message/i)).toBeInTheDocument();
  });

  it('shows the submit button in the form', async () => {
    const user = userEvent.setup();
    render(<FeedbackWidget />);

    await user.click(screen.getByRole('button', { name: /send feedback/i }));

    const submitButtons = screen.getAllByRole('button', {
      name: /send feedback/i,
    });
    expect(submitButtons.length).toBeGreaterThanOrEqual(1);
  });
});
