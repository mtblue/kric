# KRIC プロジェクト：AI セッション共通ガイド

詳細な引き継ぎ仕様は `AI_HANDOVER.md` を参照すること。
サイトページ一覧・進捗は `SITE_TREE.md` を参照すること。
本番移行手順・Docker専用変更の詳細は `MIGRATION_GUIDE.md` を参照すること。
**タスク進捗は `PROGRESS.md` を参照・更新すること。作業完了のたびに該当タスクを ✅ に更新する。**

---

## プロジェクト概要

- **サイト**: kric.co.jp
- **WordPress**: `www/kric.co.jp/www/` 配下（Dockerマウント先: `/var/www/html`）
- **CakePHP 2.9**: `www/kric.co.jp/data/` 配下（Dockerマウント先: `/var/www/data`）
- **DB**: MySQL 8.0（`_kric7` に WP テーブル `wp2_*` と CakePHP テーブルが混在）
- **旧サイトファイル**: `kric.co.jp/` 配下（参照用。本番からコピーしたもの）

## Docker環境

```bash
# WSL内で実行
cd /mnt/c/Users/web0012osk/Documents/kric
docker compose up -d    # 起動
docker compose down     # 停止
```

- WordPress: http://localhost:8080/
- 会員エリア: http://localhost:8080/member/
- IISログイン: http://localhost:8080/iis/login
- 管理画面: http://localhost:8080/wp-admin/
- WP管理DB prefix: `wp2_`
- Docker MySQL: host=`db`, user=`root`, pass=`root`

## 環境判定の仕組み

`docker-compose.yml` で `APP_ENV=docker` を設定。
`www/kric.co.jp/www/app.php` が `getenv('APP_ENV') === 'docker'` を検知し、
Docker専用のパス・DB設定・SSL設定を適用する。

## 進行中のプロジェクト

1. **PHP 8.3 移行**: CakePHP 2.9 を PHP 8.3 対応にパッチ当て（一部完了）
2. **デザインリニューアル**: WP カスタムテーマ新規作成 ＋ CakePHP View 刷新（デザイン案待ち）
3. **動画投稿機能**: 会員が1分程度の MP4 動画を投稿できる機能を新規実装（未着手）

## 完了済み対応（ローカル確認済み）

- `app.php` に Docker 環境判定を追加（ROOT パス・DB設定・SSL設定の切り替え）
- `OrgStdClass.php`: `each()` → `foreach` 置き換え、`get_magic_quotes_gpc()` 削除、float→int キャスト修正（PHP 8.1+ deprecated 対応）
- `OrgSessionClass.php`: `each()` → `foreach` 置き換え
- `OrgInitialClass.php`: SSL_FLAG の else ブランチ修正（Docker環境はHTTPSなし、本番・さくらはHTTPS有効）
- `Helper.php`: `get_magic_quotes_gpc()` → `false` に置き換え
- `database.php`: Docker用・SAKURA_TEST用 DB接続設定の分岐追加
- `iis/.htaccess`: Docker環境用にHTTPSリダイレクトをコメントアウト
- `www/.htaccess`: WordPress が `/iis/`, `/member/`, `/admin/` URLを横取りしないよう `RewriteRule ^(iis|member|admin)/ - [L]` を追加
- `lib/Cake/Core/Configure.php`: `_setErrorHandlers()` 前に `ErrorHandler.php` を require（PHP 8.x の TypeError 対応）
- `data/app/Config/core.php`: `Error.trace = false`（Debugger クラスへの連鎖依存で発生するエラーを回避）
- `lib/Cake/Event/CakeEvent.php`: `#[AllowDynamicProperties]` 追加 + 動的プロパティを明示宣言
- `lib/Cake/Core/CakeObject.php`: `#[AllowDynamicProperties]` 追加（View/Model/Controller 系サブクラスを一括対応）
- `data/app/Vendor/Org/OrgSendMailClass.php`: `#[AllowDynamicProperties]` 追加（独自クラス継承チェーンのルート）
- `data/app/Controller/MemberController.php`: `login_bar` / `login_status` の CSRF セッションキー生成を `HTTP_URL` から `(SSL_FLAG != '' ? HTTP_SSL : HTTP_URL)` に修正（さくら HTTPS 環境でログイン後に login 画面に戻る問題を修正）
- WP テーマ `default` を有効化（DBで `template`/`stylesheet` を更新）
- WP プラグイン `php-code-widget` を無効化（`create_function()` 廃止のため）
- 旧サイトの `images/` フォルダを新 Webroot にコピー
- CakePHP キャッシュクリア（`data/app/tmp/cache/` 以下）
- WordPress 7.1 へアップデート（2026/09）
- WordPress 自動更新を `AUTOMATIC_UPDATER_DISABLED = true` で無効化（wp-config.php）。公開安定後に `'minor'` に変更すること

## WordPress プラグイン構成（確定版 2026/09）

有効化するプラグイン（計8つ）：

| プラグイン | 用途 |
|---|---|
| advanced-custom-fields | メディア・会社ページのカスタムフィールド |
| all-in-one-seo-pack | SEO（本番デプロイ前に管理画面でアップデートすること） |
| disable-gutenberg | ブロックエディター無効化（クラシックエディター相当） |
| html-on-pages | `.html` 付き URL でWPページを配信（必須） |
| inquiry-form-creator | お問い合わせフォーム（contactページで使用） |
| widgets-on-pages | ウィジェット埋め込み（sitemapページで使用） |
| wp-multibyte-patch | 日本語マルチバイト文字処理の安全性向上 |
| delete-revision | リビジョン削除補助（管理補助） |

