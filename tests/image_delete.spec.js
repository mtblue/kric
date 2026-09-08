// @ts-check
/**
 * company_edit オリジナル画像削除テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * 対象:
 *   IMG-1: upload_map が設定されているとき「削除」ボタンが表示される
 *   IMG-2: 「削除」ボタンを押すと DB の upload_map が空になる
 *   IMG-3: 削除後に再度フォームを開くと「削除」ボタンが表示されない
 *   IMG-4: upload_map が空のとき「削除」ボタンが表示されない
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const DUMMY_FILE  = 'e2e_test_dummy_compfile.jpg';

function queryDb(sql) {
  const r = spawnSync(
    'docker', ['exec', '-i', 'kric-db-1', 'mysql',
      '-uroot', '-proot', '--default-character-set=utf8mb4',
      '--skip-column-names', '_kric7'],
    { input: sql, encoding: 'utf8', stdio: 'pipe' }
  );
  return r.stdout.trim();
}

function execDb(sql) {
  spawnSync(
    'docker', ['exec', '-i', 'kric-db-1', 'mysql',
      '-uroot', '-proot', '--default-character-set=utf8mb4', '_kric7'],
    { input: sql, encoding: 'utf8', stdio: 'pipe' }
  );
}

test.describe('company_edit オリジナル画像削除テスト', () => {

  let ctx;
  let companyId;
  let origUploadMap;

  test.beforeAll(async ({ browser }) => {
    // テスト対象の company_id と元の upload_map を取得
    const row = queryDb(
      `SELECT c.company_id, COALESCE(c.upload_map, '')
       FROM member m JOIN company c ON m.company_id = c.company_id
       WHERE m.login_id = '${MEMBER_ID}'`
    );
    [companyId, origUploadMap] = row.split('\t');

    // ダミーのファイル名を DB にセット（実ファイルは不要。削除処理はファイル不在でもDB更新する）
    execDb(`UPDATE company SET upload_map = '${DUMMY_FILE}' WHERE company_id = ${companyId}`);

    // ログイン
    ctx = await browser.newContext();
    const page = await ctx.newPage();
    await page.goto('/iis/login');
    await page.waitForLoadState('domcontentloaded');
    await page.fill('#login_id', MEMBER_ID);
    await page.fill('#password', MEMBER_PASS);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');
    await page.close();
  });

  test.afterAll(async () => {
    // 元の upload_map を復元
    const restoreVal = origUploadMap ? `'${origUploadMap}'` : 'NULL';
    execDb(`UPDATE company SET upload_map = ${restoreVal} WHERE company_id = ${companyId}`);
    await ctx?.close();
  });

  // ── IMG-1: upload_map が設定されているとき「削除」ボタンが表示される ──────────
  test('IMG-1: upload_map が設定されているとき「削除」ボタンが表示される', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    const delBtn = page.locator('#deleteCurrentImg');
    await expect(delBtn, '既存画像の「削除」ボタンが表示されること').toBeVisible({ timeout: 5000 });

    await page.close();
  });

  // ── IMG-2: 「削除」ボタンを押すと DB の upload_map が空になる ────────────────
  test('IMG-2: 「削除」ボタンを押すと DB の upload_map が空になる', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    const delBtn = page.locator('#deleteCurrentImg');
    await expect(delBtn, '削除ボタンが存在すること').toBeVisible({ timeout: 5000 });
    await delBtn.click();
    await page.waitForLoadState('domcontentloaded');

    const dbVal = queryDb(`SELECT COALESCE(upload_map, '') FROM company WHERE company_id = ${companyId}`);
    expect(dbVal, 'DBの upload_map が空になっていること').toBe('');

    await page.close();
  });

  // ── IMG-3: 削除後に再度フォームを開くと「削除」ボタンが表示されない ────────────
  test('IMG-3: 削除後に再度フォームを開くと「削除」ボタンが表示されない', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    const delBtn = page.locator('#deleteCurrentImg');
    await expect(delBtn, '削除後はボタンが非表示であること').not.toBeVisible();

    await page.close();
  });

  // ── IMG-4: upload_map が空のとき「削除」ボタンが表示されない ──────────────────
  test('IMG-4: upload_map が空のとき「削除」ボタンが表示されない（DBが空の初期状態）', async () => {
    // IMG-2 で upload_map は既に空になっているのでそのまま利用
    const dbVal = queryDb(`SELECT COALESCE(upload_map, '') FROM company WHERE company_id = ${companyId}`);
    expect(dbVal, '前提: DBが空であること').toBe('');

    const page = await ctx.newPage();
    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    const delBtn = page.locator('#deleteCurrentImg');
    await expect(delBtn, 'upload_map が空なら削除ボタンは表示されないこと').not.toBeVisible();

    await page.close();
  });

});
