# 本番移行手順書

最終更新: 2026-07-07

---

## 概要

本ドキュメントは、Docker（ローカル開発環境）で実施した変更のうち、
**本番サーバーで実施すべきもの・すべきでないもの**を整理した手順書です。

---

## ■ サーバー環境情報

| 項目 | テスト環境 | 本番環境 |
|------|-----------|---------|
| ドメイン | kric.sakura.ne.jp | kric.co.jp |
| DBサーバー | mysql3115.db.sakura.ne.jp | localhost |
| DB名 | kric_kric7 | _kric7 |
| DBユーザー | kric_kric7 | （本番設定を参照） |
| MySQL | 8.0 | 5.7 |
| PHP（変更前） | 5.2 | 未変更 |
| PHP（目標） | 8.3 | 8.3 |

---

## ■ サーバー環境構築手順（さくらインターネット）

### 手順1. PHP バージョンアップ

コントロールパネル > ドメイン設定 > PHP バージョン から変更する。

- バージョン: **8.3** を選択
- 実行モード: **CGI** を選択（モジュールは不可）
  - 理由: CGI モードはPHPがユーザー自身のプロセスとして動作するため、ファイルのパーミッション管理がシンプルで、さくら共用サーバーの標準的な設定

---

### 手順2. SSL 証明書の設定

コントロールパネル > ドメイン/SSL > SSL証明書の設定 から無料SSL（Let's Encrypt）を取得する。

- 反映まで数分〜十数分待つ
- 設定後 `https://` でアクセスできることを確認

---

### 手順3. ディレクトリ構成の準備

さくらサーバー上のディレクトリ構成：

```
~/www/
├── .htaccess                ← 手順4で作成
└── kric.co.jp/
    ├── www/                 ← WordPress + CakePHP webroot（手順5でアップロード）
    └── data/                ← CakePHP app/ など（Webroot外）（手順5でアップロード）
```

> ⚠️ `app.php` の SAKURA_TEST 設定で ROOT = `/home/kric/www/kric.co.jp` のため、
> CakePHP は `~/www/kric.co.jp/data/` を参照する。`~/data/` ではないので注意。

---

### 手順4. `~/www/.htaccess` の設置

`~/www/.htaccess` を以下の内容で作成する。

```apache
# IP制限（本番公開時は削除またはコメントアウト）
Require ip xxx.xxx.xxx.xxx

# kric.co.jp/www/ サブフォルダをドキュメントルートとして扱う
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_URI} !^/kric\.co\.jp/www/
RewriteRule ^(.*)$ kric.co.jp/www/$1 [L]
```

> ⚠️ 本番公開時は `Require ip` 行を削除すること。

---

### 手順5. ファイルのアップロード

#### 5-1. ローカルでアーカイブ作成（WSL）

`wp-includes` は Windows の ACL 制限でアーカイブできないため除外し、後で補完する。

```bash
cd /mnt/c/Users/web0012osk/Documents/kric/www/kric.co.jp
tar czf /tmp/kric_www.tar.gz --exclude='www/wp-includes' www
tar czf /tmp/kric_data.tar.gz data
```

#### 5-2. SCP でアップロード

```bash
scp /mnt/c/Users/web0012osk/Documents/kric/www/.htaccess kric@kric.sakura.ne.jp:~/www/
scp /tmp/kric_www.tar.gz kric@kric.sakura.ne.jp:~/
scp /tmp/kric_data.tar.gz kric@kric.sakura.ne.jp:~/
```

#### 5-3. SSH で接続して解凍

```bash
ssh kric@kric.sakura.ne.jp
```

```bash
mkdir -p ~/www/kric.co.jp
tar xzf ~/kric_www.tar.gz -C ~/www/kric.co.jp/
tar xzf ~/kric_data.tar.gz -C ~/
rm ~/kric_www.tar.gz ~/kric_data.tar.gz
```

#### 5-4. `wp-includes` の補完

除外した `wp-includes` をWordPress公式から取得する。バージョンは現行と一致させること（現在: **6.9.4**）。

```bash
cd ~/www/kric.co.jp/www
wget https://ja.wordpress.org/wordpress-6.9.4-ja.tar.gz
tar xzf wordpress-6.9.4-ja.tar.gz --strip-components=1 wordpress/wp-includes
rm wordpress-6.9.4-ja.tar.gz
```

