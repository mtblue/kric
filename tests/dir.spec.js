// @ts-check
/**
 * 会員ディレクトリ（/iis/dir）＆ プロフィール編集（/iis/member_edit）機能テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 * ─ 「保存できる = 表示される」を前提にせず、
 *   保存フィールドが全表示画面で一字一句正しく描画されることを検証する。
 *
 * 教訓: [2026-07-19] 画面またぎの列カバレッジ漏れ
 *   → 保存側(member_edit)と表示側(dir)が別コントローラーにまたがる場合、
 *     SQLのSELECT句に列が含まれていないバグは保存テストだけでは発見できない。
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

// ── DB から期待値を取得 ──────────────────────────────────────────────────────
function getMemberRow() {
  const row = queryDb(`
    SELECT m.member_id, m.name, m.kana, m.keitai1, m.keitai2, m.email1,
           m.upload_photo, m.jiyuu_toukou,
           c.company_name, c.company_kana, c.tel AS company_tel
    FROM member m JOIN company c ON m.company_id = c.company_id
    WHERE m.login_id = '${MEMBER_ID}' AND m.del_flg = 0
  `);
  const [member_id, name, kana, keitai1, keitai2, email1,
         upload_photo, jiyuu_toukou, company_name, company_kana, company_tel] = row.split('\t');
  return { member_id, name, kana, keitai1, keitai2: keitai2 === 'NULL' ? '' : keitai2,
           email1, upload_photo: upload_photo === 'NULL' ? '' : upload_photo,
           jiyuu_toukou: jiyuu_toukou === 'NULL' ? '' : jiyuu_toukou,
           company_name, company_kana, company_tel };
}

test.describe('会員ディレクトリ・プロフィール保存→表示 網羅テスト', () => {

  let ctx;

  test.beforeAll(async ({ browser }) => {
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
    await ctx.close();
  });

  // ─── ヘルパー: テスト用メンバーのカードをクリックしてモーダルを開く ──────
  async function openMyModal(page) {
    await page.goto('/iis/dir');
    await page.waitForLoadState('domcontentloaded');
    await page.fill('#dir-keyword-input', '管理者');
    await page.click('#dir-search-btn');
    await page.waitForLoadState('domcontentloaded');
    const card = page.locator('.dir-card').filter({ hasText: '管理者' }).first();
    await expect(card).toBeVisible();
    await card.click();
    await page.waitForTimeout(300);
    return card;
  }

  // ─── A. dir モーダル: 全フィールドがDBと一致して表示されるか ──────────────

  test.describe('A. dir モーダル 保存→表示 全フィールド検証', () => {

    test('A-1: 名前（name）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalName').textContent();
      expect(text?.trim()).toBe(db.name);
      await page.close();
    });

    test('A-2: カナ（kana）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalKana').textContent();
      expect(text?.trim()).toBe(db.kana);
      await page.close();
    });

    test('A-3: 会社名（company_name）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalCompany').textContent();
      expect(text?.trim()).toBe(db.company_name);
      await page.close();
    });

    test('A-4: 会社カナ（company_kana）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalCompanyKana').textContent();
      expect(text?.trim()).toBe(db.company_kana);
      await page.close();
    });

    test('A-5: 会社電話番号（company_tel）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalCompanyTel').textContent();
      expect(text?.trim()).toBe(db.company_tel);
      await page.close();
    });

    test('A-6: 携帯電話番号1（keitai1）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalKeitai1').textContent();
      expect(text?.trim()).toBe(db.keitai1);
      await page.close();
    });

    test('A-7: 携帯電話番号2（keitai2）が DB値と一致してモーダルに表示される（NULLの場合は空欄）', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalKeitai2').textContent();
      expect(text?.trim()).toBe(db.keitai2);
      await page.close();
    });

    test('A-8: メールアドレス1（email1）が DB値と一致してモーダルに表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);
      const text = await page.locator('#dirModalEmail').textContent();
      expect(text?.trim()).toBe(db.email1);
      await page.close();
    });

    test('A-9: 顔写真（upload_photo）が DB値と一致してモーダルの img src に設定される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();
      await openMyModal(page);

      if (db.upload_photo) {
        await expect(page.locator('#dirModalPhotoWrap')).toBeVisible();
        const src = await page.locator('#dirModalPhoto').getAttribute('src');
        expect(src).toContain(db.upload_photo);
      } else {
        await expect(page.locator('#dirModalPhotoWrap')).toBeHidden();
      }
      await page.close();
    });

    test('A-10: data-* 属性が全て SQL の SELECT に含まれており空でない（列取得漏れ検出）', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();

      await page.goto('/iis/dir');
      await page.waitForLoadState('domcontentloaded');
      await page.fill('#dir-keyword-input', '管理者');
      await page.click('#dir-search-btn');
      await page.waitForLoadState('domcontentloaded');

      const card = page.locator('.dir-card').filter({ hasText: '管理者' }).first();

      // 各 data-* 属性が空でないことを検証（SELECT漏れは空文字になる）
      expect(await card.getAttribute('data-name'),       'data-name が空 → name が SELECT されていない').toBeTruthy();
      expect(await card.getAttribute('data-kana'),       'data-kana が空 → kana が SELECT されていない').toBeTruthy();
      expect(await card.getAttribute('data-company'),    'data-company が空 → company_name が SELECT されていない').toBeTruthy();
      expect(await card.getAttribute('data-company-kana'), 'data-company-kana が空 → company_kana が SELECT されていない').toBeTruthy();
      expect(await card.getAttribute('data-company-tel'),  'data-company-tel が空 → company_tel が SELECT されていない').toBeTruthy();
      expect(await card.getAttribute('data-keitai1'),    'data-keitai1 が空 → keitai1 が SELECT されていない').toBeTruthy();
      expect(await card.getAttribute('data-email'),      'data-email が空 → email1 が SELECT されていない').toBeTruthy();
      if (db.upload_photo) {
        expect(await card.getAttribute('data-photo'),    'data-photo が空 → upload_photo が SELECT されていない').toBeTruthy();
      }

      await page.close();
    });

  });

  // ─── B. 顔写真なしメンバーの表示 ──────────────────────────────────────────

  test.describe('B. 顔写真の有無による表示切り替え', () => {

    test('B-1: 顔写真なしメンバーのモーダルでは写真エリアが非表示', async () => {
      const page = await ctx.newPage();

      const noPhotoName = queryDb(
        `SELECT m.name FROM member m WHERE (m.upload_photo IS NULL OR m.upload_photo = '')
         AND m.login_flg = 1 AND m.del_flg = 0 LIMIT 1`
      );
      if (!noPhotoName) {
        console.log('[B-1] 写真なしメンバーが存在しないためスキップ');
        await page.close();
        return;
      }

      await page.goto('/iis/dir');
      await page.waitForLoadState('domcontentloaded');
      await page.fill('#dir-keyword-input', noPhotoName);
      await page.click('#dir-search-btn');
      await page.waitForLoadState('domcontentloaded');

      const card = page.locator('.dir-card').filter({ hasText: noPhotoName }).first();
      await expect(card).toBeVisible();
      expect(await card.getAttribute('data-photo') || '', '写真なしは data-photo が空').toBe('');
      await card.click();
      await page.waitForTimeout(300);
      await expect(page.locator('#dirModalPhotoWrap'), '写真なしメンバーでは写真エリア非表示').toBeHidden();
      await page.close();
    });

  });

  // ─── C. member_edit 編集フォーム: 保存値が初期表示に正しく反映されるか ───

  test.describe('C. member_edit 編集フォーム 初期表示（保存→編集フォームへの引き継ぎ）', () => {

    test('C-1: 編集フォームに name・kana が現在のDB値で初期表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();

      await page.goto(`/iis/member_edit/id=${db.member_id}`);
      await page.waitForLoadState('domcontentloaded');

      expect(await page.inputValue('input[name="name"]'),  'name の初期値').toBe(db.name);
      expect(await page.inputValue('input[name="kana"]'),  'kana の初期値').toBe(db.kana);
      await page.close();
    });

    test('C-2: 編集フォームに keitai1・keitai2 が現在のDB値で初期表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();

      await page.goto(`/iis/member_edit/id=${db.member_id}`);
      await page.waitForLoadState('domcontentloaded');

      expect(await page.inputValue('input[name="keitai1"]'), 'keitai1 の初期値').toBe(db.keitai1);
      expect(await page.inputValue('input[name="keitai2"]'), 'keitai2 の初期値').toBe(db.keitai2);
      await page.close();
    });

    test('C-3: 編集フォームに email1 が現在のDB値で初期表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();

      await page.goto(`/iis/member_edit/id=${db.member_id}`);
      await page.waitForLoadState('domcontentloaded');

      expect(await page.inputValue('input[name="email1"]'), 'email1 の初期値').toBe(db.email1);
      await page.close();
    });

    test('C-4: 顔写真が登録済みの場合、編集フォームに現在の写真プレビューが表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();

      if (!db.upload_photo) {
        console.log('[C-4] upload_photo 未登録のためスキップ');
        await page.close();
        return;
      }

      await page.goto(`/iis/member_edit/id=${db.member_id}`);
      await page.waitForLoadState('domcontentloaded');

      // 既存写真の img src に upload_photo のファイル名が含まれる（/iis/file/na=filename 形式）
      const photoImg = page.locator(`img[src*="${db.upload_photo}"]`).first();
      const src = await photoImg.getAttribute('src');
      expect(src, '編集フォームの写真プレビュー src').toContain(db.upload_photo);
      await page.close();
    });

    test('C-5: jiyuu_toukou（自由投稿）が現在のDB値で textarea に初期表示される', async () => {
      const page = await ctx.newPage();
      const db = getMemberRow();

      await page.goto(`/iis/member_edit/id=${db.member_id}`);
      await page.waitForLoadState('domcontentloaded');

      const val = await page.inputValue('textarea[name="jiyuu_toukou"]');
      expect(val, 'jiyuu_toukou の初期値').toBe(db.jiyuu_toukou);
      await page.close();
    });

  });

});
