#!/bin/bash
# =============================================================================
# sync_to_prod.sh
# 本番環境（kric.co.jp）へソースコードを rsync する
#
# 目的: コード改修を素早く本番に反映する（DB・メディアは対象外）
# 実行環境: WSL
# 使い方  : bash sync_to_prod.sh [--dry-run|-n]
#            --dry-run / -n : 転送内容の確認のみ（実際の変更なし）
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
#
# ★パーミッションは絶対に転送しないこと（--no-perms / --no-group / --no-owner）
#   同期元は WSL 上の Windows ドライブ（drvfs）で、全ファイルが 777 に見える。
#   rsync -a は -p（パーミッション保持）を含むため、これを付けないと本番ツリー
#   全体が 777 になる。さくらは PHP を CGI（suexec）で実行しており、suexec は
#   group/other に書き込み権のあるスクリプト・ディレクトリの実行を拒否するため、
#   全ページが 500 になる（静的ファイルは suexec を通らないので 200 のまま）。
#   内容が同一のファイルにもパーミッションだけは適用されるので、転送件数が
#   数件でもツリー全体が破壊される。2026-09-08 にこれで本番が全落ちした。
# =============================================================================

set -euo pipefail

PROD_SSH="kric2021@kric.co.jp"
LOCAL_KRIC_DIR="/mnt/c/Users/web0012osk/Documents/kric"

# ドライランモード判定（--dry-run または -n を引数に渡すと確認なしで実行）
DRY_RUN=false
for arg in "$@"; do
  [[ "$arg" == "--dry-run" || "$arg" == "-n" ]] && DRY_RUN=true
done
RSYNC_DRY=""
$DRY_RUN && RSYNC_DRY="--dry-run"

# パーミッション・所有者は転送しない（本番側の値を維持する）
# 詳細は冒頭のコメント参照。これを外すと本番が 500 で全落ちする。
RSYNC_PERM="--no-perms --no-group --no-owner"

# ヘルパー
ok()   { printf '  \033[32m✓\033[0m %s\n' "$1"; }
step() { printf '\n  → %s\n' "$1"; }
err()  { printf '\n\033[31m✗ ERROR: %s\033[0m\n' "$1" >&2; exit 1; }

printf '\n\033[1m=== KRIC 本番 rsync ===\033[0m\n'
printf '  対象: %s\n' "$PROD_SSH"
if $DRY_RUN; then
  printf '  \033[33m【DRY-RUN モード】実際の転送は行いません\033[0m\n\n'
else
  printf '\n'
fi

# 確認プロンプト（ドライランは確認不要）
if ! $DRY_RUN; then
  printf '\033[31m  ⚠ 本番環境（kric.co.jp）へ同期します。続行しますか？ [yes/N]: \033[0m'
  read -r CONFIRM
  [[ "$CONFIRM" == "yes" ]] || { printf '\n  キャンセルしました。\n\n'; exit 0; }
fi

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
rsync -azv --progress --stats $RSYNC_PERM $RSYNC_DRY \
    -e "ssh $SSH_O" \
    --delete \
    --exclude='/wp-includes/' \
    --exclude='/wordpress/' \
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
rsync -azv --progress --stats $RSYNC_PERM $RSYNC_DRY \
    -e "ssh $SSH_O" \
    --delete \
    --exclude='/app/tmp/' \
    "$LOCAL_KRIC_DIR/www/kric.co.jp/data/" \
    "$PROD_SSH:~/www/kric.co.jp/data/"
ok "data/ rsync 完了"

# ==============================================================================
# .htaccess の HTTPS リダイレクト有効化 / CakePHP キャッシュクリア
# （ドライランモードではスキップ）
# ==============================================================================
if $DRY_RUN; then
  printf '\n  \033[33m[DRY-RUN] .htaccess 修正・キャッシュクリアはスキップします\033[0m\n'
else
  step ".htaccess HTTPS リダイレクト有効化..."
  ssh $SSH_O "$PROD_SSH" bash << 'HTACCESS_EOF'
sed -i '' 's/^ # Rewrite/ Rewrite/g' ~/www/kric.co.jp/www/iis/.htaccess
sed -i '' 's/^ # Rewrite/ Rewrite/g' ~/www/kric.co.jp/www/admin/.htaccess
HTACCESS_EOF
  ok ".htaccess 修正完了"

  step "CakePHP キャッシュクリア..."
  ssh $SSH_O "$PROD_SSH" bash << 'CACHE_EOF'
rm -f ~/www/kric.co.jp/data/app/tmp/cache/models/*     2>/dev/null || true
rm -f ~/www/kric.co.jp/data/app/tmp/cache/persistent/* 2>/dev/null || true
rm -f ~/www/kric.co.jp/data/app/tmp/cache/views/*      2>/dev/null || true
echo "  ✓ キャッシュクリア完了"
CACHE_EOF
fi

# ==============================================================================
# 完了
# ==============================================================================
if $DRY_RUN; then
  printf '\n\033[1;33m  ✓ DRY-RUN 完了（実際の転送は行われていません）\033[0m\n\n'
  printf '  実際に同期するには: bash sync_to_prod.sh\n\n'
else
  printf '\n\033[1;32m  ✓ 同期完了\033[0m\n\n'
fi
