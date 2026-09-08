// @ts-check
/**
 * member_edit（IIS会員側）保存→表示 網羅テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * 対象機能:
 *   M-1〜M-5: フォーム経由で各フィールドがDBに保存されるか
 *   M-6:     name 変更→dirモーダルへの反映（保存→別画面表示の整合性）
 *   M-7:     確認→戻るボタンでフォーム値が引き継がれるか
 *   M-8:     jiyuu_toukou ColumnList漏れ検出（バグ検出テスト）
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const MARKER      = `__E2EME__${Date.now()}`;

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

test.describe('member_edit 保存→表示 網羅テスト', () => {

  let ctx;
  let memberId, origName, origKana, origKeitai1, origEmail1, origJiyuu;

  test.beforeAll(async ({ browser }) => {
    // DB から元の値を保存
    const row = queryDb(
      `SELECT member_id, name, kana, keitai1, email1, COALESCE(jiyuu_toukou,'')
       FROM member WHERE login_id = '${MEMBER_ID}'`
    );
    [memberId, origName, origKana, origKeitai1, origEmail1, origJiyuu] = row.split('\t');

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
    // 元の値に戻す
    execDb(
      `UPDATE member SET name='${origName}', kana='${origKana}',
       keitai1='${origKeitai1}', email1='${origEmail1}',
       jiyuu_toukou='${origJiyuu}' WHERE member_id=${memberId}`
    );
    await ctx?.close();
  });

  // ── フォーム送信ヘルパー ─────────────────────────────────────────────────────
  async function submitMemberEdit(page, fillFn) {
    await page.goto(`/iis/member_edit/id=${memberId}`);
    await page.waitForLoadState('domcontentloaded');

    // フォームが初期表示されていることを確認
    await expect(page.locator('input[name="name"]'), '初期表示: name').not.toBeEmpty();

    await fillFn(page);

    // 確認ページへ
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    // 確認ページの登録ボタン（見えない場合はバリデーションエラー）
    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認ページに遷移していること（見えない場合はバリデーションエラー）').toBeVisible({ timeout: 5000 });
    await regBtn.click();
    await page.waitForLoadState('domcontentloaded');
  }

  // ─── M-1: name 保存 ──────────────────────────────────────────────────────────
  test('M-1: name を変更するとDBに保存される', async () => {
    const page = await ctx.newPage();
    const newName = `${MARKER}太郎`;

    await submitMemberEdit(page, async (p) => {
      await p.fill('input[name="name"]', newName);
    });

    const dbName = queryDb(`SELECT name FROM member WHERE member_id = ${memberId}`);
    expect(dbName, 'name がDBに保存されていること').toBe(newName);
    await page.close();
  });

  // ─── M-2: kana 保存 ──────────────────────────────────────────────────────────
  test('M-2: kana を変更するとDBに保存される', async () => {
    const page = await ctx.newPage();
    const newKana = `マーカーカナ`;

    await submitMemberEdit(page, async (p) => {
      await p.fill('input[name="kana"]', newKana);
    });

    const dbKana = queryDb(`SELECT kana FROM member WHERE member_id = ${memberId}`);
    expect(dbKana, 'kana がDBに保存されていること').toBe(newKana);
    await page.close();
  });

  // ─── M-3: keitai1 保存 ───────────────────────────────────────────────────────
  test('M-3: keitai1 を変更するとDBに保存される', async () => {
    const page = await ctx.newPage();
    const newKeitai = `09099990001`;

    await submitMemberEdit(page, async (p) => {
      await p.fill('input[name="keitai1"]', newKeitai);
    });

    const dbVal = queryDb(`SELECT keitai1 FROM member WHERE member_id = ${memberId}`);
    expect(dbVal, 'keitai1 がDBに保存されていること').toBe(newKeitai);
    await page.close();
  });

  // ─── M-4: email1 保存 ────────────────────────────────────────────────────────
  test('M-4: email1 を変更するとDBに保存される', async () => {
    const page = await ctx.newPage();
    const newEmail = `teste2e${Date.now()}@example.com`;

    await submitMemberEdit(page, async (p) => {
      await p.fill('input[name="email1"]', newEmail);
    });

    const dbVal = queryDb(`SELECT email1 FROM member WHERE member_id = ${memberId}`);
    expect(dbVal, 'email1 がDBに保存されていること').toBe(newEmail);
    await page.close();
  });

  // ─── M-5: jiyuu_toukou ColumnList漏れ検出 ────────────────────────────────────
  test('M-5: jiyuu_toukou を変更するとDBに保存される（ColumnList漏れ検出）', async () => {
    const page = await ctx.newPage();
    const newJiyuu = `${MARKER} 自由投稿テスト`;

    await submitMemberEdit(page, async (p) => {
      await p.fill('textarea[name="jiyuu_toukou"]', newJiyuu);
    });

    const dbVal = queryDb(`SELECT COALESCE(jiyuu_toukou,'') FROM member WHERE member_id = ${memberId}`);
    expect(dbVal, 'jiyuu_toukou がDBに保存されていること').toBe(newJiyuu);
    await page.close();
  });

  // ─── M-6: name 変更→dirモーダルに反映 ───────────────────────────────────────
  test('M-6: name 変更後、dirモーダルに新しい名前が表示される（保存→別画面表示）', async () => {
    const page = await ctx.newPage();

    // 現在のDB値を取得（M-1以降で書き変わっている）
    const currentName = queryDb(`SELECT name FROM member WHERE member_id = ${memberId}`);

    await page.goto('/iis/dir');
    await page.waitForLoadState('domcontentloaded');
    await page.fill('#dir-keyword-input', currentName);
    await page.click('#dir-search-btn');
    await page.waitForLoadState('domcontentloaded');

    const card = page.locator('.dir-card').filter({ hasText: currentName }).first();
    await expect(card, 'dir に更新後の名前でカードが表示される').toBeVisible();

    await card.click();
    await page.waitForTimeout(300);

    const modalName = await page.locator('#dirModalName').textContent();
    expect(modalName?.trim(), 'dirモーダルに更新後の name が表示されること').toBe(currentName);

    await page.close();
  });

  // ─── M-7: 確認→戻るボタン フォーム値引き継ぎ ────────────────────────────────
  test('M-7: 確認画面で戻るを押すと入力フォームに戻り name が引き継がれる', async () => {
    const page = await ctx.newPage();
    const testName = `${MARKER}戻るテスト`;

    await page.goto(`/iis/member_edit/id=${memberId}`);
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="name"]', testName);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    // 確認ページに到達したことを確認
    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認ページに到達していること').toBeVisible({ timeout: 5000 });

    // 戻るボタンをクリック
    const backBtn = page.locator('a.bd-reg-back-btn');
    await expect(backBtn, '戻るボタンが表示されていること').toBeVisible();
    await backBtn.click();
    await page.waitForLoadState('domcontentloaded');

    // フォームに戻り、name が引き継がれていること
    const nameVal = await page.inputValue('input[name="name"]');
    expect(nameVal, '戻ったフォームに変更した name が引き継がれていること').toBe(testName);

    await page.close();
  });

  // ─── M-8: 保存後フォーム初期表示に反映 ──────────────────────────────────────
  test('M-8: 保存後に再度フォームを開くと、保存済みの値が初期表示される', async () => {
    const page = await ctx.newPage();
    const savedName = queryDb(`SELECT name FROM member WHERE member_id = ${memberId}`);

    await page.goto(`/iis/member_edit/id=${memberId}`);
    await page.waitForLoadState('domcontentloaded');

    const displayedName = await page.inputValue('input[name="name"]');
    expect(displayedName, 'フォームの初期表示がDBの保存値と一致すること').toBe(savedName);

    await page.close();
  });

});
