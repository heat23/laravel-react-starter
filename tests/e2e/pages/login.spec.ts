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

test.describe('Login Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    assertNoConsoleErrors(errors);
    await assertCssLoaded(page);
    await assertJsLoaded(page);
    await assertPageIsStyled(page);
  });

  test('renders all key elements on desktop', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');

    // Heading
    await expect(page.getByRole('heading', { name: /welcome back/i })).toBeVisible();
    await expect(page.getByText('Sign in to your account to continue')).toBeVisible();

    // Form fields
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();

    // Password toggle
    await expect(page.getByRole('button', { name: /show password/i })).toBeVisible();

    // Remember me checkbox
    await expect(page.getByText(/keep me signed in/i)).toBeVisible();

    // Submit button
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();

    // Links
    await expect(page.getByRole('link', { name: /forgot password/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /create one for free/i })).toBeVisible();

    // Footer - Terms and Privacy
    await expect(page.getByRole('button', { name: /terms of service/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /privacy policy/i })).toBeVisible();

    // Desktop: left branded panel visible
    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).toBeVisible();

    // Desktop: theme toggle visible
    await expect(page.getByRole('button', { name: /toggle theme/i }).first()).toBeVisible();
  });

  test('renders correctly on mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/login');

    // Mobile header visible
    const mobileHeader = page.locator('header.lg\\:hidden');
    await expect(mobileHeader).toBeVisible();

    // Left branded panel hidden on mobile
    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).not.toBeVisible();

    // Form still accessible
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('renders correctly on tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/login');

    // Tablet (768px) is below lg breakpoint (1024px), same as mobile layout
    const mobileHeader = page.locator('header.lg\\:hidden');
    await expect(mobileHeader).toBeVisible();

    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).not.toBeVisible();

    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('renders correctly in dark mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/login');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { name: /welcome back/i })).toBeVisible();
    await assertDarkModeApplied(page);
    assertNoConsoleErrors(errors);
  });

  test('password toggle works', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');

    const passwordInput = page.locator('#password');
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // Use dispatchEvent — the toggle button is small (16x16) and absolutely positioned,
    // which can cause Playwright's native click to miss React's synthetic event handler
    await page.locator('button[aria-label="Show password"]').dispatchEvent('click');
    await expect(passwordInput).toHaveAttribute('type', 'text');

    await expect(page.locator('button[aria-label="Hide password"]')).toBeVisible();
    await page.locator('button[aria-label="Hide password"]').dispatchEvent('click');
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('social auth buttons consistency', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');

    // Feature flag determines if social auth is shown.
    // If any social button exists, both Google and GitHub should be present.
    const socialButtons = page.locator('button', { hasText: /google|github/i });
    const count = await socialButtons.count();

    if (count > 0) {
      await expect(page.getByRole('button', { name: /google/i })).toBeVisible();
      await expect(page.getByRole('button', { name: /github/i })).toBeVisible();
      await expect(page.getByText(/or continue with email/i)).toBeVisible();
    }
    // If count is 0, social auth is disabled — that's valid too
  });

  test('visual regression - light mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('login-light.png', { fullPage: true });
  });

  test('visual regression - dark mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('login-dark.png', { fullPage: true });
  });
});
