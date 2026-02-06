import { test, expect } from '@playwright/test';

import {
  assertAssetsLoadedCleanly,
  assertDarkModeApplied,
  assertDesktopAuthLayout,
  assertMobileAuthLayout,
  collectConsoleErrors,
  enableDarkMode,
} from '../fixtures/helpers';

test.describe('Forgot Password Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await assertAssetsLoadedCleanly(page, errors);
  });

  test('renders all key elements on desktop', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');

    // Heading
    await expect(page.getByRole('heading', { name: /forgot your password/i })).toBeVisible();
    await expect(
      page.getByText(/enter your email and we'll send you a password reset link/i),
    ).toBeVisible();

    // Form
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /email password reset link/i })).toBeVisible();

    // Back link
    await expect(page.getByRole('link', { name: /back to sign in/i })).toBeVisible();

    // Desktop layout
    await assertDesktopAuthLayout(page);
  });

  test('shows mobile layout on small viewport', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/forgot-password');

    await assertMobileAuthLayout(page);
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /email password reset link/i })).toBeVisible();
  });

  test('shows mobile layout on tablet viewport', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/forgot-password');

    await assertMobileAuthLayout(page);
    await expect(page.getByLabel(/email address/i)).toBeVisible();
  });

  test('dark mode renders without errors', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/forgot-password');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { name: /forgot your password/i })).toBeVisible();
    await assertDarkModeApplied(page);
    expect(errors).toHaveLength(0);
  });

  test('back to sign in link points to login', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');

    const href = await page.getByRole('link', { name: /back to sign in/i }).getAttribute('href');
    expect(href).toContain('/login');
  });

  // Visual regression --------------------------------------------------------

  test('visual regression — desktop light', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('forgot-password-light.png', { fullPage: true });
  });

  test('visual regression — desktop dark', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('forgot-password-dark.png', { fullPage: true });
  });

  test('visual regression — mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('forgot-password-mobile.png', { fullPage: true });
  });

  test('visual regression — tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('forgot-password-tablet.png', { fullPage: true });
  });
});
