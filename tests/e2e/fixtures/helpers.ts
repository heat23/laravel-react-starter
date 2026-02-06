import { type Page, expect } from '@playwright/test';

// ---------------------------------------------------------------------------
// Dark mode
// ---------------------------------------------------------------------------

/**
 * Enable dark mode by adding .dark class to <html>, matching ThemeProvider behavior.
 * Waits for the CSS custom property to propagate so assertions are reliable.
 */
export async function enableDarkMode(page: Page): Promise<void> {
  await page.evaluate(() => document.documentElement.classList.add('dark'));
  await page.waitForFunction(
    () =>
      document.documentElement.classList.contains('dark') &&
      window.getComputedStyle(document.body).backgroundColor !== 'rgb(255, 255, 255)',
    { timeout: 3000 },
  );
}

/**
 * Assert dark mode is active and visually applied.
 */
export async function assertDarkModeApplied(page: Page): Promise<void> {
  const isDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
  expect(isDark).toBe(true);

  const bgColor = await page.evaluate(() =>
    window.getComputedStyle(document.body).backgroundColor,
  );
  expect(bgColor, 'Dark mode background should not be white').not.toBe('rgb(255, 255, 255)');
}

// ---------------------------------------------------------------------------
// Console errors
// ---------------------------------------------------------------------------

/**
 * Start collecting console errors. Call before navigation.
 * Returns an array that accumulates error messages from console and uncaught exceptions.
 */
export function collectConsoleErrors(page: Page): string[] {
  const errors: string[] = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push(msg.text());
  });
  page.on('pageerror', (err) => errors.push(err.message));
  return errors;
}

/**
 * Assert that no console errors were captured.
 */
export function assertNoConsoleErrors(errors: string[]): void {
  expect(errors, `Console errors found: ${errors.join(', ')}`).toHaveLength(0);
}

// ---------------------------------------------------------------------------
// Asset loading
// ---------------------------------------------------------------------------

/**
 * Assert that Vite-built CSS loaded (checks for manifest-hashed asset pattern).
 */
export async function assertCssLoaded(page: Page): Promise<void> {
  const cssHref = await page.evaluate(() => {
    const link = document.querySelector('link[rel="stylesheet"][href*="/build/assets/"]');
    return link?.getAttribute('href') ?? null;
  });
  expect(cssHref, 'No Vite CSS bundle found matching /build/assets/*.css').toBeTruthy();
}

/**
 * Assert that Vite-built JS loaded (checks for manifest-hashed asset pattern).
 */
export async function assertJsLoaded(page: Page): Promise<void> {
  const jsSrc = await page.evaluate(() => {
    const script = document.querySelector('script[src*="/build/assets/"]');
    return script?.getAttribute('src') ?? null;
  });
  expect(jsSrc, 'No Vite JS bundle found matching /build/assets/*.js').toBeTruthy();
}

/**
 * Assert the page is styled (Tailwind classes applied, not browser-default rendering).
 */
export async function assertPageIsStyled(page: Page): Promise<void> {
  const hasTailwindClasses = await page.evaluate(
    () => document.querySelector('[class*="min-h-screen"]') !== null,
  );
  expect(
    hasTailwindClasses,
    'No Tailwind min-h-screen class found — page may be unstyled',
  ).toBe(true);
}

/**
 * Run the standard asset loading suite: no console errors, CSS/JS loaded, page styled.
 * Call after page.goto() and page.waitForLoadState('networkidle').
 */
export async function assertAssetsLoadedCleanly(
  page: Page,
  errors: string[],
): Promise<void> {
  assertNoConsoleErrors(errors);
  await assertCssLoaded(page);
  await assertJsLoaded(page);
  await assertPageIsStyled(page);
}

// ---------------------------------------------------------------------------
// Layout (AuthLayout responsive checks)
// ---------------------------------------------------------------------------

/**
 * Assert the desktop AuthLayout is rendered (left branded panel visible).
 */
export async function assertDesktopAuthLayout(page: Page): Promise<void> {
  const leftPanel = page.locator('.hidden.lg\\:flex').first();
  await expect(leftPanel).toBeVisible();
}

/**
 * Assert the mobile/tablet AuthLayout is rendered (mobile header visible, left panel hidden).
 * AuthLayout breakpoint is lg (1024px), so both mobile (375px) and tablet (768px) see this.
 */
export async function assertMobileAuthLayout(page: Page): Promise<void> {
  const mobileHeader = page.locator('header.lg\\:hidden');
  await expect(mobileHeader).toBeVisible();

  const leftPanel = page.locator('.hidden.lg\\:flex').first();
  await expect(leftPanel).not.toBeVisible();
}
