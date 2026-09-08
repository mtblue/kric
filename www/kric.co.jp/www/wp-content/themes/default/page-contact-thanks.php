<?php
/*
 * Template Name: お問い合わせ完了
 */
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

  <!-- ========== 送信完了 ========== -->
  <section class="thanks-sec">
    <div class="thanks-inner">
      <h2 class="thanks-heading">お問い合わせを承りました。</h2>
      <hr class="thanks-divider">
      <div class="thanks-body">
        <p>お問い合わせいただき、ありがとうございました。<br>
        確認のため、自動返信メールをお送りしております。</p>
      </div>
      <div class="thanks-back-wrap">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="thanks-back">TOPへ戻る &gt;</a>
      </div>
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
