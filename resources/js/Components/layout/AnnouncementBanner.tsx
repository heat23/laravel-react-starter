import { X } from 'lucide-react';

import { useEffect, useState } from 'react';

import { Link } from '@inertiajs/react';

export interface AnnouncementBannerProps {
  message: string;
  ctaText?: string;
  ctaUrl?: string;
  expiresAt?: string;
}

function getTimeLeft(expiresAt: string): string | null {
  const diff = new Date(expiresAt).getTime() - Date.now();
  if (diff <= 0) return null;
  const hours = Math.floor(diff / 3_600_000);
  const minutes = Math.floor((diff % 3_600_000) / 60_000);
  if (hours > 48) return null; // don't show countdown for distant dates
  if (hours > 0) return `${hours}h ${minutes}m remaining`;
  return `${minutes}m remaining`;
}

export function AnnouncementBanner({
  message,
  ctaText,
  ctaUrl,
  expiresAt,
}: AnnouncementBannerProps) {
  const storageKey = `announcement_dismissed_${btoa(message).slice(0, 32)}`;
  const [visible, setVisible] = useState(false);
  const [timeLeft, setTimeLeft] = useState<string | null>(null);

  useEffect(() => {
    try {
      if (localStorage.getItem(storageKey)) return;
    } catch {
      // localStorage unavailable — show anyway
    }
    setVisible(true);
  }, [storageKey]);

  useEffect(() => {
    if (!expiresAt || !visible) return;
    const update = () => setTimeLeft(getTimeLeft(expiresAt));
    update();
    const id = setInterval(update, 60_000);
    return () => clearInterval(id);
  }, [expiresAt, visible]);

  if (!visible) return null;

  const dismiss = () => {
    setVisible(false);
    try {
      localStorage.setItem(storageKey, '1');
    } catch {
      // ignore
    }
  };

  return (
    <div
      role="banner"
      className="relative z-50 flex items-center justify-center gap-3 bg-primary px-4 py-2.5 text-center text-sm font-medium text-primary-foreground"
    >
      <span>
        {message}
        {timeLeft && <span className="ml-2 opacity-75">· {timeLeft}</span>}
      </span>
      {ctaText && ctaUrl && (
        <Link
          href={ctaUrl}
          className="shrink-0 rounded border border-primary-foreground/30 px-2.5 py-0.5 text-xs font-semibold transition-colors hover:bg-primary-foreground hover:text-primary"
        >
          {ctaText}
        </Link>
      )}
      <button
        type="button"
        onClick={dismiss}
        aria-label="Dismiss announcement"
        className="absolute right-3 rounded p-0.5 opacity-70 transition-opacity hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-foreground"
      >
        <X className="h-4 w-4" />
      </button>
    </div>
  );
}