無効・削除：
- `php-code-widget`: PHP 8 非対応のため **削除済み**
- `akismet` / `breadcrumb-navxt` / `hello.php`: 使用箇所なしのため **削除済み**

> **注意**: `html-on-pages` が無効になると `.html` 付きの全ページリンクが 404 になる。
> DB リセット後（`import_and_setup.sh` 実行後）は必ず有効化を確認すること。

## さくらインターネット テスト環境（SAKURA_TEST）確認済み

- 環境: `kric.sakura.ne.jp`（HTTPS あり）、FreeBSD 共有ホスティング、CGIモード
- DB: `mysql3115.db.sakura.ne.jp`、ユーザー `kric_kric7`、DB名 `kric_kric7`
- `/iis/login` へのアクセス・ログイン動作確認済み（2026/05/17）
- キャッシュクリアコマンド: `ssh kric@kric.sakura.ne.jp "rm -rf ~/www/kric.co.jp/data/app/tmp/cache/{models,persistent,views}/*"`
- ファイル転送: `scp <ローカルパス> kric@kric.sakura.ne.jp:~/www/kric.co.jp/<相対パス>`

## 未着手・残課題

- `String` クラス名衝突修正（`lib/Cake/Utility/String.php` → `CakeString.php`）
- `mcrypt` → `openssl_encrypt` 差し替え
- `AppModel.php` / `AppController.php` への `#[AllowDynamicProperties]` 追加
- `OrgNaviClass.php:112` optional param before required パラメータ順序修正
- WordPress プラグインのアップデート（AIOSEO 等）
- 全ページの表示・動作確認（`SITE_TREE.md` 参照）
- デザインリニューアル（デザイン案待ち）
- 動画投稿機能実装

## DB 操作メモ

### company テーブル 住所カラム構成
`migrate_address_columns.sql` 適用済み。`address` カラムは以下の3カラムに分割されている：
- `ken`（tinyint）: 都道府県
- `city`（varchar 100）: 市区町村
- `address_town`（varchar 100）: 町名・番地（旧 `address` をリネーム）
- `building`（varchar 100）: 建物名

ビュー側が旧カラム名 `address` を参照している箇所は要修正（`Member/name.ctp.html` 等）。

### MySQL への SQL ファイル流し込み（文字化け防止）
`--default-character-set=utf8mb4` を必ず指定すること。指定しないと UTF-8 の日本語が Latin-1 として格納され文字化けする。

```bash
# WSL 経由で Docker MySQL に流し込む場合
wsl bash -c "docker exec -i kric-db-1 mysql -uroot -proot --default-character-set=utf8mb4 _kric7 < /mnt/c/Users/web0012osk/Documents/kric/<ファイル名>.sql"
```

---

## 重要な注意事項

- デバッグ時は `core.php` の `debug` レベルを `2` に設定してすべてのエラーを表示させること
- DB を変更する際は WP テーブル（`wp2_*`）と CakePHP テーブルが混在していることに注意
- `mcrypt` は廃止済みのため `openssl_encrypt` ベースの実装を使用すること
- CakePHP キャッシュが残っていると古いパスで動作するため、変更後は `data/app/tmp/cache/` をクリアすること
- `php-code-widget` は PHP 8 非対応のため使用しない方針
- Docker専用の変更を本番に適用しないこと（詳細は `MIGRATION_GUIDE.md` 参照）
- さくら環境は HTTPS あり（`SSL_FLAG = 'ssl'`）。CSRF セッションキーは URL（HTTP/HTTPS）込みで生成されるため、`login_bar` 等 Member コントローラー側でも `HTTP_SSL` を使うこと
- さくら環境でキャッシュが古いパスを保持していると `Class CakeLog not found` 等が発生するため、コード変更後は必ずキャッシュクリアすること

---

## WordPress コア更新の手順（2026-10-18 策定）

**配布アーカイブ（zip / tar.gz）をドキュメントルート内で展開しないこと。**

公式アーカイブは `wordpress/` というディレクトリに展開される。これをドキュメントルート内で行い、中身をコピーした後に空箱を消し忘れると、`wp-config.php` を持たない未設定の WordPress 一式が残る。この状態は外部から `/wordpress/wp-admin/setup-config.php` に到達でき、**第三者が任意の DB を指定してインストールを完了させ、管理画面からプラグインをアップロードできる**＝実質的な任意コード実行の入口になる。

2026-09-04 の 7.1 更新でこれが発生し、本番・STG の双方へ配布された。STG では 2026-09-09 に実際に悪用され、ウェブシェルを設置されている。

### 正しい手順

```bash
# 本番（--url は必須。CLI では HTTP_HOST が無く wp-config.php の環境分岐が誤判定するため）
ssh kric2021@kric.co.jp
cd ~/www/kric.co.jp/www
wp --url=https://kric.co.jp core check-update      # 更新の有無を確認
wp --url=https://kric.co.jp core update            # コア更新
wp --url=https://kric.co.jp plugin update --all    # プラグイン更新
```

WP-CLI は両サーバーに標準で導入済み（`/usr/local/bin/wp`）。アーカイブを展開しないため、この種の残骸が構造的に発生しない。

### 併せて守ること

- `deploy_to_sakura.sh` / `sync_to_prod.sh` の除外指定に `wordpress/` を追加済み。外さないこと
- 更新前に必ずバックアップを取得すること
- ドキュメントルート配下に、使われていない WordPress 一式・古いバックアップを置かないこと
