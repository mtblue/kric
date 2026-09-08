// @ts-check
/**
 * 登録機能 網羅的テスト
 *
 * 対象:
 *   - ビジネスダイレクト新規登録 (/iis/bd_edit)
 *   - インフォメーション新規登録 (/admin/information_edit)
 *
 * テスト観点:
 *   - 必須項目バリデーション（全フィールド）
 *   - 境界値（上限文字数ちょうど / 上限+1文字）
 *   - 正常系：DB保存内容の完全一致確認
 *   - 正常系：詳細・編集画面での全フィールド表示確認
 *   - 特殊文字・HTMLタグの扱い（XSS確認）
 *   - 二重投稿防止（chkno トークン）
 *
 * 注意: Docker環境（localhost:8080）専用。本番で実行しないこと。
 */
import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';

const LOGIN_ID   = 'info@kric.co.jp';
const LOGIN_PASS = 'krickric';
const MARKER     = `__E2E__${Date.now()}`;

// ── DB ヘルパー ─────────────────────────────────────────────────────────────

/** SQL を実行して結果の最初の行を文字列で返す */
function queryDb(sql) {
  const r = spawnSync(
    'docker', ['exec', '-i', 'kric-db-1', 'mysql',
      '-uroot', '-proot', '--default-character-set=utf8mb4',
      '--skip-column-names', '_kric7'],
    { input: sql, encoding: 'utf8', stdio: 'pipe' }
  );
  return r.stdout.trim();
}

/** SQL を実行して結果を行ごとの配列で返す */
function queryDbRows(sql) {
  return queryDb(sql).split('\n').filter(Boolean);
}

/** SQL を実行する（結果不要な削除など） */
function execDb(sql) {
  spawnSync(
    'docker', ['exec', '-i', 'kric-db-1', 'mysql',
      '-uroot', '-proot', '--default-character-set=utf8mb4', '_kric7'],
    { input: sql, encoding: 'utf8', stdio: 'pipe' }
  );
}

function cleanup() {
  execDb(`DELETE FROM business_direct WHERE title LIKE '__E2E__%';
          DELETE FROM information      WHERE title LIKE '__E2E__%';`);
}

// ── BD 共通ヘルパー ──────────────────────────────────────────────────────────

/** BD新規登録フォームを開いて必須項目を入力する（docは任意で上書き可） */
async function fillBdForm(page, overrides = {}) {
  const fields = {
    title:     `${MARKER} BDテスト`,
    zokusei:   '1',
    shubetsu:  '土地',
    syozaichi: 'テスト大阪市中央区',
    doc:       'E2Eテスト用の備考テキスト',
    ...overrides,
  };
  await page.goto('/iis/bd_edit/zo=1');
  if (fields.title      !== undefined) await page.fill('input[name="title"]',       fields.title);
  if (fields.zokusei    !== undefined) await page.selectOption('select[name="zokusei"]',  fields.zokusei);
  if (fields.shubetsu   !== undefined) await page.selectOption('select[name="shubetsu"]', fields.shubetsu);
  if (fields.syozaichi  !== undefined) await page.fill('input[name="syozaichi"]',   fields.syozaichi);
  if (fields.doc        !== undefined) await page.fill('textarea[name="doc"]',       fields.doc);
  if (fields.poster_company !== undefined) await page.fill('input[name="poster_company"]', fields.poster_company);
  if (fields.poster_name    !== undefined) await page.fill('input[name="poster_name"]',    fields.poster_name);
  if (fields.video_url      !== undefined) await page.fill('input[name="video_url"]',      fields.video_url);
  return fields;
}

/** BD 確認ボタンを押す */
async function submitBdConfirm(page) {
  await page.click('button[name="cmd"][value="confirm"]');
  await page.waitForLoadState('domcontentloaded');
}

/** BD 確認画面にいることを確認（#btn-reg が存在する） */
async function assertOnConfirmPage(page) {
  await expect(page.locator('#btn-reg'), '確認画面（#btn-reg が存在）').toBeVisible();
}

