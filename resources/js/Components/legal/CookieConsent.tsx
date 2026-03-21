import { useEffect, useState } from 'react';

import { Button } from '@/Components/ui/button';
import { grantConsent } from '@/lib/analytics';

const STORAGE_KEY = 'cookie_consent';

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

function setConsent(value: 'accepted' | 'declined') {
  try {
    localStorage.setItem(STORAGE_KEY, value);
  } catch {
    // localStorage unavailable
  }
}

export default function CookieConsent() {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const consent = getConsent();
    if (consent === null) {
      setVisible(true);
    }
  }, []);

  const handleAccept = () => {
    setConsent('accepted');
    setVisible(false);
    // Flush the pre-consent analytics queue before reload
    grantConsent();
    // Reload so the Blade nonce'd script loads GA (CSP-safe)
    window.location.reload();
  };

  const handleDecline = () => {
    setConsent('declined');
    setVisible(false);
  };

  if (!visible) return null;

  return (
    <div
      role="dialog"
      aria-label="Cookie consent"
      className="fixed bottom-0 left-0 right-0 z-50 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 p-4 shadow-lg"
    >
      <div className="container max-w-4xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <p className="text-sm text-muted-foreground flex-1">
          We use cookies for analytics to improve your experience. Read our{' '}
          <a href="/privacy" className="underline hover:text-foreground">
            Privacy Policy
          </a>{' '}
          for details.
        </p>
        <div className="flex gap-2 shrink-0">
          <Button variant="outline" size="sm" onClick={handleDecline}>
            Decline
          </Button>
          <Button size="sm" onClick={handleAccept}>
            Accept
          </Button>
        </div>
      </div>
    </div>
  );
}
