// @ts-check
/**
 * 動画URL 機能テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 *
 * ③ video_url（BD詳細ページの動画表示）
 *   - bd_detail では video_url が YouTube URL か否かで表示を切り替える
 *   - YouTube: a.bd-yt-link + img.bd-yt-thumb（サムネイル）
 *   - 非YouTube: テキストリンクのみ
 *   - 空: 動画URLセクション自体を非表示
 *
 * ④ pr_video（会社紹介動画URL / company_edit）
 *   - company_edit フォームで入力→確認→登録すると company テーブルに保存される
 *   - 確認ページで YouTube URL の場合 iframe プレビューが表示される
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const MARKER      = `__E2EVID__${Date.now()}`;

// テスト用動画URL
const YOUTUBE_URL = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
const YOUTUBE_ID  = 'dQw4w9WgXcQ';
const NON_YT_URL  = 'https://vimeo.com/123456789';

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

test.describe('動画URL 機能テスト（video_url / pr_video）', () => {

  let ctx;
  let ytBdId, nonYtBdId, noVideoBdId;
  let companyId, origPrVideo;

  const memberId = queryDb(`SELECT member_id FROM member WHERE login_id = '${MEMBER_ID}'`);

  test.beforeAll(async ({ browser }) => {
    // company_id と pr_video 元の値を保存（afterAll で復元）
    const row = queryDb(`
      SELECT c.company_id, COALESCE(c.pr_video,'')
      FROM member m JOIN company c ON m.company_id = c.company_id
      WHERE m.login_id = '${MEMBER_ID}'
    `);
    [companyId, origPrVideo] = row.split('\t');
    origPrVideo = origPrVideo ?? ''; // pr_video が NULL/空のとき trim() でタブが除去され undefined になるのを防ぐ

    // VID-2 用: YouTube URL を持つBD
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, video_url, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${memberId}, '${MARKER} VID2 YouTube', 'テスト本文', '土地', 1, '大阪府',
         '${YOUTUBE_URL}', 0, 0, NOW(), NOW())
    `);
    ytBdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${MARKER} VID2 YouTube' LIMIT 1`);

    // VID-3 用: 非YouTube URL を持つBD
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, video_url, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${memberId}, '${MARKER} VID3 非YouTube', 'テスト本文', '土地', 1, '大阪府',
         '${NON_YT_URL}', 0, 0, NOW(), NOW())
    `);
    nonYtBdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${MARKER} VID3 非YouTube' LIMIT 1`);

    // VID-4 用: video_url が空のBD
    execDb(`
      INSERT INTO business_direct
        (member_id, title, doc, shubetsu, zokusei, syozaichi, video_url, kiji_flg, del_flg, create_date, update_date)
      VALUES
        (${memberId}, '${MARKER} VID4 動画なし', 'テスト本文', '土地', 1, '大阪府',
         '', 0, 0, NOW(), NOW())
    `);
    noVideoBdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${MARKER} VID4 動画なし' LIMIT 1`);

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
    execDb(`DELETE FROM business_direct WHERE title LIKE '${MARKER}%'`);
    // pr_video を元の値に戻す（空文字の場合も NULL で上書きしないよう COALESCE を使わず直接セット）
    const escapedOrig = origPrVideo.replace(/'/g, "\\'");
    execDb(`UPDATE company SET pr_video = '${escapedOrig}' WHERE company_id = ${companyId}`);
    await ctx?.close();
  });

  // ══════════════════════════════════════════════════════════════
  // ③ video_url — BD詳細ページの動画表示
  // ══════════════════════════════════════════════════════════════

  test('VID-1: BD新規登録フォームから video_url を入力するとDBに保存される', async () => {
    const page = await ctx.newPage();
    const testTitle = `${MARKER} VID1 フォーム登録`;

    await page.goto('/iis/bd_edit');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="title"]', testTitle);
    await page.selectOption('select[name="zokusei"]', '1');
    await page.selectOption('select[name="shubetsu"]', '土地');
    await page.fill('input[name="syozaichi"]', '大阪府');
    await page.fill('textarea[name="doc"]', 'VID1 テスト本文');
    await page.fill('input[name="video_url"]', YOUTUBE_URL);

    // 確認
    await page.click('button[name="cmd"][value="confirm"]');
    await page.waitForLoadState('domcontentloaded');
    // 登録
    await page.click('#btn-reg');
    await page.waitForLoadState('domcontentloaded');

    const saved = queryDb(
      `SELECT COALESCE(video_url,'') FROM business_direct WHERE title = '${testTitle}' LIMIT 1`
    );
    expect(saved, `video_url '${YOUTUBE_URL}' がDBに保存されること`).toBe(YOUTUBE_URL);
    await page.close();
  });

  test('VID-2: bd_detail で YouTube URL の場合 YouTube サムネイルリンクが表示される', async () => {
    const page = await ctx.newPage();

    await page.goto(`/iis/bd_detail/id=${ytBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // a.bd-yt-link（YouTubeサムネイルリンク）が存在する
    const ytLink = page.locator('a.bd-yt-link');
    await expect(ytLink, 'YouTubeサムネイルリンク（a.bd-yt-link）が表示されること').toBeVisible();

    // href に動画URLが設定されている
    const href = await ytLink.getAttribute('href');
    expect(href, 'href に動画URLが設定されること').toBe(YOUTUBE_URL);

    // img.bd-yt-thumb の src に YouTube video ID が含まれる
    const thumb = page.locator('img.bd-yt-thumb');
    await expect(thumb, 'YouTubeサムネイル画像（img.bd-yt-thumb）が表示されること').toBeVisible();
    const src = await thumb.getAttribute('src');
    expect(src, `src に YouTube video ID (${YOUTUBE_ID}) が含まれること`).toContain(YOUTUBE_ID);

    await page.close();
  });

  test('VID-3: bd_detail で非YouTube URL の場合テキストリンクが表示され、サムネイルは表示されない', async () => {
    const page = await ctx.newPage();

    await page.goto(`/iis/bd_detail/id=${nonYtBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // 動画URLのテキストリンクが表示される
    const plainLink = page.locator(`a[href="${NON_YT_URL}"]`);
    await expect(plainLink, '非YouTube URL がテキストリンクとして表示されること').toBeVisible();

    // YouTubeサムネイルリンク（a.bd-yt-link）は表示されない
    expect(
      await page.locator('a.bd-yt-link').count(),
      'YouTube専用サムネイルリンク（a.bd-yt-link）は表示されないこと'
    ).toBe(0);

    // YouTube サムネイル画像（img.bd-yt-thumb）は表示されない
    expect(
      await page.locator('img.bd-yt-thumb').count(),
      'YouTubeサムネイル画像（img.bd-yt-thumb）は表示されないこと'
    ).toBe(0);

    await page.close();
  });

  test('VID-4: video_url が空の場合 bd_detail で動画URLセクションが表示されない', async () => {
    const page = await ctx.newPage();

    await page.goto(`/iis/bd_detail/id=${noVideoBdId}`);
    await page.waitForLoadState('domcontentloaded');

    // 動画URLセクションのラベル「•動画URL」が表示されない
    const bodyText = await page.locator('body').innerText();
    expect(bodyText, '動画URLセクション（•動画URL）が表示されないこと').not.toContain('動画URL');

    // YouTube リンク・サムネイル画像もない
    expect(await page.locator('a.bd-yt-link').count(), '').toBe(0);
    expect(await page.locator('img.bd-yt-thumb').count(), '').toBe(0);

    await page.close();
  });

  // ══════════════════════════════════════════════════════════════
  // ④ pr_video — company_edit 保存テスト
  // ══════════════════════════════════════════════════════════════

  test('PRV-1: pr_video（会社紹介動画URL）を company_edit で登録するとDBに保存される', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="pr_video"]', YOUTUBE_URL);

    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    await page.click('button.bd-reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    const saved = queryDb(`SELECT COALESCE(pr_video,'') FROM company WHERE company_id = ${companyId}`);
    expect(saved, `pr_video '${YOUTUBE_URL}' がDBに保存されること`).toBe(YOUTUBE_URL);
    await page.close();
  });

  test('PRV-2: 編集フォームを開くと保存済みの pr_video が input に初期表示される', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    const value = await page.locator('input[name="pr_video"]').inputValue();
    expect(value, '保存済みの pr_video が編集フォームの input に初期表示されること').toBe(YOUTUBE_URL);
    await page.close();
  });

  test('PRV-3: pr_video を空に変更して登録するとDBで空になる', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="pr_video"]', '');

    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    await page.click('button.bd-reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    const saved = queryDb(`SELECT COALESCE(pr_video,'') FROM company WHERE company_id = ${companyId}`);
    expect(saved, 'pr_video が空になっていること').toBe('');
    await page.close();
  });

  test('PRV-4: company_edit 確認画面で YouTube URL を入力すると iframe プレビューが表示される', async () => {
    const page = await ctx.newPage();

    await page.goto('/iis/company_edit');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="pr_video"]', YOUTUBE_URL);

    // 確認画面へ（登録はしない）
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    // 確認画面: YouTube動画のiframeが表示されること
    const iframe = page.locator(`iframe[src*="${YOUTUBE_ID}"]`);
    await expect(
      iframe,
      `確認画面に YouTube iframe (video ID=${YOUTUBE_ID}) が表示されること`
    ).toBeVisible({ timeout: 5000 });

    await page.close();
  });

});
