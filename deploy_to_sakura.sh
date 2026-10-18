#!/bin/bash
# =============================================================================
# deploy_to_sakura.sh
# Docker ローカル環境 → さくらサーバーへのデプロイスクリプト
# 対応環境: テスト（kric.sakura.ne.jp）/ 本番（kric.co.jp）
#
# 実行環境: WSL
# 使い方  : bash deploy_to_sakura.sh
# =============================================================================

set -euo pipefail

# ==============================================================================
# シークレット読み込み
# ==============================================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/deploy_config.sh"
if [ ! -f "$CONFIG_FILE" ]; then
  echo "ERROR: deploy_config.sh が見つかりません。deploy_config.sh.example をコピーして作成してください。"
  exit 1
fi
source "$CONFIG_FILE"

# ==============================================================================
# 環境別設定
# ==============================================================================

# --- テスト環境 ---
TEST_SSH="kric@kric.sakura.ne.jp"
TEST_DB_HOST="mysql3115.db.sakura.ne.jp"
TEST_DB_USER="kric_kric7"
TEST_DB_NAME="kric_kric7"
TEST_DB_PASS_DEFAULT=""   # deploy_config.sh から読み込む
TEST_URL="https://kric.sakura.ne.jp"
TEST_HTACCESS="deploy/www_htaccess.test"

# --- 本番環境 ---
# ⚠ 実際の値に変更してから使用すること
PROD_SSH="kric2021@kric.co.jp"
PROD_DB_HOST="localhost"
PROD_DB_USER="_kric7"
PROD_DB_NAME="_kric7"
PROD_DB_PASS_DEFAULT=""   # deploy_config.sh から読み込む
PROD_URL="https://kric.co.jp"
PROD_HTACCESS="deploy/www_htaccess.prod"

# --- 共通設定 ---
LOCAL_KRIC_DIR="/mnt/c/Users/web0012osk/Documents/kric"
WP_VERSION="7.1"                         # wp-includes 補完用（現行 WP バージョンと一致させること）
DOCKER_DB_CONTAINER="kric-db-1"
DOCKER_DB_NAME="_kric7"
DOCKER_DB_USER="root"
DOCKER_DB_PASS="root"

# ==============================================================================
# ヘルパー
# ==============================================================================
TOTAL_STEPS=5
_step=0
info() { _step=$((_step+1)); printf '\n\033[1;34m[%d/%d] %s\033[0m\n' "$_step" "$TOTAL_STEPS" "$1"; }
step() { printf '  → %s\n' "$1"; }
ok()   { printf '  \033[32m✓\033[0m %s\n' "$1"; }
warn() { printf '  \033[33m⚠\033[0m  %s\n' "$1"; }
err()  { printf '\n\033[31m✗ ERROR: %s\033[0m\n' "$1" >&2; exit 1; }

# ==============================================================================
# 0. 環境選択・事前確認
# ==============================================================================
printf '\n\033[1m=== KRIC デプロイスクリプト ===\033[0m\n\n'
printf '  デプロイ先を選択してください:\n'
printf '    1) テスト環境  (%s)\n' "$TEST_URL"
printf '    2) 本番環境    (%s)\n' "$PROD_URL"
printf '\n'
read -rp "  選択 [1/2]: " ENV_CHOICE

case "$ENV_CHOICE" in
    1)
        TARGET_LABEL="テスト環境"
        TARGET_SSH="$TEST_SSH"
        TARGET_DB_HOST="$TEST_DB_HOST"
        TARGET_DB_USER="$TEST_DB_USER"
        TARGET_DB_NAME="$TEST_DB_NAME"
        DB_PASS_DEFAULT="$TEST_DB_PASS_DEFAULT"
        TARGET_URL="$TEST_URL"
        TARGET_HTACCESS="$LOCAL_KRIC_DIR/$TEST_HTACCESS"
        SKIP_DB=false
        ;;
    2)
        TARGET_LABEL="本番環境"
        TARGET_SSH="$PROD_SSH"
        TARGET_DB_HOST="$PROD_DB_HOST"
        TARGET_DB_USER="$PROD_DB_USER"
        TARGET_DB_NAME="$PROD_DB_NAME"
        DB_PASS_DEFAULT="$PROD_DB_PASS_DEFAULT"
        TARGET_URL="$PROD_URL"
        TARGET_HTACCESS="$LOCAL_KRIC_DIR/$PROD_HTACCESS"
        # 本番デプロイ確認（誤操作防止）
        printf '\n\033[31m  ⚠ 本番環境へのデプロイです。本当に続行しますか？ [yes/N]: \033[0m'
        read -r PROD_CONFIRM
        [[ "$PROD_CONFIRM" == "yes" ]] || { printf '\n  キャンセルしました。\n\n'; exit 0; }
        # DB デプロイ選択（初回: y / 2回目以降ファイルのみ: N）
        printf '  DB もデプロイしますか？（初回: y / ループ時はファイルのみ: N）[y/N]: '
        read -r DB_CHOICE
        [[ "$DB_CHOICE" =~ ^[Yy]$ ]] && SKIP_DB=false || SKIP_DB=true
        ;;
    *)
        err "無効な選択です。1 または 2 を入力してください。"
        ;;
