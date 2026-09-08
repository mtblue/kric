// @ts-check
/**
 * アクセスマップ（map_flg / google_map）保存テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * map_flg はフォーム submit 時に JS が入力値から自動計算する。
 *   bit1(1): google_map 入力が空でない
 *   bit2(2): 既存画像（upload_map）がある / ファイルをアップロードした
 * ※チェックボックス UI は廃止済み（2026/09）
 *
 * bit2 の操作は DB に upload_map を事前セットして currentPreview を表示させる方法で制御する。
 *
 * 対象機能:
 *   MAP-1: google_map あり・オリジナル画像なし → map_flg=1
 *   MAP-2: google_map なし・オリジナル画像あり → map_flg=2
 *   MAP-3: 両方あり → map_flg=3
 *   MAP-4: 両方なし → map_flg=0
 *   MAP-5: google_map URL を入力 → DBに保存される
 *   MAP-6: google_map URL を空に変更 → DBで空になる
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';

// テスト用ダミー upload_map ファイル名（実ファイル不要：bit2 フラグ制御のみ）
const DUMMY_MAP_IMG = '__test_dummy_map__.jpg';

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

test.describe('アクセスマップ（map_flg / google_map）保存テスト', () => {

  let ctx;
  let companyId, origMapFlg, origGoogleMap, origUploadMap;

  test.beforeAll(async ({ browser }) => {
    // 元の値を保存（テスト後に復元）
    const row = queryDb(
      `SELECT c.company_id, c.map_flg, COALESCE(c.google_map,''), COALESCE(c.upload_map,'')
       FROM member m JOIN company c ON m.company_id = c.company_id
       WHERE m.login_id = '${MEMBER_ID}'`
    );
    [companyId, origMapFlg, origGoogleMap, origUploadMap] = row.split('\t');

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
    // 元の値に戻す（google_map に特殊文字が含まれる可能性があるため COALESCE で安全に）
    execDb(
      `UPDATE company SET map_flg=${origMapFlg}, google_map='${origGoogleMap}', upload_map='${origUploadMap}' WHERE company_id=${companyId}`
    );
    await ctx?.close();
  });

  /**
   * google_map と upload_map（既存画像フラグ）を制御してフォーム送信→確認→登録
   *
   * @param {import('@playwright/test').Page} page
   * @param {boolean} hasGoogleMap - google_map 入力に URL を入れるか
   * @param {boolean} hasOriginalImg - 既存 upload_map を DB にセットして currentPreview を表示させるか
   */
  async function submitWithMapFlg(page, hasGoogleMap, hasOriginalImg) {
    // bit2 制御: DB の upload_map を事前セット（実ファイル不要）
    // コントローラーはファイルアップロードがない場合 upload_map カラムを更新しないため、
    // DB にダミー値を入れておくと currentPreview が表示され JS が bit2 をセットする
    if (hasOriginalImg) {
      execDb(`UPDATE company SET upload_map='${DUMMY_MAP_IMG}' WHERE company_id=${companyId}`);
    } else {
      execDb(`UPDATE company SET upload_map='' WHERE company_id=${companyId}`);
    }

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    // bit1 制御: google_map 入力を埋める or 空にする
    const googleMapInput = page.locator('input[name="google_map"]');
    await expect(googleMapInput, 'google_map 入力が表示されること').toBeVisible();
    if (hasGoogleMap) {
      await googleMapInput.fill('https://maps.google.com/maps?q=osaka+city&z=15');
    } else {
      await googleMapInput.fill('');
    }

    // 確認ページへ（submit イベントで JS が map_flg を計算して hidden に書き込む）
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認ページに遷移していること（バリデーションエラーでない場合）').toBeVisible({ timeout: 5000 });
    await regBtn.click();
    await page.waitForLoadState('domcontentloaded');
  }

  test('MAP-1: google_map あり・オリジナル画像なし → map_flg=1 がDBに保存される', async () => {
    const page = await ctx.newPage();
    await submitWithMapFlg(page, true, false);

    const dbVal = queryDb(`SELECT map_flg FROM company WHERE company_id=${companyId}`);
    expect(parseInt(dbVal), 'map_flg=1 (bit1=ON, bit2=OFF)').toBe(1);
    await page.close();
  });

  test('MAP-2: google_map なし・オリジナル画像あり → map_flg=2 がDBに保存される', async () => {
    const page = await ctx.newPage();
    await submitWithMapFlg(page, false, true);

    const dbVal = queryDb(`SELECT map_flg FROM company WHERE company_id=${companyId}`);
    expect(parseInt(dbVal), 'map_flg=2 (bit1=OFF, bit2=ON)').toBe(2);
    await page.close();
  });

  test('MAP-3: 両方あり → map_flg=3 がDBに保存される', async () => {
    const page = await ctx.newPage();
    await submitWithMapFlg(page, true, true);

    const dbVal = queryDb(`SELECT map_flg FROM company WHERE company_id=${companyId}`);
    expect(parseInt(dbVal), 'map_flg=3 (bit1=ON, bit2=ON)').toBe(3);
    await page.close();
  });

  test('MAP-4: 両方なし → map_flg=0 がDBに保存される', async () => {
    const page = await ctx.newPage();
    await submitWithMapFlg(page, false, false);

    const dbVal = queryDb(`SELECT map_flg FROM company WHERE company_id=${companyId}`);
    expect(parseInt(dbVal), 'map_flg=0 (bit1=OFF, bit2=OFF)').toBe(0);
    await page.close();
  });

  test('MAP-5: google_map URL を入力するとDBに保存される', async () => {
    const page = await ctx.newPage();
    const testUrl = 'https://maps.google.com/maps?q=osaka+city&z=15';

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="google_map"]', testUrl);

    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認ページに遷移していること').toBeVisible({ timeout: 5000 });
    await regBtn.click();
    await page.waitForLoadState('domcontentloaded');

    const dbVal = queryDb(`SELECT COALESCE(google_map,'') FROM company WHERE company_id=${companyId}`);
    expect(dbVal, 'google_map URLがDBに保存されていること').toBe(testUrl);
    await page.close();
  });

  test('MAP-6: google_map URL を空に変更するとDBで空になる', async () => {
    const page = await ctx.newPage();

    // まずURLをセットしておく（空クリア確認のための前提条件）
    execDb(`UPDATE company SET google_map='https://maps.google.com/test' WHERE company_id=${companyId}`);

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="google_map"]', '');

    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認ページに遷移していること').toBeVisible({ timeout: 5000 });
    await regBtn.click();
    await page.waitForLoadState('domcontentloaded');

    const dbVal = queryDb(`SELECT COALESCE(google_map,'') FROM company WHERE company_id=${companyId}`);
    expect(dbVal, 'google_map が空になっていること').toBe('');
    await page.close();
  });

});
