import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';

import AdminPanel from './AdminPanel';

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
    title: 'Built-in Laravel Admin Panel — User Management, Billing, Health Monitoring',
    metaDescription: 'A full React + TypeScript admin panel.',
};

describe('Features/AdminPanel', () => {
    it('renders without crashing', () => {
        render(<AdminPanel {...defaultProps} />);
        expect(document.body).toBeTruthy();
    });

    it('renders the H1', () => {
        render(<AdminPanel {...defaultProps} />);
        expect(
            screen.getByRole('heading', { level: 1 })
        ).toHaveTextContent(/full-featured admin panel/);
    });

    it('has CTA link to pricing', () => {
        render(<AdminPanel {...defaultProps} />);
        const pricingLinks = screen.getAllByRole('link', { name: /view pricing/i });
        expect(pricingLinks.length).toBeGreaterThan(0);
        expect(pricingLinks[0]).toHaveAttribute('href', '/pricing');
    });

    it('has CTA link back to overview', () => {
        render(<AdminPanel {...defaultProps} />);
        const overviewLink = screen.getByRole('link', { name: /back to overview/i });
        expect(overviewLink).toHaveAttribute('href', '/');
    });

    it('renders all 8 admin feature cards', () => {
        render(<AdminPanel {...defaultProps} />);
        expect(screen.getByText('User management')).toBeInTheDocument();
        expect(screen.getByText('Billing dashboard')).toBeInTheDocument();
        expect(screen.getByText('Audit logs')).toBeInTheDocument();
        expect(screen.getByText('Feature flag UI')).toBeInTheDocument();
        expect(screen.getByText('Health monitoring')).toBeInTheDocument();
        expect(screen.getByText('Config viewer')).toBeInTheDocument();
        expect(screen.getByText('Failed jobs')).toBeInTheDocument();
        expect(screen.getByText('System info')).toBeInTheDocument();
    });

    it('renders security model section', () => {
        render(<AdminPanel {...defaultProps} />);
        expect(screen.getByText('Security model')).toBeInTheDocument();
    });

    it('renders TypeScript section', () => {
        render(<AdminPanel {...defaultProps} />);
        expect(screen.getByText('Built for TypeScript')).toBeInTheDocument();
    });
});
