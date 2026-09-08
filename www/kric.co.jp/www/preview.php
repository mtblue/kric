<?php
/**
 * メンテナンス期間中のシークレットURLバイパス
 *
 * アクセス方法: https://kric.co.jp/preview.php?token=KRIC2026LAUNCH
 * → クッキー kric_preview=1 をセットしてトップへリダイレクト（7日間有効）
 *
 * 公開後は本ファイルを削除すること
 */
if (isset($_GET['token']) && $_GET['token'] === 'KRIC2026LAUNCH') {
    setcookie('kric_preview', '1', time() + 86400 * 7, '/', '', true, true);
    header('Location: /');
    exit;
}
http_response_code(403);
echo '403 Forbidden';
