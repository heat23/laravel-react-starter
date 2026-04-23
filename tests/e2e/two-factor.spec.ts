import { test, expect, type Page } from '@playwright/test';
import { createHmac } from 'crypto';
import { execSync } from 'child_process';

// ---------------------------------------------------------------------------
// Inline TOTP (RFC 6238) — no extra dependency needed
// ---------------------------------------------------------------------------

function base32Decode(s: string): Buffer {
  const ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  const bits: number[] = [];
  for (const c of s.toUpperCase().replace(/=+$/, '')) {
    const v = ALPHA.indexOf(c);
    if (v < 0) continue;
    for (let i = 4; i >= 0; i--) bits.push((v >> i) & 1);
  }
  const bytes: number[] = [];
  for (let i = 0; i + 7 < bits.length; i += 8) {
    bytes.push(bits.slice(i, i + 8).reduce((a, b) => (a << 1) | b, 0));
  }
  return Buffer.from(bytes);
}

function generateTOTP(secret: string, timestamp?: number): string {
  const counter = Math.floor((timestamp ?? Date.now()) / 1000 / 30);
  const key = base32Decode(secret);
  const buf = Buffer.alloc(8);
  buf.writeBigInt64BE(BigInt(counter));
  const hmac = createHmac('sha1', key).update(buf).digest();
  const offset = hmac[19] & 0xf;
  const code =
    (((hmac[offset] & 0x7f) << 24) |
      (hmac[offset + 1] << 16) |
      (hmac[offset + 2] << 8) |
      hmac[offset + 3]) %
    1_000_000;
  return code.toString().padStart(6, '0');
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const TEST_PASS = process.env.E2E_TEST_PASS ?? 'Password123!';

function uniqueEmail(): string {
  return `e2e-2fa-${Date.now()}@test.invalid`;
}

async function isTwoFactorEnabled(baseURL: string): Promise<boolean> {
  try {
    const res = await fetch(`${baseURL}/two-factor-challenge`);
    return res.status !== 404;
  } catch {
    return false;
  }
}

async function registerUser(page: Page, email: string): Promise<void> {
  await page.goto('/register');
  await page.getByLabel(/full name/i).fill('2FA Test User');
  await page.getByLabel(/email address/i).fill(email);
  await page.getByLabel(/^password$/i).fill(TEST_PASS);
  await page.getByLabel(/confirm password/i).fill(TEST_PASS);
  const termsCheckbox = page.getByLabel(/i agree/i);
  if (await termsCheckbox.isVisible()) {
    await termsCheckbox.check();
  }
  await page.getByRole('button', { name: /create account/i }).click();
  await page.waitForURL(/\/dashboard|\//);
}

async function loginUser(page: Page, email: string): Promise<void> {
  await page.goto('/login');
  await page.getByLabel(/email address/i).fill(email);
  await page.getByLabel(/^password$/i).fill(TEST_PASS);
  await page.getByRole('button', { name: /^log in$/i }).click();
}

async function logoutUser(page: Page): Promise<void> {
  await page.getByRole('button', { name: /user menu/i }).click();
  await page.getByRole('button', { name: /log out/i }).click();
  await page.waitForURL(/\/login/);
}

function artisan(cmd: string): string {
  return execSync(`php artisan ${cmd}`, { cwd: process.cwd() }).toString().trim();
}

function seedVerifiedUser(email: string): void {
  artisan(
    `tinker --no-interaction --execute="` +
      `App\\\\Models\\\\User::factory()->create([` +
      `'email'=>'${email}',` +
      `'name'=>'2FA Test',` +
      `'email_verified_at'=>now()]);"`,
  );
}

function markEmailVerified(email: string): void {
  artisan(
    `tinker --no-interaction --execute="` +
      `App\\\\Models\\\\User::where('email','${email}')->update(['email_verified_at'=>now()]);"`,
  );
}

function seedUserWith2FA(email: string): string {
  return artisan(
    `tinker --no-interaction --execute="` +
      `\\$u=App\\\\Models\\\\User::factory()->create([` +
      `'email'=>'${email}','name'=>'2FA Test','email_verified_at'=>now()]);` +
      `\\$u->createTwoFactorAuth();\\$u->twoFactorAuth->enable();` +
      `echo \\$u->twoFactorAuth->toString();"`,
  );
}

// ---------------------------------------------------------------------------
// Suite
// ---------------------------------------------------------------------------

test.describe('Two-Factor Authentication', () => {
  let featureEnabled = false;

  test.beforeAll(async ({}, testInfo) => {
    featureEnabled = await isTwoFactorEnabled(
      testInfo.project.use.baseURL ?? 'http://localhost:8000',
    );
  });

  // ── Unauthenticated ───────────────────────────────────────────────────────

  test('challenge page redirects to login when accessed without a 2FA session', async ({ page }) => {
    test.skip(!featureEnabled, 'FEATURE_TWO_FACTOR disabled in this environment');

    await page.goto('/two-factor-challenge');
    await expect(page).toHaveURL(/\/login/);
  });

  // ── Challenge page UI ─────────────────────────────────────────────────────

  test('challenge page renders TOTP and recovery code UI', async ({ page }, testInfo) => {
    test.skip(!featureEnabled, 'FEATURE_TWO_FACTOR disabled in this environment');
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');

    const email = uniqueEmail();
    const secret = seedUserWith2FA(email);

    await loginUser(page, email);
    await page.waitForURL(/\/two-factor-challenge/);

    await expect(page.getByRole('heading', { name: /two-factor authentication/i })).toBeVisible();
    await expect(page.locator('#code')).toBeVisible();
    await expect(page.getByRole('button', { name: /verify/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /use a recovery code/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /return to login/i })).toBeVisible();

    // Switch to recovery mode
    await page.getByRole('button', { name: /use a recovery code/i }).click();
    await expect(page.getByLabel(/recovery code/i)).toBeVisible();
    await expect(page.getByText(/emergency recovery codes/i)).toBeVisible();

    // Switch back
    await page.getByRole('button', { name: /use authentication code instead/i }).click();
    await expect(page.locator('#code')).toBeVisible();

    // Enter valid TOTP → lands on dashboard
    const code = generateTOTP(secret);
    await page.locator('#code').first().click();
    for (const digit of code) {
      await page.keyboard.type(digit);
    }
    await page.getByRole('button', { name: /verify/i }).click();
    await page.waitForURL(/\/dashboard/);
  });

  // ── Settings security page ────────────────────────────────────────────────

  test('settings security page shows 2FA section when feature is enabled', async ({ page }, testInfo) => {
    test.skip(!featureEnabled, 'FEATURE_TWO_FACTOR disabled in this environment');
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');

    const email = uniqueEmail();
    await registerUser(page, email);
    markEmailVerified(email);

    await page.goto('/settings/security');

    await expect(page.getByRole('heading', { name: /two-factor authentication/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /enable two-factor authentication/i })).toBeVisible();
    await expect(page.getByText(/not enabled/i)).toBeVisible();
  });

  // ── Full enrollment + challenge flow ──────────────────────────────────────

  test('full flow: enroll, log out, log back in, pass challenge', async ({ page }, testInfo) => {
    test.skip(!featureEnabled, 'FEATURE_TWO_FACTOR disabled in this environment');
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Desktop only');

    const email = uniqueEmail();
    await registerUser(page, email);
    markEmailVerified(email);

    // ── 1. Navigate to Settings > Security and enable 2FA ──
    await page.goto('/settings/security');
    await page.getByRole('button', { name: /enable two-factor authentication/i }).click();

    await expect(page.getByText(/set up two-factor authentication/i)).toBeVisible();
    await expect(page.getByText(/manual entry key/i)).toBeVisible();

    // Read the secret displayed in the <code> element
    const secret = (await page.locator('code.font-mono').innerText()).trim();
    expect(secret.length).toBeGreaterThan(10);

    // ── 2. Confirm enrollment with the current TOTP code ──
    const enrollCode = generateTOTP(secret);
    await page.locator('#code').first().click();
    for (const digit of enrollCode) {
      await page.keyboard.type(digit);
    }
    await page.getByRole('button', { name: /confirm/i }).click();

    await expect(page.getByText(/two-factor authentication has been enabled/i)).toBeVisible();

    // ── 3. Log out ──
    await logoutUser(page);

    // ── 4. Log back in — should redirect to challenge ──
    await loginUser(page, email);
    await page.waitForURL(/\/two-factor-challenge/);

    // ── 5. Pass TOTP challenge → reach dashboard ──
    const challengeCode = generateTOTP(secret);
    await page.locator('#code').first().click();
    for (const digit of challengeCode) {
      await page.keyboard.type(digit);
    }
    await page.getByRole('button', { name: /verify/i }).click();
    await page.waitForURL(/\/dashboard/);

    await expect(page).toHaveURL(/\/dashboard/);
  });
});
