// @ts-check
/**
 * cmd ハンドラー網羅テスト
 *
 * 目的: 全ビューに存在する cmd 値が Controller で正しく処理されることを確認する。
 *      「ボタンはあるがハンドラーがない」バグを網羅的に検出する。
 *
 * グループ:
 *   CAT-*:   Admin category CRUD (category_add / category_edit / cmd=delete)
 *   CCAT-*:  Admin category_child CRUD
 *   ADEL-*:  Admin delete 操作（information / library / company / member）
 *   AME-*:   Admin member_edit confirm / registration
 *   NEW-*:   IIS member_new confirm / registration
 *   IDEL-*:  IIS delete 操作（bd_detail / member）
 *   REM-1:   IIS reminder reminder_send
 *
 * Docker環境（localhost:8080）専用。本番には絶対に実行しないこと。
 */

import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const MEMBER_ID   = '9a6c4e38b5';
const MEMBER_PASS = '36c890bd2c';
const ADMIN_ID    = 'info@kric.co.jp';
const ADMIN_PASS  = 'krickric';
const MARKER      = `__E2ECMD${Date.now()}__`;

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

async function loginAdmin(browser) {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  await page.goto('/admin/login');
  await page.waitForLoadState('domcontentloaded');
  await page.fill('#id', ADMIN_ID);
  await page.fill('#pass', ADMIN_PASS);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');
  await page.close();
  return ctx;
}

async function loginIis(browser) {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  await page.goto('/iis/login');
  await page.waitForLoadState('domcontentloaded');
  await page.fill('#login_id', MEMBER_ID);
  await page.fill('#password', MEMBER_PASS);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('domcontentloaded');
  await page.close();
  return ctx;
}

// =============================================================================
// グループA: Admin category CRUD
// =============================================================================
test.describe('CAT. Admin category CRUD', () => {
  let adminCtx;
  let testCatId;

  test.beforeAll(async ({ browser }) => {
    adminCtx = await loginAdmin(browser);
  });

  test.afterAll(async () => {
    if (testCatId) execDb(`UPDATE category SET del_flg=1 WHERE category_id=${testCatId}`);
    execDb(`UPDATE category SET del_flg=1 WHERE category_name LIKE '${MARKER}%' AND del_flg=0`);
    await adminCtx?.close();
  });

  test('CAT-1: category_add → DB にカテゴリが追加される', async () => {
    const page = await adminCtx.newPage();
    const catName = `${MARKER}CAT`;

    await page.goto('/admin/category');
    await page.waitForLoadState('domcontentloaded');
    await page.fill('input[name="category_name"]', catName);
    await page.click('#catBtn');
    await page.waitForLoadState('domcontentloaded');

    testCatId = queryDb(`SELECT category_id FROM category WHERE category_name='${catName}' AND del_flg=0 ORDER BY category_id DESC LIMIT 1`);
    expect(testCatId, `カテゴリ "${catName}" が DB に追加されているべき`).not.toBe('');

    await page.close();
  });

  test('CAT-2: category_edit → DB のカテゴリ名が更新される', async () => {
    const page = await adminCtx.newPage();
    const newName = `${MARKER}CAT_EDIT`;

    await page.goto('/admin/category');
    await page.waitForLoadState('domcontentloaded');

    await page.evaluate(({ id, name }) => {
      document.getElementById('catCmd').value = 'category_edit';
      document.getElementById('catId').value = String(id);
      document.getElementById('catName').value = name;
    }, { id: testCatId, name: newName });

    await page.click('#catBtn');
    await page.waitForLoadState('domcontentloaded');

    const dbName = queryDb(`SELECT category_name FROM category WHERE category_id=${testCatId} AND del_flg=0`);
    expect(dbName, 'カテゴリ名が更新されているべき').toBe(newName);

    await page.close();
  });

  test('CAT-3: cmd=delete → DB のカテゴリが del_flg=1 になる', async () => {
    const page = await adminCtx.newPage();

    await page.goto(`/admin/category/cmd=delete/id=${testCatId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM category WHERE category_id=${testCatId}`);
    expect(delFlg, '削除後 del_flg が 1 になっているべき').toBe('1');
    testCatId = null;

    await page.close();
  });
});

