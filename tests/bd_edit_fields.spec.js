// @ts-check
/**
 * BD編集フォーム フィールド保存テスト（shubetsu / syozaichi）
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * 問題: IisController.php の bd_edit UPDATE ColumnList に shubetsu / syozaichi が
 * 含まれていないため、編集フォームで変更しても DB に保存されない。
 *
 * 対象:
 *   BEF-1: shubetsu（種別）を変更して保存 → DBに反映される
 *   BEF-2: syozaichi（所在地）を変更して保存 → DBに反映される
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const MARKER      = `__E2EBEF__${Date.now()}`;

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

test.describe('BD編集フォーム フィールド保存テスト（shubetsu / syozaichi）', () => {

  let ctx;
  let bdId;
  const memberId = queryDb(`SELECT member_id FROM member WHERE login_id = '${MEMBER_ID}'`);

  const INITIAL_SHUBETSU  = '土地';
  const INITIAL_SYOZAICHI = '大阪府';

  test.beforeAll(async ({ browser }) => {
    // テスト用BD作成（shubetsu・syozaichi を明示的に設定）
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${memberId}, '${MARKER} BEF編集テスト', 'BEFテスト本文',
         '${INITIAL_SHUBETSU}', 1, '${INITIAL_SYOZAICHI}', 0, 0, NOW(), NOW())
    `);
    bdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${MARKER} BEF編集テスト' LIMIT 1`);

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

  test('BEF-1: shubetsu（種別）を変更して保存するとDBに反映される', async () => {
    const page = await ctx.newPage();
    const newShubetsu = '一戸建';

    await page.goto(`/iis/bd_edit/id=${bdId}/zo=1`);
    await page.waitForLoadState('domcontentloaded');

    // 初期値確認（テストデータが正しくロードされていること）
    const initialVal = await page.inputValue
      ? undefined
      : undefined;
    const initSelect = await page.locator('select[name="shubetsu"]').inputValue();
    expect(initSelect, '初期表示が DB の値と一致すること').toBe(INITIAL_SHUBETSU);

    // shubetsu を変更
    await page.selectOption('select[name="shubetsu"]', newShubetsu);

    // is_edit モード: 確認ステップなし、直接登録
    await page.click('button[name="cmd"][value="registration"]');
    await page.waitForLoadState('domcontentloaded');

    const dbVal = queryDb(`SELECT shubetsu FROM business_direct WHERE business_direct_id = ${bdId}`);
    expect(dbVal, `shubetsu が '${newShubetsu}' としてDBに保存されること`).toBe(newShubetsu);
    await page.close();
  });

  test('BEF-2: syozaichi（所在地）を変更して保存するとDBに反映される', async () => {
    const page = await ctx.newPage();
    const newSyozaichi = '東京都渋谷区1-1';

    await page.goto(`/iis/bd_edit/id=${bdId}/zo=1`);
    await page.waitForLoadState('domcontentloaded');

    // syozaichi フィールドが表示されていること
    const syozaichiInput = page.locator('input[name="syozaichi"]');
    await expect(syozaichiInput, 'syozaichi 入力欄が表示されること').toBeVisible();

    // syozaichi を変更
    await syozaichiInput.fill(newSyozaichi);

    // is_edit モード: 確認ステップなし、直接登録
    await page.click('button[name="cmd"][value="registration"]');
    await page.waitForLoadState('domcontentloaded');

    const dbVal = queryDb(`SELECT syozaichi FROM business_direct WHERE business_direct_id = ${bdId}`);
    expect(dbVal, `syozaichi が '${newSyozaichi}' としてDBに保存されること`).toBe(newSyozaichi);
    await page.close();
  });

});
