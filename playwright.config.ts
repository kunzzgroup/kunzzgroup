import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright E2E — 针对线上 kunzzgroup 后台库存相关页面
 * 凭据通过环境变量传入，勿写入仓库：
 *   TEST_EMAIL / TEST_PASSWORD
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  timeout: 60_000,
  expect: { timeout: 15_000 },
  use: {
    baseURL: process.env.BASE_URL || 'https://www.kunzzgroup.com',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    locale: 'zh-CN',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