// =============================================================================
// グループB: Admin category_child CRUD
// =============================================================================
test.describe('CCAT. Admin category_child CRUD', () => {
  let adminCtx;
  let parentCatId;
  let testChildId;

  test.beforeAll(async ({ browser }) => {
    const catName = `${MARKER}PARENT`;
    execDb(`INSERT INTO category (oya_flg, opener_id, category_name, del_flg) VALUES (0, 0, '${catName}', 0)`);
    parentCatId = queryDb(`SELECT category_id FROM category WHERE category_name='${catName}' AND del_flg=0 ORDER BY category_id DESC LIMIT 1`);

    adminCtx = await loginAdmin(browser);
  });

  test.afterAll(async () => {
    if (testChildId) execDb(`UPDATE category SET del_flg=1 WHERE category_id=${testChildId}`);
    if (parentCatId) execDb(`UPDATE category SET del_flg=1 WHERE category_id=${parentCatId}`);
    // MARKER パターンの残骸を全削除（テスト失敗時に cleanup が漏れた場合の保険）
    execDb(`UPDATE category SET del_flg=1 WHERE category_name LIKE '${MARKER}%' AND del_flg=0`);
    await adminCtx?.close();
  });

  test('CCAT-1: category_add → DB に子カテゴリが追加される', async () => {
    const page = await adminCtx.newPage();
    const childName = `${MARKER}CHILD`;

    await page.goto(`/admin/category_child/cid=${parentCatId}`);
    await page.waitForLoadState('domcontentloaded');
    await page.fill('input[name="category_name"]', childName);
    await page.click('#catBtn');
    await page.waitForLoadState('domcontentloaded');

    testChildId = queryDb(`SELECT category_id FROM category WHERE category_name='${childName}' AND opener_id=${parentCatId} AND del_flg=0 ORDER BY category_id DESC LIMIT 1`);
    expect(testChildId, `子カテゴリ "${childName}" が DB に追加されているべき`).not.toBe('');

    await page.close();
  });

  test('CCAT-2: category_edit → DB の子カテゴリ名が更新される', async () => {
    const page = await adminCtx.newPage();
    const newName = `${MARKER}CHILD_EDIT`;

    await page.goto(`/admin/category_child/cid=${parentCatId}`);
    await page.waitForLoadState('domcontentloaded');

    await page.evaluate(({ id, name }) => {
      document.getElementById('catCmd').value = 'category_edit';
      document.getElementById('catId').value = String(id);
      document.getElementById('catName').value = name;
    }, { id: testChildId, name: newName });

    await page.click('#catBtn');
    await page.waitForLoadState('domcontentloaded');

    const dbName = queryDb(`SELECT category_name FROM category WHERE category_id=${testChildId}`);
    expect(dbName, '子カテゴリ名が更新されているべき').toBe(newName);

    await page.close();
  });

  test('CCAT-3: cmd=delete → DB の子カテゴリが del_flg=1 になる', async () => {
    const page = await adminCtx.newPage();

    await page.goto(`/admin/category_child/cmd=delete/id=${testChildId}/cid=${parentCatId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM category WHERE category_id=${testChildId}`);
    expect(delFlg, '削除後 del_flg が 1 になっているべき').toBe('1');
    testChildId = null;

    await page.close();
  });
});

// =============================================================================
// グループC: Admin delete 操作（information / library / company / member）
// =============================================================================
test.describe('ADEL. Admin delete 操作', () => {
  let adminCtx;
  let testInfoId, testLibId, testCompanyId, testMemberDelId;
  let companyId;

  test.beforeAll(async ({ browser }) => {
    companyId = queryDb(
      `SELECT c.company_id FROM member m JOIN company c ON m.company_id = c.company_id WHERE m.login_id = '${MEMBER_ID}' LIMIT 1`
    );

    execDb(`INSERT INTO information (title, doc, del_flg) VALUES ('${MARKER}INFO', 'test', 0)`);
    testInfoId = queryDb(`SELECT information_id FROM information WHERE title='${MARKER}INFO' AND del_flg=0 ORDER BY information_id DESC LIMIT 1`);

    execDb(`INSERT INTO library (title, doc, opener_id, del_flg) VALUES ('${MARKER}LIB', 'test', 0, 0)`);
    testLibId = queryDb(`SELECT library_id FROM library WHERE title='${MARKER}LIB' AND del_flg=0 ORDER BY library_id DESC LIMIT 1`);

    execDb(`INSERT INTO company (company_name, del_flg) VALUES ('${MARKER}COMP', 0)`);
    testCompanyId = queryDb(`SELECT company_id FROM company WHERE company_name='${MARKER}COMP' AND del_flg=0 ORDER BY company_id DESC LIMIT 1`);

    execDb(`INSERT INTO member (company_id, authority_id, login_id, pass, name, del_flg, login_flg) VALUES (${companyId}, 1, '${MARKER}del', 'pass', '${MARKER}del', 0, 1)`);
    testMemberDelId = queryDb(`SELECT member_id FROM member WHERE login_id='${MARKER}del' AND del_flg=0 ORDER BY member_id DESC LIMIT 1`);

    adminCtx = await loginAdmin(browser);
  });

  test.afterAll(async () => {
    // テスト失敗時の保険: MARKER パターンの残骸を全削除
    execDb(`UPDATE information SET del_flg=1 WHERE title LIKE '${MARKER}%' AND del_flg=0`);
    execDb(`UPDATE library SET del_flg=1 WHERE title LIKE '${MARKER}%' AND del_flg=0`);
    execDb(`UPDATE company SET del_flg=1 WHERE company_name LIKE '${MARKER}%' AND del_flg=0`);
    execDb(`UPDATE member SET del_flg=1 WHERE login_id LIKE '${MARKER}%' AND del_flg=0`);
    await adminCtx?.close();
  });

  test('ADEL-1: Admin index (information) cmd=delete → del_flg=1 になる', async () => {
    const page = await adminCtx.newPage();
    await page.goto(`/admin/index/cmd=delete/id=${testInfoId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM information WHERE information_id=${testInfoId}`);
    expect(delFlg, 'information が論理削除されているべき').toBe('1');
    await page.close();
  });

  test('ADEL-2: Admin library cmd=delete → del_flg=1 になる', async () => {
    const page = await adminCtx.newPage();
    await page.goto(`/admin/library/cmd=delete/id=${testLibId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM library WHERE library_id=${testLibId}`);
    expect(delFlg, 'library が論理削除されているべき').toBe('1');
    await page.close();
  });

  test('ADEL-3: Admin company cmd=delete → del_flg=1 になる', async () => {
    const page = await adminCtx.newPage();
    await page.goto(`/admin/company/cmd=delete/id=${testCompanyId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM company WHERE company_id=${testCompanyId}`);
    expect(delFlg, 'company が論理削除されているべき').toBe('1');
    await page.close();
  });

  test('ADEL-4: Admin member cmd=delete → del_flg=1 になる', async () => {
    const page = await adminCtx.newPage();
    await page.goto(`/admin/member/cmd=delete/id=${testMemberDelId}/cid=${companyId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM member WHERE member_id=${testMemberDelId}`);
    expect(delFlg, 'member が論理削除されているべき').toBe('1');
    await page.close();
  });
});

