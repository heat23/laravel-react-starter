import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Billing from './Billing';

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
    title: 'Production-Grade Stripe Billing for Laravel — Feature Overview',
    metaDescription: 'Double-charge prevention, 4 billing plans, team seats, dunning emails, and incomplete payment recovery.',
};

describe('Features/Billing', () => {
    it('renders without crashing', () => {
        render(<Billing {...defaultProps} />);
        expect(document.body).toBeTruthy();
    });

    it('renders the H1', () => {
        render(<Billing {...defaultProps} />);
        expect(
            screen.getByRole('heading', { level: 1 })
        ).toHaveTextContent(/Stripe Billing That Handles the Edge Cases/);
    });

    it('has CTA link to pricing', () => {
        render(<Billing {...defaultProps} />);
        const pricingLinks = screen.getAllByRole('link', { name: /view pricing/i });
        expect(pricingLinks.length).toBeGreaterThan(0);
        expect(pricingLinks[0]).toHaveAttribute('href', '/pricing');
    });

    it('has CTA link back to overview', () => {
        render(<Billing {...defaultProps} />);
        const overviewLink = screen.getByRole('link', { name: /back to overview/i });
        expect(overviewLink).toHaveAttribute('href', '/');
    });

    it('renders the 6 billing feature cards', () => {
        render(<Billing {...defaultProps} />);
        expect(screen.getByText('Double-charge prevention')).toBeInTheDocument();
        expect(screen.getByText('4 billing plans')).toBeInTheDocument();
        expect(screen.getByText('Team seat billing')).toBeInTheDocument();
        expect(screen.getByText('Dunning emails')).toBeInTheDocument();
        expect(screen.getByText('Incomplete payment recovery')).toBeInTheDocument();
        expect(screen.getByText('Admin billing dashboard')).toBeInTheDocument();
    });

    it('renders FAQ section', () => {
        render(<Billing {...defaultProps} />);
        expect(screen.getByText('Does this work with Stripe Tax?')).toBeInTheDocument();
        expect(screen.getByText('Can I add metered billing?')).toBeInTheDocument();
        expect(screen.getByText('What happens if Redis is down?')).toBeInTheDocument();
    });
});