---

### 手順6. 設定ファイルの調整

#### 6-1. `wp-config.php`

DBの接続情報をさくら用に書き換える。

```php
define( 'DB_NAME', 'データベース名' );
define( 'DB_USER', 'DBユーザー名' );
define( 'DB_PASSWORD', 'DBパスワード' );
define( 'DB_HOST', 'DBホスト名' );  // 本番: localhost
```

本番公開時には以下も変更：
```php
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
```

#### 6-2. `data/app/Config/database.php`

`APP_ENV=docker` でない場合は本番設定（localhost 接続）が使われるため変更不要。

#### 6-3. `data/app/Vendor/Org/OrgInitialClass.php`

本番では SSL_FLAG を `''` に固定してよい（`.htaccess` の HTTPS リダイレクトで担保）。

```php
// 変更前（Docker対応版）
define('SSL_FLAG', getenv('APP_ENV') === 'docker' ? '' : 'ssl');

// 変更後（本番用）
define('SSL_FLAG', '');
```

#### 6-4. `iis/.htaccess`

本番では HTTPS リダイレクトを有効にすること（Dockerではコメントアウトしているが本番は戻す）。

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R,L]
```

---

### 手順7. データベースのインポート

#### 7-1. エクスポート

本番の phpMyAdmin からエクスポートする（本番サーバーへのSSHは不要）。

1. phpMyAdmin を開き `_kric7` を選択
2. 「エクスポート」タブ → 方式：**簡易** / フォーマット：**SQL** → 実行
3. SQLファイルをローカルPCに保存

> 簡易エクスポートは `USE _kric7;` や `CREATE DATABASE` を含まないため、DB名が異なっていてもそのままインポート可能。

#### 7-2. テスト環境へアップロード（WSL）

```bash
scp /mnt/c/Users/web0012osk/Downloads/_kric7.sql kric@kric.sakura.ne.jp:~/
```

#### 7-3. インポート（SSH）

```bash
ssh kric@kric.sakura.ne.jp
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7 < ~/_kric7.sql
```

#### 7-4. WordPress の URL 更新

MySQL に接続してから UPDATE を実行する（ワンライナーでは動作しない）。

```bash
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7
```

```sql
UPDATE wp2_options SET option_value='https://kric.sakura.ne.jp' WHERE option_name='siteurl';
UPDATE wp2_options SET option_value='https://kric.sakura.ne.jp' WHERE option_name='home';
```

> 本番移行時は `kric.sakura.ne.jp` を `kric.co.jp` に変更する。

#### 7-5. `business_direct` / `member` テーブルのカラム追加

デザインリニューアルで以下のカラムを追加した。既存DBにはこれらが存在しないため、インポート後に手動で実行すること。
**`setup_after_import.sql` に追加済みのため、`import_and_setup.sh` を使う場合は自動適用される。**

> ⚠️ MySQL 8.0 の strict モードにより `ADD COLUMN` 単体では `create_date` のデフォルト値でエラーになる。
> `SET sql_mode = ''` を同一セッションで先行実行すること。

**さくらテスト環境（SSH接続後）**
```bash
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7
```
```sql
SET sql_mode = '';
-- business_direct
ALTER TABLE business_direct ADD COLUMN shubetsu       varchar(50)  DEFAULT NULL AFTER zokusei;
ALTER TABLE business_direct ADD COLUMN syozaichi      varchar(255) DEFAULT NULL AFTER shubetsu;
ALTER TABLE business_direct ADD COLUMN video_url      varchar(512) DEFAULT NULL AFTER syozaichi;
ALTER TABLE business_direct ADD COLUMN poster_company varchar(255) DEFAULT NULL AFTER video_url;
ALTER TABLE business_direct ADD COLUMN poster_name    varchar(255) DEFAULT NULL AFTER poster_company;
-- member
ALTER TABLE member ADD COLUMN jiyuu_toukou text         DEFAULT NULL;
ALTER TABLE member ADD COLUMN upload_photo varchar(255) DEFAULT NULL;
```

#### 7-6. `company` テーブルへのカラム追加

デザインリニューアルで `company` テーブルに会社紹介動画URL用のカラムを追加した。
Docker環境には追加済み。本番DBインポート後に手動で実行すること。

**さくらテスト環境（SSH接続後）**
```bash
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7
```
```sql
ALTER TABLE company ADD COLUMN pr_video text DEFAULT NULL;
```

**Dockerローカル環境（WSL）**
```bash
docker exec -it kric-db-1 mysql -uroot -proot _kric7 -e "ALTER TABLE company ADD COLUMN pr_video text DEFAULT NULL;"
```

#### 7-6-2. `company` テーブルの住所カラムを3カラムに分割

会社住所を `address`（旧: 市区町村＋町名番地＋建物名を1カラムに格納）から
`city`（市区町村）・`address_town`（町名番地）・`building`（建物名）の3カラムに分割した。

- Docker環境: `city`, `address_town`, `building` カラムは追加済み。`address` カラムは削除済み。
- さくら環境: 本番DBインポート後に以下を実行すること。

> ⚠️ 既存の `address` カラムのデータは移行しない（Excelから新データを直接入力する運用）。

**さくらテスト環境（SSH接続後）**
```bash
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7
```
```sql
SET sql_mode = '';
ALTER TABLE company ADD COLUMN city         VARCHAR(255) NOT NULL DEFAULT '' AFTER ken;
ALTER TABLE company ADD COLUMN address_town VARCHAR(255) NOT NULL DEFAULT '' AFTER city;
ALTER TABLE company ADD COLUMN building     VARCHAR(255) NOT NULL DEFAULT '' AFTER address_town;
ALTER TABLE company DROP COLUMN address;
```

**Dockerローカル環境（WSL）**（参考: 既に適用済み）
```bash
docker compose exec -T db mysql -uroot -proot _kric7 -e "ALTER TABLE company DROP COLUMN address;"
```

---

#### 7-6-3. `company` テーブルへの `lat` / `lng` カラム追加

マップ表示用の緯度・経度カラムを追加する。データはジオコーディングバッチ（手順7-6-4）で初回一括投入する。
新規会社の登録・編集時は IisController / AdminController が自動でジオコーディングを実行するため、バッチの再実行は不要。

**さくらテスト環境（SSH接続後）**
```bash
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7
```
```sql
ALTER TABLE company ADD COLUMN lat DECIMAL(10,7) DEFAULT NULL;
ALTER TABLE company ADD COLUMN lng DECIMAL(10,7) DEFAULT NULL;
```

**Dockerローカル環境（WSL）**（参考: 既に適用済み）
```bash
wsl docker exec kric-db-1 mysql -uroot -proot _kric7 -e "ALTER TABLE company ADD COLUMN lat DOUBLE DEFAULT NULL; ALTER TABLE company ADD COLUMN lng DOUBLE DEFAULT NULL;"
```

---

#### 7-6-4. ジオコーディングバッチの実行

`lat`/`lng` カラム追加後、`geocode_batch.php` を実行して座標データを**初回一括投入**する。

> ℹ️ **新規会社の登録・編集時は自動実行**: IisController / AdminController の `_geocodeCompany()` が保存後にジオコーディングを自動実行するため、このバッチを再実行する必要はない。バッチは初回インポート時の一括処理専用。

このスクリプトは以下の2段階で処理する：
- **Phase 1**: `google_map` カラムのURL文字列から緯度経度を正規表現で抽出（`ll=`, `/@`, `q=` の各パターン）
- **Phase 2**: Nominatim（OpenStreetMap）APIで住所から緯度経度を取得（`zip + city + address_town + building` を使用）

**制約事項**:
- Nominatim は利用規約により 1 req/sec の呼び出し制限あり。スクリプト内で 1.1 秒待機しているため、全社処理に約5〜10分かかる
- API通信エラーになった企業は再実行すれば補完できる（スクリプトは `lat IS NULL OR lng IS NULL` の企業のみ処理する）
- `del_flg = 1` の企業は除外される

**Dockerローカル環境（WSL）**
```bash
wsl docker exec kric-app-1 php /var/www/data/geocode_batch.php
```

**さくらテスト環境**

スクリプトをアップロードしてCGI実行するか、SSH接続してPHPを直接呼び出す。

```bash
scp www/kric.co.jp/data/geocode_batch.php kric@kric.sakura.ne.jp:~/www/kric.co.jp/data/
ssh kric@kric.sakura.ne.jp "php ~/www/kric.co.jp/data/geocode_batch.php"
ssh kric@kric.sakura.ne.jp "rm ~/www/kric.co.jp/data/geocode_batch.php"  # 実行後は削除
```

> ⚠️ スクリプト内の `$host = 'db'` はDocker専用。さくら環境で実行する場合は `mysql3115.db.sakura.ne.jp` に変更すること。

**Docker実行結果（2026-07-07時点）**:
- 対象: 289社中 259社（既に座標あり: 30社）
- Nominatim取得成功: 249社
- 失敗（API通信エラー）: 10社
- 結果: 279/289社（96.5%）が座標登録済み

スクリプトファイル: `www/kric.co.jp/data/geocode_batch.php`（プロジェクトルート）

---

#### 7-6. `php-code-widget` プラグインを無効化

本番DBには `php-code-widget` がアクティブな状態で残っているため、PHPスクリプトでDBを更新して無効化する。
PHPシリアライズデータを正しく扱うためSQLではなくスクリプトで処理する。

```bash
cat > ~/www/kric.co.jp/www/fix_plugins.php << 'EOF'
<?php
$conn = new mysqli('[DBホスト]', '[DBユーザー]', '[DBパスワード]', '[DB名]');
$result = $conn->query("SELECT option_value FROM wp2_options WHERE option_name = 'active_plugins'");
$row = $result->fetch_assoc();
$plugins = unserialize($row['option_value']);
$plugins = array_values(array_filter($plugins, function($p) {
    return $p !== 'php-code-widget/execphp.php';
}));
$new_value = serialize($plugins);
$conn->query("UPDATE wp2_options SET option_value = '" . $conn->real_escape_string($new_value) . "' WHERE option_name = 'active_plugins'");
echo "完了";
$conn->close();
EOF
```

ブラウザで `https://[ドメイン]/fix_plugins.php` にアクセスし「完了」と表示されたらスクリプトを削除する。

