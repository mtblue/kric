// @ts-check
/**
 * linghtbox（BD一覧「登録者情報を見る」モーダル）表示テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * 対象:
 *   LB-1: 顔写真ありメンバーの linghtbox URL で img が表示される（バグ修正確認）
 *   LB-2: 顔写真なしメンバーの linghtbox URL でプレースホルダー div が表示される
 *   LB-3: BD一覧から「登録者情報を見る」をクリックするとモーダルに会員名が表示される
 *   LB-4: BD一覧の顔写真ありメンバーの「登録者情報を見る」でモーダルに img が表示される
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

// 顔写真ありメンバー（テストメンバー member_id=959）
const memberWithPhoto = queryDb(
  `SELECT member_id FROM member WHERE login_id = '${MEMBER_ID}'`
);
// 顔写真なしメンバー
const memberWithoutPhoto = queryDb(
  `SELECT member_id FROM member
   WHERE (upload_photo IS NULL OR upload_photo = '')
     AND del_flg = 0 AND login_flg = 1 AND member_id > 0 LIMIT 1`
);
// 顔写真ありメンバーのBD投稿（zokusei も取得してページを特定）
const bdRowWithPhoto = queryDb(
  `SELECT bd.business_direct_id, bd.zokusei
   FROM business_direct bd
   JOIN member m ON bd.member_id = m.member_id
   WHERE bd.del_flg = 0 AND bd.kiji_flg = 0
     AND m.member_id = ${memberWithPhoto}
   ORDER BY bd.business_direct_id DESC LIMIT 1`
);

test.describe('linghtbox（BD登録者情報モーダル）表示テスト', () => {

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
    await ctx?.close();
  });

  // ── LB-1: 顔写真ありメンバーの linghtbox URL で img が表示される ─────────────
  test('LB-1: 顔写真ありメンバーの linghtbox で img 要素が表示される（upload_photo 取得漏れ修正確認）', async () => {
    const page = await ctx.newPage();

    await page.goto(`/iis/linghtbox/id=${memberWithPhoto}/`);
    await page.waitForLoadState('domcontentloaded');

    // img.dir-modal-photo-img が存在し、src が空でないこと
    const img = page.locator('img.dir-modal-photo-img');
    await expect(img, '顔写真 img 要素が存在すること').toBeVisible({ timeout: 5000 });

    const src = await img.getAttribute('src');
    expect(src, 'img の src が空でないこと').toBeTruthy();
    expect(src, 'img の src に /iis/file/ パスが含まれること').toContain('/file/');

    // プレースホルダー div は存在しないこと
    const placeholder = page.locator('div.dir-modal-photo-img');
    await expect(placeholder, 'プレースホルダー div は非表示であること').not.toBeVisible();

    await page.close();
  });

  // ── LB-2: 顔写真なしメンバーの linghtbox で写真エリアが非表示 ─────────────────
  // 仕様変更：プレースホルダー div は出力しない（写真なし = 写真エリア自体なし）
  test('LB-2: 顔写真なしメンバーの linghtbox で写真 img もプレースホルダーも表示されない', async () => {
    if (!memberWithoutPhoto) {
      console.log('[LB-2] 顔写真なしメンバーが見つからないためスキップ');
      return;
    }
    const page = await ctx.newPage();

    await page.goto(`/iis/linghtbox/id=${memberWithoutPhoto}/`);
    await page.waitForLoadState('domcontentloaded');

    // img 要素は表示されないこと
    const img = page.locator('img.dir-modal-photo-img');
    await expect(img, '顔写真 img は非表示であること').not.toBeVisible();

    // プレースホルダー div も表示されないこと
    const placeholder = page.locator('div.dir-modal-photo-img');
    await expect(placeholder, 'プレースホルダー div も非表示であること').not.toBeVisible();

    await page.close();
  });

  // ── LB-3: BD一覧の lightbox リンク href 経由で会員名が取得できる ─────────────
  test('LB-3: BD一覧の「登録者情報を見る」リンク先に会員名が存在する', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/bd');
    await page.waitForLoadState('domcontentloaded');

    // 「登録者情報を見る」リンクが1件以上存在すること
    const links = page.locator('a.bd-card-member.js-lightbox-link');
    const count = await links.count();
    if (count === 0) {
      console.log('[LB-3] BD一覧に投稿がないためスキップ');
      await page.close();
      return;
    }

    // 1件目のリンクの href を取得して直接アクセス
    const href = await links.first().getAttribute('href');
    expect(href, 'linghtbox href が存在すること').toBeTruthy();

    await page.goto(href);
    await page.waitForLoadState('domcontentloaded');

    // 会員名が表示されること
    const nameEl = page.locator('.dir-modal-name');
    await expect(nameEl, '会員名要素が存在すること').toBeVisible({ timeout: 5000 });
    const nameText = await nameEl.textContent();
    expect(nameText?.trim(), '会員名が空でないこと').toBeTruthy();

    await page.close();
  });

  // ── LB-4: 顔写真ありメンバーの linghtbox に img が表示される（BD経由URL確認）──
  test('LB-4: BD一覧の顔写真ありメンバーの「登録者情報を見る」リンク先に img が表示される', async () => {
    if (!bdRowWithPhoto) {
      console.log('[LB-4] 顔写真ありメンバーのBD投稿が見つからないためスキップ');
      return;
    }
    const [, zokusei] = bdRowWithPhoto.split('\t');
    const zoPath = ({ '1': 'bd', '2': 'bd02', '3': 'bd03', '4': 'bd04' })[zokusei] || 'bd';
    const page = await ctx.newPage();

    await page.goto(`/iis/${zoPath}`);
    await page.waitForLoadState('domcontentloaded');

    // 対象メンバーの「登録者情報を見る」リンクを探す
    const targetLink = page.locator(`a.js-lightbox-link[href*="id=${memberWithPhoto}"]`);
    const linkCount = await targetLink.count();
    if (linkCount === 0) {
      console.log('[LB-4] 対象メンバーのリンクが一覧に見つからないためスキップ（ページネーション等）');
      await page.close();
      return;
    }

    // リンク先URLに直接アクセスして img を確認
    const href = await targetLink.first().getAttribute('href');
    await page.goto(href);
    await page.waitForLoadState('domcontentloaded');

    const img = page.locator('img.dir-modal-photo-img');
    await expect(img, 'linghtbox ページに顔写真 img が表示されること').toBeVisible({ timeout: 5000 });

    await page.close();
  });

});