esac

printf '\n  対象  : %s (%s)\n' "$TARGET_LABEL" "$TARGET_URL"
printf '  ソース: %s\n\n' "$LOCAL_KRIC_DIR"

# htaccess テンプレートの存在確認
[ -f "$TARGET_HTACCESS" ] \
    || err "htaccess テンプレートが見つかりません: $TARGET_HTACCESS"

# DB パスワード入力（本番は DB を触らないためスキップ）
TARGET_DB_PASS=""
if [ "$SKIP_DB" = false ]; then
    read -rsp "  DB パスワード（Enter でデフォルト使用）: " TARGET_DB_PASS
    echo
    if [ -z "$TARGET_DB_PASS" ]; then
        [ -n "$DB_PASS_DEFAULT" ] \
            || err "DB パスワードが未設定です。スクリプト上部の TEST_DB_PASS_DEFAULT を確認してください。"
        TARGET_DB_PASS="$DB_PASS_DEFAULT"
        printf '  （デフォルトパスワードを使用）\n'
    fi
fi

# ==============================================================================
# SSH ControlMaster 確立
# スクリプト全体で1本の SSH 接続を使い回す（接続数制限・タイムアウト対策）
# ==============================================================================
SSH_CTL="/tmp/kric_ssh_ctl_$$"
# ControlPersist=yes: 最後のコマンド終了後もマスター接続を維持（明示的に閉じるまで）
SSH_O="-o ControlMaster=auto -o ControlPath=$SSH_CTL -o ControlPersist=yes -o ServerAliveInterval=30 -o ServerAliveCountMax=6"

step "SSH 接続確立中..."
ssh $SSH_O -o ConnectTimeout=15 -o BatchMode=yes "$TARGET_SSH" 'echo ok' > /dev/null 2>&1 \
    || err "SSH 接続失敗。鍵が登録されているか確認してください:\n  ssh-copy-id ${TARGET_SSH}"
ok "SSH 接続 OK（ControlMaster 確立）"

# 終了時に SSH マスター接続を閉じて一時ファイルを削除
DEPLOY_TMP="/tmp/kric_deploy_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$DEPLOY_TMP"
trap '
    printf "\n  → SSH 接続を閉じています...\n"
    ssh -O exit -o ControlPath="$SSH_CTL" "$TARGET_SSH" 2>/dev/null || true
    rm -f "$SSH_CTL"
    printf "  → 一時ファイルを削除しています...\n"
    rm -rf "$DEPLOY_TMP"
' EXIT

# Docker 稼働確認（本番は DB を触らないためスキップ）
if [ "$SKIP_DB" = false ]; then
    step "Docker 確認中..."
    cd "$LOCAL_KRIC_DIR"
    if ! docker compose ps --format '{{.Name}}' 2>/dev/null | grep -q "db"; then
        warn "Docker が起動していません。起動します..."
        docker compose up -d
        printf '  起動待機中（10秒）...\n'
        sleep 10
    fi
    docker exec "$DOCKER_DB_CONTAINER" mysqladmin -u"$DOCKER_DB_USER" -p"$DOCKER_DB_PASS" ping -s > /dev/null 2>&1 \
        || err "Docker MySQL に接続できません。コンテナ名を確認してください: $DOCKER_DB_CONTAINER"
    ok "Docker MySQL 稼働中"
fi

# ==============================================================================
# [1/5] DB エクスポート（Docker MySQL → SQL ファイル）
# ==============================================================================
info "DB エクスポート（Docker MySQL → SQL）"

if [ "$SKIP_DB" = true ]; then
    warn "DB デプロイをスキップします（ファイルのみデプロイ）。"
