import { test, expect } from '@playwright/test';

import {
  assertAssetsLoadedCleanly,
  assertDarkModeApplied,
  assertDesktopAuthLayout,
  assertMobileAuthLayout,
  collectConsoleErrors,
  enableDarkMode,
} from '../fixtures/helpers';

test.describe('Login Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await assertAssetsLoadedCleanly(page, errors);
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
    await expect(page.getByRole('button', { name: /show password/i })).toBeVisible();
    await expect(page.getByText(/keep me signed in/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();

    // Links
    await expect(page.getByRole('link', { name: /forgot password/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /create one for free/i })).toBeVisible();

    // Footer — legal buttons
    await expect(page.getByRole('button', { name: /terms of service/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /privacy policy/i })).toBeVisible();

    // Desktop layout
    await assertDesktopAuthLayout(page);
    await expect(page.getByRole('button', { name: /toggle theme/i }).first()).toBeVisible();
  });

  test('shows mobile layout on small viewport', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/login');

    await assertMobileAuthLayout(page);

    // Form still accessible
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('shows mobile layout on tablet viewport', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/login');

    // 768px is below the lg (1024px) breakpoint — same layout as mobile
    await assertMobileAuthLayout(page);
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('dark mode renders without errors', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/login');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { name: /welcome back/i })).toBeVisible();
    await assertDarkModeApplied(page);
    expect(errors).toHaveLength(0);
  });

  test('password toggle switches input type', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');

    const passwordInput = page.locator('#password');
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // The toggle button is absolutely positioned inside the input wrapper.
    // The input element's box overlaps the button, absorbing pointer events
    // before they reach the button. Use evaluate() to trigger the DOM click
    // directly — this fires React's synthetic onClick handler correctly.
    const showBtn = page.locator('button[aria-label="Show password"]');
    await showBtn.evaluate((el) => (el as HTMLElement).click());
    await expect(passwordInput).toHaveAttribute('type', 'text');

    const hideBtn = page.locator('button[aria-label="Hide password"]');
    await expect(hideBtn).toBeVisible();
    await hideBtn.evaluate((el) => (el as HTMLElement).click());
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('social auth buttons present when feature enabled', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');

    const hasSocialAuth = (await page.locator('button', { hasText: /google|github/i }).count()) > 0;
    test.skip(!hasSocialAuth, 'Social auth feature is disabled in this environment');

    await expect(page.getByRole('button', { name: /google/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /github/i })).toBeVisible();
    await expect(page.getByText(/or continue with email/i)).toBeVisible();
  });

  // Visual regression --------------------------------------------------------

  test('visual regression — desktop light', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('login-light.png', { fullPage: true });
  });

  test('visual regression — desktop dark', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('login-dark.png', { fullPage: true });
  });

  test('visual regression — mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('login-mobile.png', { fullPage: true });
  });

  test('visual regression — tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('login-tablet.png', { fullPage: true });
  });
});
