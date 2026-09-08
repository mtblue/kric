<?php
/**
 * プライバシーポリシーページ自動生成スクリプト
 * 使用後は削除すること
 *
 * アクセス: http://localhost:8080/wp-content/themes/kric-renewal/setup-privacy-page.php
 */

define('ABSPATH', dirname(__FILE__) . '/../../../../');
require_once(ABSPATH . 'wp-load.php');

// 既存チェック
$existing = get_page_by_path('privacy');
if ($existing) {
    echo "既存ページ発見 ID=" . $existing->ID . " status=" . $existing->post_status . "<br>";
    // テンプレートだけ更新
    update_post_meta($existing->ID, '_wp_page_template', 'page-privacy.php');
    echo "テンプレートを page-privacy.php に設定しました。";
    die();
}

$page_id = wp_insert_post([
    'post_title'   => 'プライバシーポリシー',
    'post_name'    => 'privacy',
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_author'  => 1,
]);

if (is_wp_error($page_id)) {
    echo "エラー: " . $page_id->get_error_message();
    die();
}

update_post_meta($page_id, '_wp_page_template', 'page-privacy.php');
echo "ページ作成完了 ID=$page_id<br>";
echo '<a href="/privacy/">プライバシーポリシーページを開く</a>';
