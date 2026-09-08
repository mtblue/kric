<?php
add_action('init', 'kric_session_start', 1);
function kric_session_start() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}

add_action('wp_enqueue_scripts', 'kric_renewal_enqueue');
function kric_renewal_enqueue() {
    wp_enqueue_style(
        'default-css',
        get_template_directory_uri() . '/css/style.css',
        [],
        filemtime(get_template_directory() . '/css/style.css')
    );
    wp_enqueue_script(
        'default-js',
        get_template_directory_uri() . '/js/main.js',
        [],
        filemtime(get_template_directory() . '/js/main.js'),
        true
    );
}

add_action('wp_head', 'kric_preload_assets', 1);
function kric_preload_assets() {
    if (!is_front_page()) return;
    $url = get_template_directory_uri() . '/images/back.png';
    echo '<link rel="preload" href="' . esc_url($url) . '" as="image">' . "\n";
}

add_action('after_setup_theme', 'kric_renewal_setup');
function kric_renewal_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
}

add_action('init', 'create_post_type');
function create_post_type() {
    register_post_type('kric-media', [
        'label'        => 'KRICメディア',
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'menu_position' => 5,
        'supports'     => ['title', 'editor', 'thumbnail', 'revisions'],
    ]);

    register_taxonomy('kric-media', 'kric-media', [
        'label'        => 'KRICメディア',
        'hierarchical' => true,
        'public'       => true,
        'show_in_rest' => true,
    ]);
}

/* =============================================
   入会専用フォーム処理 (admin-post.php 経由)
   ============================================= */

// STEP1: 入力 → バリデーション → 確認ページへ
add_action('admin_post_nopriv_nyukai_form_submit', 'kric_handle_nyukai_form_submit');
add_action('admin_post_nyukai_form_submit',        'kric_handle_nyukai_form_submit');
function kric_handle_nyukai_form_submit() {
    if (!isset($_POST['nyukai_form_nonce']) || !wp_verify_nonce($_POST['nyukai_form_nonce'], 'nyukai_form')) {
        wp_redirect(home_url('/nyukai-form.html'));
        exit;
    }

    $f = [
        'subject'       => sanitize_text_field($_POST['subject']       ?? ''),
        'name'          => sanitize_text_field($_POST['name']          ?? ''),
        'furigana'      => sanitize_text_field($_POST['furigana']      ?? ''),
        'email'         => sanitize_email(    $_POST['email']          ?? ''),
        'email_confirm' => sanitize_email(    $_POST['email_confirm']  ?? ''),
        'tel'           => sanitize_text_field($_POST['tel']           ?? ''),
        'message'       => sanitize_textarea_field($_POST['message']   ?? ''),
    ];

    $errors = [];
    if ($f['subject'] === '') $errors[] = 'お問合せ件名を入力してください。';
    if ($f['name']    === '') $errors[] = 'お名前を入力してください。';
    if ($f['email']   === '') {
        $errors[] = 'E-mailを入力してください。';
    } elseif (!is_email($f['email'])) {
        $errors[] = 'E-mailの形式が正しくありません。';
    }
    if ($f['email_confirm'] === '') {
        $errors[] = 'E-mail（確認用）を入力してください。';
    } elseif ($f['email'] !== '' && $f['email'] !== $f['email_confirm']) {
        $errors[] = 'E-mailとE-mail（確認用）が一致しません。';
    }

    $token = bin2hex(random_bytes(16));

    if (!empty($errors)) {
        set_transient('nyukai_err_' . $token, ['errors' => $errors, 'input' => $f], 300);
        wp_redirect(home_url('/nyukai-form.html?t=' . $token));
        exit;
    }

    set_transient('nyukai_data_' . $token, $f, 300);
    wp_redirect(home_url('/nyukai-form-confirm.html?t=' . $token));
    exit;
}

