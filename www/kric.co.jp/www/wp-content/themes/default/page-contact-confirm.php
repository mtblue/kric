<?php
/*
 * Template Name: お問い合わせ確認
 */

$token = sanitize_key($_GET['t'] ?? '');
$f     = $token ? get_transient('contact_data_' . $token) : null;

if (!$f) {
    wp_redirect(home_url('/contact.html'));
    exit;
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
    <a href="<?php echo esc_url(home_url('/contact.html')); ?>">お問い合わせ</a>
    <span class="page-breadcrumb-sep">&gt;</span>
    確認
  </div>

  <!-- ========== 確認画面 ========== -->
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

      <!-- 確認テーブル -->
      <h2 class="toiawase-sec-heading toiawase-sec-heading--border">お問い合わせフォーム</h2>
      <p class="form-desc">
        以下の内容でよろしければ「送信」ボタンを押してください。<br>
        内容を修正する場合は「戻る」ボタンを押してください。
      </p>

      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="contact_form_send">
        <input type="hidden" name="token"  value="<?php echo esc_attr($token); ?>">
        <?php wp_nonce_field('contact_form_send', 'contact_send_nonce'); ?>

        <table class="form-table form-confirm-table">
          <tbody>
            <tr>
              <td class="form-label-cell">お問合せ件名</td>
              <td class="form-confirm-cell"><?php echo esc_html($f['subject']); ?></td>
            </tr>
            <tr>
              <td class="form-label-cell">お名前</td>
              <td class="form-confirm-cell"><?php echo esc_html($f['name']); ?></td>
            </tr>
            <tr>
              <td class="form-label-cell">フリガナ</td>
              <td class="form-confirm-cell"><?php echo esc_html($f['furigana']); ?></td>
            </tr>
            <tr>
              <td class="form-label-cell">E-mail</td>
              <td class="form-confirm-cell"><?php echo esc_html($f['email']); ?></td>
            </tr>
            <tr>
              <td class="form-label-cell">E-mail（確認用）</td>
              <td class="form-confirm-cell"><?php echo esc_html($f['email_confirm']); ?></td>
            </tr>
            <tr>
              <td class="form-label-cell">電話番号</td>
              <td class="form-confirm-cell"><?php echo esc_html($f['tel']); ?></td>
            </tr>
            <tr>
              <td class="form-label-cell">お問合せ内容</td>
              <td class="form-confirm-cell"><?php echo nl2br(esc_html($f['message'])); ?></td>
            </tr>
          </tbody>
        </table>

        <div class="form-btn-group">
          <button type="submit" class="btn-form-submit">
            <span class="btn-text">送信</span>
            <span class="btn-sep">|</span>
            <span class="btn-arrow">&#x25B6;&#xFE0E;</span>
          </button>
          <a href="<?php echo esc_url(home_url('/contact.html')); ?>" class="btn-form-back">
            <span class="btn-text">戻る</span>
            <span class="btn-sep">|</span>
            <span class="btn-arrow">&#x25B6;&#xFE0E;</span>
          </a>
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
