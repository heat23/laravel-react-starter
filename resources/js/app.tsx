import './bootstrap';
import '../css/app.css';

import { Suspense } from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { ThemeProvider } from '@/Components/theme';
import { Toaster } from '@/Components/ui/sonner';
import { TooltipProvider } from '@/Components/ui/tooltip';

// Loading fallback for lazy-loaded pages
const PageLoader = () => (
    <div className="flex h-screen items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
    </div>
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pages = import.meta.glob('./Pages/**/*.{tsx,jsx}');

createInertiaApp({
    title: (title) => title ? `${title} | ${appName}` : appName,
    resolve: (name) => {
        const tsxPath = `./Pages/${name}.tsx`;
        const jsxPath = `./Pages/${name}.jsx`;
        const page = pages[tsxPath] || pages[jsxPath];

        if (!page) {
            throw new Error(`Page not found: ${tsxPath} or ${jsxPath}`);
        }

        return page();
    },
    setup({ el, App, props }) {
        const renderApp = (
            <App {...props}>
                {({ Component, props: pageProps, key }) => {
                    const page = <Component key={key} {...pageProps} />;
                    const content =
                        typeof Component.layout === "function"
                            ? Component.layout(page)
                            : Array.isArray(Component.layout)
                              ? Component.layout
                                    .concat(page)
                                    .reverse()
                                    .reduce(
                                        (children, Layout) => (
                                            <Layout {...pageProps}>{children}</Layout>
                                        )
                                    )
                              : page;

                    return (
                        <ThemeProvider>
                            <TooltipProvider>
                                <Suspense fallback={<PageLoader />}>
                                    {content}
                                </Suspense>
                                <Toaster richColors position="top-right" />
                            </TooltipProvider>
                        </ThemeProvider>
                    );
                }}
            </App>
        );

        // If SSR rendered content exists, hydrate; otherwise create new root
        if (el.hasChildNodes()) {
            hydrateRoot(el, renderApp);
        } else {
            createRoot(el).render(renderApp);
        }
    },
    progress: {
        color: '#1e6dd9', // Primary brand color
    },
});
