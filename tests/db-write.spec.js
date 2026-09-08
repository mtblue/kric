// @ts-check
/**
 * DB書き込みテスト
 * ブラウザ操作で登録→確認→完了の流れを検証し、後始末として物理DELETE。
 * Docker環境（localhost:8080）専用。本番には絶対に実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { execSync } from 'child_process';

const LOGIN_ID   = 'info@kric.co.jp';
const LOGIN_PASS = 'krickric';

// テストデータを識別するマーカー（実行ごとにユニーク）
const RUN_ID = Date.now();
const MARKER = `__E2E__${RUN_ID}`;

function dbCleanup(sql) {
  try {
    execSync(
      `docker exec kric-db-1 mysql -uroot -proot --default-character-set=utf8mb4 _kric7 -e "${sql}"`,
      { stdio: 'pipe' }
    );
  } catch (e) {
    console.warn('DB cleanup warning:', e.stderr?.toString() ?? e.message);
  }
}

// ============================================================
// ビジネスダイレクト 新規投稿 → 確認 → 登録 → 物理削除
// ============================================================
test.describe('ビジネスダイレクト新規投稿', () => {
  let context;
  const testTitle = `${MARKER} BDテスト`;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const loginPage = await context.newPage();
    await loginPage.goto('/iis/login');
    await loginPage.fill('#login_id', LOGIN_ID);
    await loginPage.fill('#password', LOGIN_PASS);
    await loginPage.click('button[type="submit"]');
    await loginPage.waitForURL(/iis\/index/);
    await loginPage.close();
  });

  test.afterAll(async () => {
    dbCleanup(`DELETE FROM business_direct WHERE title LIKE '__E2E__%'`);
    await context.close();
  });

  test('入力→確認→登録 が正常に完了する', async () => {
    const page = await context.newPage();

    // 1. 新規登録フォームを開く
    const formResp = await page.goto('/iis/bd_edit/zo=1');
    expect(formResp.status()).toBe(200);

    // 2. 必須項目を入力（doc/備考は必須項目）
    await page.fill('input[name="title"]', testTitle);
    await page.selectOption('select[name="zokusei"]', '1');   // 売りたい
    await page.selectOption('select[name="shubetsu"]', '土地');
    await page.fill('input[name="syozaichi"]', 'テスト大阪市中央区');
    await page.fill('textarea[name="doc"]', 'E2Eテスト用の備考');

    // 3. 「確認」ボタンを押す
    await page.click('button[name="cmd"][value="confirm"]');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面: エラーなし・#btn-reg が存在すること（バリデーションで差し戻された場合は存在しない）
    await expect(page.locator('#btn-reg'), '確認画面に登録ボタンが表示されること').toBeVisible();
    const confirmBody = await page.content();
    expect(confirmBody, '確認画面にタイトルが表示されること').toContain(testTitle);
    expect(confirmBody).not.toMatch(/Fatal error|Missing View|内部サーバーエラー/i);

    // 4. 「登録」ボタンを押す（#btn-reg はJS でdisable→submit）
    await page.click('#btn-reg');
    await page.waitForLoadState('domcontentloaded');

    // 5. BD一覧にリダイレクトされること
    expect(page.url(), '登録後にBD一覧へリダイレクト').toMatch(/iis\/(bd|index)/);

    // 6. エラーなし
    const listBody = await page.content();
    expect(listBody).not.toMatch(/Fatal error|Missing View|内部サーバーエラー/i);

    await page.close();
  });
});

// ============================================================
// Admin インフォメーション 新規投稿 → 確認 → 登録 → 物理削除
// ============================================================
test.describe('インフォメーション新規投稿（Admin）', () => {
  let context;
  const testTitle = `${MARKER} Infoテスト`;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const loginPage = await context.newPage();
    await loginPage.goto('/admin/login');
    await loginPage.fill('#id', LOGIN_ID);
    await loginPage.fill('#pass', LOGIN_PASS);
    await loginPage.click('button[type="submit"]');
    await loginPage.waitForURL(/admin\//);
    await loginPage.close();
  });

  test.afterAll(async () => {
    dbCleanup(`DELETE FROM information WHERE title LIKE '__E2E__%'`);
    await context.close();
  });

  test('入力→確認→登録 が正常に完了する', async () => {
    const page = await context.newPage();

    // 1. 新規登録フォームを開く
    const formResp = await page.goto('/admin/information_edit');
    expect(formResp.status()).toBe(200);

    // 2. 必須項目を入力
    await page.fill('input[name="title"]', testTitle);
    await page.fill('textarea[name="doc"]', 'E2Eテスト用のお知らせ内容');

    // 3. 「確認」ボタンを押す（class="reg-submit-btn"）
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面: タイトルが表示されエラーなし
    const confirmBody = await page.content();
    expect(confirmBody, '確認画面にタイトルが表示されること').toContain(testTitle);
    expect(confirmBody).not.toMatch(/Fatal error|Missing View|内部サーバーエラー/i);

    // 4. 「登録」ボタンを押す（class="bd-reg-submit-btn"）
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      page.click('button.bd-reg-submit-btn'),
    ]);

    // 5. Admin indexにリダイレクトされること
    expect(page.url(), '登録後にAdmin一覧へリダイレクト').toMatch(/admin\/(index)?/);

    // 6. エラーなし
    const listBody = await page.content();
    expect(listBody).not.toMatch(/Fatal error|Missing View|内部サーバーエラー/i);

    await page.close();
  });
});
