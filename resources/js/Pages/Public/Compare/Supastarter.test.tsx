import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Supastarter from './Supastarter';

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
    competitor: 'supastarter',
    competitorName: 'Supastarter',
    title: 'Laravel React Starter vs Supastarter — Laravel vs Supabase SaaS Starter',
    metaDescription: 'Supastarter uses Supabase + Next.js.',
    features: [
        { feature: 'Backend', us: 'Laravel 12 (PHP)', them: 'Supabase (BaaS) + Next.js' },
        { feature: 'Database', us: 'MySQL / PostgreSQL (Eloquent ORM)', them: 'PostgreSQL (Supabase)' },
    ],
    breadcrumbs: [
        { name: 'Home', url: 'http://localhost' },
        { name: 'Compare', url: 'http://localhost/compare/supastarter' },
        { name: 'Supastarter', url: 'http://localhost/compare/supastarter' },
    ],
};

describe('Compare/Supastarter', () => {
    it('renders without crashing', () => {
        render(<Supastarter {...defaultProps} />);
        expect(document.body).toBeTruthy();
    });

    it('renders the H1', () => {
        render(<Supastarter {...defaultProps} />);
        expect(
            screen.getByRole('heading', { level: 1 })
        ).toHaveTextContent(/Laravel React Starter vs Supastarter/);
    });

    it('has CTA links to home and pricing', () => {
        render(<Supastarter {...defaultProps} />);
        const homeLinks = screen.getAllByRole('link', { name: /get started/i });
        expect(homeLinks.length).toBeGreaterThan(0);
        expect(homeLinks[0]).toHaveAttribute('href', '/');
        const pricingLinks = screen.getAllByRole('link', { name: /view pricing/i });
        expect(pricingLinks.length).toBeGreaterThan(0);
        expect(pricingLinks[0]).toHaveAttribute('href', '/pricing');
    });
});