```bash
rm ~/www/kric.co.jp/www/fix_plugins.php
```

> ⚠️ **本番での注意**: 本番DBは `phpMyAdmin` からエクスポートしたものをインポートするため、Dockerで行った無効化が反映されていない。本番移行時も必ずこの手順を実施すること。

---

## ■ 本番で実施すべきコードパッチ（必須）

### 1. CakePHP コアパッチ（`data/lib/Cake/` 配下）

#### 1-1. `View/Helper.php`

```
変更箇所: lib/Cake/View/Helper.php（約961行目）
内容: get_magic_quotes_gpc() を false に置き換え
理由: PHP 8.0 で廃止された関数
```

```php
// 変更前
if (get_magic_quotes_gpc()) {

// 変更後
if (false) {
```

---

### 2. アプリケーションコードのパッチ（`data/app/` 配下）

#### 2-1. `Vendor/Org/OrgStdClass.php`

**変更1: `queryCheck()` メソッド内の `each()` を `foreach` に置き換え**
```
理由: each() は PHP 8.0 で廃止
対象行: 約132〜164行目
```

**変更2: `formToAddsSession()` 内の `each()` を置き換え**
```
対象行: 約544行目
変更前: while(list($na,$vl) = @each($form))
変更後: foreach($form AS $na => $vl)
```

