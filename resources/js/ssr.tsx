/**
 * Server-Side Rendering Entry Point
 *
 * This file enables SSR for Inertia.js with React, improving:
 * - LCP (Largest Contentful Paint) - content renders on server
 * - SEO crawlability - search engines see fully rendered HTML
 * - Initial page load performance - no JS needed for first paint
 */
import ReactDOMServer from 'react-dom/server';

import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';

import { ThemeProvider } from '@/Components/theme';
import { TooltipProvider } from '@/Components/ui/tooltip';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Import pages eagerly for SSR (no dynamic imports)
const pages = import.meta.glob('./Pages/**/*.{tsx,jsx}', { eager: true });

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => title ? `${title} | ${appName}` : appName,
        resolve: (name) => {
            const tsxPath = `./Pages/${name}.tsx`;
            const jsxPath = `./Pages/${name}.jsx`;
            const pageModule = pages[tsxPath] || pages[jsxPath];

            if (!pageModule) {
                throw new Error(`Page not found: ${tsxPath} or ${jsxPath}`);
            }

            return pageModule;
        },
        setup: ({ App, props }) => {
            return (
                <ThemeProvider>
                    <TooltipProvider>
                        <App {...props} />
                    </TooltipProvider>
                </ThemeProvider>
            );
        },
    }),
);
