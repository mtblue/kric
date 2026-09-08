#!/usr/bin/env bash
# ==============================================================
# 本番ダンプを Docker DB にインポートしてローカル開発環境をセットアップする
# 使い方: bash import_and_setup.sh <dump_file.sql>
# 例:     bash import_and_setup.sh _kric.sql
#
# ※ WSL 内から実行すること（docker コマンドが使える環境）
# ※ Docker が起動していること（docker compose up -d）
# ==============================================================
set -euo pipefail

# --------------------------------------------------------------
# 引数チェック
# --------------------------------------------------------------
if [[ $# -eq 0 ]]; then
    echo "使い方: bash $0 <dump_file.sql>" >&2
    exit 1
fi

DUMP_FILE="$1"
DB_NAME="_kric7"
CONTAINER="kric-db-1"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
CACHE_DIR="$SCRIPT_DIR/www/kric.co.jp/data/app/tmp/cache"

# 実行する SQL ファイル（順序厳守）
SQL_SETUP="$SCRIPT_DIR/setup_after_import.sql"        # スキーマ変更・WP設定
SQL_COMPANY="$SCRIPT_DIR/kric_company.sql"            # 会社住所データ（スキーマ変更後に実行）
SQL_MEDIA_TABLES="$SCRIPT_DIR/kric_media_tables.sql"  # KRICメディア用テーブル作成
SQL_MEDIA_ACF="$SCRIPT_DIR/kric_media_acf_field.sql"  # ACF「グループ名」フィールド追加
SQL_RANGE_LABELS="$SCRIPT_DIR/set_range_labels.sql"   # KRICメディア range_label（グループ名）設定
SQL_PAGES="$SCRIPT_DIR/create_renewal_pages.sql"      # リニューアル用WPページ作成（本番DBに存在しない新規ページ）

# ダンプファイルの絶対パス解決
[[ "$DUMP_FILE" = /* ]] && DUMP_PATH="$DUMP_FILE" || DUMP_PATH="$SCRIPT_DIR/$DUMP_FILE"

# ファイル存在チェック
check_file() {
    if [[ ! -f "$1" ]]; then
        echo "[ERROR] ファイルが見つかりません: $1" >&2
        exit 1
    fi
}
check_file "$DUMP_PATH"
check_file "$SQL_SETUP"
check_file "$SQL_COMPANY"
check_file "$SQL_MEDIA_TABLES"
check_file "$SQL_MEDIA_ACF"
check_file "$SQL_RANGE_LABELS"
check_file "$SQL_PAGES"

# Docker 起動確認
if ! docker ps --format '{{.Names}}' 2>/dev/null | grep -q "^${CONTAINER}$"; then
    echo "[ERROR] コンテナ '$CONTAINER' が起動していません。" >&2
    echo "        cd /mnt/c/Users/web0012osk/Documents/kric && docker compose up -d" >&2
    exit 1
fi

MYSQL="docker exec -i $CONTAINER mysql -uroot -proot --default-character-set=utf8mb4"

echo "========================================"
echo " 本番データインポート & セットアップ"
echo " ダンプ: $(basename "$DUMP_PATH")"
echo "========================================"

# --------------------------------------------------------------
# STEP 1: DB リセット（既存データを全削除して作り直し）
# --------------------------------------------------------------
echo ""
echo "[1/9] DB リセット中 ($DB_NAME) ..."
$MYSQL -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "      完了"

# --------------------------------------------------------------
# STEP 2: 本番ダンプをインポート
# --------------------------------------------------------------
echo ""
echo "[2/9] ダンプをインポート中 ..."
$MYSQL "$DB_NAME" < "$DUMP_PATH"
echo "      完了"

# --------------------------------------------------------------
# STEP 3: ローカル開発用セットアップSQL（スキーマ変更・WP設定）
#   - company テーブル住所カラム分割（address → address_town, city, building 追加）
#   - WordPress テーマ kric-renewal 有効化
#   - php-code-widget プラグイン無効化
#   - WordPress URL を localhost:8080 に設定
# --------------------------------------------------------------
echo ""
echo "[3/8] セットアップSQL を実行中（スキーマ変更・WP設定）..."
$MYSQL "$DB_NAME" < "$SQL_SETUP"
echo "      完了"

# --------------------------------------------------------------
# STEP 4: 会社住所データを投入
#   ※ STEP 3 で address_town / city / building カラムを作成した後に実行
# --------------------------------------------------------------
echo ""
echo "[4/8] 会社住所データを投入中 (kric_company.sql) ..."
$MYSQL "$DB_NAME" < "$SQL_COMPANY"
echo "      完了"

# --------------------------------------------------------------
# STEP 5: KRICメディア用テーブルを作成
#   （本番には存在しない新テーブル: kric_media_group / kric_media）
# --------------------------------------------------------------
echo ""
echo "[5/8] KRICメディアテーブルを作成中 (kric_media_tables.sql) ..."
$MYSQL "$DB_NAME" < "$SQL_MEDIA_TABLES"
echo "      完了"

# --------------------------------------------------------------
# STEP 6: ACF「グループ名」フィールドを追加
#   （本番にはない wp2_posts エントリ）
# --------------------------------------------------------------
echo ""
echo "[6/8] ACFフィールドを追加中 (kric_media_acf_field.sql) ..."
$MYSQL "$DB_NAME" < "$SQL_MEDIA_ACF"
echo "      完了"

# --------------------------------------------------------------
# STEP 7: KRICメディア range_label（グループ名）を設定
#   kric-media 投稿の orderView 日付を元にグループラベルを wp2_postmeta に挿入
# --------------------------------------------------------------
echo ""
echo "[7/8] KRICメディア range_label を設定中 (set_range_labels.sql) ..."
$MYSQL "$DB_NAME" < "$SQL_RANGE_LABELS"
echo "      完了"

# --------------------------------------------------------------
# STEP 8: リニューアル用 WordPress ページ作成
#   本番DBに存在しない新規ページ（news, katsudo, dantai 等）を追加
# --------------------------------------------------------------
echo ""
echo "[8/8] リニューアル用ページを作成中 (create_renewal_pages.sql) ..."
$MYSQL "$DB_NAME" < "$SQL_PAGES"
echo "      完了"

# --------------------------------------------------------------
# STEP 9: ジオコーディングバッチ（lat/lng が未設定の会社のみ座標を補完）
#   - kric_company.sql に既存290社の lat/lng が含まれているため通常は即完了。
#   - インポート後に新たに追加された会社（lat IS NULL）がある場合のみ実行される。
#   - Phase 1  : google_map URL から !3d!4d ピン座標を優先抽出
#   - Phase 1.5: 短縮URL（maps.app.goo.gl）をリダイレクト追跡して座標抽出
#   - Phase 2  : Google Maps Geocoding API で都道府県含む住所から座標取得
# --------------------------------------------------------------
echo ""
echo "[9/9] ジオコーディングバッチを実行中 (geocode_batch.php) ..."
docker cp "$SCRIPT_DIR/geocode_batch.php" kric-app-1:/tmp/geocode_batch.php
docker exec kric-app-1 php /tmp/geocode_batch.php
docker exec kric-app-1 rm /tmp/geocode_batch.php
echo "      完了"

# --------------------------------------------------------------
# BONUS: CakePHP キャッシュクリア
# --------------------------------------------------------------
echo ""
echo "[+] CakePHP キャッシュクリア ..."
for subdir in models persistent views; do
    TARGET="$CACHE_DIR/$subdir"
    if [[ -d "$TARGET" ]]; then
        find "$TARGET" -mindepth 1 -type f -delete
        echo "    cleared: $TARGET"
    fi
done
echo "    完了"

# --------------------------------------------------------------
# 完了メッセージ
# --------------------------------------------------------------
echo ""
echo "========================================"
echo " セットアップ完了"
echo "========================================"
echo " WordPress トップ  : http://localhost:8080/"
echo " WP 管理画面       : http://localhost:8080/wp-admin/"
echo " 会員エリア         : http://localhost:8080/member/"
echo " IIS ログイン       : http://localhost:8080/iis/login"
echo "========================================"