**変更3: `newLineInsert()` 内の `each()` を置き換え**
```
対象行: 約831行目
変更前: while(list($inc,$vl) = each($TMP))
変更後: foreach($TMP AS $inc => $vl)
```

**変更4: `doCheck()` 内の `get_magic_quotes_gpc()` 削除**
```
対象行: 約165〜168行目
理由: PHP 8.0 で廃止。magic_quotes は常に無効のため処理ごと削除
変更前:
    if (get_magic_quotes_gpc()) {
        $do = stripslashes($do);
    }
変更後:
    // magic_quotes_gpc は PHP 8.0 で廃止（常にfalse）
```

#### 2-2. `Vendor/Org/OrgSessionClass.php`

**`each()` を `foreach` に置き換え（2箇所）**
```
対象行: 約266行目、345行目
変更前: while(list($na,$vl) = each($D_ROW))  /  while(list($na,$vl) = @each($D_ROW))
変更後: foreach($D_ROW AS $na => $vl)
```

#### 2-3. `Vendor/Org/OrgInitialClass.php`

**SSL_FLAG の else ブランチ修正**（手順6-3 参照）

#### 2-4. `Config/database.php`

```
変更内容: __construct() を追加し、Docker環境のみ接続先を切り替える仕組みを追加
本番への影響: なし（getenv('APP_ENV') が 'docker' でない限り既存設定が使われる）
```