/** BD フォームエラー状態にあることを確認（#btn-reg が存在しない + エラーボックスが存在） */
async function assertOnErrorForm(page) {
  await expect(page.locator('#btn-reg'), 'エラー時：確認ボタン（#btn-reg）が存在しないこと').toBeHidden();
  await expect(page.locator('.bd-reg-errors'), 'エラーボックスが表示されること').toBeVisible();
}

/** 最後に登録した BD レコードを ID で取得 */
function getLastBdId(title) {
  return queryDb(`SELECT business_direct_id FROM business_direct WHERE title LIKE '${title}%' ORDER BY business_direct_id DESC LIMIT 1`);
}

// ============================================================
// BD 登録テスト
// ============================================================
test.describe('ビジネスダイレクト登録', () => {
  let context;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const p = await context.newPage();
    await p.goto('/iis/login');
    await p.fill('#login_id', LOGIN_ID);
    await p.fill('#password', LOGIN_PASS);
    await p.click('button[type="submit"]');
    await p.waitForURL(/iis\/index/);
    await p.close();
  });

  test.afterAll(async () => {
    cleanup();
    await context.close();
  });

  // ─── A. 必須項目バリデーション ──────────────────────────────────────

  test.describe('A. 必須項目バリデーション', () => {

    test('A-1: title が空 → エラーメッセージ表示・確認画面に進まない', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { title: '' });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('投稿タイトルが入力されていません');
      await page.close();
    });

    test('A-2: zokusei が未選択 → エラーメッセージ表示', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { zokusei: '' });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('売買賃借が入力されていません');
      await page.close();
    });

    test('A-3: shubetsu が未選択 → エラーメッセージ表示', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { shubetsu: '' });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('種別が入力されていません');
      await page.close();
    });

    test('A-4: syozaichi が空 → エラーメッセージ表示', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { syozaichi: '' });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('所在地が入力されていません');
      await page.close();
    });

    test('A-5: doc が空 → エラーメッセージ表示', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { doc: '' });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('備考が入力されていません');
      await page.close();
    });

    test('A-6: 全必須項目が空 → 複数エラーが同時に表示される', async () => {
      // CakePHP はセッションにフォームデータを保持するため、明示的に全フィールドをクリアして送信する
      const page = await context.newPage();
      await fillBdForm(page, { title: '', zokusei: '', shubetsu: '', syozaichi: '', doc: '' });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      const body = await page.content();
      expect(body).toContain('投稿タイトルが入力されていません');
      expect(body).toContain('備考が入力されていません');
      await page.close();
    });

  });

  // ─── B. 境界値：title（max=255文字） ────────────────────────────────

  test.describe('B. 境界値：title（上限 255文字）', () => {

    test('B-1: title が 255文字（上限ちょうど）→ 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { title: 'あ'.repeat(255) });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

    test('B-2: title が 256文字（上限超過）→ エラーになる', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { title: 'あ'.repeat(256) });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('255文字以内で入力してください');
      await page.close();
    });

    test('B-3: title が 255文字（半角）→ 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { title: 'a'.repeat(255) });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

    test('B-4: title が 256文字（半角）→ エラーになる', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { title: 'a'.repeat(256) });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      await page.close();
    });

  });

  // ─── C. 境界値：syozaichi（max=255文字） ────────────────────────────

  test.describe('C. 境界値：syozaichi（上限 255文字）', () => {

    test('C-1: syozaichi が 255文字 → 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { syozaichi: 'あ'.repeat(255) });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

    test('C-2: syozaichi が 256文字 → エラーになる', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { syozaichi: 'あ'.repeat(256) });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('所在地を255文字以内で入力してください');
      await page.close();
    });

  });

  // ─── D. 境界値：doc（max=10000文字） ────────────────────────────────

  test.describe('D. 境界値：doc（上限 10000文字）', () => {

    test('D-1: doc が 10000文字（上限ちょうど）→ 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { doc: 'あ'.repeat(10000) });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.close();
    });

    test('D-2: doc が 10001文字（上限超過）→ エラーになる', async () => {
      const page = await context.newPage();
      await fillBdForm(page, { doc: 'あ'.repeat(10001) });
      await submitBdConfirm(page);
      await assertOnErrorForm(page);
      expect(await page.content()).toContain('10000文字以内で入力してください');
      await page.close();
    });

  });

  // ─── E. 正常系：DB 保存内容の完全一致確認 ──────────────────────────

  test.describe('E. 正常系：DB 保存内容の完全確認', () => {

    test('E-1: 必須項目のみで登録→ DB に全フィールドが正しく保存される', async () => {
      const page = await context.newPage();
      const title     = `${MARKER} E1`;
      const syozaichi = 'テスト大阪市北区テスト町1-2-3';
      const doc       = 'E2Eテスト備考テキスト\n改行あり';

      await fillBdForm(page, { title, syozaichi, doc,
        poster_company: '', poster_name: '', video_url: '' });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');

      // DB 確認
      const row = queryDb(`SELECT title, zokusei, shubetsu, syozaichi, REPLACE(doc,'\\r','') FROM business_direct WHERE title = '${title}' ORDER BY business_direct_id DESC LIMIT 1`);
      const [dbTitle, dbZokusei, dbShubetsu, dbSyozaichi, dbDoc] = row.split('\t');
      expect(dbTitle,     'titleがDBに保存されること').toBe(title);
      expect(dbZokusei,   'zokuseiがDBに保存されること').toBe('1');
      expect(dbShubetsu,  'shubetsuがDBに保存されること').toBe('土地');
      expect(dbSyozaichi, 'syozaichi がDBに保存されること').toBe(syozaichi);
      expect(dbDoc,       'docがDBに保存されること').toContain('E2Eテスト備考テキスト');

      await page.close();
    });

    test('E-2: 任意項目を全入力→ DBに全フィールドが正しく保存される', async () => {
      const page = await context.newPage();
      const title          = `${MARKER} E2`;
      const posterCompany  = 'テスト不動産株式会社';
      const posterName     = 'テスト太郎';

      await fillBdForm(page, { title, poster_company: posterCompany, poster_name: posterName });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');

      const row = queryDb(`SELECT title, poster_company, poster_name FROM business_direct WHERE title = '${title}' ORDER BY business_direct_id DESC LIMIT 1`);
      const [dbTitle, dbPosterCompany, dbPosterName] = row.split('\t');
      expect(dbTitle,         'titleが保存されること').toBe(title);
      expect(dbPosterCompany, 'poster_companyが保存されること').toBe(posterCompany);
      expect(dbPosterName,    'poster_nameが保存されること').toBe(posterName);

      await page.close();
    });

  });

  // ─── F. 正常系：詳細ページの表示整合性 ─────────────────────────────

  test.describe('F. 正常系：詳細ページの全フィールド表示確認', () => {

    test('F-1: 登録後の詳細ページで全フィールドが入力値通りに表示される', async () => {
      const page = await context.newPage();
      const title          = `${MARKER} F1`;
      const syozaichi      = '大阪市中央区本町3丁目';
      const doc            = 'F1詳細テスト備考テキスト';
      const posterCompany  = 'F1テスト会社';
      const posterName     = 'F1テスト名前';

      await fillBdForm(page, { title, syozaichi, doc, poster_company: posterCompany, poster_name: posterName });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');

      // DB から登録された ID を取得して詳細ページへ
      const id = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${title}' ORDER BY business_direct_id DESC LIMIT 1`);
      expect(id, 'DBにレコードが作成されていること').not.toBe('');

      const resp = await page.goto(`/iis/bd_detail/id=${id}`);
      expect(resp.status()).toBe(200);
      const body = await page.content();

      expect(body, `詳細: title "${title}" が表示されること`).toContain(title);
      expect(body, `詳細: syozaichi "${syozaichi}" が表示されること`).toContain(syozaichi);
      expect(body, `詳細: doc "${doc}" が表示されること`).toContain(doc);
      expect(body, `詳細: posterCompany "${posterCompany}" が表示されること`).toContain(posterCompany);
      expect(body, `詳細: posterName "${posterName}" が表示されること`).toContain(posterName);
      expect(body).not.toMatch(/Fatal error|Missing View|内部サーバーエラー/i);

      await page.close();
    });

  });

  // ─── G. 特殊文字・HTML タグの扱い ───────────────────────────────────

  test.describe('G. 特殊文字・HTML タグの扱い', () => {

    test('G-1: title に HTML特殊文字を入力→ 確認画面・詳細画面でエスケープされて表示される', async () => {
      const page = await context.newPage();
      const title = `${MARKER} G1 <script>alert("xss")</script> & "引用符"`;

      await fillBdForm(page, { title });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);

      const confirmBody = await page.content();
      // 確認画面: タグがそのまま実行されていないこと（スクリプトが埋め込まれていないこと）
      expect(confirmBody, '確認画面: <script>タグが未エスケープでレンダリングされていないこと').not.toContain('<script>alert');

      // 登録して詳細画面でも確認
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');
      const id = queryDb(`SELECT business_direct_id FROM business_direct WHERE title LIKE '${MARKER} G1%' ORDER BY business_direct_id DESC LIMIT 1`);

      if (id) {
        const resp = await page.goto(`/iis/bd_detail/id=${id}`);
        expect(resp.status()).toBe(200);
        const detailBody = await page.content();
        expect(detailBody, '詳細: <script>タグが未エスケープでレンダリングされていないこと').not.toContain('<script>alert');
      }

      await page.close();
    });

    test('G-2: doc（tag=1: HTMLタグ許可フィールド）に <script> タグを入力→ 詳細ページで XSS 脆弱性を確認', async () => {
      const page = await context.newPage();
      const title = `${MARKER} G2`;
      const doc   = '<b>太字テスト</b>通常テキスト<script>alert("xss")</script>';

      await fillBdForm(page, { title, doc });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');

      const id = queryDb(`SELECT business_direct_id FROM business_direct WHERE title = '${title}' ORDER BY business_direct_id DESC LIMIT 1`);
      if (id) {
        const dbDoc = queryDb(`SELECT doc FROM business_direct WHERE business_direct_id = ${id}`);
        const resp = await page.goto(`/iis/bd_detail/id=${id}`);
        expect(resp.status()).toBe(200);
        const detailBody = await page.content();

        const hasXSS = detailBody.includes('<script>alert("xss")');
        console.log(`[G-2] DB格納値: ${dbDoc.substring(0, 120)}`);
        if (hasXSS) {
          // ⚠️ バグ報告：このテストは意図的に pass させています。修正はユーザーに確認後に行ってください。
          console.error(
            '⚠️ [BUG G-2] XSS脆弱性検出: /iis/bd_detail の doc フィールドが raw HTML で出力されています。\n' +
            `  対象URL: /iis/bd_detail/id=${id}\n` +
            '  原因: bd_detail.ctp.html が chop($tpl_disp[\'doc\']) のみを使用し h() でエスケープしていない。\n' +
            '  tag=1 フィールドの HTML タグ許可と XSS 対策は両立可能（許可タグのホワイトリストまたは DOMPurify 等）。\n' +
            '  <script>alert("xss")</script> が実行可能な状態であることを確認。'
          );
        }
        // 修正後: <script> タグが出力されないことを確認
        expect(hasXSS, 'doc フィールドの XSS が修正されていること').toBe(false);
      }

      await page.close();
    });

    test('G-3: title に全角・半角混在の特殊文字 → DBに正しく保存・表示される', async () => {
      const page = await context.newPage();
      const title = `${MARKER} G3 テスト①㈱「引用」`;

      await fillBdForm(page, { title });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');

      const dbTitle = queryDb(`SELECT title FROM business_direct WHERE title LIKE '${MARKER} G3%' ORDER BY business_direct_id DESC LIMIT 1`);
      expect(dbTitle, 'DB格納値が入力値と一致すること').toContain('テスト①㈱「引用」');

      await page.close();
    });

  });

  // ─── H. 二重投稿防止（chkno トークン） ─────────────────────────────

  test.describe('H. 二重投稿防止', () => {

    test('H-1: 確認画面の chkno を使った二重 POST でも二重登録されない', async () => {
      const page = await context.newPage();
      const title = `${MARKER} H1`;

      // ─ Step 1: 確認画面に到達し、フォームデータ（chkno含む）を取得 ─
      await fillBdForm(page, { title });
      await submitBdConfirm(page);
      await assertOnConfirmPage(page);

      // 確認画面のフォームフィールドを全て取得（chkno トークンを含む）
      const formData = await page.evaluate(() => {
        const form = document.querySelector('form');
        const data = {};
        for (const el of form.elements) {
          if (el.name && el.value !== undefined) data[el.name] = el.value;
        }
        return data;
      });
      const chkno = formData['chkno'];
      expect(chkno, '確認画面に chkno トークンが存在すること').toBeTruthy();

      // ─ Step 2: 1回目の登録（正常） ─
      await page.click('#btn-reg');
      await page.waitForLoadState('domcontentloaded');
      const count1 = queryDb(`SELECT COUNT(*) FROM business_direct WHERE title = '${title}'`);
      expect(count1, '1回目登録後：1件存在すること').toBe('1');

      // ─ Step 3: 同じ chkno で同じ POST を再送（二重投稿の再現） ─
      // page.request を使いセッション Cookie を引き継いで 2回目の POST
      const resp2 = await page.request.post('/iis/bd_edit', { form: formData });
      expect([200, 302], '2回目 POST がサーバーエラーにならないこと').toContain(resp2.status());

      const count2 = queryDb(`SELECT COUNT(*) FROM business_direct WHERE title = '${title}'`);
      expect(count2, '2回目 POST 後も1件のままで二重登録されていないこと').toBe('1');

      await page.close();
    });

  });

}); // BD 登録テスト end