else
    step "mysqldump 実行中..."
    docker exec "$DOCKER_DB_CONTAINER" mysqldump \
        -u"$DOCKER_DB_USER" -p"$DOCKER_DB_PASS" \
        --add-drop-table \
        --no-tablespaces \
        --default-character-set=utf8mb4 \
        "$DOCKER_DB_NAME" > "$DEPLOY_TMP/kric_db.sql" \
        || err "mysqldump 失敗"
    ok "エクスポート完了（$(wc -l < "$DEPLOY_TMP/kric_db.sql") 行）"

    step "照合順序変換（Docker MySQL 8.0 → さくら MySQL 5.7 互換）..."
    # Docker は utf8mb4_0900_ai_ci（MySQL 8.0 専用）を使うが
    # さくらの MySQL 5.7 はこの照合順序を知らないため事前に変換する
    sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_general_ci/g' "$DEPLOY_TMP/kric_db.sql"
    ok "照合順序変換完了（utf8mb4_0900_ai_ci → utf8mb4_general_ci）"
fi

# ==============================================================================
# [2/5] ファイルアーカイブ
# ==============================================================================
info "ファイルアーカイブ"

cd "$LOCAL_KRIC_DIR/www/kric.co.jp"

step "www アーカイブ作成中..."
# wp-includes: Windows ACL のためアーカイブ不可 → リモートで WordPress 公式から補完
# wp-content/uploads: ユーザーがアップロードしたメディアファイル → 本番を保持（送信しない）
tar czf "$DEPLOY_TMP/kric_www.tar.gz" \
    --exclude='www/wp-includes' \
    --exclude='www/wordpress' \
    --exclude='www/wp-config.php' \
    --exclude='www/wp-content/uploads' \
    --exclude='www/wp-content/cache' \
    --exclude='www/wp-content/upgrade' \
    www/ 2>/dev/null \
    || warn "tar に警告あり（ACL 制限による可能性あり、続行します）"
ok "kric_www.tar.gz 作成完了"

step "data アーカイブ作成中..."
tar czf "$DEPLOY_TMP/kric_data.tar.gz" \
    --exclude='data/app/tmp/cache' \
    --exclude='data/app/tmp/logs' \
    --exclude='data/app/tmp/upload' \
    --exclude='data/app/tmp/sessions' \
    --exclude='data/app/tmp/tests' \
    data/ 2>/dev/null \
    || warn "tar に警告あり（続行します）"
ok "kric_data.tar.gz 作成完了"

# ==============================================================================
# [3/5] さくらへアップロード（ControlMaster で確立済みの接続を再利用）
# ==============================================================================
info "アップロード（scp）"

# 小さいファイルを先にアップロード
step "~/www/.htaccess（${TARGET_LABEL}用）..."
scp $SSH_O "$TARGET_HTACCESS" "${TARGET_SSH}:~/www/.htaccess"
ok "~/www/.htaccess アップロード完了"

if [ "$SKIP_DB" = false ]; then
    step "kric_db.sql..."
    scp $SSH_O "$DEPLOY_TMP/kric_db.sql" "${TARGET_SSH}:~/"
    ok "DB SQL アップロード完了"
fi

step "kric_www.tar.gz..."
scp $SSH_O "$DEPLOY_TMP/kric_www.tar.gz" "${TARGET_SSH}:~/"
ok "www アーカイブ アップロード完了"

step "kric_data.tar.gz..."
scp $SSH_O "$DEPLOY_TMP/kric_data.tar.gz" "${TARGET_SSH}:~/"
ok "data アーカイブ アップロード完了"

# ==============================================================================
# [4/5] リモート：初期化・ファイル展開・設定修正
# ==============================================================================
info "リモート：初期化・ファイル展開・設定修正"

_WP_VER="$WP_VERSION"

# FreeBSD のデフォルトシェルは tcsh のため bash を明示指定
# heredoc 内: ${_WP_VER} 等はローカル bash が展開、\$HTACCESS はリモートで評価
ssh $SSH_O "$TARGET_SSH" bash << REMOTE_EOF
set -e

echo "  → 会員アップロードファイルを退避中..."
if [ -d ~/www/kric.co.jp/data/app/tmp/upload ]; then
    mv ~/www/kric.co.jp/data/app/tmp/upload ~/kric_upload_backup
    echo "  ✓ upload 退避完了"
else
    echo "  （upload フォルダなし、スキップ）"
fi

