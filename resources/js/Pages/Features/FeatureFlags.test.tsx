import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import FeatureFlags from './FeatureFlags';

vi.mock('@inertiajs/react', async () => {
    const actual = await vi.importActual('@inertiajs/react');
    return {
        ...actual,
        Head: ({ title }: { title: string }) => <title>{title}</title>,
        Link: ({
            children,
            href,
        }: {
            children: React.ReactNode;
            href: string;
        }) => <a href={href}>{children}</a>,
    };
});

vi.mock('@/Components/theme/use-theme', () => ({
    useTheme: vi.fn(() => ({
        theme: 'system',
        setTheme: vi.fn(),
        resolvedTheme: 'light',
    })),
}));

const defaultProps = {
    title: 'Feature Flags for Laravel SaaS — Toggle Features Per-User and Globally',
    metaDescription: '11 built-in feature flags with database overrides.',
};

describe('Features/FeatureFlags', () => {
    it('renders without crashing', () => {
        render(<FeatureFlags {...defaultProps} />);
        expect(document.body).toBeTruthy();
    });

    it('renders the H1', () => {
        render(<FeatureFlags {...defaultProps} />);
        expect(
            screen.getByRole('heading', { level: 1 })
        ).toHaveTextContent(/Ship Features Safely/);
    });

    it('has CTA link to pricing', () => {
        render(<FeatureFlags {...defaultProps} />);
        const pricingLinks = screen.getAllByRole('link', { name: /view pricing/i });
        expect(pricingLinks.length).toBeGreaterThan(0);
        expect(pricingLinks[0]).toHaveAttribute('href', '/pricing');
    });

    it('has CTA link to overview', () => {
        render(<FeatureFlags {...defaultProps} />);
        const overviewLink = screen.getByRole('link', { name: /see all features/i });
        expect(overviewLink).toHaveAttribute('href', '/');
    });

    it('renders the flags table with all 11 flags', () => {
        render(<FeatureFlags {...defaultProps} />);
        expect(screen.getByText('billing.enabled')).toBeInTheDocument();
        expect(screen.getByText('admin.enabled')).toBeInTheDocument();
        expect(screen.getByText('webhooks.enabled')).toBeInTheDocument();
        expect(screen.getByText('two_factor.enabled')).toBeInTheDocument();
        expect(screen.getByText('email_verification.enabled')).toBeInTheDocument();
    });

    it('renders database overrides section', () => {
        render(<FeatureFlags {...defaultProps} />);
        expect(screen.getByText('Database overrides')).toBeInTheDocument();
        expect(screen.getByText('Runtime toggles')).toBeInTheDocument();
        expect(screen.getByText('Audit trail')).toBeInTheDocument();
        expect(screen.getByText('Priority resolution')).toBeInTheDocument();
    });
});