---

### 3. WordPress 関連

#### 3-1. プラグイン「php-code-widget」を無効化

```
理由: create_function() が PHP 8.0 で廃止。Fatal Error の原因。
方法: WordPress管理画面 > プラグイン > 「PHP Code Widget」を無効化
影響: サイドバーへの配置はなかったため、表示への影響なし
```

#### 3-2. プラグインのアップデート

以下のプラグインは PHP 8.3 との非互換による Deprecated 警告が出ているため、
**最新版へアップデートする**。

| プラグイン | 警告内容 | 優先度 |
|-----------|---------|--------|
| All in One SEO Pack | 動的プロパティの大量 Deprecated | 中（表示には影響なし） |
| breadcrumb-navxt | textdomain タイミング警告 | 低 |
| disable-gutenberg | textdomain タイミング警告 | 低 |

**方法**: WordPress管理画面 > プラグイン > 各プラグインの「今すぐ更新」

---

## ■ ⚠️ テスト用一時変更（本番デプロイ前に必ず戻すこと）

### メール送信先アドレスの一時変更（2026-07-22）

**変更ファイル**: `www/kric.co.jp/data/app/Controller/IisController.php`

**変更内容**: 事務局宛通知メール（A〜E）の `to` アドレスをデザイナーUATテスト用に一時変更した。

| 変更前 | 変更後 |
|--------|--------|
| `info@kric.co.jp` | `info@inlet-inc.com` |

**対象箇所（6箇所）**:
- 初回ログイン通知（`member_first_login`）
- 会社情報更新通知（`company_edit_send`）
- 会員情報更新通知（`member_edit_send`）× 3箇所
- テストメール関数（`testmail`）

**本番デプロイ前の復元手順**:

ファイル内の `info@inlet-inc.com` を全て `info@kric.co.jp` に戻すこと。
`// TODO: 本番デプロイ前に info@kric.co.jp に戻すこと` コメントが目印。

```bash
# WSL上で確認（6件ヒットすればOK）
grep -n "info@inlet-inc.com" www/kric.co.jp/data/app/Controller/IisController.php
```

> ⚠️ このまま本番にデプロイすると、会員操作に伴う事務局への通知メールがすべて
> `info@inlet-inc.com`（デザイナー）に届いてしまう。**必ず戻すこと。**

---

## ■ 本番で実施しない（Docker専用）変更

以下はローカルDocker環境専用の設定であり、**本番サーバーには適用しないこと**。

| ファイル | 変更内容 | 理由 |
|---------|---------|------|
| `www/kric.co.jp/www/app.php` | `DOCKER` 環境判定の追加、ROOT を `/var/www` に設定 | Docker のディレクトリ構造専用 |
| `docker-compose.yml` | `APP_ENV=docker` 環境変数の追加 | Docker専用 |
| `www/php.ini` | `display_errors=On` の追加 | 開発時のデバッグ用。本番は Off |
| `iis/.htaccess` | HTTPS リダイレクトのコメントアウト | Docker に HTTPS 環境がないため |
| `Config/database.php` | Docker用DB接続先（host=db, root/root）の分岐 | Docker専用DB設定 |
| `Vendor/Org/OrgInitialClass.php` | `getenv('APP_ENV') === 'docker'` による SSL_FLAG 切り替え | 本番では `''` で固定でよい |
| `Config/email.php` | Docker環境でメール送信を無効化（Debug transport） | 誤送信防止。本番はそのまま |
| `Vendor/Org/OrgSendMailClass.php` | `DEVELOPMENT_FLAG === 'DOCKER'` で `method=0`（送信しない） | 誤送信防止。本番はそのまま |
| `Controller/IisController.php` | `getenv('APP_ENV') === 'docker'` の場合 `_addMailDB()` をスキップ | Docker環境でメールキューが蓄積しないよう抑止。さくら環境では通常通りキューに積まれる |
| `wp-config.php` | `WP_DEBUG=true`, `WP_DEBUG_LOG=true` | 開発時確認用。本番は false に戻すこと |

