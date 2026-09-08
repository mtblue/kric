// @ts-check
/**
 * ライブラリ登録・編集機能 網羅的テスト
 *
 * QAペルソナ: 意地悪なシニアQAエンジニア
 * — 開発者がサボりがちな観点を徹底的に叩く
 *
 * 観点:
 *   A. バリデーション（必須・文字数上限）
 *   B. 境界値（title 100/101文字、doc 10000/10001文字）
 *   C. DB保存の完全一致（一字一句）
 *   D. 詳細ページ（/iis/library_detail）表示 — タグ・XSS
 *   E. 確認ページ表示整合性（入力→確認で一字一句一致）
 *   F. 二重投稿防止（chkno トークン）
 *   G. 編集フロー（既存レコードの修正）
 *   H. 画面をまたいだデータ整合性（入力→確認→DB→詳細）
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const ADMIN_ID    = 'info@kric.co.jp';
const ADMIN_PASS  = 'krickric';
const MEMBER_ID   = 'info@kric.co.jp';
const MEMBER_PASS = 'krickric';
const MARKER = `__E2ELIB__${Date.now()}`;

// ── DB ヘルパー ─────────────────────────────────────────────────────────────

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

function getLibraryId(title) {
  const escaped = title.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
  return queryDb(`SELECT library_id FROM library WHERE title = '${escaped}' ORDER BY library_id DESC LIMIT 1`);
}

function cleanup() {
  execDb(`DELETE FROM library WHERE title LIKE '${MARKER.replace(/'/g, "\\'")}%';`);
}

// ============================================================
// ライブラリ登録・編集 網羅テスト
// ============================================================
test.describe('ライブラリ登録・編集 網羅テスト', () => {
  let adminCtx;   // /admin/library_edit 操作用
  let memberCtx;  // /iis/library_detail 閲覧用

  test.beforeAll(async ({ browser }) => {
    // 管理者ログイン
    adminCtx = await browser.newContext();
    const a = await adminCtx.newPage();
    await a.goto('/admin/login');
    await a.fill('#id', ADMIN_ID);
    await a.fill('#pass', ADMIN_PASS);
    await a.click('button[type="submit"]');
    await a.waitForURL(/admin\//);
    await a.close();

    // 会員ログイン（詳細ページ閲覧用）
    memberCtx = await browser.newContext();
    const m = await memberCtx.newPage();
    await m.goto('/iis/login');
    await m.fill('#login_id', MEMBER_ID);
    await m.fill('#password', MEMBER_PASS);
    await m.click('button[type="submit"]');
    await m.waitForURL(/iis\/index/);
    await m.close();
  });

  test.afterAll(async () => {
    cleanup();
    await adminCtx.close();
    await memberCtx.close();
  });

  // ── 共通ヘルパー ────────────────────────────────────────────

  async function fillLibraryForm(page, overrides = {}) {
    await page.goto('/admin/library_edit');
    await page.waitForLoadState('domcontentloaded');
    const fields = {
      title: `${MARKER} 基本テスト`,
      doc:   'E2Eテスト用ライブラリ内容',
      ...overrides,
    };
    if (fields.title !== undefined) await page.fill('input[name="title"]',  fields.title);
    if (fields.doc   !== undefined) await page.fill('textarea[name="doc"]', fields.doc);
    return fields;
  }

  async function submitConfirm(page) {
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');
  }

  async function assertOnConfirmPage(page) {
    await expect(page.locator('button.bd-reg-submit-btn'), '確認画面の登録ボタンが存在すること').toBeVisible();
  }

  async function assertOnErrorPage(page) {
    await expect(page.locator('button.bd-reg-submit-btn'), 'エラー時は確認画面の登録ボタンが非表示').toBeHidden();
    await expect(page.locator('.reg-error-box'), 'エラーボックスが表示されること').toBeVisible();
  }

  /** 登録まで完了させて library_id を返す */
  async function registerLibrary(page, overrides = {}) {
    const fields = await fillLibraryForm(page, overrides);
    await submitConfirm(page);
    await assertOnConfirmPage(page);
    await page.click('button.bd-reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');
    return getLibraryId(fields.title);
  }

  // ─── A. バリデーション ───────────────────────────────────────

  test.describe('A. バリデーション', () => {

    test('A-1: title が空 → エラーが表示され確認画面に進まない', async () => {
      const page = await adminCtx.newPage();
      await fillLibraryForm(page, { title: '' });
      await submitConfirm(page);
      await assertOnErrorPage(page);
      expect(await page.content()).toContain('タイトルが入力されていません');
      await page.close();
    });

    test('A-2: title と doc ともに空 → title エラーのみ（doc は任意項目）', async () => {
      const page = await adminCtx.newPage();
      await fillLibraryForm(page, { title: '', doc: '' });
      await submitConfirm(page);
      await assertOnErrorPage(page);
      const body = await page.content();
      expect(body).toContain('タイトルが入力されていません');
      expect(body).not.toContain('内容が入力されていません');
      await page.close();
    });

    test('A-3: doc が空でも title があれば確認画面に進める（doc は任意）', async () => {
      const page = await adminCtx.newPage();
      await fillLibraryForm(page, { title: `${MARKER} A3`, doc: '' });
      await submitConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

  });

  // ─── B. 境界値 ──────────────────────────────────────────────

  test.describe('B. 境界値', () => {

    test('B-1: title ちょうど 100文字 → 正常登録（確認画面に進む）', async () => {
      const page = await adminCtx.newPage();
      const base = `${MARKER} B1`;
      const title100 = base + 'あ'.repeat(100 - [...base].length);
      expect([...title100].length, 'title が 100文字であること').toBe(100);
      await fillLibraryForm(page, { title: title100 });
      await submitConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

    test('B-2: title 101文字 → 文字数超過エラー', async () => {
      const page = await adminCtx.newPage();
      const base = `${MARKER} B2`;
      const title101 = base + 'あ'.repeat(101 - [...base].length);
      expect([...title101].length, 'title が 101文字であること').toBe(101);
      await fillLibraryForm(page, { title: title101 });
      await submitConfirm(page);
      await assertOnErrorPage(page);
      expect(await page.content()).toContain('100文字以内');
      await page.close();
    });

    test('B-3: doc ちょうど 10000文字 → 正常登録（確認画面に進む）', async () => {
      const page = await adminCtx.newPage();
      const doc = 'あ'.repeat(10000);
      expect([...doc].length, 'doc が 10000文字であること').toBe(10000);
      await fillLibraryForm(page, { title: `${MARKER} B3`, doc });
      await submitConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

    test('B-4: doc 10001文字 → 文字数超過エラー', async () => {
      const page = await adminCtx.newPage();
      const doc = 'あ'.repeat(10001);
      await fillLibraryForm(page, { title: `${MARKER} B4`, doc });
      await submitConfirm(page);
      await assertOnErrorPage(page);
      expect(await page.content()).toContain('10000文字以内');
      await page.close();
    });

  });

  // ─── C. DB保存の完全一致 ─────────────────────────────────────

  test.describe('C. DB保存の完全一致（一字一句）', () => {

    test('C-1: title が DB に一字一句一致で保存される（全角・記号含む）', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} C1 特殊①㈱「テスト」`;
      await registerLibrary(page, { title, doc: 'C1テスト内容' });
      const dbTitle = queryDb(`SELECT title FROM library WHERE title LIKE '${MARKER} C1%' ORDER BY library_id DESC LIMIT 1`);
      expect(dbTitle, 'DB の title が入力値と一致すること').toBe(title);
      await page.close();
    });

    test('C-2: doc（プレーンテキスト）が DB に一字一句一致で保存される', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} C2`;
      const doc   = 'テスト内容　改行なし　サンプルテキスト　一字一句確認';
      const id = await registerLibrary(page, { title, doc });
      const dbDoc = queryDb(`SELECT doc FROM library WHERE library_id = ${id}`);
      // MySQL バッチモードは改行を \n（2文字エスケープ）で出力するため、改行なし文字列で比較
      expect(dbDoc, 'DB の doc が入力値と一致すること').toBe(doc);
      await page.close();
    });

    test('C-2b: doc に改行 → 詳細ページで改行（<br>）として表示される', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} C2b`;
      const doc   = '1行目テキスト\n2行目テキスト';
      const id = await registerLibrary(page, { title, doc });
      await page.close();

      // 詳細ページで改行が <br> に変換されて表示されることを確認
      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      const bodyHtml = await mp.locator('.lib-detail-body').innerHTML();
      expect(bodyHtml, '改行が <br> として表示されること').toContain('<br');
      expect(bodyHtml).toContain('1行目テキスト');
      expect(bodyHtml).toContain('2行目テキスト');
      await mp.close();
    });

    test('C-3: doc に <a> タグ → DB には生 HTML（entity-encoded でなく）で保存される', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} C3`;
      const doc   = '<a href="http://test.example.com">テストリンク</a>';
      const id = await registerLibrary(page, { title, doc });
      const dbDoc = queryDb(`SELECT doc FROM library WHERE library_id = ${id}`);
      expect(dbDoc, 'DB に生 HTML が保存されること').toContain('<a href=');
      expect(dbDoc, 'DB に &#60; が残らないこと').not.toContain('&#60;');
      await page.close();
    });

    test('C-4: doc に & " \' → DB に生文字で保存される（二重エンコードなし）', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} C4`;
      const doc   = '売買 & 賃貸 "引用" it\'s fine';
      const id = await registerLibrary(page, { title, doc });
      const dbDoc = queryDb(`SELECT doc FROM library WHERE library_id = ${id}`);
      expect(dbDoc).toContain('&');
      expect(dbDoc).not.toContain('&#38;');
      await page.close();
    });

  });

  // ─── D. 詳細ページ（/iis/library_detail）表示 ───────────────

  test.describe('D. 詳細ページ表示（/iis/library_detail）', () => {

    test('D-1: 詳細ページに title が一字一句一致で表示される', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} D1`;
      const id = await registerLibrary(ap, { title, doc: 'D1テスト内容' });
      await ap.close();

      expect(id, 'DBにレコードが作成されていること').not.toBe('');
      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      const h2 = await mp.locator('h2.lib-detail-title').textContent();
      expect(h2?.trim(), '詳細ページの title が登録値と一致すること').toBe(title);
      await mp.close();
    });

    test('D-2: 詳細ページに doc（プレーンテキスト）が一字一句一致で表示される', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} D2`;
      const doc   = 'D2テスト用の本文内容です。一字一句確認。';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      const bodyText = await mp.locator('.lib-detail-body').textContent();
      expect(bodyText?.trim(), '詳細ページの doc テキストが一致すること').toContain('D2テスト用の本文内容です。一字一句確認。');
      await mp.close();
    });

    test('D-3: doc に <a> タグ → 詳細ページでクリッカブルなリンクとして表示される（entity テキストでない）', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} D3`;
      const doc   = '<a href="http://d3.example.com">D3リンクテスト</a>';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      const link = mp.locator('.lib-detail-body a[href="http://d3.example.com"]');
      await expect(link, '<a> タグが実際のリンク要素として表示されること').toBeVisible();
      expect(await link.textContent(), 'リンクテキストが一致すること').toBe('D3リンクテスト');
      await mp.close();
    });

    test('D-4: doc の <script> → 詳細ページで実行されない（XSS防止）', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} D4`;
      const doc   = '<script>alert("xss-D4")</script>テキスト';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      const mp = await memberCtx.newPage();
      let dialogFired = false;
      mp.on('dialog', async d => { dialogFired = true; await d.dismiss(); });
      await mp.goto(`/iis/library_detail/id=${id}`);
      await mp.waitForLoadState('domcontentloaded');

      expect(dialogFired, '<script> が実行されないこと').toBe(false);
      expect(await mp.content()).not.toContain('<script>alert("xss-D4")');
      await mp.close();
    });

    test('D-5: doc の <b><img onerror> → 詳細ページの lib-detail-body から strip_tags により除去される', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} D5`;
      const doc   = '<b>ボールド</b>テキスト<img src="x" onerror="alert(1)">続き';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      const mp = await memberCtx.newPage();
      let dialogFired = false;
      mp.on('dialog', async d => { dialogFired = true; await d.dismiss(); });
      await mp.goto(`/iis/library_detail/id=${id}`);
      await mp.waitForLoadState('domcontentloaded');

      expect(dialogFired, 'onerror が実行されないこと').toBe(false);
      // .lib-detail-body の innerHTML のみで検証（ページ全体HTMLにはヘッダ等に <b> が存在しうる）
      const bodyHtml = await mp.locator('.lib-detail-body').innerHTML();
      expect(bodyHtml, 'lib-detail-body 内に <b> タグが残らないこと').not.toContain('<b>');
      expect(bodyHtml, 'lib-detail-body 内に <img> タグが残らないこと').not.toContain('<img');
      await mp.close();
    });

    test('D-6: 詳細ページの lib-detail-body で <a><br> 以外のタグがすべて strip される', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} D6`;
      const doc   = '<h1>見出し</h1><p>段落</p><span>スパン</span><a href="http://d6.example.com">OK</a>';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      // .lib-detail-body の innerHTML のみで検証（ページ全体HTMLには <p> 等がヘッダーに存在する）
      const bodyHtml = await mp.locator('.lib-detail-body').innerHTML();
      expect(bodyHtml, 'lib-detail-body 内に <h1> タグが残らないこと').not.toContain('<h1>');
      expect(bodyHtml, 'lib-detail-body 内に <p> タグが残らないこと').not.toContain('<p>');
      expect(bodyHtml, 'lib-detail-body 内に <span> タグが残らないこと').not.toContain('<span>');
      // <a> は残る
      await expect(mp.locator('.lib-detail-body a[href="http://d6.example.com"]')).toBeVisible();
      await mp.close();
    });

  });

  // ─── E. 確認ページ表示整合性 ─────────────────────────────────

  test.describe('E. 確認ページ表示整合性（入力→確認）', () => {

    test('E-1: 入力した title が確認ページに一字一句一致で表示される', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} E1 タイトル確認`;
      await fillLibraryForm(page, { title });
      await submitConfirm(page);
      await assertOnConfirmPage(page);
      expect(await page.content()).toContain(title);
      await page.close();
    });

    test('E-2: doc の <a> タグが確認ページでクリッカブルなリンクとして表示される', async () => {
      const page = await adminCtx.newPage();
      await fillLibraryForm(page, { doc: '<a href="http://e2.example.com">確認リンク</a>' });
      await submitConfirm(page);
      await assertOnConfirmPage(page);
      const link = page.locator('.info-detail-body a[href="http://e2.example.com"]');
      await expect(link, '確認ページで <a> がリンクとして表示されること').toBeVisible();
      await page.close();
    });

    test('E-3: doc の <script> タグが確認ページで実行されない（XSS防止）', async () => {
      const page = await adminCtx.newPage();
      await fillLibraryForm(page, { doc: '<script>alert("xss-confirm")</script>' });

      let dialogFired = false;
      page.on('dialog', async d => { dialogFired = true; await d.dismiss(); });

      await submitConfirm(page);
      await page.waitForLoadState('domcontentloaded');
      expect(dialogFired, '確認ページで XSS が実行されないこと').toBe(false);
      await page.close();
    });

  });

  // ─── F. 二重投稿防止 ─────────────────────────────────────────

  test.describe('F. 二重投稿防止（chkno トークン）', () => {

    test('F-1: 確認ページに chkno トークンが存在し、同一 POST の再送を防止する', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} F1`;

      await fillLibraryForm(page, { title });
      await submitConfirm(page);
      await assertOnConfirmPage(page);

      // 確認ページのフォームデータを全取得
      const formData = await page.evaluate(() => {
        const form = document.querySelector('form');
        const data = {};
        for (const el of form.elements) {
          if (el.name && el.value !== undefined) data[el.name] = el.value;
        }
        return data;
      });

      // chkno が存在し空でないこと（bd_edit と同等の防止策）
      expect(formData['chkno'], '確認ページに chkno hidden フィールドが存在すること').toBeTruthy();
      console.log(`[F-1] chkno 存在: true, 値: ${formData['chkno']}`);

      // 1回目の登録（正規フロー）
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');
      const count1 = queryDb(`SELECT COUNT(*) FROM library WHERE title = '${title}'`);
      expect(count1, '1回目登録後: 1件存在すること').toBe('1');

      // 同じ chkno で 2回目 POST → chkno 不一致でリダイレクトされ二重登録されない
      const resp2 = await page.request.post('/admin/library_edit', { form: formData });
      expect([200, 302]).toContain(resp2.status());

      const count2 = queryDb(`SELECT COUNT(*) FROM library WHERE title = '${title}'`);
      expect(count2, 'chkno 不一致により 2回目 POST がガードされ 1件のまま').toBe('1');

      await page.close();
    });

    test('F-2: 古い chkno を含む POST を直接再送しても二重登録されない', async () => {
      const page = await adminCtx.newPage();
      const title = `${MARKER} F2`;

      await fillLibraryForm(page, { title });
      await submitConfirm(page);
      await assertOnConfirmPage(page);

      // 確認ページのフォームデータ（古い chkno）を保存
      const staleFormData = await page.evaluate(() => {
        const form = document.querySelector('form');
        const data = {};
        for (const el of form.elements) {
          if (el.name && el.value !== undefined) data[el.name] = el.value;
        }
        return data;
      });
      expect(staleFormData['chkno'], '古い chkno が取得できること').toBeTruthy();

      // 1回目の登録（正規フロー、chkno が消費される）
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');
      const count1 = queryDb(`SELECT COUNT(*) FROM library WHERE title = '${title}'`);
      expect(count1, '1回目登録後: 1件').toBe('1');

      // 古い chkno を含む同一 POST を直接再送（中間者攻撃・F5押下相当）
      const resp2 = await page.request.post('/admin/library_edit', { form: staleFormData });
      expect([200, 302]).toContain(resp2.status());

      const count2 = queryDb(`SELECT COUNT(*) FROM library WHERE title = '${title}'`);
      expect(count2, '古い chkno は拒否され DB は 1件のまま').toBe('1');

      await page.close();
    });

  });

  // ─── G. 編集フロー（既存レコードの修正） ──────────────────

  test.describe('G. 編集フロー（既存レコードの修正）', () => {

    test('G-1: 既存レコードの編集画面に title・doc が正しく表示される', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} G1`;
      const doc   = 'G1テスト本文です。';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      expect(id, 'DBにレコードが作成されていること').not.toBe('');
      const ep = await adminCtx.newPage();
      await ep.goto(`/admin/library_edit/id=${id}`);
      await ep.waitForLoadState('domcontentloaded');

      expect(await ep.locator('input[name="title"]').inputValue(),  '編集画面の title が一致すること').toBe(title);
      expect(await ep.locator('textarea[name="doc"]').inputValue(), '編集画面の doc が一致すること').toBe(doc);
      await ep.close();
    });

    test('G-2: <a> タグを含む既存レコード → 編集画面の textarea に生 HTML が表示される（エンティティ化なし）', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} G2`;
      const doc   = '<a href="http://g2.example.com">G2リンク</a>';
      const id = await registerLibrary(ap, { title, doc });
      await ap.close();

      const ep = await adminCtx.newPage();
      await ep.goto(`/admin/library_edit/id=${id}`);
      await ep.waitForLoadState('domcontentloaded');

      const docVal = await ep.locator('textarea[name="doc"]').inputValue();
      expect(docVal, 'textarea に <a> タグが表示されること').toContain('<a href=');
      expect(docVal, '&#60; が残らないこと（エンティティ化なし）').not.toContain('&#60;');
      await ep.close();
    });

    test('G-3: 既存レコード編集後、変更値が DB に上書き保存される', async () => {
      const ap = await adminCtx.newPage();
      const titleOld = `${MARKER} G3 OLD`;
      const titleNew = `${MARKER} G3 NEW`;
      const id = await registerLibrary(ap, { title: titleOld, doc: 'G3旧内容' });
      await ap.close();

      const ep = await adminCtx.newPage();
      await ep.goto(`/admin/library_edit/id=${id}`);
      await ep.waitForLoadState('domcontentloaded');
      await ep.fill('input[name="title"]',  titleNew);
      await ep.fill('textarea[name="doc"]', 'G3新内容（変更後）');
      await submitConfirm(ep);
      await assertOnConfirmPage(ep);
      await ep.click('button.bd-reg-submit-btn');
      await ep.waitForLoadState('domcontentloaded');

      expect(queryDb(`SELECT title FROM library WHERE library_id = ${id}`), '変更後 title がDBに保存されること').toBe(titleNew);
      expect(queryDb(`SELECT doc FROM library WHERE library_id = ${id}`),   '変更後 doc がDBに保存されること').toBe('G3新内容（変更後）');
      await ep.close();
    });

    test('G-4: 編集画面で戻るを押すと入力フォームに戻れ、title が引き継がれる', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} G4`;
      const id = await registerLibrary(ap, { title, doc: 'G4内容' });
      await ap.close();

      const ep = await adminCtx.newPage();
      await ep.goto(`/admin/library_edit/id=${id}`);
      await ep.waitForLoadState('domcontentloaded');
      await submitConfirm(ep);
      await assertOnConfirmPage(ep);

      await ep.click('.bd-reg-back-btn');
      await ep.waitForLoadState('domcontentloaded');

      expect(await ep.locator('input[name="title"]').inputValue(), '戻ったフォームの title が維持されること').toBe(title);
      await ep.close();
    });

  });

  // ─── H. 画面をまたいだデータ整合性 ─────────────────────────

  test.describe('H. 画面をまたいだデータ整合性（入力→確認→DB→詳細）', () => {

    test('H-1: title が 入力→確認→DB→詳細ページ で一字一句一致', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} H1 整合性テスト①`;
      await fillLibraryForm(ap, { title, doc: 'H1テスト' });
      await submitConfirm(ap);
      await assertOnConfirmPage(ap);

      // 確認ページで title が表示されること
      expect(await ap.content()).toContain(title);

      await ap.click('button.bd-reg-submit-btn');
      await ap.waitForLoadState('domcontentloaded');
      await ap.close();

      const id = getLibraryId(title);
      // DB
      expect(queryDb(`SELECT title FROM library WHERE library_id = ${id}`), 'DBの title が一致すること').toBe(title);
      // 詳細ページ
      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      const h2 = await mp.locator('h2.lib-detail-title').textContent();
      expect(h2?.trim(), '詳細ページの title が一致すること').toBe(title);
      await mp.close();
    });

    test('H-2: doc（<a>含む）が 入力→確認→DB→詳細ページ で一貫して正しく扱われる', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} H2`;
      const doc   = 'テキスト<a href="http://h2.example.com">H2リンク</a>テキスト続き';
      await fillLibraryForm(ap, { title, doc });
      await submitConfirm(ap);
      await assertOnConfirmPage(ap);

      // 確認ページ: <a> がリンクとして表示されること
      await expect(ap.locator('.info-detail-body a[href="http://h2.example.com"]'), '確認ページで <a> がリンク表示').toBeVisible();

      await ap.click('button.bd-reg-submit-btn');
      await ap.waitForLoadState('domcontentloaded');
      await ap.close();

      const id = getLibraryId(title);
      // DB: 生 HTML で保存
      const dbDoc = queryDb(`SELECT doc FROM library WHERE library_id = ${id}`);
      expect(dbDoc, 'DBに生 HTML が保存されること').toContain('<a href=');
      expect(dbDoc, 'DBに &#60; が残らないこと').not.toContain('&#60;');

      // 詳細ページ: <a> がリンクとして表示されること
      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      const link = mp.locator('.lib-detail-body a[href="http://h2.example.com"]');
      await expect(link, '詳細ページで <a> がリンク表示されること').toBeVisible();
      expect(await link.textContent(), 'リンクテキストが一致すること').toBe('H2リンク');
      await mp.close();
    });

    test('H-3: 戻るを経由した後の登録データが DB・詳細ページで正しく反映される', async () => {
      const ap = await adminCtx.newPage();
      const title = `${MARKER} H3`;
      const doc   = '<a href="http://h3.example.com">H3リンク</a>';

      await fillLibraryForm(ap, { title, doc });
      await submitConfirm(ap);
      await assertOnConfirmPage(ap);

      // 戻る → doc が正しく復元されること
      await ap.click('.bd-reg-back-btn');
      await ap.waitForLoadState('domcontentloaded');
      const docVal = await ap.locator('textarea[name="doc"]').inputValue();
      expect(docVal, '戻り後の textarea に <a> タグが表示されること').toContain('<a href=');
      expect(docVal, '&#60; が残らないこと').not.toContain('&#60;');

      // 再確認 → 登録
      await submitConfirm(ap);
      await assertOnConfirmPage(ap);
      await ap.click('button.bd-reg-submit-btn');
      await ap.waitForLoadState('domcontentloaded');
      await ap.close();

      const id = getLibraryId(title);
      expect(queryDb(`SELECT title FROM library WHERE library_id = ${id}`), 'DBに title が保存されること').toBe(title);

      const mp = await memberCtx.newPage();
      await mp.goto(`/iis/library_detail/id=${id}`);
      await expect(mp.locator('.lib-detail-body a[href="http://h3.example.com"]'), '詳細ページで <a> がリンク表示').toBeVisible();
      await mp.close();
    });

  });

}); // ライブラリ登録・編集 網羅テスト end