// STEP2: 確認 → メール送信 → 完了ページへ
add_action('admin_post_nopriv_nyukai_form_send', 'kric_handle_nyukai_form_send');
add_action('admin_post_nyukai_form_send',        'kric_handle_nyukai_form_send');
function kric_handle_nyukai_form_send() {
    if (!isset($_POST['nyukai_send_nonce']) || !wp_verify_nonce($_POST['nyukai_send_nonce'], 'nyukai_form_send')) {
        wp_redirect(home_url('/nyukai-form.html'));
        exit;
    }

    $token = sanitize_key($_POST['token'] ?? '');
    $f     = $token ? get_transient('nyukai_data_' . $token) : null;
    if (!$f) {
        wp_redirect(home_url('/nyukai-form.html'));
        exit;
    }

    // メール送信
    $to      = get_option('admin_email');
    $subject = '【KRIC入会専用フォーム】' . $f['subject'];
    $body    = "件名：{$f['subject']}\n"
             . "お名前：{$f['name']}（{$f['furigana']}）\n"
             . "E-mail：{$f['email']}\n"
             . "電話番号：{$f['tel']}\n"
             . "お問合せ内容：\n{$f['message']}\n";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    if (is_email($f['email'])) {
        $headers[] = 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>';
    }
    wp_mail($to, $subject, $body, $headers);

    // 自動返信
    if (is_email($f['email'])) {
        $auto_subject = '【KRIC】お問合せを受け付けました';
        $auto_body    = $f['name'] . " 様\n\n"
                      . "この度はお問合せいただきありがとうございます。\n"
                      . "以下の内容でお問合せを受け付けました。\n\n"
                      . "件名：{$f['subject']}\n"
                      . "お問合せ内容：\n{$f['message']}\n\n"
                      . "担当者より折り返しご連絡させていただきます。\n\n"
                      . "関西不動産情報センター\n"
                      . "〒530-0012 大阪市北区芝田2丁目1番18号 西阪急ビル7階\n"
                      . "TEL：06-6292-7791\n";
        wp_mail($f['email'], $auto_subject, $auto_body, ['Content-Type: text/plain; charset=UTF-8']);
    }

    delete_transient('nyukai_data_' . $token);
    wp_redirect(home_url('/nyukai-form-thanks.html'));
    exit;
}

/* =============================================
   お問い合わせフォーム処理 (admin-post.php 経由)
   ============================================= */

// STEP1: 入力 → バリデーション → 確認ページへ
add_action('admin_post_nopriv_contact_form_submit', 'kric_handle_contact_form_submit');
add_action('admin_post_contact_form_submit',        'kric_handle_contact_form_submit');
function kric_handle_contact_form_submit() {
    if (!isset($_POST['contact_form_nonce']) || !wp_verify_nonce($_POST['contact_form_nonce'], 'contact_form')) {
        wp_redirect(home_url('/contact.html'));
        exit;
    }

    $f = [
        'subject'       => sanitize_text_field($_POST['subject']       ?? ''),
        'name'          => sanitize_text_field($_POST['name']          ?? ''),
        'furigana'      => sanitize_text_field($_POST['furigana']      ?? ''),
        'email'         => sanitize_email(    $_POST['email']          ?? ''),
        'email_confirm' => sanitize_email(    $_POST['email_confirm']  ?? ''),
        'tel'           => sanitize_text_field($_POST['tel']           ?? ''),
        'message'       => sanitize_textarea_field($_POST['message']   ?? ''),
    ];

    $errors = [];
    if ($f['subject']  === '') $errors[] = 'お問合せ件名を入力してください。';
    if ($f['name']     === '') $errors[] = 'お名前を入力してください。';
    if ($f['furigana'] === '') $errors[] = 'フリガナを入力してください。';
    if ($f['email'] === '') {
        $errors[] = 'E-mailを入力してください。';
    } elseif (!is_email($f['email'])) {
        $errors[] = 'E-mailの形式が正しくありません。';
    }
    if ($f['email_confirm'] === '') {
        $errors[] = 'E-mail（確認用）を入力してください。';
    } elseif ($f['email'] !== '' && $f['email'] !== $f['email_confirm']) {
        $errors[] = 'E-mailとE-mail（確認用）が一致しません。';
    }
    if ($f['tel']     === '') $errors[] = '電話番号を入力してください。';
    if ($f['message'] === '') $errors[] = 'お問合せ内容を入力してください。';

    $token = bin2hex(random_bytes(16));

    if (!empty($errors)) {
        set_transient('contact_err_' . $token, ['errors' => $errors, 'input' => $f], 300);
        wp_redirect(home_url('/contact.html?t=' . $token));
        exit;
    }

    set_transient('contact_data_' . $token, $f, 300);
    wp_redirect(home_url('/contact-confirm.html?t=' . $token));
    exit;
}

