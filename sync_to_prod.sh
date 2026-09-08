#!/bin/bash
# =============================================================================
# sync_to_prod.sh
# 本番環境（kric.co.jp）へソースコードを rsync する
#
# 目的: コード改修を素早く本番に反映する（DB・メディアは対象外）
# 実行環境: WSL
# 使い方  : bash sync_to_prod.sh
#
# 対象:
#   www/kric.co.jp/www/  → ~/www/kric.co.jp/www/
#   www/kric.co.jp/data/ → ~/www/kric.co.jp/data/
#
# 除外（本番を保持）:
#   wp-includes/          : WordPress コア（初回デプロイ時に取得済み）
#   wp-content/uploads/   : ユーザーがアップロードしたメディアファイル
#   wp-content/cache/     : WP キャッシュ
#   wp-content/upgrade/   : WP アップグレード一時ファイル
#   data/app/tmp/         : キャッシュ・セッション・ログ・会員アップロード
#
# 除外（Docker 専用。本番に送らない）:
#   iis/.htaccess         : ローカルは HTTPS リダイレクト OFF → 同期後に本番で ON に修正
#   admin/.htaccess       : 同上
# =============================================================================

set -euo pipefail

PROD_SSH="kric2021@kric.co.jp"
LOCAL_KRIC_DIR="/mnt/c/Users/web0012osk/Documents/kric"

# ヘルパー
ok()   { printf '  \033[32m✓\033[0m %s\n' "$1"; }
step() { printf '\n  → %s\n' "$1"; }
err()  { printf '\n\033[31m✗ ERROR: %s\033[0m\n' "$1" >&2; exit 1; }

printf '\n\033[1m=== KRIC 本番 rsync ===\033[0m\n'
printf '  対象: %s\n\n' "$PROD_SSH"

# 確認プロンプト
printf '\033[31m  ⚠ 本番環境（kric.co.jp）へ同期します。続行しますか？ [yes/N]: \033[0m'
read -r CONFIRM
[[ "$CONFIRM" == "yes" ]] || { printf '\n  キャンセルしました。\n\n'; exit 0; }

# ==============================================================================
# SSH ControlMaster（スクリプト全体で接続を使い回す）
# ==============================================================================
SSH_CTL="/tmp/kric_sync_ctl_$$"
SSH_O="-o ControlMaster=auto -o ControlPath=$SSH_CTL -o ControlPersist=yes -o ServerAliveInterval=30 -o ServerAliveCountMax=6"

step "SSH 接続確立中..."
ssh $SSH_O -o ConnectTimeout=15 -o BatchMode=yes "$PROD_SSH" 'echo ok' > /dev/null 2>&1 \
    || err "SSH 接続失敗。鍵が登録されているか確認してください: ssh-copy-id $PROD_SSH"
ok "SSH 接続 OK（ControlMaster 確立）"

trap '
    printf "\n  → SSH 接続を閉じています...\n"
    ssh -O exit -o ControlPath="$SSH_CTL" "$PROD_SSH" 2>/dev/null || true
    rm -f "$SSH_CTL"
' EXIT

# ==============================================================================
# www/ を rsync
# ==============================================================================
step "www/ を rsync 中..."
rsync -azv --progress --stats \
    -e "ssh $SSH_O" \
    --delete \
    --exclude='/wp-includes/' \
    --exclude='/wp-content/uploads/' \
    --exclude='/wp-content/cache/' \
    --exclude='/wp-content/upgrade/' \
    "$LOCAL_KRIC_DIR/www/kric.co.jp/www/" \
    "$PROD_SSH:~/www/kric.co.jp/www/"
ok "www/ rsync 完了"

# ==============================================================================
# data/ を rsync
# ==============================================================================
step "data/ を rsync 中..."
rsync -azv --progress --stats \
    -e "ssh $SSH_O" \
    --delete \
    --exclude='/app/tmp/' \
    "$LOCAL_KRIC_DIR/www/kric.co.jp/data/" \
    "$PROD_SSH:~/www/kric.co.jp/data/"
ok "data/ rsync 完了"

# ==============================================================================
# .htaccess の HTTPS リダイレクト有効化
# （ローカルは Docker 用に OFF、本番では ON にする）
# ==============================================================================
step ".htaccess HTTPS リダイレクト有効化..."
ssh $SSH_O "$PROD_SSH" bash << 'HTACCESS_EOF'
sed -i '' 's/^ # Rewrite/ Rewrite/g' ~/www/kric.co.jp/www/iis/.htaccess
sed -i '' 's/^ # Rewrite/ Rewrite/g' ~/www/kric.co.jp/www/admin/.htaccess
HTACCESS_EOF
ok ".htaccess 修正完了"

# ==============================================================================
# CakePHP キャッシュクリア
# ==============================================================================
step "CakePHP キャッシュクリア..."
ssh $SSH_O "$PROD_SSH" bash << 'CACHE_EOF'
rm -f ~/www/kric.co.jp/data/app/tmp/cache/models/*     2>/dev/null || true
rm -f ~/www/kric.co.jp/data/app/tmp/cache/persistent/* 2>/dev/null || true
rm -f ~/www/kric.co.jp/data/app/tmp/cache/views/*      2>/dev/null || true
echo "  ✓ キャッシュクリア完了"
CACHE_EOF

# ==============================================================================
# 完了
# ==============================================================================
printf '\n\033[1;32m  ✓ 同期完了\033[0m\n\n'
