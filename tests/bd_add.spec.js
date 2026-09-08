// @ts-check
/**
 * BD 追記モード（kiji_flg=1）テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * 追記モードの仕様:
 *   - URL: /iis/bd_edit/addid=X（X=親BD の business_direct_id）
 *   - 入力: doc のみ（title='追記'固定、shubetsu/syozaichi/zokusei は親から継承）
 *   - フロー: 入力 → 確認 → 登録（新規登録と同じ2ステップ）
 *   - chkno による二重送信保護あり（フォームタグ直下の共通 hidden field）
 *   - 他会員の BD に対する追記はサーバー側 member_id チェックでブロック
 *
 * 対象テスト:
 *   BKK-1: 追記フォームが正しく表示される（kiji_flg=1 が hidden で設定）
 *   BKK-2: 入力→確認→登録 正常系（DB 全フィールド確認）
 *   BKK-3: 登録後の追記が bd_detail ページに表示される
 *   BKK-4: 確認→戻る で doc が入力フォームに引き継がれる
 *   BKK-5: 他会員の BD への追記はブロックされる（認可）
 *   BKK-6: 二重送信はブロックされる（chkno トークン検証）
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const MARKER      = `__E2EBKK__${Date.now()}`;

// 親BD の初期値（追記で継承されることを検証するため明示的に設定）
const PARENT_SHUBETSU  = '一戸建';
const PARENT_SYOZAICHI = '京都府伏見区';
const PARENT_ZOKUSEI   = 1; // 売りたい

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

test.describe('BD 追記モード（kiji_flg=1）テスト', () => {

  let ctx;
  let parentBdId;
  let otherBdId;

  const memberId = queryDb(`SELECT member_id FROM member WHERE login_id = '${MEMBER_ID}'`);
  // テスト用に「別会員」のIDを取得（認可テスト用）
  const otherMemberId = queryDb(`SELECT member_id FROM member WHERE login_id != '${MEMBER_ID}' AND del_flg = 0 LIMIT 1`);

  test.beforeAll(async ({ browser }) => {
    // 親BD（追記元）を作成
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${memberId}, '${MARKER} BKK親BD', '元記事本文',
         '${PARENT_SHUBETSU}', ${PARENT_ZOKUSEI}, '${PARENT_SYOZAICHI}', 0, 0, NOW(), NOW())
    `);
    parentBdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${MARKER} BKK親BD' LIMIT 1`);

    // BKK-5 用: 別会員の BD を作成
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${otherMemberId}, '${MARKER} BKK5他会員BD', '他会員の記事', '土地', 1, '東京都', 0, 0, NOW(), NOW())
    `);
    otherBdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${MARKER} BKK5他会員BD' LIMIT 1`);

    // ログイン（テストユーザー）
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
    // 追記レコード → 親BD の順に削除（opener_id 参照のため先に追記を消す）
    if (parentBdId) {
      execDb(`DELETE FROM business_direct WHERE opener_id = ${parentBdId}`);
    }
    execDb(`DELETE FROM business_direct WHERE title LIKE '${MARKER}%'`);
    await ctx?.close();
  });

  // ────────────────────────────────────────────────────────────
  // BKK-1: 追記フォーム表示確認
  // ────────────────────────────────────────────────────────────
  test('BKK-1: 追記フォームが正しく表示される（kiji_flg=1 が hidden で設定されている）', async () => {
    const page = await ctx.newPage();

    await page.goto(`/iis/bd_edit/addid=${parentBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // ページラベル「追記登録」が表示される
    const bodyText = await page.locator('body').innerText();
    expect(bodyText, '「追記登録」というページラベルが表示されること').toContain('追記登録');

    // 追記内容テキストエリアが存在する
    await expect(page.locator('textarea[name="doc"]'), '追記内容の textarea が表示されること').toBeVisible();

    // kiji_flg=1 が hidden field に設定されている
    const kijiFLgValue = await page.locator('input[name="kiji_flg"]').inputValue();
    expect(kijiFLgValue, 'kiji_flg が 1 に設定されていること').toBe('1');

    // opener_id が親BD の ID に設定されている
    const openerIdValue = await page.locator('input[name="opener_id"]').inputValue();
    expect(openerIdValue, 'opener_id が親BD の ID に設定されていること').toBe(parentBdId);

    await page.close();
  });

  // ────────────────────────────────────────────────────────────
  // BKK-2: 入力→確認→登録 正常系（DB 全フィールド確認）
  // ────────────────────────────────────────────────────────────
  test('BKK-2: 追記を入力→確認→登録するとDBに全フィールドが正しく保存される', async () => {
    const page = await ctx.newPage();
    const testDoc = `${MARKER} BKK2 追記テスト内容`;

    await page.goto(`/iis/bd_edit/addid=${parentBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // 元記事タイトルが確認として表示されること
    const bodyText = await page.locator('body').innerText();
    expect(bodyText, '元記事タイトルがフォームに表示されること').toContain('BKK親BD');

    // 追記内容を入力
    await page.fill('textarea[name="doc"]', testDoc);

    // 確認画面へ
    await page.click('button[name="cmd"][value="confirm"]');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面: 追記内容が表示されていること
    const confirmText = await page.locator('body').innerText();
    expect(confirmText, '確認画面に入力した追記内容が表示されること').toContain(testDoc);
    expect(confirmText, '確認画面に「追記確認」というラベルが表示されること').toContain('追記確認');

    // 登録ボタンが表示されていること
    await expect(page.locator('#btn-reg'), '登録ボタンが表示されること').toBeVisible();

    // 登録実行
    await page.click('#btn-reg');
    await page.waitForLoadState('domcontentloaded');

    // DB 確認: 追記レコードが1件存在すること
    const dbRow = queryDb(`
      SELECT kiji_flg, opener_id, title, doc, shubetsu, syozaichi, zokusei
      FROM business_direct
      WHERE opener_id = ${parentBdId} AND kiji_flg = 1
      LIMIT 1
    `);
    expect(dbRow, 'DB に追記レコードが存在すること').not.toBe('');

    const [dbKijiFLg, dbOpenerId, dbTitle, dbDoc, dbShubetsu, dbSyozaichi, dbZokusei] = dbRow.split('\t');

    expect(dbKijiFLg,   'kiji_flg = 1 が保存されること').toBe('1');
    expect(dbOpenerId,  `opener_id = ${parentBdId} が保存されること`).toBe(parentBdId);
    expect(dbTitle,     "title = '追記' が保存されること").toBe('追記');
    expect(dbDoc,       '入力した追記内容が保存されること').toBe(testDoc);
    expect(dbShubetsu,  `shubetsu が親BD の '${PARENT_SHUBETSU}' を継承すること`).toBe(PARENT_SHUBETSU);
    expect(dbSyozaichi, `syozaichi が親BD の '${PARENT_SYOZAICHI}' を継承すること`).toBe(PARENT_SYOZAICHI);
    expect(dbZokusei,   `zokusei が親BD の ${PARENT_ZOKUSEI} を継承すること`).toBe(String(PARENT_ZOKUSEI));

    await page.close();
  });

  // ────────────────────────────────────────────────────────────
  // BKK-3: 追記が bd_detail に表示される（BKK-2 の登録後）
  // ────────────────────────────────────────────────────────────
  test('BKK-3: 登録した追記内容が bd_detail ページに表示される', async () => {
    const page = await ctx.newPage();
    // BKK-2 で登録された追記内容を DB から取得
    const registeredDoc = queryDb(`
      SELECT doc FROM business_direct WHERE opener_id = ${parentBdId} AND kiji_flg = 1 LIMIT 1
    `);
    expect(registeredDoc, 'BKK-2 で追記が登録済みであること（前提確認）').not.toBe('');

    await page.goto(`/iis/bd_detail/id=${parentBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // 追記エリアが存在すること
    const addonBody = page.locator('.bd-detail-addon-body').first();
    await expect(addonBody, '追記表示エリア（.bd-detail-addon-body）が存在すること').toBeVisible();

    // 追記内容が表示されていること
    const addonText = await addonBody.innerText();
    expect(addonText, '追記内容が bd_detail に表示されること').toContain(registeredDoc);

    // 「追記」の日付ラベルが表示されること
    const addonDate = page.locator('.bd-detail-addon-date').first();
    await expect(addonDate, '追記の日付ラベルが表示されること').toBeVisible();
    const dateText = await addonDate.innerText();
    expect(dateText, '追記日付ラベルに「追記」と表示されること').toContain('追記');

    await page.close();
  });

  // ────────────────────────────────────────────────────────────
  // BKK-4: 確認→戻る で doc が入力フォームに引き継がれる
  // ────────────────────────────────────────────────────────────
  test('BKK-4: 確認画面で「戻る」を押すと doc が入力フォームに引き継がれる', async () => {
    const page = await ctx.newPage();
    const testDoc = `${MARKER} BKK4 戻るテスト内容`;

    await page.goto(`/iis/bd_edit/addid=${parentBdId}`);
    await page.waitForLoadState('domcontentloaded');

    await page.fill('textarea[name="doc"]', testDoc);
    await page.click('button[name="cmd"][value="confirm"]');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面に遷移していること
    const confirmText = await page.locator('body').innerText();
    expect(confirmText, '確認画面に遷移していること').toContain('追記確認');

    // 「戻る」ボタンをクリック
    await page.click('button[name="button_back"]');
    await page.waitForLoadState('domcontentloaded');

    // 入力フォームに戻っていること
    const textarea = page.locator('textarea[name="doc"]');
    await expect(textarea, '入力フォームに戻っていること（textarea が表示）').toBeVisible();

    // doc の内容が引き継がれていること
    const returnedDoc = await textarea.inputValue();
    expect(returnedDoc, '戻った後の textarea に入力した追記内容が引き継がれること').toBe(testDoc);

    await page.close();
  });

  // ────────────────────────────────────────────────────────────
  // BKK-5: 他会員の BD への追記はブロックされる（認可）
  // ────────────────────────────────────────────────────────────
  test('BKK-5: 他会員の BD に対する追記は認可エラーでブロックされる', async () => {
    const page = await ctx.newPage();

    // テストユーザーが他会員の BD の addid でアクセスを試みる
    await page.goto(`/iis/bd_edit/addid=${otherBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // 追記登録フォームが表示されないこと（リダイレクトまたはアクセス拒否）
    const bodyText = await page.locator('body').innerText();
    expect(bodyText, '他会員の BD に対して追記フォームが表示されないこと').not.toContain('追記登録');

    // DB に不正な追記レコードが作成されていないこと
    const illegalCount = queryDb(`
      SELECT COUNT(*) FROM business_direct WHERE opener_id = ${otherBdId} AND kiji_flg = 1
    `);
    expect(parseInt(illegalCount), '他会員の BD に追記レコードが作成されていないこと').toBe(0);

    await page.close();
  });

  // ────────────────────────────────────────────────────────────
  // BKK-6: 二重送信はブロックされる（chkno トークン検証）
  // ────────────────────────────────────────────────────────────
  test('BKK-6: 確認画面からの二重送信は chkno トークンでブロックされ重複登録されない', async () => {
    const page = await ctx.newPage();
    const testDoc = `${MARKER} BKK6 二重送信テスト`;

    await page.goto(`/iis/bd_edit/addid=${parentBdId}`);
    await page.waitForLoadState('domcontentloaded');
    await page.fill('textarea[name="doc"]', testDoc);
    await page.click('button[name="cmd"][value="confirm"]');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面のフォームデータ（action URL + 全 hidden フィールド）を取得
    const { formAction, formFields } = await page.evaluate(() => {
      const form = document.querySelector('form');
      const data = {};
      form.querySelectorAll('input').forEach(inp => {
        if (inp.name) data[inp.name] = inp.value;
      });
      return { formAction: form.action, formFields: data };
    });

    // 1回目: 正規の登録
    await page.click('#btn-reg');
    await page.waitForLoadState('domcontentloaded');

    // DB に1件登録されていること
    const countAfterFirst = queryDb(`
      SELECT COUNT(*) FROM business_direct WHERE opener_id = ${parentBdId} AND doc = '${testDoc}'
    `);
    expect(parseInt(countAfterFirst), '正規の登録で1件のみ作成されること').toBe(1);

    // 2回目: 同じ chkno（使用済みトークン）で再送信
    const postBody = Object.entries(formFields)
      .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
      .join('&');

    await page.request.post(formAction, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      data: postBody
    });

    // DB のレコード数が増えていないこと（二重登録がブロックされていること）
    const countAfterSecond = queryDb(`
      SELECT COUNT(*) FROM business_direct WHERE opener_id = ${parentBdId} AND doc = '${testDoc}'
    `);
    expect(parseInt(countAfterSecond), '使用済み chkno による二重送信はブロックされ1件のまま').toBe(1);

    await page.close();
  });

});