// STEP2: 確認 → メール送信 → 完了ページへ
add_action('admin_post_nopriv_contact_form_send', 'kric_handle_contact_form_send');
add_action('admin_post_contact_form_send',        'kric_handle_contact_form_send');
function kric_handle_contact_form_send() {
    if (!isset($_POST['contact_send_nonce']) || !wp_verify_nonce($_POST['contact_send_nonce'], 'contact_form_send')) {
        wp_redirect(home_url('/contact.html'));
        exit;
    }

    $token = sanitize_key($_POST['token'] ?? '');
    $f     = $token ? get_transient('contact_data_' . $token) : null;
    if (!$f) {
        wp_redirect(home_url('/contact.html'));
        exit;
    }

    // 運営者へのメール
    $to      = get_option('admin_email');
    $subject = '【KRICお問合せ】' . $f['subject'];
    $body    = "件名：{$f['subject']}\n"
             . "お名前：{$f['name']}（{$f['furigana']}）\n"
             . "E-mail：{$f['email']}\n"
             . "電話番号：{$f['tel']}\n"
             . "お問合せ内容：\n{$f['message']}\n";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    if (is_email($f['email'])) {
        $headers[] = 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>';
    }
    wp_mail($to, $subject, $body, $headers);

    // 自動返信
    if (is_email($f['email'])) {
        $auto_subject = '【KRIC】お問合せを受け付けました';
        $auto_body    = $f['name'] . " 様\n\n"
                      . "この度はお問合せいただきありがとうございます。\n"
                      . "以下の内容でお問合せを受け付けました。\n\n"
                      . "件名：{$f['subject']}\n"
                      . "お問合せ内容：\n{$f['message']}\n\n"
                      . "担当者より折り返しご連絡させていただきます。\n\n"
                      . "関西不動産情報センター\n"
                      . "〒530-0012 大阪市北区芝田2丁目1番18号 西阪急ビル7階\n"
                      . "TEL：06-6292-7791\n";
        wp_mail($f['email'], $auto_subject, $auto_body, ['Content-Type: text/plain; charset=UTF-8']);
    }

    delete_transient('contact_data_' . $token);
    wp_redirect(home_url('/contact-thanks.html'));
    exit;
}

/**
 * KRICメディア: ACFフィールドからサムネイル URL を取得
 */
function kric_get_media_image_url($post_id, $youtube_url) {
    if ($youtube_url) {
        preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]+)/',
            $youtube_url,
            $m
        );
        if (!empty($m[1])) {
            return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
        }
    }

    if (function_exists('get_field')) {
        $img = get_field('mediaimg', $post_id);
        if (is_array($img) && isset($img['url'])) {
            return $img['url'];
        }
        if (is_string($img) && $img) {
            return $img;
        }
        if (is_numeric($img) && $img) {
            return wp_get_attachment_image_url($img, 'medium') ?: '';
        }
    }

    $thumb = get_the_post_thumbnail_url($post_id, 'medium');
    return $thumb ?: '';
}

/**
 * NEWSカテゴリスラッグ → CSSクラス
 */
function kric_news_cat_class($slug) {
    $map = [
        'info'    => 'news-cat--info',
        'event'   => 'news-cat--event',
        'media'   => 'news-cat--media',
        'study'   => 'news-cat--study',
        'tell'    => 'news-cat--info',
    ];
    return $map[$slug] ?? 'news-cat--info';
}