---

## ■ 今後の作業（未着手）

| 項目 | 担当 | 状態 |
|------|------|------|
| CakePHP `String` クラス名衝突の修正 | プログラム | 🔲 未着手（現時点では動作しているため後回し） |
| `mcrypt` → `openssl` 差し替え | プログラム | 🔲 未着手 |
| `AppModel.php` / `AppController.php` への `#[AllowDynamicProperties]` 追加 | プログラム | 🔲 未着手 |
| WordPress テーマのデザインリニューアル | デザイン | 🔲 モック完成・実装待ち |
| CakePHP View のデザインリニューアル | デザイン | 🔲 モック完成・実装待ち |
| 動画投稿機能の実装 | プログラム | 🔲 未着手 |
| UAT用 さくらcron設定（ビジネスダイレクトメール配信） | インフラ | 🔲 UAT開始前に設定。`send_cron_db` アクションを定期実行すること |
| WordPress プラグインのアップデート | 両方 | 🔲 未着手 |
| 全ページの表示確認（SITE_TREE.md 参照） | 両方 | 🔲 進行中 |

---

## ■ 本番移行チェックリスト（最終確認用）

### サーバー設定
- [ ] PHP 8.3 へ切り替え（CGI モード）
- [ ] SSL 証明書の設定（Let's Encrypt）
- [ ] `~/www/.htaccess` の設置（本番公開時は IP 制限行を削除）

### ファイル配置
- [ ] `~/www/kric.co.jp/www/` にファイルをアップロード
- [ ] `~/data/` に CakePHP data フォルダをアップロード
- [ ] `wp-includes` を WordPress 5.8.13 公式から補完

### 設定ファイル
- [ ] `wp-config.php` の DB 接続情報をさくら用に更新
- [ ] `wp-config.php` の `WP_DEBUG` を `false` に変更
- [ ] `OrgInitialClass.php` の SSL_FLAG を `''` に変更
- [ ] `iis/.htaccess` の HTTPS リダイレクトが有効なことを確認

### コードパッチ
- [ ] 上記「本番で実施すべきコードパッチ」をすべて適用済みか確認

### WordPress
- [ ] `php-code-widget` を無効化
- [ ] WordPress プラグインをすべてアップデート

### データベース
- [ ] DB バックアップ取得（移行前）
- [ ] DB インポート
- [ ] `wp2_options` の `siteurl` / `home` をさくらの URL に更新
- [ ] `business_direct` テーブルへのカラム追加（手順7-5）: `shubetsu`, `syozaichi`, `video_url`, `poster_company`, `poster_name`
- [ ] `member` テーブルへのカラム追加（手順7-5）: `jiyuu_toukou`, `upload_photo`
- [ ] `company` テーブルへのカラム追加（手順7-6）: `pr_video`
- [ ] `company` テーブルへの住所3カラム追加（手順7-6-2）: `city`, `address_town`, `building`
- [ ] `company` テーブルから `address` カラムを削除（手順7-6-2）
- [ ] `company` テーブルへの `lat`, `lng` カラム追加（手順7-6-3）
- [ ] ジオコーディングバッチの実行（手順7-6-4）
- [ ] KRICメディアの `range_label` 設定（手順7-7）
- [ ] WordPress ページ登録: `contact-confirm`, `contact-thanks`, `nyukai-form-thanks`, `katsudo`（手順7-8）

#### 7-7. KRICメディア `range_label` の設定

デザインリニューアルで KRICメディアのグループ表示を `range_label` ACF フィールドで管理する方式に変更した。
本番DBをインポートした後、以下のSQLスクリプトを実行して各投稿に `range_label` を設定すること。

