import { test, expect } from '@playwright/test';

test.describe('公开页面冒烟', () => {
  test('登录页可打开且表单完整', async ({ page }) => {
    const res = await page.goto('/frontend/login');
    expect(res?.ok()).toBeTruthy();

    await expect(page.getByRole('heading', { name: /请登入您的账号/ })).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.getByRole('button', { name: '登入' })).toBeVisible();
  });

  test('未登录访问回收站应被拦到登录', async ({ page }) => {
    await page.goto('/backend/stock_recycle');
    await expect(page).toHaveURL(/login/i, { timeout: 15_000 });
  });

  test('未登录访问总库存应被拦到登录', async ({ page }) => {
    await page.goto('/backend/stocklistall');
    await expect(page).toHaveURL(/login/i, { timeout: 15_000 });
  });
});
