KRIC近代化改修プロジェクト：詳細引き継ぎ仕様書 (2026/05/17 版)

## 1. プロジェクト概要

15年前の遺産と思われていたシステムが、調査の結果 PHP 7.4.33 / CakePHP 2.9 で稼働していることが判明した。本プロジェクトでは、これを PHP 8.3 環境へ移行し、デザインのリニューアルおよび「動画投稿機能」の新規実装を行う。

**開発ワークフロー**: Docker（開発・確認）→ さくらテスト環境（動作確認）→ 本番（デプロイ）

## 2. 現在のシステム構成

- フレームワーク: CakePHP 2.9.x（PHP 8.3 対応パッチ適用中）
- CMS: WordPress（ルートディレクトリ www/ を支配、CakePHP は /iis/, /member/, /admin/ で共存）
- DB: MySQL 8.0（`_kric7` に WP テーブル `wp2_*` と CakePHP テーブルが混在）
- Docker MySQL: host=`db`, user=`root`, pass=`root`, DB=`_kric7`
- さくらテスト MySQL: host=`mysql3115.db.sakura.ne.jp`, user=`kric_kric7`, pass=`d1xmmjasdeyc-q3`, DB=`kric_kric7`

## 3. PHP 8.3 移行：適用済みパッチ一覧

### 3.1. CakePHP コアファイル（data/lib/Cake）

| ファイル | 修正内容 |
|---|---|
| `Core/Configure.php` | `_setErrorHandlers()` 前に `ErrorHandler.php` を require（PHP 8.x で未ロードクラスを文字列コールバックで渡すと TypeError になるため） |
| `Core/CakeObject.php` | `#[AllowDynamicProperties]` 追加（View/Model/Controller 系サブクラスを一括対応） |
| `Event/CakeEvent.php` | `#[AllowDynamicProperties]` 追加 + 動的プロパティを明示宣言（`$omitSubject`, `$modParams`, `$break`, `$breakOn`, `$collectReturn`, `$data`） |

### 3.2. アプリケーションコード（data/app）

| ファイル | 修正内容 |
|---|---|
| `Config/core.php` | `Error.trace = false`（true だと Debugger クラスへの連鎖依存でエラー） |
| `Config/database.php` | Docker用・SAKURA_TEST用 DB接続設定の分岐追加（`__construct()` 内） |
| `Vendor/Org/OrgStdClass.php` | `each()` → `foreach`、`get_magic_quotes_gpc()` 削除、`mt_srand((double)microtime()*10000)` → `(int)` キャスト（PHP 8.1+ deprecated 対応） |
| `Vendor/Org/OrgSessionClass.php` | `each()` → `foreach` |
| `Vendor/Org/OrgInitialClass.php` | SSL_FLAG の else ブランチ修正（Docker環境は `''`、それ以外は `'ssl'`） |
| `Vendor/Org/OrgSendMailClass.php` | `OrgSendMail` クラスに `#[AllowDynamicProperties]` 追加（独自クラス継承チェーンのルート） |
| `Helper.php` | `get_magic_quotes_gpc()` → `false` |
| `Controller/MemberController.php` | `login_bar()` / `login_status()` の CSRF セッションキー生成を `HTTP_URL` → `(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL)` に修正（HTTPS 環境でセッションキー不一致によりログイン後に login 画面に戻る問題を修正） |

### 3.3. WordPress / .htaccess

| ファイル | 修正内容 |
|---|---|
| `www/.htaccess` | WordPress が `/iis/`, `/member/`, `/admin/` を横取りしないよう `RewriteRule ^(iis|member|admin)/ - [L]` を追加 |
| `www/app.php` | Docker 環境判定（ROOT パス・DB設定・SSL設定の切り替え） |
| `iis/.htaccess` | Docker環境用に HTTPS リダイレクトをコメントアウト |

### 3.4. その他

- WP テーマ `default` を有効化（DB で `template`/`stylesheet` を更新）
- WP プラグイン `php-code-widget` を無効化（`create_function()` 廃止のため、使用しない方針）
- 旧サイトの `images/` フォルダを新 Webroot にコピー

## 4. 残課題（PHP 8.3 移行）