```bash
# Docker環境
docker exec -i kric-db-1 mysql --default-character-set=utf8mb4 -u root -proot _kric7 < /mnt/c/Users/web0012osk/Documents/kric/set_range_labels.sql

# さくらテスト環境（SSH接続後）
mysql -u kric_kric7 -p -h mysql3115.db.sakura.ne.jp kric_kric7 < ~/set_range_labels.sql
```

SQLファイル: `set_range_labels.sql`（プロジェクトルート）

設定されるグループ:

| チャンネル | グループ | orderView範囲 |
|---|---|---|
| MBS1 | 第1回～第13回 (2022/10/03 ～ 2022/12/26) | 20220701 ～ 20230101 |
| MBS1 | 第14回～第39回 (2023/01/02 ～ 2023/06/26) | 20230101 ～ 20230701 |
| MBS1 | 第40回～第65回 (2023/07/03 ～ 2023/12/25) | 20230701 ～ 20240101 |
| MBS1 | 第66回～第91回 (2024/01/06 ～ 2024/06/24) | 20240101 ～ 20240701 |
| MBS1 | 第92回～第105回 (2024/07/01 ～ 2024/09/30) | 20240701 ～ 20241001 |
| MBS1 | 第106回～ (2024/10/07 ～) | 20241001～ |
| MBS2 | 第1回～第18回 (2024/10/14 ～ 2025/03/17) | 20241001 ～ 20250401 |
| MBS2 | 第19回～第36回 (2025/04/07 ～ 2025/09/15) | 20250401 ～ 20251001 |
| MBS2 | 第37回～ (2025/10/06 ～) | 20251001～ |
| YouTube | 第1回～ (2024/10/25 ～) | 全件 |

> ⚠️ 本番DBを再取得するたびにこのスクリプトの再実行が必要。

---

#### 7-8. WordPress ページ登録

デザインリニューアルで新規追加したページテンプレート用のWPページを登録する。

```sql
-- contact-confirm
INSERT INTO wp2_posts (post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES (1,NOW(),NOW(),'','お問い合わせ確認','','publish','closed','closed','','contact-confirm','','',NOW(),NOW(),'',0,'',0,'page','',0);
SET @id=LAST_INSERT_ID();
INSERT INTO wp2_postmeta (post_id,meta_key,meta_value) SELECT @id,'_wp_page_template','page-contact-confirm.php' WHERE @id>0;

-- contact-thanks
INSERT INTO wp2_posts (post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES (1,NOW(),NOW(),'','お問い合わせ完了','','publish','closed','closed','','contact-thanks','','',NOW(),NOW(),'',0,'',0,'page','',0);
SET @id=LAST_INSERT_ID();
INSERT INTO wp2_postmeta (post_id,meta_key,meta_value) SELECT @id,'_wp_page_template','page-contact-thanks.php' WHERE @id>0;

-- nyukai-form-thanks
INSERT INTO wp2_posts (post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES (1,NOW(),NOW(),'','入会専用フォーム完了','','publish','closed','closed','','nyukai-form-thanks','','',NOW(),NOW(),'',0,'',0,'page','',0);
SET @id=LAST_INSERT_ID();
INSERT INTO wp2_postmeta (post_id,meta_key,meta_value) SELECT @id,'_wp_page_template','page-nyukai-form-thanks.php' WHERE @id>0;

-- katsudo
INSERT INTO wp2_posts (post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES (1,NOW(),NOW(),'','活動内容','','publish','closed','closed','','katsudo','','',NOW(),NOW(),'',0,'',0,'page','',0);
SET @id=LAST_INSERT_ID();
INSERT INTO wp2_postmeta (post_id,meta_key,meta_value) SELECT @id,'_wp_page_template','page-katsudo.php' WHERE @id>0;
```

> ⚠️ `--default-character-set=utf8mb4` を付けて実行すること（日本語タイトルの文字化け防止）。
> 実行後、WP管理画面「設定 → パーマリンク設定」で「変更を保存」をクリックしてリライトルールを更新すること。

---

### 最終確認
- [ ] **【必須】`IisController.php` の通知メール宛先を `info@kric.co.jp` に戻す（現在 `info@inlet-inc.com` に変更中）**
- [ ] 全ページ表示確認（SITE_TREE.md の全項目）
- [ ] メール送信テスト（テスト環境で1通確認）
