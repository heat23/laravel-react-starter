import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Error from './Error';

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    Head: ({ title }: { title: string }) => <title>{title}</title>,
  };
});

describe('Error Page', () => {
  it('renders 404 with correct title and description', () => {
    render(<Error status={404} />);

    expect(screen.getByText('404')).toBeInTheDocument();
    expect(screen.getByText('Page Not Found')).toBeInTheDocument();
    expect(screen.getByText(/doesn't exist/)).toBeInTheDocument();
  });

  it('renders 403 with correct title and description', () => {
    render(<Error status={403} />);

    expect(screen.getByText('403')).toBeInTheDocument();
    expect(screen.getByText('Forbidden')).toBeInTheDocument();
  });

  it('renders 500 with correct title and description', () => {
    render(<Error status={500} />);

    expect(screen.getByText('500')).toBeInTheDocument();
    expect(screen.getByText('Server Error')).toBeInTheDocument();
  });

  it('renders 419 with session expired message', () => {
    render(<Error status={419} />);

    expect(screen.getByText('419')).toBeInTheDocument();
    expect(screen.getByText('Page Expired')).toBeInTheDocument();
  });

  it('renders 429 with rate limit message', () => {
    render(<Error status={429} />);

    expect(screen.getByText('429')).toBeInTheDocument();
    expect(screen.getByText('Too Many Requests')).toBeInTheDocument();
  });

  it('renders 503 with maintenance message', () => {
    render(<Error status={503} />);

    expect(screen.getByText('503')).toBeInTheDocument();
    expect(screen.getByText('Service Unavailable')).toBeInTheDocument();
  });

  it('renders fallback for unknown status codes', () => {
    render(<Error status={418} />);

    expect(screen.getByText('418')).toBeInTheDocument();
    expect(screen.getByText('Error')).toBeInTheDocument();
  });

  it('renders Go Back and Go Home buttons', () => {
    render(<Error status={404} />);

    expect(screen.getByRole('button', { name: 'Go Back' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Go Home' })).toBeInTheDocument();
  });

  it('Go Home link points to root', () => {
    render(<Error status={404} />);

    const link = screen.getByRole('link', { name: 'Go Home' });
    expect(link).toHaveAttribute('href', '/');
  });
});
