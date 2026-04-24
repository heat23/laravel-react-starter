import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8000';
const shouldStartLocalServer = /localhost:8000|127\.0\.0\.1:8000/.test(baseURL);

export default defineConfig({
  testDir: './tests/e2e',
  outputDir: './tests/e2e/results',
  snapshotPathTemplate:
    '{testDir}/__screenshots__/{testFilePath}/{projectName}/{arg}{ext}',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  expect: {
    toHaveScreenshot: {
      // Font rendering differs between macOS (local) and Linux (CI).
      // 8% ratio tolerates antialiasing, font rendering, and minor layout differences across platforms.
      // Some mobile viewports may render at slightly different pixel densities.
      maxDiffPixelRatio: 0.08,
    },
  },
  use: {
    baseURL,
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium-desktop',
      use: {
        ...devices['Desktop Chrome'],
        deviceScaleFactor: 1,
      },
    },
    {
      name: 'chromium-tablet',
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 768, height: 1024 },
        deviceScaleFactor: 1,
      },
    },
    {
      name: 'chromium-mobile',
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 375, height: 667 },
        deviceScaleFactor: 2,
        isMobile: true,
        hasTouch: true,
      },
    },
  ],
  webServer: shouldStartLocalServer
    ? {
        command: 'php artisan serve',
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        env: {
          // Register-page specs and visual regressions assert the inline "Full name" field,
          // which only renders when the onboarding wizard is off.
          FEATURE_ONBOARDING: 'false',
        },
      }
    : undefined,
});
