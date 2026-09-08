// @ts-check
/**
 * 認証・アクセス制御テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * ① ログイン失敗テスト
 *   LGN-1: 誤ったパスワードでログインするとエラーメッセージが表示される
 *   LGN-2: 存在しないログインIDでログインするとエラーメッセージが表示される
 *
 * ② 未ログイン時のアクセス制御
 *   UAC-1〜3: 保護URLへのセッションなしアクセスでホームへリダイレクトされる
 *
 * ⑤ ログアウト後のセッション保護
 *   LGO-1: ログアウト後に保護URLへアクセスするとリダイレクトされる
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';

// ── ① ログイン失敗テスト ─────────────────────────────────────────────────────────

test('LGN-1: 誤ったパスワードでログインするとエラーメッセージが表示される', async ({ browser }) => {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  await page.goto('/iis/login');
  await page.waitForLoadState('domcontentloaded');
  await page.fill('#login_id', MEMBER_ID);
  await page.fill('#password', 'wrongpassword_notreal_xyz999');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');

  // ログインページに留まること
  expect(page.url(), 'ログインページのままであること').toContain('/iis/login');
  // エラーメッセージが表示されること
  await expect(page.locator('.mem-login-msg'), 'エラーメッセージが表示されること').toBeVisible();
  const msg = await page.locator('.mem-login-msg').innerText();
  expect(msg, 'エラーメッセージの文言確認').toContain('ＩＤまたはパスワードが間違っています');

  await ctx.close();
});

test('LGN-2: ログインIDが空で送信するとバリデーションエラーが表示される', async ({ browser }) => {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  await page.goto('/iis/login');
  await page.waitForLoadState('domcontentloaded');
  // ログインID を空のまま（fill しない）、パスワードのみ入力
  await page.fill('#password', 'somepassword');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');

  // 空のログインIDはサーバーバリデーションでエラーになること
  await expect(page.locator('.mem-login-msg'), 'バリデーションエラーが表示されること').toBeVisible();

  await ctx.close();
});

// ── ② 未ログイン時のアクセス制御 ──────────────────────────────────────────────────

const PROTECTED_PATHS = [
  [1, '/iis/company_edit', 'company_editフォーム（input[name="company_name"]）'],
  [2, '/iis/bd_edit',      'bd_editフォーム（input[name="title"]）'],
  [3, '/iis/member_edit',  'member_editフォーム（input[name="name"]）'],
];

for (const [n, path, formDesc] of PROTECTED_PATHS) {
  test(`UAC-${n}: 未ログインで ${path} にアクセスするとホームへリダイレクトされる`, async ({ browser }) => {
    const ctx = await browser.newContext(); // セッションなし（新規コンテキスト）
    const page = await ctx.newPage();

    await page.goto(path);
    await page.waitForLoadState('domcontentloaded');

    // /iis/ 配下でなければリダイレクト済み
    expect(page.url(), `/iis/ 配下からリダイレクトされること`).not.toContain('/iis/');

    await ctx.close();
  });
}

// ── ⑤ ログアウト後のセッション保護 ────────────────────────────────────────────────

test('LGO-1: ログアウト後に /iis/company_edit にアクセスするとリダイレクトされる', async ({ browser }) => {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  // ログイン
  await page.goto('/iis/login');
  await page.waitForLoadState('domcontentloaded');
  await page.fill('#login_id', MEMBER_ID);
  await page.fill('#password', MEMBER_PASS);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');
  expect(page.url(), 'ログイン後は /iis/login でないこと').not.toContain('/iis/login');

  // 保護ページにアクセスできることを確認
  await page.goto('/iis/company_edit');
  await page.waitForLoadState('domcontentloaded');
  expect(
    await page.locator('input[name="company_name"]').count(),
    'ログイン中は company_edit フォームが表示されること'
  ).toBeGreaterThan(0);

  // ログアウト
  await page.goto('/iis/logout');
  await page.waitForLoadState('domcontentloaded');

  // ログアウト後に保護URLへアクセス → リダイレクトされること
  await page.goto('/iis/company_edit');
  await page.waitForLoadState('domcontentloaded');

  expect(page.url(), 'ログアウト後は /iis/ 配下からリダイレクトされること').not.toContain('/iis/');
  expect(
    await page.locator('input[name="company_name"]').count(),
    'ログアウト後は company_edit フォームが表示されないこと'
  ).toBe(0);

  await ctx.close();
});