- `String` クラス名衝突修正（`lib/Cake/Utility/String.php` → `CakeString.php` リネーム＋呼び出し一括置換）
- `mcrypt` → `openssl_encrypt` 差し替え（`Security::cipher()` 等）
- `AppModel.php` / `AppController.php` への `#[AllowDynamicProperties]` 追加
- `OrgNaviClass.php:112` optional param before required パラメータ順序修正
- WordPress プラグインのアップデート（AIOSEO 等）
- 全ページの表示・動作確認（`SITE_TREE.md` 参照）

## 5. 動画投稿機能の設計（新規実装・未着手）

「日常の1分程度のプロモーション動画」を想定。

- 保存場所: `www/up/video/`
- DB構成: `videos` テーブルを新設（id, title, filename, member_id, created）
- `MemberController.php` に `video_add` アクションを新設
- Validation: `extension` だけでなく `mime_type`（video/mp4, video/quicktime）を厳格にチェック
- HTML5 `<video>` タグによるブラウザ標準再生
- php.ini: `upload_max_filesize=100M`, `post_max_size=100M`, `max_execution_time=300`, `memory_limit=256M`

## 6. デザインリニューアル（デザイン案待ち）

CakePHP（会員エリア）のデザインは、デザイン案（HTML/CSS）をもとに `app/View/Layouts/default.ctp` および各 View を刷新する。

## 7. さくらテスト環境 操作メモ

```bash
# ファイル転送
scp <ローカルパス> kric@kric.sakura.ne.jp:~/www/kric.co.jp/<相対パス>

# キャッシュクリア（コード変更後は必ず実行）
ssh kric@kric.sakura.ne.jp "rm -rf ~/www/kric.co.jp/data/app/tmp/cache/{models,persistent,views}/*"
```

## 8. ローカル環境セットアップ時の既知課題

### 8-1. KRICメディア サムネイル画像が表示されない

**現象**: TOPページ・会員専用メディアページでサムネイルが表示されない（画像なし状態）  
**原因**: メディア投稿の `mediaimg` ACF フィールドは WordPress 添付ファイル ID（例: 10915）を格納しており、実ファイルは `wp-content/uploads/` に存在する。本番から DB をインポートするだけでは画像ファイルが存在しない。  
**対処**: 本番さくらサーバーから `wp-content/uploads/` をローカルにコピーする（ユーザーが別途対応）

```bash
# さくら → ローカルへコピー（WSL内で実行）
scp -r kric@kric.sakura.ne.jp:~/www/kric.co.jp/www/wp-content/uploads/ \
  /mnt/c/Users/web0012osk/Documents/kric/www/kric.co.jp/www/wp-content/
```

### 8-2. TOPページ 会員紹介マップにピンが表示されない

**現象**: `http://localhost:8080/` の「会員紹介」セクションのGoogle Mapにピンが1件も表示されない  
**原因**: `IntroController.php::dir_json()` が `company.lat` / `company.lng` カラムを SELECT しているが、`company` テーブルにこれらのカラムが存在しない。JSON は全社 `"lat":null,"lng":null` で返るため JS がマーカーをスキップする。  
**対処（未対応）**: `company` テーブルに `lat DECIMAL(10,7)` / `lng DECIMAL(10,7)` を追加し、`google_map` URL から座標を抽出して投入する。URLフォーマットは複数種あり:
- `&ll=34.711385,135.496799` パターン（多数）
- `/@34.690499,135.516510,17z` パターン
- `&q=34.734489,136.512208` パターン（座標直指定）
- `goo.gl/maps/...` 短縮URL（座標抽出不可 → 手動入力が必要）

## 9. 重要な注意事項

- デバッグ時は `core.php` の `debug` レベルを `2` に設定（終了後は `0` に戻すこと）
- CakePHP キャッシュが残っていると古いパスで動作するため、変更後は `data/app/tmp/cache/` をクリアすること
- さくら環境は HTTPS あり（`SSL_FLAG = 'ssl'`）。CSRF セッションキーは URL（HTTP/HTTPS）込みで生成されるため、Member コントローラー側でも `HTTP_SSL` を使うこと
- `mcrypt` は廃止済みのため `openssl_encrypt` ベースの実装を使用すること
- Docker専用の変更を本番に適用しないこと（詳細は `MIGRATION_GUIDE.md` 参照）
- DB を変更する際は WP テーブル（`wp2_*`）と CakePHP テーブルが混在していることに注意
