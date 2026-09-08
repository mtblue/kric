<?php
/*
 * Template Name: お問い合わせ
 */

$errors = [];
$f = ['subject'=>'', 'name'=>'', 'furigana'=>'', 'email'=>'', 'email_confirm'=>'', 'tel'=>'', 'message'=>''];

if (!empty($_GET['t'])) {
    $token  = sanitize_key($_GET['t']);
    $stored = get_transient('contact_err_' . $token);
    if ($stored) {
        $errors = $stored['errors'];
        $f      = $stored['input'];
        delete_transient('contact_err_' . $token);
    }
}

$err_subject       = false;
$err_name          = false;
$err_furigana      = false;
$err_email         = false;
$err_email_confirm = false;
$err_tel           = false;
$err_message       = false;
if (!empty($errors)) {
    $err_subject       = $f['subject']       === '';
    $err_name          = $f['name']          === '';
    $err_furigana      = $f['furigana']      === '';
    $err_email         = $f['email']         === '' || !is_email($f['email']);
    $err_email_confirm = $f['email_confirm'] === '' || $f['email'] !== $f['email_confirm'];
    $err_tel           = $f['tel']           === '';
    $err_message       = $f['message']       === '';
}
?>
<?php get_header(); ?>

  <!-- ========== ページKV ========== -->
  <section class="page-kv--bg">
    <div class="page-kv-placeholder" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/ct_bg.png')"></div>
    <div class="page-kv-overlay">
      <h1 class="page-kv-title">お問い合わせ</h1>
    </div>
  </section>

  <!-- パンくず -->
  <div class="page-breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">関西不動産情報センター TOP</a>
    <span class="page-breadcrumb-sep">&gt;</span>
    お問い合わせ
  </div>

  <!-- ========== お問い合わせ本文 ========== -->
  <section class="toiawase-sec">
    <div class="toiawase-inner">

      <p class="toiawase-intro">KRICについての質問や相談はお気軽にお問い合わせください。</p>

      <!-- 電話の場合 -->
      <h2 class="toiawase-sec-heading">電話の場合</h2>
      <div class="toiawase-phone-box">
        <div class="toiawase-phone-left">
          <div class="toiawase-phone-logo">
            <img src="<?php echo get_template_directory_uri(); ?>/images/ct_logo.png" alt="KRIC" class="toiawase-phone-logo-kric">
            <span class="toiawase-phone-org">関西不動産情報センター</span>
          </div>
          <p class="toiawase-phone-address">〒530-0012 大阪市北区芝田2丁目1番18号 西阪急ビル7階</p>
        </div>
        <div class="toiawase-phone-right">
          <p class="toiawase-phone-tel">
            <span class="toiawase-phone-tel-label">TEL.</span>06-6292-7791
          </p>
          <p class="toiawase-phone-hours">・受付時間／10:00〜16:00（土・日・祝除く）</p>
        </div>
      </div>

      <!-- お問い合わせフォーム -->
      <h2 class="toiawase-sec-heading toiawase-sec-heading--border">お問い合わせフォーム</h2>
      <p class="form-desc">
        下記にお問合せ内容をご入力の上、「確認画面」ボタンを押してください。<br>
        お問合せ完了後、確認メールが届かない場合は、念のため迷惑メールフォルダをご確認ください。<br>
        ※マークのついた項目は必須項目となっております。ご入力漏れのないようお願い致します。<br>
        お問合せいただいた個人情報の取扱いに関しては<a href="<?php echo esc_url(home_url('/privacy.html')); ?>">「プライバシーポリシー」</a>のページをご確認ください。
      </p>

      <?php if (!empty($errors)): ?>
      <div class="form-error-box">
        <?php foreach ($errors as $err): ?>
        <p><?php echo esc_html($err); ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="contact_form_submit">
        <?php wp_nonce_field('contact_form', 'contact_form_nonce'); ?>
        <table class="form-table">
          <tbody>
            <tr>
              <td class="form-label-cell">お問合せ件名<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <input type="text" class="form-input<?php echo $err_subject ? ' is-error' : ''; ?>" name="subject" value="<?php echo esc_attr($f['subject']); ?>" placeholder="">
              </td>
            </tr>
            <tr>
              <td class="form-label-cell">お名前<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <input type="text" class="form-input<?php echo $err_name ? ' is-error' : ''; ?>" name="name" value="<?php echo esc_attr($f['name']); ?>" placeholder="例：山田　太郎">
              </td>
            </tr>
            <tr>
              <td class="form-label-cell">フリガナ<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <input type="text" class="form-input<?php echo $err_furigana ? ' is-error' : ''; ?>" name="furigana" value="<?php echo esc_attr($f['furigana']); ?>" placeholder="例：ヤマダ　タロウ">
              </td>
            </tr>
            <tr>
              <td class="form-label-cell">E-mail<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <input type="text" class="form-input<?php echo $err_email ? ' is-error' : ''; ?>" name="email" value="<?php echo esc_attr($f['email']); ?>" placeholder="例：info@example.co.jp">
              </td>
            </tr>
            <tr>
              <td class="form-label-cell">E-mail（確認用）<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <input type="text" class="form-input<?php echo $err_email_confirm ? ' is-error' : ''; ?>" name="email_confirm" value="<?php echo esc_attr($f['email_confirm']); ?>" placeholder="例：info@example.co.jp">
              </td>
            </tr>
            <tr>
              <td class="form-label-cell">電話番号<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <input type="tel" class="form-input<?php echo $err_tel ? ' is-error' : ''; ?>" name="tel" value="<?php echo esc_attr($f['tel']); ?>" placeholder="例：06-0000-0000">
              </td>
            </tr>
            <tr>
              <td class="form-label-cell">お問合せ内容<span class="form-required">※</span></td>
              <td class="form-input-cell">
                <textarea class="form-textarea<?php echo $err_message ? ' is-error' : ''; ?>" name="message" placeholder="ご質問・ご要望をご記入ください"><?php echo esc_textarea($f['message']); ?></textarea>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="form-btn-group">
          <button type="submit" class="btn-form-submit">
            <span class="btn-text">確認</span>
            <span class="btn-sep">|</span>
            <span class="btn-arrow">&#x25B6;&#xFE0E;</span>
          </button>
        </div>
      </form>

    </div>
  </section>

  <!-- ========== お問い合わせ CTA ========== -->
  <section class="contact-cta" id="contact">
    <div class="contact-cta-photo">
      <img src="<?php echo get_template_directory_uri(); ?>/images/ft_contact.png" alt="お問い合わせ">
      <div class="contact-cta-photo-text">
        <div class="contact-photo-inner">
          <p class="contact-photo-label">JOIN</p>
          <p class="contact-photo-title">入会を<br>お考えの方は<br>こちら</p>
        </div>
        <a href="<?php echo esc_url(home_url('/nyukai.html')); ?>" class="contact-photo-link">入会案内ページへ <span class="contact-photo-arrow">›</span></a>
      </div>
    </div>
    <div class="contact-cta-inner">
      <p class="contact-label">CONTACT</p>
      <h2 class="contact-title">お問い合わせ</h2>
      <p class="contact-subtitle">KRICについての質問や相談は<br class="contact-subtitle-br">お気軽にお問い合わせください。</p>
      <hr class="contact-divider">
      <div class="contact-cols">
        <div class="contact-col">
          <p class="contact-col-label">[電話の場合]</p>
          <p class="contact-col-note">・受付時間／10:00〜16:00</p>
          <p class="contact-tel"><span class="contact-tel-label">TEL.</span> <a href="tel:0662927791">06-6292-7791</a></p>
        </div>
        <div class="contact-col">
          <p class="contact-col-label">[Webの場合]</p>
          <p class="contact-col-note">・24時間受付中</p>
          <a href="<?php echo esc_url(home_url('/contact.html')); ?>" class="btn-contact-form"><span class="btn-text">お問合せフォーム</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
        </div>
      </div>
    </div>
  </section>

<?php get_footer(); ?>
