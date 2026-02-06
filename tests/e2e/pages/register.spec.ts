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

test.describe('Register Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/register');
    await page.waitForLoadState('networkidle');

    assertNoConsoleErrors(errors);
    await assertCssLoaded(page);
    await assertJsLoaded(page);
    await assertPageIsStyled(page);
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

    // Password toggle
    await expect(page.getByRole('button', { name: /show password/i })).toBeVisible();

    // Terms checkbox
    await expect(page.getByText(/i agree to the/i)).toBeVisible();

    // Remember me checkbox
    await expect(page.getByText(/keep me signed in/i)).toBeVisible();

    // Submit button
    await expect(page.getByRole('button', { name: /create account/i })).toBeVisible();

    // Login link
    await expect(page.getByRole('link', { name: /sign in instead/i })).toBeVisible();

    // Desktop: left branded panel with features
    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).toBeVisible();
    await expect(page.getByText('Secure by Default')).toBeVisible();
    await expect(page.getByText('Modern Stack')).toBeVisible();
    await expect(page.getByText('Stay Informed')).toBeVisible();
    await expect(page.getByText('Lightning Fast')).toBeVisible();

    // Left panel footer
    await expect(page.getByText('Free to get started')).toBeVisible();
    await expect(page.getByText('No credit card required')).toBeVisible();
  });

  test('renders correctly on mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/register');

    // Mobile header visible
    const mobileHeader = page.locator('header.lg\\:hidden');
    await expect(mobileHeader).toBeVisible();

    // Left branded panel hidden
    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).not.toBeVisible();

    // Form still accessible
    await expect(page.getByLabel(/full name/i)).toBeVisible();
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByLabel(/confirm password/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /create account/i })).toBeVisible();
  });

  test('renders correctly on tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/register');

    // Tablet below lg breakpoint — mobile layout
    const mobileHeader = page.locator('header.lg\\:hidden');
    await expect(mobileHeader).toBeVisible();

    const leftPanel = page.locator('.hidden.lg\\:flex').first();
    await expect(leftPanel).not.toBeVisible();

    await expect(page.getByLabel(/full name/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /create account/i })).toBeVisible();
  });

  test('renders correctly in dark mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/register');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { name: /create your account/i })).toBeVisible();
    await assertDarkModeApplied(page);
    assertNoConsoleErrors(errors);
  });

  test('password strength indicator shows requirements', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    const passwordInput = page.getByLabel(/^password$/i);

    // Type a weak password
    await passwordInput.fill('ab');
    await expect(page.getByText('At least 8 characters')).toBeVisible();
    await expect(page.getByText('One uppercase letter')).toBeVisible();
    await expect(page.getByText('One number')).toBeVisible();

    // Type a strong password
    await passwordInput.fill('StrongPass1');
    await expect(page.getByText('At least 8 characters')).toBeVisible();
    await expect(page.getByText('One uppercase letter')).toBeVisible();
    await expect(page.getByText('One lowercase letter')).toBeVisible();
    await expect(page.getByText('One number')).toBeVisible();
  });

  test('create account button disabled until terms accepted and password strong', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    const submitButton = page.getByRole('button', { name: /create account/i });

    // Initially disabled (no terms, no password)
    await expect(submitButton).toBeDisabled();

    // Fill strong password but no terms
    await page.getByLabel(/^password$/i).fill('StrongPass1');
    await expect(submitButton).toBeDisabled();

    // Accept terms
    await page.getByLabel(/i agree to the/i).check();
    await expect(submitButton).toBeEnabled();
  });

  test('social auth buttons consistency', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');

    const socialButtons = page.locator('button', { hasText: /google|github/i });
    const count = await socialButtons.count();

    if (count > 0) {
      await expect(page.getByRole('button', { name: /google/i })).toBeVisible();
      await expect(page.getByRole('button', { name: /github/i })).toBeVisible();
      await expect(page.getByText(/or register with email/i)).toBeVisible();
    }
  });

  test('visual regression - light mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('register-light.png', { fullPage: true });
  });

  test('visual regression - dark mode', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/register');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('register-dark.png', { fullPage: true });
  });
});
