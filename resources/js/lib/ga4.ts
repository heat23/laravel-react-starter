// Initialize Google Analytics 4 programmatically.
// Called when the user grants analytics consent mid-session,
// so we don't need a full page reload to activate GA4.

declare global {
    interface Window {
        dataLayer: unknown[];
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        gtag: (...args: any[]) => void;
    }
}

export function initGA4(measurementId: string): void {
    if (typeof window === 'undefined' || !measurementId) return;

    // Avoid double-initialization
    if (window.gtag) return;

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${measurementId}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag(...args: unknown[]) {
        window.dataLayer.push(args);
    };
    window.gtag('js', new Date());
    window.gtag('config', measurementId);
}
