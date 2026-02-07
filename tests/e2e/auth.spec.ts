import { test, expect } from '@playwright/test';

test.describe('Authentication Flows', () => {
  test('unauthenticated users are redirected to login', async ({ page }) => {
    await page.goto('/dashboard');

    // Should redirect to login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('login page renders correctly', async ({ page }) => {
    await page.goto('/login');

    // Check key elements are visible
    await expect(page.getByRole('heading', { name: /welcome back/i })).toBeVisible();
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('register page renders correctly', async ({ page }) => {
    await page.goto('/register');

    // Check key elements are visible
    await expect(page.getByRole('heading', { name: /create your account/i })).toBeVisible();
    await expect(page.getByLabel(/full name/i)).toBeVisible();
    await expect(page.getByLabel(/email address/i)).toBeVisible();
    await expect(page.getByLabel(/^password$/i)).toBeVisible();
    await expect(page.getByLabel(/confirm password/i)).toBeVisible();
  });

  test('login shows validation errors for invalid email', async ({ page }) => {
    await page.goto('/login');

    // Use keyboard typing for reliable controlled-input updates in headless runs.
    const emailInput = page.getByLabel(/email address/i);
    await emailInput.click();
    await emailInput.type('foo@bar', { delay: 30 });
    await emailInput.blur();

    // Should show client-side validation error
    await expect(page.locator('#login-email-error')).toHaveText('Please enter a valid email address');
  });
});
