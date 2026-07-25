import { test, expect, Page } from '@playwright/test';
import { loginAsTestUser } from '../helpers/auth';

/** 点击展开 smartSearch，再填入关键字 */
async function searchInSmartSearch(page: Page, inputId: string, keyword: string) {
  const input = page.locator(`#${inputId}`);
  const wrapper = input.locator('xpath=ancestor::*[contains(@class,"smartSearchWrapper")][1]');
  await expect(wrapper).toBeVisible({ timeout: 20_000 });
  await wrapper.click();
  await expect(input).toBeVisible({ timeout: 5_000 });
  await input.fill(keyword);
  await input.dispatchEvent('input');
  await page.waitForTimeout(800);
}

test.describe('库存回收站 & 总库存（需登录）', () => {
  test.beforeEach(async ({ page }) => {
    test.skip(!process.env.TEST_EMAIL || !process.env.TEST_PASSWORD, '缺少 TEST_EMAIL / TEST_PASSWORD');
    await loginAsTestUser(page);
  });

  test('回收站页面加载并显示标题', async ({ page }) => {
    await page.goto('/backend/stock_recycle');
    await expect(page).toHaveURL(/stock_recycle/);
    await expect(page.getByRole('heading', { name: /回收站/ })).toBeVisible();
  });

  test('回收站搜索 SUSHI EBI 可见结果或空表', async ({ page }) => {
    await page.goto('/backend/stock_recycle');
    await expect(page.locator('table').first()).toBeVisible();
    await searchInSmartSearch(page, 'recycle-search', 'SUSHI EBI');
    await expect(page.locator('table').first()).toBeVisible();
  });

  test('J1 总库存页可打开', async ({ page }) => {
    await page.goto('/backend/stocklistall?system=j1');
    await expect(page).toHaveURL(/stocklist/);
    await expect(page.getByText(/总库存/).first()).toBeVisible({ timeout: 20_000 });
  });

  test('J1 总库存搜索 YUZU SAUCE 可定位行', async ({ page }) => {
    await page.goto('/backend/stocklistall?system=j1');
    await expect(page.locator('#j1-page.active, #j1-page')).toBeVisible({ timeout: 20_000 });

    // 确保切到 J1
    const systemLabel = page.locator('#current-system');
    if ((await systemLabel.textContent())?.trim() !== 'J1') {
      await page.locator('.system-selector .selector-button').click();
      await page.locator('#selector-dropdown .dropdown-item', { hasText: 'J1' }).click();
    }

    await searchInSmartSearch(page, 'j1-unified-filter', 'YUZU SAUCE');

    const row = page.locator('#j1-page tr', { hasText: 'YUZU SAUCE' }).first();
    await expect(row).toBeVisible({ timeout: 15_000 });
    const text = (await row.innerText()).replace(/\s+/g, ' ').trim();
    console.log('YUZU SAUCE row:', text);
    // review：当前已知单价 27.50 为负库存
    expect(text).toMatch(/YUZU SAUCE/i);
  });
});
