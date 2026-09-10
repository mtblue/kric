<?php
/**
 * メンテナンス期間中のシークレットURLバイパス（次回メンテナンス時に再利用する）
 *
 * ★使用前に必ず PREVIEW_TOKEN を推測されない値に変更すること。
 *   リポジトリに実際のトークンを残すと、履歴から誰でもバイパスできてしまう。
 *
 * アクセス方法: https://kric.co.jp/preview.php?token=<設定したトークン>
 * → クッキー kric_preview=1 をセットしてトップへリダイレクト（7日間有効）
 *
 * 公開後は本ファイルを公開領域から削除し、_retired/ に戻すこと
 */
define('PREVIEW_TOKEN', 'CHANGE_ME_BEFORE_USE');
if (isset($_GET['token']) && PREVIEW_TOKEN !== 'CHANGE_ME_BEFORE_USE' && $_GET['token'] === PREVIEW_TOKEN) {
    setcookie('kric_preview', '1', time() + 86400 * 7, '/', '', true, true);
    header('Location: /');
    exit;
}
http_response_code(403);
echo '403 Forbidden';
