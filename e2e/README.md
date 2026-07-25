# Playwright E2E

## 安装（已完成可跳过）

```bash
npm install
npx playwright install chromium
```

## 配置登录（库存相关测试需要）

复制 `.env.example` 为 `.env`（勿提交），或临时设置：

```powershell
$env:TEST_EMAIL="你的邮箱"
$env:TEST_PASSWORD="你的密码"
$env:BASE_URL="https://www.kunzzgroup.com"
```

## 运行

```bash
npm test                 # 全部
npm run test:stock       # 仅库存
npm run test:headed      # 有界面
npm run test:ui          # Playwright UI
npm run test:report      # 打开 HTML 报告
```

## 覆盖范围

- `e2e/smoke.spec.ts`：登录页、未登录拦截
- `e2e/stock/inventory.spec.ts`：回收站、J1 总库存（需账号）
