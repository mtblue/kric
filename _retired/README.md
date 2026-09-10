# _retired/

役目を終えたが、削除せず参照できる状態で残しているファイル置き場。
デプロイ対象外（`sync_to_prod.sh` / `deploy_to_sakura.sh` は `www/` 配下のみを転送する）。

| ファイル | 用途 | 退避日 |
|---|---|---|
| `preview.php` | メンテナンス期間中のシークレットURLバイパス。**再利用時は `PREVIEW_TOKEN` を必ず変更すること** | 2026-09-10 |
| `maintenance.html` | メンテナンス画面。次回メンテナンス時に `www/kric.co.jp/www/` へ戻して使う | 2026-09-10 |
| `IisController_sakura.php` | さくらテスト環境向けの派生版 | 2026-09-10 |
| `OrgInitialClass_sakura.php` | 同上 | 2026-09-10 |
| `dir_sakura.ctp` | 同上 | 2026-09-10 |
| `media_shared_ref.ctp.html` | KRICメディアの参照用テンプレート断片 | 2026-09-10 |
| `find_hascap.sh` | 調査用の一時スクリプト | 2026-09-10 |
| `fix_member_css.py` | member.css の一時修正スクリプト | 2026-09-10 |

## メンテナンスモードを再度使う場合

1. `preview.php` の `PREVIEW_TOKEN` を推測されない値に変更
2. `preview.php` と `maintenance.html` を `www/kric.co.jp/www/` へコピー
3. `www/kric.co.jp/www/.htaccess` にメンテナンスブロックを追加
   （503 + `ErrorDocument` 方式。302 にすると検索インデックスから落ちる）
4. 公開時は 1〜3 を戻し、公開領域から 2 のファイルを削除する
