import { test, expect } from '@playwright/test';

import {
  assertAssetsLoadedCleanly,
  assertDarkModeApplied,
  collectConsoleErrors,
  enableDarkMode,
} from '../fixtures/helpers';

test.describe('Welcome Page', () => {
  test('loads assets without console errors', async ({ page }) => {
    const errors = collectConsoleErrors(page);
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await assertAssetsLoadedCleanly(page, errors);
  });

  test('renders hero, features, tech stack, and footer on desktop', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/');

    // Navigation
    await expect(page.getByRole('link', { name: /log in/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /get started/i })).toBeVisible();

    // Hero
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Build your next');
    await expect(page.getByText('great application')).toBeVisible();

    // Feature cards
    await expect(page.getByText('Secure by Default')).toBeVisible();
    await expect(page.getByText('Lightning Fast')).toBeVisible();
    await expect(page.getByText('User Management')).toBeVisible();

    // Tech stack
    await expect(page.getByText('Laravel 12')).toBeVisible();
    await expect(page.getByText('React 18')).toBeVisible();
    await expect(page.getByText('TypeScript', { exact: true })).toBeVisible();
    await expect(page.getByText('Tailwind CSS v4')).toBeVisible();
    await expect(page.getByText('Inertia.js')).toBeVisible();

    // Footer
    await expect(page.getByText(/all rights reserved/i)).toBeVisible();
  });

  test('renders correctly on mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/');

    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await expect(page.getByRole('link', { name: /log in/i })).toBeVisible();
    await expect(page.getByText('Secure by Default')).toBeVisible();
  });

  test('renders correctly on tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/');

    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await expect(page.getByRole('link', { name: /log in/i })).toBeVisible();
    await expect(page.getByText('Secure by Default')).toBeVisible();
  });

  test('dark mode renders without errors', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    const errors = collectConsoleErrors(page);
    await page.goto('/');
    await enableDarkMode(page);

    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await assertDarkModeApplied(page);
    expect(errors).toHaveLength(0);
  });

  test('navigation links point to correct routes', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/');

    // Ziggy generates full URLs using APP_URL which may differ from localhost,
    // so verify the path portion only.
    const loginHref = await page.getByRole('link', { name: /log in/i }).getAttribute('href');
    expect(loginHref).toContain('/login');

    const getStartedHref = await page
      .getByRole('link', { name: /get started/i })
      .first()
      .getAttribute('href');
    expect(getStartedHref).toContain('/register');

    const startBuildingHref = await page
      .getByRole('link', { name: /start building/i })
      .getAttribute('href');
    expect(startBuildingHref).toContain('/register');
  });

  // Visual regression --------------------------------------------------------

  test('visual regression — desktop light', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('welcome-light.png', { fullPage: true });
  });

  test('visual regression — desktop dark', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await enableDarkMode(page);
    await expect(page).toHaveScreenshot('welcome-dark.png', { fullPage: true });
  });

  test('visual regression — mobile', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-mobile', 'Mobile only');
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('welcome-mobile.png', { fullPage: true });
  });

  test('visual regression — tablet', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-tablet', 'Tablet only');
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveScreenshot('welcome-tablet.png', { fullPage: true });
  });
});
