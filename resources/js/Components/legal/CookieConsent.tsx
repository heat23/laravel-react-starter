import { useEffect, useState } from 'react';

import { Button } from '@/Components/ui/button';
import { grantConsent } from '@/lib/analytics';
import { initGA4 } from '@/lib/ga4';

const STORAGE_KEY = 'cookie_consent';
const CATEGORIES_KEY = 'cookie_consent_categories';
const VERSION_KEY = 'cookie_consent_version';
const DATE_KEY = 'cookie_consent_date';
const CONSENT_VERSION = '1.0';

interface ConsentCategories {
  necessary: boolean;
  analytics: boolean;
  marketing: boolean;
}

type ConsentState = 'accepted' | 'declined' | null;

function getConsent(): ConsentState {
  try {
    const value = localStorage.getItem(STORAGE_KEY);
    if (value === 'accepted' || value === 'declined') return value;
    return null;
  } catch {
    return null;
  }
}

function persistConsent(categories: ConsentCategories): void {
  try {
    // Backward-compat: keep binary 'accepted'/'declined' for analytics.ts hasConsent()
    const overall: 'accepted' | 'declined' = categories.analytics || categories.marketing ? 'accepted' : 'declined';
    localStorage.setItem(STORAGE_KEY, overall);
    localStorage.setItem(CATEGORIES_KEY, JSON.stringify(categories));
    localStorage.setItem(VERSION_KEY, CONSENT_VERSION);
    localStorage.setItem(DATE_KEY, new Date().toISOString());
  } catch {
    // localStorage unavailable
  }

  // Fire-and-forget POST to /api/consent for audit trail
  fetch('/api/consent', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      categories,
      version: CONSENT_VERSION,
      timestamp: new Date().toISOString(),
    }),
  }).catch(() => {});
}

export default function CookieConsent() {
  const [visible, setVisible] = useState(false);
  const [showPreferences, setShowPreferences] = useState(false);
  const [analytics, setAnalytics] = useState(false);
  const [marketing, setMarketing] = useState(false);

  useEffect(() => {
    const storedVersion = localStorage.getItem(VERSION_KEY);
    if (getConsent() === null || storedVersion !== CONSENT_VERSION) {
      setVisible(true);
    }
  }, []);

  const handleAcceptAll = () => {
    const categories: ConsentCategories = { necessary: true, analytics: true, marketing: true };
    persistConsent(categories);
    setVisible(false);
    const gaMeasurementId = import.meta.env.VITE_GA_MEASUREMENT_ID as string | undefined;
    if (gaMeasurementId) {
      // initGA4 must run before grantConsent: it synchronously assigns window.gtag,
      // so the queue flush inside grantConsent will find gtag available.
      // Wrap in try/catch so a script-load failure does not prevent consent
      // from being persisted and the queue from being flushed/discarded.
      try {
        initGA4(gaMeasurementId);
      } catch (err) {
        console.error('[analytics] initGA4 failed:', err);
      }
    }
    // Always call grantConsent regardless of GA4 configuration — it flushes queued
    // events when gtag is available, or discards them when GA4 is not configured.
    // Without this call, the pre-consent event queue would persist in memory for
    // the page lifetime with no flush path.
    grantConsent();
  };

  const handleDeclineAll = () => {
    const categories: ConsentCategories = { necessary: true, analytics: false, marketing: false };
    persistConsent(categories);
    setVisible(false);
  };

  const handleSavePreferences = () => {
    const categories: ConsentCategories = { necessary: true, analytics, marketing };
    persistConsent(categories);
    setVisible(false);
    if (analytics) {
      const gaMeasurementId = import.meta.env.VITE_GA_MEASUREMENT_ID as string | undefined;
      if (gaMeasurementId) {
        // initGA4 before grantConsent so window.gtag is available when queue flushes.
        // Wrap in try/catch so a script-load failure does not prevent consent
        // from being persisted and the queue from being flushed/discarded.
        try {
          initGA4(gaMeasurementId);
        } catch (err) {
          console.error('[analytics] initGA4 failed:', err);
        }
      }
      // Always call grantConsent when analytics is accepted — flushes queued events
      // when gtag is available, or discards them when GA4 is not configured.
      grantConsent();
    }
  };

  if (!visible) return null;

  return (
    <div
      role="dialog"
      aria-label="Cookie consent"
      className="fixed bottom-0 left-0 right-0 z-50 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 shadow-lg"
    >
      <div className="container max-w-4xl mx-auto p-4">
        <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4">
          <p className="text-sm text-muted-foreground flex-1">
            We use cookies to improve your experience. Read our{' '}
            <a href="/privacy" className="underline hover:text-foreground">
              Privacy Policy
            </a>{' '}
            for details.
          </p>
          <div className="flex flex-wrap gap-2 shrink-0">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setShowPreferences((p) => !p)}
              aria-expanded={showPreferences}
            >
              Manage Preferences
            </Button>
            <Button variant="outline" size="sm" onClick={handleDeclineAll}>
              Decline All
            </Button>
            <Button size="sm" onClick={handleAcceptAll}>
              Accept All
            </Button>
          </div>
        </div>

        {showPreferences && (
          <div className="mt-4 rounded-lg border border-border bg-muted/30 p-4 space-y-4">
            <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Cookie Categories
            </p>

            {/* Strictly Necessary */}
            <div className="flex items-start gap-3">
              <input
                type="checkbox"
                id="consent-necessary"
                checked
                disabled
                aria-label="Strictly necessary cookies (always on)"
                className="mt-0.5 h-4 w-4 rounded border-border"
              />
              <div>
                <label htmlFor="consent-necessary" className="text-sm font-medium">
                  Strictly Necessary
                </label>
                <p className="text-xs text-muted-foreground">
                  Required for the site to function. Cannot be disabled.
                </p>
              </div>
            </div>

            {/* Analytics */}
            <div className="flex items-start gap-3">
              <input
                type="checkbox"
                id="consent-analytics"
                checked={analytics}
                onChange={(e) => setAnalytics(e.target.checked)}
                aria-label="Analytics cookies"
                className="mt-0.5 h-4 w-4 rounded border-border"
              />
              <div>
                <label htmlFor="consent-analytics" className="text-sm font-medium">
                  Analytics
                </label>
                <p className="text-xs text-muted-foreground">
                  Help us understand how visitors use the site (Google Analytics).
                </p>
              </div>
            </div>

            {/* Marketing */}
            <div className="flex items-start gap-3">
              <input
                type="checkbox"
                id="consent-marketing"
                checked={marketing}
                onChange={(e) => setMarketing(e.target.checked)}
                aria-label="Marketing cookies"
                className="mt-0.5 h-4 w-4 rounded border-border"
              />
              <div>
                <label htmlFor="consent-marketing" className="text-sm font-medium">
                  Marketing
                </label>
                <p className="text-xs text-muted-foreground">
                  Used to deliver relevant advertisements and track campaign performance.
                </p>
              </div>
            </div>

            <Button size="sm" onClick={handleSavePreferences}>
              Save Preferences
            </Button>
          </div>
        )}
      </div>
    </div>
  );
}
