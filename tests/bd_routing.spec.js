// @ts-check
/**
 * BD zokusei ルーティングテスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * /iis/bd02(買いたい) / /iis/bd03(貸したい) / /iis/bd04(借りたい) は
 * SQL で "WHERE zokusei=N" によってフィルタリングしている。
 * smoke.spec.js でページ表示は確認済みだが、フィルタリングの正確性は未確認。
 *
 * 対象:
 *   BZ-1: zokusei=2 のBDが /iis/bd02 に表示され /iis/bd には表示されない
 *   BZ-2: zokusei=3 のBDが /iis/bd03 に表示され /iis/bd には表示されない
 *   BZ-3: zokusei=4 のBDが /iis/bd04 に表示され /iis/bd には表示されない
 *
 * 検証戦略:
 *   - テストデータをDBに直接挿入（update_date=NOW() で最新→ページ1に表示）
 *   - 正しい一覧ページ: bd-card-more リンクが存在する
 *   - 誤った一覧ページ (/iis/bd): 同リンクが存在しない
 *     （最新データがあるにも関わらず表示されない＝SQLフィルタが機能している証明）
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const MARKER      = `__E2EBZ__${Date.now()}`;

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

const BD_CASES = [
  { zokusei: 2, listPath: 'bd02', label: '買いたい' },
  { zokusei: 3, listPath: 'bd03', label: '貸したい' },
  { zokusei: 4, listPath: 'bd04', label: '借りたい' },
];

test.describe('BD zokusei ルーティングテスト', () => {

  let ctx;
  let memberId;

  test.beforeAll(async ({ browser }) => {
    memberId = queryDb(`SELECT member_id FROM member WHERE login_id = '${MEMBER_ID}'`);

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
    execDb(`DELETE FROM business_direct WHERE title LIKE '${MARKER}%'`);
    await ctx?.close();
  });

  for (const { zokusei, listPath, label } of BD_CASES) {
    test(`BZ-${zokusei - 1}: zokusei=${zokusei}(${label}) のBDが /iis/${listPath} に表示され /iis/bd には表示されない`, async () => {
      const page = await ctx.newPage();
      const title = `${MARKER} BZ${zokusei} ${label}テスト`;

      // テストデータをDBに直接挿入（update_date=NOW() で最新順page1に来る）
      execDb(`
        INSERT INTO business_direct
          (member_id, title, doc, shubetsu, zokusei, syozaichi, kiji_flg, del_flg, create_date, update_date)
        VALUES
          (${memberId}, '${title}', 'ルーティングテスト本文', '土地', ${zokusei}, '大阪府', 0, 0, NOW(), NOW())
      `);
      const bdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${title}' LIMIT 1`);

      // DB値確認
      const dbZokusei = queryDb(`SELECT zokusei FROM business_direct WHERE business_direct_id = ${bdId}`);
      expect(parseInt(dbZokusei), `zokusei=${zokusei} がDBに正しく保存されていること`).toBe(zokusei);

      // ── 正しい一覧ページに表示されること ──────────────────────────────────────────
      await page.goto(`/iis/${listPath}`);
      await page.waitForLoadState('domcontentloaded');

      // update_date=NOW() で最新なのでページ1に表示されるはず
      const correctLink = page.locator(`a.bd-card-more[href*="bd_detail/id=${bdId}"]`);
      await expect(
        correctLink,
        `/iis/${listPath} に zokusei=${zokusei} のBD（id=${bdId}）の詳細リンクが表示されること`
      ).toBeVisible({ timeout: 5000 });

      // ── 誤った一覧ページ（zokusei=1フィルタ）には表示されないこと ────────────────
      // /iis/bd は zokusei=1 のみ。最新データにもかかわらずリンクがなければフィルタが機能している証明
      await page.goto('/iis/bd');
      await page.waitForLoadState('domcontentloaded');

      const wrongLink = page.locator(`a.bd-card-more[href*="bd_detail/id=${bdId}"]`);
      expect(
        await wrongLink.count(),
        `/iis/bd（zokusei=1専用）に zokusei=${zokusei} のBDが混入しないこと`
      ).toBe(0);

      await page.close();
    });
  }

});
