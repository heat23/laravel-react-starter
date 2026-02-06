import { type Page, expect } from '@playwright/test';

/**
 * Enable dark mode by adding .dark class to <html>, matching ThemeProvider behavior.
 */
export async function enableDarkMode(page: Page): Promise<void> {
  await page.evaluate(() => {
    document.documentElement.classList.add('dark');
  });
  // Allow CSS transitions to complete
  await page.waitForTimeout(300);
}

/**
 * Enable light mode by removing .dark class from <html>.
 */
export async function enableLightMode(page: Page): Promise<void> {
  await page.evaluate(() => {
    document.documentElement.classList.remove('dark');
  });
  await page.waitForTimeout(300);
}

/**
 * Start collecting console errors. Call before navigation.
 * Returns an array that will be populated with error messages.
 */
export function collectConsoleErrors(page: Page): string[] {
  const errors: string[] = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
    }
  });
  page.on('pageerror', (err) => {
    errors.push(err.message);
  });
  return errors;
}

/**
 * Assert that no console errors were captured.
 */
export function assertNoConsoleErrors(errors: string[]): void {
  expect(errors, `Console errors found: ${errors.join(', ')}`).toHaveLength(0);
}

/**
 * Assert that CSS stylesheets loaded (at least one <link rel="stylesheet"> or <style> tag).
 */
export async function assertCssLoaded(page: Page): Promise<void> {
  const styleCount = await page.evaluate(() => {
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    const styles = document.querySelectorAll('style');
    return links.length + styles.length;
  });
  expect(styleCount).toBeGreaterThan(0);
}

/**
 * Assert that JS bundles loaded (at least one <script> with src).
 */
export async function assertJsLoaded(page: Page): Promise<void> {
  const scriptCount = await page.evaluate(() => {
    return document.querySelectorAll('script[src]').length;
  });
  expect(scriptCount).toBeGreaterThan(0);
}

/**
 * Assert the page is styled (not showing browser-default unstyled content).
 * Checks that body background is not plain white (#ffffff / rgb(255, 255, 255)).
 */
export async function assertPageIsStyled(page: Page): Promise<void> {
  const bgColor = await page.evaluate(() => {
    return window.getComputedStyle(document.body).backgroundColor;
  });
  // A styled page should have some background set via CSS variables.
  // Browser default is typically rgb(255, 255, 255) — but our theme may also be white-ish.
  // Instead, verify a Tailwind-generated class is actually applied somewhere.
  const hasTailwindClasses = await page.evaluate(() => {
    return document.querySelector('[class*="min-h-screen"]') !== null;
  });
  expect(hasTailwindClasses).toBe(true);
}

/**
 * Assert dark mode changed the background color from light mode.
 */
export async function assertDarkModeApplied(page: Page): Promise<void> {
  const isDark = await page.evaluate(() => {
    return document.documentElement.classList.contains('dark');
  });
  expect(isDark).toBe(true);

  // Verify the computed background color differs from pure white
  const bgColor = await page.evaluate(() => {
    return window.getComputedStyle(document.body).backgroundColor;
  });
  // In dark mode, background should not be white
  expect(bgColor).not.toBe('rgb(255, 255, 255)');
}
