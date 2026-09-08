// @ts-check
/**
 * 削除済みBD アクセステスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * ⑥ 削除済みBD の bd_detail アクセス
 *   BDA-1: 削除前は bd_detail にBD内容が表示される（ポジティブコントロール）
 *   BDA-2: del_flg=1 の後は bd_detail にBD内容が表示されない
 *
 * 仕様: IisController::bd_detail() は
 *   WHERE del_flg = 0 AND member_id = ? AND business_direct_id = ?
 * で絞り込む。del_flg=1 の場合は D_ROW が空配列になり、
 * bd_detail.ctp.html では空のタイトル・空の本文が描画される。
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const MARKER      = `__E2EBDA__${Date.now()}`;

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

test.describe('削除済みBD アクセステスト', () => {

  let ctx;
  let bdId;
  const bdTitle = `${MARKER} BDA テスト BD`;
  const bdDoc   = 'BDAテスト本文ユニーク内容';

  test.beforeAll(async ({ browser }) => {
    const memberId = queryDb(`SELECT member_id FROM member WHERE login_id = '${MEMBER_ID}'`);

    // テスト用BD を del_flg=0 で挿入
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${memberId}, '${bdTitle}', '${bdDoc}', '土地', 1, '大阪府', 0, 0, NOW(), NOW())
    `);
    bdId = queryDb(
      `SELECT business_direct_id FROM business_direct WHERE title = '${bdTitle}' LIMIT 1`
    );

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
    execDb(`DELETE FROM business_direct WHERE title = '${bdTitle}'`);
    await ctx?.close();
  });

  test('BDA-1: 削除前（del_flg=0）は bd_detail にBD内容が表示される', async () => {
    const page = await ctx.newPage();

    await page.goto(`/iis/bd_detail/id=${bdId}`);
    await page.waitForLoadState('domcontentloaded');

    const bodyText = await page.locator('body').innerText();
    expect(bodyText, 'BD タイトルが表示されること').toContain(bdTitle);
    expect(bodyText, 'BD 本文が表示されること').toContain(bdDoc);

    await page.close();
  });

  test('BDA-2: del_flg=1 の後は bd_detail にBD内容が表示されない', async () => {
    // del_flg を 1 に設定（論理削除）
    execDb(`UPDATE business_direct SET del_flg = 1 WHERE business_direct_id = ${bdId}`);

    const page = await ctx.newPage();

    await page.goto(`/iis/bd_detail/id=${bdId}`);
    await page.waitForLoadState('domcontentloaded');

    const bodyText = await page.locator('body').innerText();
    expect(bodyText, '削除済みBDのタイトルは表示されないこと').not.toContain(bdTitle);
    expect(bodyText, '削除済みBDの本文は表示されないこと').not.toContain(bdDoc);

    await page.close();
  });

});