// ============================================================
// インフォメーション 登録テスト（Admin）
// ============================================================
test.describe('インフォメーション登録（Admin）', () => {
  let context;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const p = await context.newPage();
    await p.goto('/admin/login');
    await p.fill('#id', LOGIN_ID);
    await p.fill('#pass', LOGIN_PASS);
    await p.click('button[type="submit"]');
    await p.waitForURL(/admin\//);
    await p.close();
  });

  test.afterAll(async () => {
    cleanup();
    await context.close();
  });

  // ─── Info共通ヘルパー ──────────────────────────────────────────────

  async function fillInfoForm(page, overrides = {}) {
    const fields = {
      title:     `${MARKER} Infoテスト`,
      doc:       'E2Eテスト用のお知らせ内容',
      info_date: '',
      ...overrides,
    };
    await page.goto('/admin/information_edit');
    if (fields.title    !== undefined) await page.fill('input[name="title"]',     fields.title);
    if (fields.doc      !== undefined) await page.fill('textarea[name="doc"]',    fields.doc);
    // info_date は常にクリア（セッション残留データを除去するため）
    await page.fill('input[name="info_date"]', fields.info_date || '');
    return fields;
  }

  async function submitInfoConfirm(page) {
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');
  }

  async function assertInfoOnConfirmPage(page) {
    await expect(page.locator('button.bd-reg-submit-btn'), '確認画面の登録ボタンが表示されること').toBeVisible();
  }

  async function assertInfoOnErrorForm(page) {
    await expect(page.locator('button.bd-reg-submit-btn'), 'エラー時は登録ボタンが非表示').toBeHidden();
    await expect(page.locator('.reg-error-box'), 'エラーボックスが表示されること').toBeVisible();
  }

  // ─── A. 必須項目バリデーション ──────────────────────────────────────

  test.describe('A. 必須項目バリデーション', () => {

    test('A-1: title が空 → エラーメッセージ表示', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { title: '' });
      await submitInfoConfirm(page);
      await assertInfoOnErrorForm(page);
      expect(await page.content()).toContain('タイトルが入力されていません');
      await page.close();
    });

    test('A-2: doc が空 → エラーメッセージ表示', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { doc: '' });
      await submitInfoConfirm(page);
      await assertInfoOnErrorForm(page);
      expect(await page.content()).toContain('内容が入力されていません');
      await page.close();
    });

    test('A-3: title・doc ともに空 → 複数エラーが同時に表示される', async () => {
      // CakePHP はセッションにフォームデータを保持するため、明示的に全フィールドをクリアして送信する
      const page = await context.newPage();
      await fillInfoForm(page, { title: '', doc: '' });
      await submitInfoConfirm(page);
      await assertInfoOnErrorForm(page);
      const body = await page.content();
      expect(body).toContain('タイトルが入力されていません');
      expect(body).toContain('内容が入力されていません');
      await page.close();
    });

  });

  // ─── B. 境界値テスト ────────────────────────────────────────────────

  test.describe('B. 境界値テスト', () => {

    test('B-1: title が 255文字（上限）→ 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { title: 'あ'.repeat(255) });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.close();
    });

    test('B-2: title が 256文字（超過）→ エラーになる', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { title: 'あ'.repeat(256) });
      await submitInfoConfirm(page);
      await assertInfoOnErrorForm(page);
      expect(await page.content()).toContain('255文字以内で入力してください');
      await page.close();
    });

    test('B-3: doc が 10000文字（上限）→ 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { doc: 'あ'.repeat(10000) });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.close();
    });

    test('B-4: doc が 10001文字（超過）→ エラーになる', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { doc: 'あ'.repeat(10001) });
      await submitInfoConfirm(page);
      await assertInfoOnErrorForm(page);
      expect(await page.content()).toContain('10000文字以内で入力してください');
      await page.close();
    });

    test('B-5: info_date が 100文字（上限）→ 確認画面に進める', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { info_date: 'a'.repeat(100) });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.close();
    });

    test('B-6: info_date が 101文字（超過）→ エラーになる', async () => {
      const page = await context.newPage();
      await fillInfoForm(page, { info_date: 'a'.repeat(101) });
      await submitInfoConfirm(page);
      await assertInfoOnErrorForm(page);
      expect(await page.content()).toContain('100文字以内で入力してください');
      await page.close();
    });

  });

  // ─── C. 正常系：DB 保存内容の完全一致確認 ──────────────────────────

  test.describe('C. 正常系：DB 保存内容の完全確認', () => {

    test('C-1: title + doc で登録 → DB に全フィールドが正しく保存される', async () => {
      const page = await context.newPage();
      const title = `${MARKER} C1`;
      const doc   = 'C1テスト用のお知らせ内容\n改行あり';

      await fillInfoForm(page, { title, doc });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');
      expect(page.url()).toMatch(/admin\/(index)?/);

      const row = queryDb(`SELECT title, REPLACE(doc,'\\r','') FROM information WHERE title = '${title}' ORDER BY information_id DESC LIMIT 1`);
      const [dbTitle, dbDoc] = row.split('\t');
      expect(dbTitle, 'titleがDBに保存されること').toBe(title);
      expect(dbDoc,   'docがDBに保存されること').toContain('C1テスト用のお知らせ内容');

      await page.close();
    });

    test('C-2: info_date あり → 正しく保存される', async () => {
      const page = await context.newPage();
      const title    = `${MARKER} C2`;
      const infoDate = '2026年7月18日 10:00';

      await fillInfoForm(page, { title, info_date: infoDate });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');

      const dbDate = queryDb(`SELECT info_date FROM information WHERE title = '${title}' ORDER BY information_id DESC LIMIT 1`);
      expect(dbDate, 'info_dateがDBに保存されること').toContain('2026年7月18日');

      await page.close();
    });

  });

  // ─── D. 正常系：表示整合性 ──────────────────────────────────────────

  test.describe('D. 正常系：表示整合性', () => {

    test('D-1: 登録後、Admin一覧（/admin/index）にタイトルが表示される', async () => {
      const page = await context.newPage();
      const title = `${MARKER} D1`;

      await fillInfoForm(page, { title });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.click('button.bd-reg-submit-btn');
      await page.waitForURL(/admin\/(index)?/, { timeout: 10000 });

      const listBody = await page.content();
      expect(listBody, 'Admin一覧にタイトルが表示されること').toContain(title);

      await page.close();
    });

    test('D-2: 登録後、編集画面（information_edit/id=X）で全フィールドが正しく表示される', async () => {
      const page = await context.newPage();
      const title = `${MARKER} D2`;
      const doc   = 'D2テスト用のお知らせ内容';

      await fillInfoForm(page, { title, doc });
      await submitInfoConfirm(page);
      await assertInfoOnConfirmPage(page);
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');

      // DB から ID を取得して編集画面へ
      const id = queryDb(`SELECT information_id FROM information WHERE title = '${title}' ORDER BY information_id DESC LIMIT 1`);
      expect(id, 'DBにレコードが作成されていること').not.toBe('');

      const resp = await page.goto(`/admin/information_edit/id=${id}`);
      expect(resp.status()).toBe(200);

      const titleVal = await page.locator('input[name="title"]').inputValue();
      const docVal   = await page.locator('textarea[name="doc"]').inputValue();
      expect(titleVal, '編集画面: title が正しく表示される').toBe(title);
      expect(docVal,   '編集画面: doc が正しく表示される').toBe(doc);

      await page.close();
    });

  });

}); // インフォメーション 登録テスト end


