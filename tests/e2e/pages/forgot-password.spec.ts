import { test, expect } from '@playwright/test';

import {
  collectConsoleErrors,
  assertNoConsoleErrors,
  assertCssLoaded,
  assertJsLoaded,
  assertPageIsStyled,
  enableDarkMode,
  assertDarkModeApplied,
} from '../fixtures/helpers';

test.describe('Forgot Password Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');

    assertNoConsoleErrors(errors);
    await assertCssLoaded(page);
    await assertJsLoaded(page);
    await assertPageIsStyled(page);
  });

  test('renders all key elements on desktop', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');

    // Heading
    await expect(page.getByRole('heading', { name: /forgot your password/i })).toBeVisible();
    await expect(page.getByText(/enter your email and we'll send you a password reset link/i)).toBeVisible();

    // Form
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /email password reset link/i })).toBeVisible();

    // Back link
    await expect(page.getByRole('link', { name: /back to sign in/i })).toBeVisible();

    // Desktop: left branded panel visible
    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).toBeVisible();
  });

  test('renders correctly on mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/forgot-password');

    // Mobile header visible
    const mobileHeader = page.locator('header.lg\\:hidden');
    await expect(mobileHeader).toBeVisible();

    // Left panel hidden
    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).not.toBeVisible();

    // Form accessible
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /email password reset link/i })).toBeVisible();
  });

  test('renders correctly on tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/forgot-password');

    const mobileHeader = page.locator('header.lg\\:hidden');
    await expect(mobileHeader).toBeVisible();

    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).not.toBeVisible();

    await expect(page.getByLabel(/email address/i)).toBeVisible();
  });

  test('renders correctly in dark mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/forgot-password');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { name: /forgot your password/i })).toBeVisible();
    await assertDarkModeApplied(page);
    assertNoConsoleErrors(errors);
  });

  test('back to sign in link points to login', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');

    const href = await page.getByRole('link', { name: /back to sign in/i }).getAttribute('href');
    expect(href).toContain('/login');
  });

  test('visual regression - light mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('forgot-password-light.png', { fullPage: true });
  });

  test('visual regression - dark mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/forgot-password');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('forgot-password-dark.png', { fullPage: true });
  });
});
