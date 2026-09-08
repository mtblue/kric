// @ts-check
/**
 * company_edit 追加フィールド 保存テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * ④ ken（都道府県）保存テスト
 *   CKN-1: select[name="ken"] を変更して保存するとDBに反映される
 *   CKN-2: 保存後に編集フォームを開くと ken の選択値が初期表示される
 *
 * ⑦ tel / daihyousha / menkyo フィールド保存テスト
 *   CTF-1: tel（電話番号）を変更して保存するとDBに反映される
 *   CTF-2: daihyousha（代表者名）を変更して保存するとDBに反映される
 *   CTF-3: menkyo（免許番号）を変更して保存するとDBに反映される
 *   CTF-4: 保存後に編集フォームを開くと tel/daihyousha/menkyo が初期表示される
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';

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

test.describe('company_edit 追加フィールド 保存テスト', () => {

  let ctx;
  let companyId;
  let origKen, origTel, origDaihyousha, origMenkyo;

  test.beforeAll(async ({ browser }) => {
    // 変更前の値を保存（afterAll で復元するため）
    const row = queryDb(`
      SELECT c.company_id,
             COALESCE(c.ken, 0),
             COALESCE(c.tel, ''),
             COALESCE(c.daihyousha, ''),
             COALESCE(c.menkyo, '')
      FROM member m JOIN company c ON m.company_id = c.company_id
      WHERE m.login_id = '${MEMBER_ID}'
    `);
    [companyId, origKen, origTel, origDaihyousha, origMenkyo] = row.split('\t');

    // ログイン（セッション確立）
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
    // 全フィールドを元の値に戻す
    const e = (s) => s.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    execDb(`
      UPDATE company SET
        ken        = ${parseInt(origKen) || 0},
        tel        = '${e(origTel)}',
        daihyousha = '${e(origDaihyousha)}',
        menkyo     = '${e(origMenkyo)}'
      WHERE company_id = ${companyId}
    `);
    await ctx?.close();
  });

  // フォーム経由で保存するヘルパー（入力→確認→登録）
  async function submitCompanyEdit(page, fillFn) {
    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');
    await expect(page.locator('input[name="company_name"]'), '初期表示: company_name').not.toBeEmpty();

    await fillFn(page);

    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認画面に遷移し登録ボタンが表示されること').toBeVisible({ timeout: 5000 });
    await regBtn.click();
    await page.waitForLoadState('domcontentloaded');
  }

  // ── ④ ken（都道府県）───────────────────────────────────────────────────────

  test('CKN-1: ken（都道府県）を変更して保存するとDBに反映される', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    // 現在の選択値を取得
    const currentKen = await page.locator('select[name="ken"]').inputValue();

    // 利用可能な有効オプション値を動的に取得（空白・空値以外）
    const availableValues = await page.locator('select[name="ken"] option').evaluateAll(
      opts => opts.map(o => o.value).filter(v => v !== '' && v !== '0')
    );
    expect(availableValues.length, '都道府県選択肢が1つ以上存在すること').toBeGreaterThan(1);

    // 現在値と異なる有効な都道府県コードを選ぶ
    const newKen = availableValues.find(v => v !== currentKen);

    await page.selectOption('select[name="ken"]', newKen);
    // 選択した都道府県のラベルを取得
    const kenLabel = await page.locator(`select[name="ken"] option[value="${newKen}"]`).innerText();

    // 確認
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面に都道府県名が表示されること
    const confirmText = await page.locator('body').innerText();
    expect(confirmText, `確認画面に ${kenLabel} が表示されること`).toContain(kenLabel.trim());

    // 登録
    await page.locator('button.bd-reg-submit-btn').click();
    await page.waitForLoadState('domcontentloaded');

    // DB 確認
    const dbKen = queryDb(`SELECT ken FROM company WHERE company_id = ${companyId}`);
    expect(dbKen, `ken が '${newKen}' としてDBに保存されること`).toBe(newKen);

    await page.close();
  });

  test('CKN-2: 保存後に編集フォームを開くと ken の選択値が初期表示される', async () => {
    const page = await ctx.newPage();

    // CKN-1 で保存した ken をDB から取得
    const savedKen = queryDb(`SELECT ken FROM company WHERE company_id = ${companyId}`);

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    const formKen = await page.locator('select[name="ken"]').inputValue();
    expect(formKen, `ken の初期表示がDB値 '${savedKen}' と一致すること`).toBe(savedKen);

    await page.close();
  });

  // ── ⑦ tel / daihyousha / menkyo ────────────────────────────────────────────

  test('CTF-1: tel（電話番号）を変更して保存するとDBに反映される', async () => {
    const page = await ctx.newPage();
    const newTel = '06-9999-0001';

    await submitCompanyEdit(page, async (p) => {
      await p.fill('input[type="tel"][name="tel"]', newTel);
    });

    const dbTel = queryDb(`SELECT COALESCE(tel,'') FROM company WHERE company_id = ${companyId}`);
    expect(dbTel, `tel が '${newTel}' としてDBに保存されること`).toBe(newTel);
    await page.close();
  });

  test('CTF-2: daihyousha（代表者名）を変更して保存するとDBに反映される', async () => {
    const page = await ctx.newPage();
    const newDaihyousha = 'CTFテスト 代表者';

    await submitCompanyEdit(page, async (p) => {
      await p.fill('input[name="daihyousha"]', newDaihyousha);
    });

    const dbVal = queryDb(`SELECT COALESCE(daihyousha,'') FROM company WHERE company_id = ${companyId}`);
    expect(dbVal, `daihyousha が '${newDaihyousha}' としてDBに保存されること`).toBe(newDaihyousha);
    await page.close();
  });

  test('CTF-3: menkyo（免許番号）を変更して保存するとDBに反映される', async () => {
    const page = await ctx.newPage();
    const newMenkyo = 'CTFテスト 大阪府知事（テスト）第9999999号';

    await submitCompanyEdit(page, async (p) => {
      await p.fill('input[name="menkyo"]', newMenkyo);
    });

    const dbVal = queryDb(`SELECT COALESCE(menkyo,'') FROM company WHERE company_id = ${companyId}`);
    expect(dbVal, `menkyo が '${newMenkyo}' としてDBに保存されること`).toBe(newMenkyo);
    await page.close();
  });

  test('CTF-4: 保存後に編集フォームを開くと tel/daihyousha/menkyo が初期表示される', async () => {
    const page = await ctx.newPage();

    // CTF-1〜3 で保存した値をDBから取得
    const row = queryDb(`
      SELECT COALESCE(tel,''), COALESCE(daihyousha,''), COALESCE(menkyo,'')
      FROM company WHERE company_id = ${companyId}
    `);
    const [dbTel, dbDaihyousha, dbMenkyo] = row.split('\t');

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    expect(
      await page.locator('input[type="tel"][name="tel"]').inputValue(),
      `tel の初期表示が '${dbTel}' であること`
    ).toBe(dbTel);
    expect(
      await page.locator('input[name="daihyousha"]').inputValue(),
      `daihyousha の初期表示が '${dbDaihyousha}' であること`
    ).toBe(dbDaihyousha);
    expect(
      await page.locator('input[name="menkyo"]').inputValue(),
      `menkyo の初期表示が '${dbMenkyo}' であること`
    ).toBe(dbMenkyo);

    await page.close();
  });

});
