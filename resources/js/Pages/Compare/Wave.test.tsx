import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Wave from './Wave';

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
    competitor: 'wave',
    competitorName: 'Wave',
    title: 'Laravel React Starter vs Wave — SaaS Boilerplate Comparison',
    metaDescription: 'Wave uses Blade templates and Spark for billing.',
    features: [
        { feature: 'Frontend', us: 'React 18 + TypeScript', them: 'Blade + Livewire + Alpine.js' },
        { feature: 'Admin panel', us: 'React + TypeScript', them: 'Filament-based' },
    ],
    breadcrumbs: [
        { name: 'Home', url: 'http://localhost' },
        { name: 'Compare', url: 'http://localhost/compare/wave' },
        { name: 'Wave', url: 'http://localhost/compare/wave' },
    ],
};

describe('Compare/Wave', () => {
    it('renders without crashing', () => {
        render(<Wave {...defaultProps} />);
        expect(document.body).toBeTruthy();
    });

    it('renders the H1', () => {
        render(<Wave {...defaultProps} />);
        expect(
            screen.getByRole('heading', { level: 1 })
        ).toHaveTextContent(/Laravel React Starter vs Wave/);
    });

    it('has CTA links to home and pricing', () => {
        render(<Wave {...defaultProps} />);
        const ctaLinks = screen.getAllByRole('link', { name: /see what.s included/i });
        expect(ctaLinks.length).toBeGreaterThan(0);
        expect(ctaLinks[0]).toHaveAttribute('href', '/');
        const pricingLinks = screen.getAllByRole('link', { name: /view pricing/i });
        expect(pricingLinks.length).toBeGreaterThan(0);
        expect(pricingLinks[0]).toHaveAttribute('href', '/pricing');
    });
});
