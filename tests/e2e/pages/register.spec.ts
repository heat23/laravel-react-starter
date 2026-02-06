import { test, expect } from '@playwright/test';

import {
  assertAssetsLoadedCleanly,
  assertDarkModeApplied,
  assertDesktopAuthLayout,
  assertMobileAuthLayout,
  collectConsoleErrors,
  enableDarkMode,
} from '../fixtures/helpers';

test.describe('Register Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await assertAssetsLoadedCleanly(page, errors);
  });

  test('renders all key elements on desktop', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    // Heading
    await expect(page.getByRole('heading', { name: /create your account/i })).toBeVisible();
    await expect(page.getByText('Start your journey with us today')).toBeVisible();

    // Form fields
    await expect(page.getByLabel(/full name/i)).toBeVisible();
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByLabel(/confirm password/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /show password/i })).toBeVisible();

    // Checkboxes
    await expect(page.getByText(/i agree to the/i)).toBeVisible();
    await expect(page.getByText(/keep me signed in/i)).toBeVisible();

    // Submit + link
    await expect(page.getByRole('button', { name: /create account/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /sign in instead/i })).toBeVisible();

    // Desktop layout — left branded panel with feature highlights
    await assertDesktopAuthLayout(page);
    await expect(page.getByText('Secure by Default')).toBeVisible();
    await expect(page.getByText('Modern Stack')).toBeVisible();
    await expect(page.getByText('Stay Informed')).toBeVisible();
    await expect(page.getByText('Lightning Fast')).toBeVisible();
    await expect(page.getByText('Free to get started')).toBeVisible();
    await expect(page.getByText('No credit card required')).toBeVisible();
  });

  test('shows mobile layout on small viewport', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/register');

    await assertMobileAuthLayout(page);

    await expect(page.getByLabel(/full name/i)).toBeVisible();
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByLabel(/confirm password/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /create account/i })).toBeVisible();
  });

  test('shows mobile layout on tablet viewport', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/register');

    await assertMobileAuthLayout(page);
    await expect(page.getByLabel(/full name/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /create account/i })).toBeVisible();
  });

  test('dark mode renders without errors', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/register');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { name: /create your account/i })).toBeVisible();
    await assertDarkModeApplied(page);
    expect(errors).toHaveLength(0);
  });

  test('password strength indicator shows requirements', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    const passwordInput = page.getByLabel(/^password$/i);

    // Weak password — requirements visible but not all met
    await passwordInput.fill('ab');
    await expect(page.getByText('At least 8 characters')).toBeVisible();
    await expect(page.getByText('One uppercase letter')).toBeVisible();
    await expect(page.getByText('One number')).toBeVisible();

    // Strong password — all requirements visible
    await passwordInput.fill('StrongPass1');
    await expect(page.getByText('At least 8 characters')).toBeVisible();
    await expect(page.getByText('One uppercase letter')).toBeVisible();
    await expect(page.getByText('One lowercase letter')).toBeVisible();
    await expect(page.getByText('One number')).toBeVisible();
  });

  test('submit button disabled until terms accepted and password strong', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    const submitButton = page.getByRole('button', { name: /create account/i });

    // Initially disabled
    await expect(submitButton).toBeDisabled();

    // Strong password but no terms → still disabled
    await page.getByLabel(/^password$/i).fill('StrongPass1');
    await expect(submitButton).toBeDisabled();

    // Accept terms → enabled
    await page.getByLabel(/i agree to the/i).check();
    await expect(submitButton).toBeEnabled();
  });

  test('social auth buttons present when feature enabled', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    const hasSocialAuth = (await page.locator('button', { hasText: /google|github/i }).count()) > 0;
    test.skip(!hasSocialAuth, 'Social auth feature is disabled in this environment');

    await expect(page.getByRole('button', { name: /google/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /github/i })).toBeVisible();
    await expect(page.getByText(/or register with email/i)).toBeVisible();
  });

  // Visual regression --------------------------------------------------------

  test('visual regression — desktop light', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('register-light.png', { fullPage: true });
  });

  test('visual regression — desktop dark', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('register-dark.png', { fullPage: true });
  });

  test('visual regression — mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('register-mobile.png', { fullPage: true });
  });

  test('visual regression — tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('register-tablet.png', { fullPage: true });
  });
});
