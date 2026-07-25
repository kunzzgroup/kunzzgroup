import { Page, expect } from '@playwright/test';

export async function loginAsTestUser(page: Page) {
  const email = process.env.TEST_EMAIL;
  const password = process.env.TEST_PASSWORD;

  if (!email || !password) {
    throw new Error('请设置环境变量 TEST_EMAIL 和 TEST_PASSWORD 后再跑登录相关测试');
  }

  await page.goto('/frontend/login');
  await page.locator('input[name="username"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.getByRole('button', { name: '登入' }).click();

  // 登录成功应离开登录页（dashboard / 首页 / 改密页均可）
  await expect(page).not.toHaveURL(/\/frontend\/login\/?$/i, { timeout: 20_000 });
}
