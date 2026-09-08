// @ts-check
/**
 * link-check.spec.js
 * 各ページのリンク切れ・意図しないリダイレクトを検出するテスト
 *
 * カバー範囲:
 *  - 公開ページを巡回し、内部リンクを収集
 *  - 各リンクが 2xx を返すこと
 *  - 各リンクが TOP（/）に意図せずリダイレクトしていないこと
 *  - 重要 CTA リンク（「入会案内ページへ」等）が正しい URL を指していること
 */
import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

// 巡回対象ページ（smoke.spec.js の PUBLIC_PAGES + nyukai 系を追加）
const CRAWL_PAGES = [
  '/',
  '/nyukai.html',
  '/nyukai-form.html',
  '/kric.html',
  '/kric/history.html',
  '/kric/object.html',
  '/kric/formation.html',
  '/activity.html',
  '/activity/communication.html',
  '/activity/training.html',
  '/activity/friendship.html',
  '/service.html',
  '/member.html',
  '/member/member_search_name.html',
  '/member/member_search_area.html',
  '/member/member_search_work.html',
  '/enter.html',
  '/enter/guide.html',
  '/about.html',
  '/kric-media.html',
  '/contact.html',
  '/privacy.html',
];

// チェックから除外するパスのパターン
const SKIP_PATTERNS = [
  /^\/iis\//,          // 認証必要
  /^\/admin\//,        // 認証必要
  /^\/wp-admin\//,
  /^\/wp-login/,
  /^\/wp-json\//,
  /\/feed$/,           // WordPress RSS フィード
  /\/feed\//,
  /\?/,                // クエリパラメータ付きは除外（無限巡回防止）
  /\.pdf$/i,
  /\.zip$/i,
  /\.css$/i,           // CSS ファイルは対象外
  /\.js$/i,            // JS ファイルは対象外
  /\.png$/i,           // 画像ファイルは対象外
  /\.jpg$/i,
  /\.gif$/i,
  /\.svg$/i,
  /\.ico$/i,
  /\.woff/i,
];

// リンクを内部パスに正規化する
function normalizeHref(href) {
  if (!href) return null;
  // アンカーのみ・空は除外
  if (href === '' || href.startsWith('#') || href.startsWith('javascript:')) return null;
  // mailto・tel は除外
  if (href.startsWith('mailto:') || href.startsWith('tel:')) return null;
  // 外部リンクは除外
  if (href.startsWith('http') && !href.startsWith(BASE_URL)) return null;
  // BASE_URL プレフィックスを除去してパスのみにする
  if (href.startsWith(BASE_URL)) href = href.slice(BASE_URL.length);
  // パスが空の場合は /
  if (href === '') href = '/';
  // アンカー部分を除去
  const withoutHash = href.split('#')[0] || '/';
  return withoutHash;
}

// ============================================================
// 1. 重要 CTA リンクの href 値チェック（コードレビュー的な確認）
// ============================================================
test.describe('重要リンクの href チェック', () => {

  // 「入会案内ページへ」ボタンが /nyukai.html を指しているか
  const NYUKAI_PAGES = [
    '/nyukai.html',
    '/activity.html',
    '/activity/communication.html',
    '/activity/training.html',
    '/activity/friendship.html',
    '/kric.html',
    '/contact.html',
    '/privacy.html',
    '/about.html',
    '/kric-media.html',
    '/member.html',
  ];

  for (const path of NYUKAI_PAGES) {
    test(`「入会案内ページへ」リンクが /nyukai.html を指している: ${path}`, async ({ page }) => {
      await page.goto(path);
      const links = page.locator('a.contact-photo-link');
      const count = await links.count();
      if (count === 0) return; // このページにボタンがない場合はスキップ

      for (let i = 0; i < count; i++) {
        const href = await links.nth(i).getAttribute('href');
        const text = await links.nth(i).textContent();
        // 入会案内ページへ のリンクのみ対象
        if (text && text.includes('入会案内')) {
          expect(
            href,
            `${path} の「入会案内ページへ」リンクが /nyukai.html 以外を指しています: "${href}"`
          ).toContain('/nyukai.html');
        }
      }
    });
  }
});

// ============================================================
// 2. 内部リンクの全数チェック（リンク切れ・TOPリダイレクト検出）
// ============================================================
test.describe('内部リンク一括チェック', () => {

  test('全公開ページの内部リンクを収集して検証', async () => {
    // PHP-FPM の同時処理数に合わせ、並列数3のバッチで実行
    test.setTimeout(300000); // 最大5分

    const CONCURRENCY = 3; // 同時リクエスト数（Docker PHP-FPM に合わせて調整）

    // ---- helper: 並列数制限付き実行 ----
    async function batchSettled(items, fn) {
      const results = [];
      for (let i = 0; i < items.length; i += CONCURRENCY) {
        const batch = items.slice(i, i + CONCURRENCY);
        const batchRes = await Promise.allSettled(batch.map(fn));
        results.push(...batchRes);
      }
      return results;
    }

    // ---- helper: HTML から href を抽出 ----
    function extractHrefs(html) {
      const results = new Set();
      const hrefRe = /href="([^"]+)"/gi;
      let m;
      while ((m = hrefRe.exec(html)) !== null) {
        const normalized = normalizeHref(m[1]);
        if (!normalized) continue;
        if (SKIP_PATTERNS.some(p => p.test(normalized))) continue;
        results.add(normalized);
      }
      return results;
    }

    // ---- Step 1: CRAWL_PAGES をバッチ並列で取得 → リンク収集 ----
    const collected = new Set();
    const crawlResults = await batchSettled(CRAWL_PAGES, path =>
      fetch(BASE_URL + path, { signal: AbortSignal.timeout(20000) }).then(r => r.text())
    );
    crawlResults.forEach(r => {
      if (r.status === 'fulfilled') {
        for (const href of extractHrefs(r.value)) { collected.add(href); }
      }
    });

    console.log(`収集した内部リンク数: ${collected.size}`);
    if (collected.size > 0) {
      console.log('収集リンク:\n  ' + [...collected].sort().join('\n  '));
    }

    // ---- Step 2: 収集リンクをバッチ並列で検証 ----
    const paths = [...collected];
    const checkResults = await batchSettled(paths, path =>
      fetch(BASE_URL + path, { redirect: 'follow', signal: AbortSignal.timeout(15000) })
    );

    const failures = [];
    checkResults.forEach((r, i) => {
      const path = paths[i];
      if (r.status === 'rejected') {
        failures.push(`[接続失敗] ${path}: ${r.reason.message}`);
        return;
      }
      const resp      = r.value;
      const finalUrl  = resp.url;
      const status    = resp.status;
      const finalPath = finalUrl.replace(BASE_URL, '') || '/';

      if (status === 404) { failures.push(`[404] ${path}`); return; }
      if (status >= 500)  { failures.push(`[${status}] ${path}`); return; }
      // TOPへのリダイレクト検出（元が / でない場合）
      if (path !== '/' && (finalPath === '/' || finalPath === '')) {
        failures.push(`[TOPリダイレクト] ${path} → ${finalUrl}`);
      }
    });

    if (failures.length > 0) {
      const report = failures.join('\n');
      console.error('リンクチェック失敗一覧:\n' + report);
      expect(failures, `リンクチェック失敗 ${failures.length} 件:\n${report}`).toHaveLength(0);
    }

    console.log('リンクチェック完了: 問題なし');
  });
});
