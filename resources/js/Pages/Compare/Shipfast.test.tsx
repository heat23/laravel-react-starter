import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import Shipfast from './Shipfast';

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
    competitor: 'shipfast',
    competitorName: 'Shipfast',
    title: 'Laravel React Starter vs Shipfast — Laravel vs Next.js SaaS Starter',
    metaDescription: 'Shipfast is a Next.js starter.',
    features: [
        { feature: 'Backend', us: 'Laravel 12 (PHP)', them: 'Next.js (Node.js)' },
        { feature: 'Frontend', us: 'React 18 + TypeScript', them: 'React + TypeScript' },
    ],
    breadcrumbs: [
        { name: 'Home', url: 'http://localhost' },
        { name: 'Compare', url: 'http://localhost/compare/shipfast' },
        { name: 'Shipfast', url: 'http://localhost/compare/shipfast' },
    ],
};

describe('Compare/Shipfast', () => {
    it('renders without crashing', () => {
        render(<Shipfast {...defaultProps} />);
        expect(document.body).toBeTruthy();
    });

    it('renders the H1', () => {
        render(<Shipfast {...defaultProps} />);
        expect(
            screen.getByRole('heading', { level: 1 })
        ).toHaveTextContent(/Laravel React Starter vs Shipfast/);
    });

    it('has CTA links to home and pricing', () => {
        render(<Shipfast {...defaultProps} />);
        const homeLinks = screen.getAllByRole('link', { name: /get started/i });
        expect(homeLinks.length).toBeGreaterThan(0);
        expect(homeLinks[0]).toHaveAttribute('href', '/');
        const pricingLinks = screen.getAllByRole('link', { name: /view pricing/i });
        expect(pricingLinks.length).toBeGreaterThan(0);
        expect(pricingLinks[0]).toHaveAttribute('href', '/pricing');
    });
});
