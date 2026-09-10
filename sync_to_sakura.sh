#!/bin/bash
# Sakura テスト環境への同期スクリプト

LOCAL="/mnt/c/Users/web0012osk/Documents/kric/www/kric.co.jp"
REMOTE="kric@kric.sakura.ne.jp:~/www/kric.co.jp"

echo "=== Sakura テスト環境への同期開始 ==="

# ----- コントローラー -----
scp "$LOCAL/data/app/Controller/IisController.php" \
    "$REMOTE/data/app/Controller/IisController.php" && echo "OK: IisController.php"

# ----- Vendor クラス -----
scp "$LOCAL/data/app/Vendor/Org/OrgDbShareQueryExtendsClass.php" \
    "$REMOTE/data/app/Vendor/Org/OrgDbShareQueryExtendsClass.php" && echo "OK: OrgDbShareQueryExtendsClass.php"

# ----- レイアウト -----
scp "$LOCAL/data/app/View/Layouts/iis.ctp" \
    "$REMOTE/data/app/View/Layouts/iis.ctp" && echo "OK: iis.ctp"

# ----- IIS ビュー -----
scp "$LOCAL/data/app/View/Iis/login.ctp.html" \
    "$REMOTE/data/app/View/Iis/login.ctp.html" && echo "OK: login.ctp.html"

scp "$LOCAL/data/app/View/Iis/reminder.ctp.html" \
    "$REMOTE/data/app/View/Iis/reminder.ctp.html" && echo "OK: reminder.ctp.html"

scp "$LOCAL/data/app/View/Iis/library.ctp.html" \
    "$REMOTE/data/app/View/Iis/library.ctp.html" && echo "OK: library.ctp.html"

scp "$LOCAL/data/app/View/Iis/library_list.ctp.html" \
    "$REMOTE/data/app/View/Iis/library_list.ctp.html" && echo "OK: library_list.ctp.html"

scp "$LOCAL/data/app/View/Iis/library_detail.ctp.html" \
    "$REMOTE/data/app/View/Iis/library_detail.ctp.html" && echo "OK: library_detail.ctp.html"

scp "$LOCAL/data/app/View/Iis/media_shared.ctp.html" \
    "$REMOTE/data/app/View/Iis/media_shared.ctp.html" && echo "OK: media_shared.ctp.html"

scp "$LOCAL/data/app/View/Iis/dir.ctp.html" \
    "$REMOTE/data/app/View/Iis/dir.ctp.html" && echo "OK: dir.ctp.html"

scp "$LOCAL/data/app/View/Iis/member_edit.ctp.html" \
    "$REMOTE/data/app/View/Iis/member_edit.ctp.html" && echo "OK: member_edit.ctp.html"

scp "$LOCAL/data/app/View/Iis/member.ctp.html" \
    "$REMOTE/data/app/View/Iis/member.ctp.html" && echo "OK: member.ctp.html"

scp "$LOCAL/data/app/View/Iis/member_new.ctp.html" \
    "$REMOTE/data/app/View/Iis/member_new.ctp.html" && echo "OK: member_new.ctp.html"

# ----- CSS -----
scp "$LOCAL/www/wp-content/themes/kric-renewal/css/member.css" \
    "$REMOTE/www/wp-content/themes/kric-renewal/css/member.css" && echo "OK: member.css"

# ----- 画像 -----
scp "$LOCAL/www/wp-content/themes/kric-renewal/images/folder.png" \
    "$REMOTE/www/wp-content/themes/kric-renewal/images/folder.png" && echo "OK: folder.png"

scp "$LOCAL/www/wp-content/themes/kric-renewal/images/folder2.png" \
    "$REMOTE/www/wp-content/themes/kric-renewal/images/folder2.png" && echo "OK: folder2.png"

scp "$LOCAL/www/wp-content/themes/kric-renewal/images/folder3.png" \
    "$REMOTE/www/wp-content/themes/kric-renewal/images/folder3.png" && echo "OK: folder3.png"

scp "$LOCAL/www/wp-content/themes/kric-renewal/images/folder4.png" \
    "$REMOTE/www/wp-content/themes/kric-renewal/images/folder4.png" && echo "OK: folder4.png"

scp "$LOCAL/www/wp-content/themes/kric-renewal/images/file.png" \
    "$REMOTE/www/wp-content/themes/kric-renewal/images/file.png" && echo "OK: file.png"

scp "$LOCAL/www/wp-content/themes/kric-renewal/images/link.png" \
    "$REMOTE/www/wp-content/themes/kric-renewal/images/link.png" && echo "OK: link.png"

# ----- キャッシュクリア -----
echo ""
echo "=== キャッシュクリア ==="
ssh kric@kric.sakura.ne.jp \
    "rm -rf ~/www/kric.co.jp/data/app/tmp/cache/{models,persistent,views}/*" \
    && echo "OK: キャッシュクリア完了"

echo ""
echo "=== 同期完了 ==="
echo ""
echo "※ 初回のみ: DBテーブル作成が必要です"
echo "   ssh kric@kric.sakura.ne.jp でログインし、以下を実行:"
echo "   mysql -h mysql3115.db.sakura.ne.jp -u kric_kric7 -p kric_kric7 < ~/kric_media_tables.sql"