echo "  → WP メディアファイルを退避中（wp-content/uploads）..."
if [ -d ~/www/kric.co.jp/www/wp-content/uploads ]; then
    mv ~/www/kric.co.jp/www/wp-content/uploads ~/kric_uploads_wp_backup
    echo "  ✓ wp-content/uploads 退避完了"
else
    echo "  （wp-content/uploads フォルダなし、スキップ）"
fi

echo "  → 旧ファイル削除中..."
rm -rf ~/www/kric.co.jp/www
rm -rf ~/www/kric.co.jp/data
echo "  ✓ 削除完了"

echo "  → ファイル展開中..."
mkdir -p ~/www/kric.co.jp
tar xzf ~/kric_www.tar.gz -C ~/www/kric.co.jp/
tar xzf ~/kric_data.tar.gz -C ~/www/kric.co.jp/
echo "  ✓ 展開完了"

echo "  → 会員アップロードファイルを復元中..."
mkdir -p ~/www/kric.co.jp/data/app/tmp
if [ -d ~/kric_upload_backup ]; then
    mv ~/kric_upload_backup ~/www/kric.co.jp/data/app/tmp/upload
    echo "  ✓ upload 復元完了"
fi

echo "  → WP メディアファイルを復元中（wp-content/uploads）..."
mkdir -p ~/www/kric.co.jp/www/wp-content
if [ -d ~/kric_uploads_wp_backup ]; then
    mv ~/kric_uploads_wp_backup ~/www/kric.co.jp/www/wp-content/uploads
    echo "  ✓ wp-content/uploads 復元完了"
fi

echo "  → wp-includes 取得中（wordpress-${_WP_VER}-ja）..."
cd ~/www/kric.co.jp/www
wget "https://ja.wordpress.org/wordpress-${_WP_VER}-ja.tar.gz" -O /tmp/kric_wp_tmp.tar.gz \
    || { echo "  ✗ ダウンロード失敗（手動での wp-includes 補完が必要です）"; exit 1; }
tar xzf /tmp/kric_wp_tmp.tar.gz --strip-components=1 wordpress/wp-includes
rm -f /tmp/kric_wp_tmp.tar.gz
echo "  ✓ wp-includes 展開完了"

echo "  → iis/.htaccess HTTPS リダイレクト有効化..."
HTACCESS=~/www/kric.co.jp/www/iis/.htaccess
# Docker でコメントアウトした ' # RewriteXxx' 行を復元
# BSD sed（FreeBSD）は -i '' が必要、GNU sed は -i のみ
if sed --version 2>&1 | grep -q GNU; then
    sed -i    's/^ # Rewrite/ Rewrite/g' "\$HTACCESS"
else
    sed -i '' 's/^ # Rewrite/ Rewrite/g' "\$HTACCESS"
fi
echo "  ✓ iis/.htaccess 修正完了"

echo "  → admin/.htaccess さくら環境用に修正..."
HTACCESS_ADM=~/www/kric.co.jp/www/admin/.htaccess
if sed --version 2>&1 | grep -q GNU; then
    sed -i    's/^ # Rewrite/ Rewrite/g' "\$HTACCESS_ADM"
else
    sed -i '' 's/^ # Rewrite/ Rewrite/g' "\$HTACCESS_ADM"
fi
echo "  ✓ admin/.htaccess 修正完了"

