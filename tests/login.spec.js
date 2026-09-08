// @ts-check
import { test, expect } from '@playwright/test';

const LOGIN_URL = '/iis/login';
const LOGIN_ID = 'info@kric.co.jp';
const LOGIN_PASS = 'krickric';

test('ログインページが表示される', async ({ page }) => {
  await page.goto(LOGIN_URL);
  await expect(page).toHaveURL(/iis\/login/);
  await expect(page.locator('#login_id')).toBeVisible();
  await expect(page.locator('#password')).toBeVisible();
});

test('正しい認証情報でログインできる', async ({ page }) => {
  await page.goto(LOGIN_URL);
  await page.fill('#login_id', LOGIN_ID);
  await page.fill('#password', LOGIN_PASS);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/iis\/index/);
});

test('誤ったパスワードではログインできない', async ({ page }) => {
  await page.goto(LOGIN_URL);
  await page.fill('#login_id', LOGIN_ID);
  await page.fill('#password', 'wrongpassword');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/iis\/login/);
});