// ============================================================
// ライブラリ編集 戻るボタン テスト
// ============================================================
test.describe('ライブラリ編集 戻るボタン（Admin）', () => {
  let context;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    const p = await context.newPage();
    await p.goto('/admin/login');
    await p.fill('#id', LOGIN_ID);
    await p.fill('#pass', LOGIN_PASS);
    await p.click('button[type="submit"]');
    await p.waitForURL(/admin\//);
    await p.close();
  });

  test.afterAll(async () => {
    execDb(`DELETE FROM library WHERE title LIKE '__E2E__%';`);
    await context.close();
  });

  // ─── 共通ヘルパー ────────────────────────────────────────────

  async function fillLibraryForm(page, overrides = {}) {
    await page.goto('/admin/library_edit');
    await page.waitForLoadState('domcontentloaded');

    const fields = {
      title: `${MARKER} LibTest`,
      doc:   'E2Eテスト用ライブラリ内容',
      ...overrides,
    };

    if (fields.title !== undefined) await page.fill('input[name="title"]',  fields.title);
    if (fields.doc   !== undefined) await page.fill('textarea[name="doc"]', fields.doc);
    return fields;
  }

  async function submitLibraryConfirm(page) {
    await page.click('button.reg-submit-btn');
    await page.waitForLoadState('domcontentloaded');
  }

  async function assertOnLibraryConfirmPage(page) {
    await expect(page.locator('button.bd-reg-submit-btn'), '確認画面の登録ボタンが表示されること').toBeVisible();
  }

  async function assertOnLibraryFormPage(page) {
    await expect(page.locator('textarea[name="doc"]'), '入力フォームが表示されること').toBeVisible();
    await expect(page.locator('button.bd-reg-submit-btn'), '確認画面の登録ボタンが非表示').toBeHidden();
  }

  // ─── A. 戻るボタン基本動作 ───────────────────────────────────

  test.describe('A. 戻るボタン基本動作', () => {

    test('A-1: 確認画面で「戻る」を押すと入力フォームに戻れる', async () => {
      const page = await context.newPage();
      await fillLibraryForm(page);
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      await assertOnLibraryFormPage(page);
      await page.close();
    });

    test('A-2: 戻ったフォームに title が引き継がれる', async () => {
      const page = await context.newPage();
      const title = `${MARKER} LibBack A2`;
      await fillLibraryForm(page, { title });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      const titleVal = await page.locator('input[name="title"]').inputValue();
      expect(titleVal, '戻ったフォームの title が元の値と一致すること').toBe(title);
      await page.close();
    });

    test('A-3: 戻ったフォームに doc（プレーンテキスト）が引き継がれる', async () => {
      const page = await context.newPage();
      const doc = 'プレーンテキストの内容です。改行なし。';
      await fillLibraryForm(page, { doc });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      const docVal = await page.locator('textarea[name="doc"]').inputValue();
      expect(docVal, '戻ったフォームの doc が元の値と一致すること').toBe(doc);
      await page.close();
    });

  });

  // ─── B. tag=1 フィールド（doc）の二重エンコード防止 ─────────

  test.describe('B. docフィールド（tag=1）戻り時の二重エンコード防止', () => {

    test('B-1: <a> タグを含む doc → 戻ったとき textarea に元の HTML が表示される', async () => {
      const page = await context.newPage();
      const doc = '<a href="http://test.example.com">テストリンク</a>';
      await fillLibraryForm(page, { doc });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      const docVal = await page.locator('textarea[name="doc"]').inputValue();
      expect(docVal, 'textarea に元の <a> タグが含まれること').toContain('<a href=');
      expect(docVal, '&#60; が表示されないこと（二重エンコードなし）').not.toContain('&#60;');
      await page.close();
    });

    test('B-2: & を含む doc → 戻ったとき &#38; に二重エンコードされない', async () => {
      const page = await context.newPage();
      const doc = 'テスト & サンプル 売買賃貸';
      await fillLibraryForm(page, { doc });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      const docVal = await page.locator('textarea[name="doc"]').inputValue();
      expect(docVal, 'textarea に & が含まれること').toContain('&');
      expect(docVal, '&#38; が表示されないこと（二重エンコードなし）').not.toContain('&#38;');
      await page.close();
    });

    test('B-3: ダブルクォートを含む doc → 戻ったとき &#34; に二重エンコードされない', async () => {
      const page = await context.newPage();
      const doc = '「サンプル」"引用テスト"の内容';
      await fillLibraryForm(page, { doc });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      const docVal = await page.locator('textarea[name="doc"]').inputValue();
      expect(docVal, '"引用テスト" が含まれること').toContain('"引用テスト"');
      expect(docVal, '&#34; が表示されないこと（二重エンコードなし）').not.toContain('&#34;');
      await page.close();
    });

    test('B-4: <a> + & + " を複合した doc → 戻ったとき全て正しく復元される', async () => {
      const page = await context.newPage();
      const doc = '<a href="http://test.example.com/?q=1&p=2">リンク "テスト"</a>';
      await fillLibraryForm(page, { doc });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      const docVal = await page.locator('textarea[name="doc"]').inputValue();
      expect(docVal, '<a タグが復元されること').toContain('<a href=');
      expect(docVal, '&#60; が残らないこと').not.toContain('&#60;');
      expect(docVal, '&#38; が残らないこと').not.toContain('&#38;');
      expect(docVal, '&#34; が残らないこと').not.toContain('&#34;');
      await page.close();
    });

  });

  // ─── C. 戻り後の再確認・登録 ─────────────────────────────────

  test.describe('C. 戻り後の再確認・登録', () => {

    test('C-1: 確認→戻る→そのまま再確認→登録まで完了できる', async () => {
      const page = await context.newPage();
      const title = `${MARKER} LibBack C1`;
      const doc = '<a href="http://test.example.com">サンプルリンク</a>';

      await fillLibraryForm(page, { title, doc });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      // 1回目の戻る
      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');
      await assertOnLibraryFormPage(page);

      // 戻り後フォームの title を確認
      const titleVal = await page.locator('input[name="title"]').inputValue();
      expect(titleVal, '戻り後フォームの title が正しいこと').toBe(title);

      // 再度確認→登録
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');

      // DB に登録されていることを確認
      const dbTitle = queryDb(`SELECT title FROM library WHERE title = '${title}' ORDER BY library_id DESC LIMIT 1`);
      expect(dbTitle, '戻り後再登録がDBに保存されること').toBe(title);

      await page.close();
    });

    test('C-2: 確認→戻る→内容変更→再確認→登録でも正しい値がDBに保存される', async () => {
      const page = await context.newPage();
      const titleOld = `${MARKER} LibBack C2 OLD`;
      const titleNew = `${MARKER} LibBack C2 NEW`;

      await fillLibraryForm(page, { title: titleOld });
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);

      // 戻る
      await page.click('.bd-reg-back-btn');
      await page.waitForLoadState('domcontentloaded');

      // title を変更して再送信
      await page.fill('input[name="title"]', titleNew);
      await submitLibraryConfirm(page);
      await assertOnLibraryConfirmPage(page);
      await page.click('button.bd-reg-submit-btn');
      await page.waitForLoadState('domcontentloaded');

      // 変更後の title がDBに保存されていること
      const dbTitle = queryDb(`SELECT title FROM library WHERE title = '${titleNew}' ORDER BY library_id DESC LIMIT 1`);
      expect(dbTitle, '変更後の title がDBに保存されること').toBe(titleNew);

      // 古い title は登録されていないこと
      const oldCount = queryDb(`SELECT COUNT(*) FROM library WHERE title = '${titleOld}'`);
      expect(oldCount, '変更前の title は登録されていないこと').toBe('0');

      await page.close();
    });

  });

}); // ライブラリ編集 戻るボタン end
