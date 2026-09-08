// @ts-check
import { test, expect } from '@playwright/test';

const LOGIN_ID   = 'info@kric.co.jp';
const LOGIN_PASS = 'krickric';

// エラーテキストが含まれていないことを確認するヘルパー
async function checkNoError(page, response) {
  const url = page.url();
  expect(response.status(), `${url} が 500 を返した`).not.toBe(500);
  const body = await page.content();
  expect(body, `${url} : Fatal error`).not.toMatch(/Fatal error/i);
  expect(body, `${url} : Parse error`).not.toMatch(/Parse error/i);
  expect(body, `${url} : PHP Warning`).not.toMatch(/<b>Warning<\/b>:/);
  expect(body, `${url} : PHP Notice`).not.toMatch(/<b>Notice<\/b>:/);
  expect(body, `${url} : PHP Deprecated`).not.toMatch(/<b>Deprecated<\/b>:/);
  expect(body, `${url} : 内部サーバーエラー`).not.toMatch(/内部サーバーエラー/);
  expect(body, `${url} : Internal Server Error`).not.toMatch(/Internal Server Error/i);
  expect(body, `${url} : Missing View`).not.toMatch(/Missing View/i);
  expect(body, `${url} : Missing Controller`).not.toMatch(/Missing Controller/i);
  expect(body, `${url} : Missing Method/Action`).not.toMatch(/Missing Method|Missing Action/i);
  expect(body, `${url} : Uncaught Exception`).not.toMatch(/Uncaught Exception/i);
}

// JS エラーを収集するセットアップ
function watchJsErrors(page) {
  const errors = [];
  page.on('pageerror', err => errors.push(err.message));
  return errors;
}

async function checkNoJsError(url, errors) {
  expect(errors, `${url} で JS エラーが発生: ${errors.join(', ')}`).toHaveLength(0);
}

// 一覧ページで最初のリンクの href を返す（なければ null）
async function firstHref(page, selector) {
  const link = page.locator(selector).first();
  if (await link.count() === 0) return null;
  return await link.getAttribute('href');
}

// ============================================================
// 1. 公開ページ（ログイン不要）
// ============================================================
const PUBLIC_PAGES = [
  // WordPress
  '/',
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
  '/sitemap.html',
  '/privacy.html',
  // CakePHP 公開
  '/iis/login',
  '/iis/reminder',
  '/admin/login',
];

for (const url of PUBLIC_PAGES) {
  test(`[公開] ${url}`, async ({ page }) => {
    const jsErrors = watchJsErrors(page);
    const response = await page.goto(url);
    await checkNoError(page, response);
    await checkNoJsError(url, jsErrors);
  });
}

// ============================================================
// 2. IIS ページ（会員ログイン必要）
// ============================================================
const IIS_PAGES = [
  '/iis/',
  '/iis/bd',
  '/iis/bd02',
  '/iis/bd03',
  '/iis/bd04',
  '/iis/bd_edit/zo=1',
  '/iis/library',
  '/iis/library_list',
  '/iis/company_edit',
  '/iis/member',
  '/iis/member_new',
  '/iis/media_mbs1',
  '/iis/media_mbs2',
  '/iis/media_youtube',
  '/iis/linghtbox',
  '/iis/dir',
];

test.describe('IIS ページ（ログイン後）', () => {
  let context;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('/iis/login');
    await page.fill('#login_id', LOGIN_ID);
    await page.fill('#password', LOGIN_PASS);
    await page.click('button[type="submit"]');
    await page.waitForURL(/iis\/index/);
    await page.close();
  });

  test.afterAll(async () => {
    await context.close();
  });

  for (const url of IIS_PAGES) {
    test(`[IIS] ${url}`, async () => {
      const page = await context.newPage();
      const jsErrors = watchJsErrors(page);
      const response = await page.goto(url);
      await checkNoError(page, response);
      await checkNoJsError(url, jsErrors);
      await page.close();
    });
  }

  // --- 詳細ページ：一覧の最初の項目から遷移 ---

  test('[IIS] /iis/information_detail（一覧最初の項目）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/iis/');
    const href = await firstHref(page, 'a.info-title');
    if (!href) { await page.close(); return; } // データなし→スキップ
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });

  test('[IIS] /iis/bd_detail（一覧最初の項目）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/iis/bd');
    const href = await firstHref(page, 'a.bd-card-more');
    if (!href) { await page.close(); return; }
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });

  test('[IIS] /iis/library_detail（ライブラリ→カテゴリ→資料）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);

    // library → library_list（カテゴリ選択）
    await page.goto('/iis/library');
    const listHref = await firstHref(page, 'a.lib-folder-item');
    if (!listHref) { await page.close(); return; }

    // library_list → library_detail
    await page.goto(listHref);
    const detailHref = await firstHref(page, 'a.lib-card');
    if (!detailHref) { await page.close(); return; }

    const response = await page.goto(detailHref);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });

  test('[IIS] /iis/member_edit（一覧最初の項目）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/iis/member');
    const href = await firstHref(page, 'a.mem-member-edit-btn');
    if (!href) { await page.close(); return; }
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });
});

// ============================================================
// 3. Admin ページ（管理者ログイン必要）
// ============================================================
const ADMIN_PAGES = [
  '/admin/',
  '/admin/company',
  '/admin/information_edit',
  '/admin/library',
  '/admin/library_edit',
  '/admin/category',
  '/admin/category_child',
  '/admin/information_view',
  '/admin/library_view',
  // company_view は旧デザイン+POST専用ページのため除外（廃止済みGoogle Maps JSエラーあり）
];

test.describe('Admin ページ（ログイン後）', () => {
  let context;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const page = await context.newPage();
    await page.goto('/admin/login');
    await page.fill('#id', LOGIN_ID);
    await page.fill('#pass', LOGIN_PASS);
    await page.click('button[type="submit"]');
    await page.waitForURL(/admin\//);
    await page.close();
  });

  test.afterAll(async () => {
    await context.close();
  });

  for (const ADMIN_PAGE of ADMIN_PAGES) {
    test(`[Admin] ${ADMIN_PAGE}`, async () => {
      const page = await context.newPage();
      const jsErrors = watchJsErrors(page);
      const response = await page.goto(ADMIN_PAGE);
      await checkNoError(page, response);
      await checkNoJsError(ADMIN_PAGE, jsErrors);
      await page.close();
    });
  }

  // --- 詳細ページ：一覧の最初の項目から遷移 ---

  test('[Admin] /admin/information_edit（一覧最初の項目・編集）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/admin/');
    const href = await firstHref(page, 'td a.adm-link');
    if (!href) { await page.close(); return; }
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });

  test('[Admin] /admin/company_edit（一覧最初の項目・編集）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/admin/company');
    const href = await firstHref(page, 'a.mem-member-edit-btn');
    if (!href) { await page.close(); return; }
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });

  test('[Admin] /admin/member（会社一覧の最初のメンバー一覧）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/admin/company');
    const href = await firstHref(page, 'a.adm-btn-member');
    if (!href) { await page.close(); return; }
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });

  test('[Admin] /admin/library_edit（一覧最初の項目・編集）', async () => {
    const page = await context.newPage();
    const jsErrors = watchJsErrors(page);
    await page.goto('/admin/library');
    const href = await firstHref(page, 'td a.adm-link');
    if (!href) { await page.close(); return; }
    const response = await page.goto(href);
    await checkNoError(page, response);
    await checkNoJsError(page.url(), jsErrors);
    await page.close();
  });
});