// =============================================================================
// グループD: Admin member_edit confirm / registration
// =============================================================================
test.describe('AME. Admin member_edit confirm / registration', () => {
  let adminCtx;
  let testMemberId;
  let companyId;
  const E2E_PASS = 'E2ETestPassAme';

  test.beforeAll(async ({ browser }) => {
    companyId = queryDb(
      `SELECT c.company_id FROM member m JOIN company c ON m.company_id = c.company_id WHERE m.login_id = '${MEMBER_ID}' LIMIT 1`
    );
    execDb(`INSERT INTO member (company_id, authority_id, login_id, pass, name, kana, email1, del_flg, login_flg) VALUES (${companyId}, 1, '${MARKER}ame', '${E2E_PASS}', '${MARKER}AME', 'テスト', '${MARKER}ame@e2e.jp', 0, 1)`);
    testMemberId = queryDb(`SELECT member_id FROM member WHERE login_id='${MARKER}ame' ORDER BY member_id DESC LIMIT 1`);

    adminCtx = await loginAdmin(browser);
  });

  test.afterAll(async () => {
    if (testMemberId) execDb(`UPDATE member SET del_flg=1 WHERE member_id=${testMemberId}`);
    execDb(`UPDATE member SET del_flg=1 WHERE login_id LIKE '${MARKER}%' AND del_flg=0`);
    await adminCtx?.close();
  });

  test('AME-1: Admin member_edit confirm → 確認画面に遷移する', async () => {
    const page = await adminCtx.newPage();

    await page.goto(`/admin/member_edit/id=${testMemberId}/cid=${companyId}`);
    await page.waitForLoadState('domcontentloaded');
    await page.fill('input[name="login_id"]', `${MARKER}ame`);
    await page.fill('input[name="pass"]', E2E_PASS);
    await page.fill('input[name="name"]', `${MARKER}AME`);
    await page.fill('input[name="kana"]', 'テスト');
    await page.fill('input[name="email1"]', `${MARKER}ame@e2e.jp`);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認画面の「登録」ボタンが表示されること').toBeVisible({ timeout: 5000 });

    await page.close();
  });

  test('AME-2: Admin member_edit registration → /admin/member/ へリダイレクトされる', async () => {
    const page = await adminCtx.newPage();

    await page.goto(`/admin/member_edit/id=${testMemberId}/cid=${companyId}`);
    await page.waitForLoadState('domcontentloaded');
    await page.fill('input[name="login_id"]', `${MARKER}ame`);
    await page.fill('input[name="pass"]', E2E_PASS);
    await page.fill('input[name="name"]', `${MARKER}AME`);
    await page.fill('input[name="kana"]', 'テスト');
    await page.fill('input[name="email1"]', `${MARKER}ame@e2e.jp`);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    await page.click('button.bd-reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    await expect(page, '登録後 /admin/member/ へリダイレクトされること').toHaveURL(/\/admin\/member\//);

    await page.close();
  });
});

// =============================================================================
// グループE: IIS member_new confirm / registration → IDEL-2 member delete
// =============================================================================
test.describe('NEW. IIS member_new confirm / registration + IDEL-2 member delete', () => {
  let iisCtx;
  let newMemberId;

  test.beforeAll(async ({ browser }) => {
    iisCtx = await loginIis(browser);
  });

  test.afterAll(async () => {
    // テスト失敗時の保険: MARKER パターンで作成されたメンバーを物理削除
    execDb(`DELETE FROM member WHERE name LIKE '${MARKER}%'`);
    await iisCtx?.close();
  });

  test('NEW-1: IIS member_new confirm → 確認画面に遷移する', async () => {
    const page = await iisCtx.newPage();

    await page.goto('/iis/member_new');
    await page.waitForLoadState('domcontentloaded');
    await page.fill('input[name="name"]', `${MARKER}NEW`);
    await page.fill('input[name="kana"]', 'テスト');
    await page.fill('input[name="email1"]', `${MARKER}new@e2e.jp`);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    const regBtn = page.locator('button.bd-reg-submit-btn');
    await expect(regBtn, '確認画面の「登録」ボタンが表示されること').toBeVisible({ timeout: 5000 });

    await page.close();
  });

  test('NEW-2: IIS member_new registration → DB に member が追加される', async () => {
    const page = await iisCtx.newPage();

    await page.goto('/iis/member_new');
    await page.waitForLoadState('domcontentloaded');
    await page.fill('input[name="name"]', `${MARKER}NEW`);
    await page.fill('input[name="kana"]', 'テスト');
    await page.fill('input[name="email1"]', `${MARKER}new@e2e.jp`);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    await page.click('button.bd-reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');

    newMemberId = queryDb(`SELECT member_id FROM member WHERE name='${MARKER}NEW' AND del_flg=0 ORDER BY member_id DESC LIMIT 1`);
    expect(newMemberId, 'DB に新規 member が追加されているべき').not.toBe('');

    await page.close();
  });

  test('IDEL-2: IIS member cmd=delete → DB から物理削除される', async () => {
    expect(newMemberId, 'NEW-2 が成功している必要がある').toBeTruthy();

    const page = await iisCtx.newPage();
    await page.goto(`/iis/member/cmd=delete/id=${newMemberId}`);
    await page.waitForLoadState('domcontentloaded');

    const count = queryDb(`SELECT COUNT(*) FROM member WHERE member_id=${newMemberId}`);
    expect(count, 'IIS member delete 後 DB から行が消えているべき').toBe('0');

    await page.close();
  });
});

// =============================================================================
// グループF: IIS bd_detail cmd=delete
// =============================================================================
test.describe('IDEL. IIS bd_detail cmd=delete', () => {
  let iisCtx;
  let testBdId;
  let memberId;

  test.beforeAll(async ({ browser }) => {
    memberId = queryDb(`SELECT member_id FROM member WHERE login_id='${MEMBER_ID}' AND del_flg=0 LIMIT 1`);
    execDb(`INSERT INTO business_direct (member_id, title, del_flg) VALUES (${memberId}, '${MARKER}BD_DEL', 0)`);
    testBdId = queryDb(`SELECT business_direct_id FROM business_direct WHERE title='${MARKER}BD_DEL' AND del_flg=0 ORDER BY business_direct_id DESC LIMIT 1`);

    iisCtx = await loginIis(browser);
  });

  test.afterAll(async () => {
    await iisCtx?.close();
  });

  test('IDEL-1: IIS bd_detail cmd=delete → del_flg=1 になる', async () => {
    const page = await iisCtx.newPage();

    await page.goto(`/iis/bd_detail/cmd=delete/id=${testBdId}`);
    await page.waitForLoadState('domcontentloaded');

    const delFlg = queryDb(`SELECT del_flg FROM business_direct WHERE business_direct_id=${testBdId}`);
    expect(delFlg, 'business_direct が論理削除されているべき').toBe('1');

    await page.close();
  });
});

// =============================================================================
// グループG: IIS reminder reminder_send
// =============================================================================
test.describe('REM. IIS reminder_send', () => {
  test('REM-1: reminder_send → 「送信いたしました」完了メッセージが表示される', async ({ page }) => {
    await page.goto('/iis/reminder');
    await page.waitForLoadState('domcontentloaded');

    await page.fill('input[name="email"]', 'nonexistent_e2e@e2e.jp');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('domcontentloaded');

    await expect(
      page.locator('.mem-login-intro'),
      '「送信いたしました」完了メッセージが表示されること'
    ).toContainText('送信いたしました');
  });
});