echo "  → CakePHP キャッシュクリア..."
rm -f ~/www/kric.co.jp/data/app/tmp/cache/models/*     2>/dev/null || true
rm -f ~/www/kric.co.jp/data/app/tmp/cache/persistent/* 2>/dev/null || true
rm -f ~/www/kric.co.jp/data/app/tmp/cache/views/*      2>/dev/null || true
echo "  ✓ キャッシュクリア完了"

rm -f ~/kric_www.tar.gz ~/kric_data.tar.gz
echo "  ✓ リモート処理完了"
REMOTE_EOF

# ==============================================================================
# [5/5] DB インポート・WordPress URL 設定
# ==============================================================================
info "DB インポート・WordPress URL 設定"

if [ "$SKIP_DB" = true ]; then
    warn "DB インポートをスキップしました（ファイルのみデプロイ）。"
else
    step "DB インポート中（数分かかる場合があります）..."
    ssh $SSH_O "$TARGET_SSH" bash << DB_IMPORT_EOF
printf '[client]\nhost=%s\nuser=%s\npassword=%s\n' '${TARGET_DB_HOST}' '${TARGET_DB_USER}' '${TARGET_DB_PASS}' > /tmp/kric_my.cnf
chmod 600 /tmp/kric_my.cnf
mysql --defaults-file=/tmp/kric_my.cnf '${TARGET_DB_NAME}' < ~/kric_db.sql
rm -f ~/kric_db.sql
echo '  ✓ インポート完了'
DB_IMPORT_EOF
    ok "DB インポート完了"

    step "テストデータリセット（KRIC事務局 company_id=0）..."
    ssh $SSH_O "$TARGET_SSH" bash << RESET_TEST_EOF
printf '[client]\nhost=%s\nuser=%s\npassword=%s\n' '${TARGET_DB_HOST}' '${TARGET_DB_USER}' '${TARGET_DB_PASS}' > /tmp/kric_my.cnf
chmod 600 /tmp/kric_my.cnf
mysql --defaults-file=/tmp/kric_my.cnf '${TARGET_DB_NAME}' -e "UPDATE company SET company_kana=NULL, daihyousha=NULL, menkyo=NULL, zip=NULL, ken=NULL, city=NULL, address_town=NULL, building=NULL, koutsu=NULL, shozoku=NULL, tel=NULL, fax=NULL, url=NULL, email=NULL, jigyounaiyou=NULL, kameidantai=NULL, enkaku=NULL, shoukaibun=NULL, gyoushu=NULL, map_flg=NULL, google_map=NULL, url_map=NULL, upload_map=NULL, update_date='2013-06-25 12:58:22' WHERE company_id=0;"
rm -f /tmp/kric_my.cnf
echo '  ✓ KRIC事務局テストデータをリセット'
RESET_TEST_EOF
    ok "テストデータリセット完了"

    step "WordPress siteurl / home 更新・Docker 固有データ削除..."
    # ヒアドキュメント内に IN ('a','b') のような括弧付きリストを書くと
    # FreeBSD bash の一部バージョンで構文エラーになるため、
    # UPDATE を siteurl / home に分けて実行し IN (...) を回避する
    ssh $SSH_O "$TARGET_SSH" bash << WP_URL_EOF
printf '[client]\nhost=%s\nuser=%s\npassword=%s\n' '${TARGET_DB_HOST}' '${TARGET_DB_USER}' '${TARGET_DB_PASS}' > /tmp/kric_my.cnf
chmod 600 /tmp/kric_my.cnf
mysql --defaults-file=/tmp/kric_my.cnf '${TARGET_DB_NAME}' -e "UPDATE wp2_options SET option_value='${TARGET_URL}' WHERE option_name='siteurl';"
mysql --defaults-file=/tmp/kric_my.cnf '${TARGET_DB_NAME}' -e "UPDATE wp2_options SET option_value='${TARGET_URL}' WHERE option_name='home';"
echo '  更新後の値:'
mysql --defaults-file=/tmp/kric_my.cnf '${TARGET_DB_NAME}' -e "SELECT option_name, option_value FROM wp2_options WHERE option_name='siteurl' OR option_name='home';"
rm -f /tmp/kric_my.cnf
WP_URL_EOF

    # E2E テストユーザーは本番のみ削除（テスト環境では Playwright テストに必要なため残す）
    if [ "$ENV_CHOICE" = "2" ]; then
        step "E2E テストユーザー削除（本番のみ）..."
        ssh $SSH_O "$TARGET_SSH" bash << DEL_USER_EOF
printf '[client]\nhost=%s\nuser=%s\npassword=%s\n' '${TARGET_DB_HOST}' '${TARGET_DB_USER}' '${TARGET_DB_PASS}' > /tmp/kric_my.cnf
chmod 600 /tmp/kric_my.cnf
mysql --defaults-file=/tmp/kric_my.cnf '${TARGET_DB_NAME}' -e "DELETE FROM member WHERE login_id='9a6c4e38b5';"
rm -f /tmp/kric_my.cnf
echo '  ✓ E2E テストユーザー削除完了'
DEL_USER_EOF
        ok "E2E テストユーザー削除完了"
    fi
fi

# ==============================================================================
# 完了
# ==============================================================================
printf '\n\033[1;32m=== デプロイ完了（%s）===\033[0m\n' "$TARGET_LABEL"
printf '  %-20s %s\n' "サイト:"        "${TARGET_URL}/"
printf '  %-20s %s\n' "IIS ログイン:"  "${TARGET_URL}/iis/login"
printf '  %-20s %s\n' "WP 管理画面:"   "${TARGET_URL}/wp-admin/"
printf '\n'
