<?php get_header(); ?>

  <!-- ========== ページKV ========== -->
  <section class="page-kv">
    <div class="page-kv-photos">
      <div class="page-kv-photo"><img src="<?php echo get_template_directory_uri(); ?>/images/ab_img1.png" alt=""></div>
      <div class="page-kv-photo"><img src="<?php echo get_template_directory_uri(); ?>/images/ab_img2.png" alt=""></div>
      <div class="page-kv-photo"><img src="<?php echo get_template_directory_uri(); ?>/images/ab_img3.png" alt=""></div>
    </div>
    <div class="page-kv-title">
      <p class="page-kv-label">KRICについて</p>
    </div>
  </section>

  <!-- ========== KRIC PRIDE OF MEMBERSHIP ========== -->
  <section class="about-pride-sec">
    <div class="about-pride-bg" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/ab_bg.png')"></div>
    <nav class="page-breadcrumb" aria-label="パンくずリスト">
      <ol class="page-breadcrumb-list">
        <li><a href="<?php echo esc_url(home_url('/')); ?>">関西不動産情報センター TOP</a></li>
        <li>KRICについて</li>
      </ol>
    </nav>
    <div class="about-pride-sec-inner">
      <div class="about-pride-graphic">
        <picture>
          <source srcset="<?php echo get_template_directory_uri(); ?>/images/ab_logo_sp.png" media="(max-width: 768px)">
          <img src="<?php echo get_template_directory_uri(); ?>/images/ab_logo.png" alt="KRIC PRIDE of MEMBERSHIP">
        </picture>
      </div>
      <p class="about-pride-text">
        1969年設立以来、関西の不動産流通業界の合理化・近代化を図り、不動産取引の信頼性と安全性を確保する諸制度を実施し、世益に資することを目的に設立された団体です。関西一円の信託銀行・都市銀行・大手私鉄・商社・生保・建設会社などの直系不動産会社、そして由緒ある老舗など優良不動産業者を中心に信用と実績を誇る不動産会社で組織されております。『関西不動産情報センター』を英文で『Kansai Real estate Information Center』と表示し、略称をKRIC＝クリックと称します。
      </p>
      <div class="about-concepts">
        <img src="<?php echo get_template_directory_uri(); ?>/images/ab_concepts.png" alt="5つのコンセプト" class="about-concepts-pc">
        <div class="about-concepts-sp">
          <img src="<?php echo get_template_directory_uri(); ?>/images/ab_concepts_sp.png" alt="5つのコンセプト" class="about-concepts-sp-img">
          <hr class="about-concepts-divider">
          <div class="about-concepts-grid">
            <div class="concept-item">
              <p class="concept-en">RESPONSIBILITY</p>
              <p class="concept-ja">社会的な責任</p>
              <p class="concept-desc">お客さまや地域社会への貢献、<br>その信頼と期待に応える。</p>
            </div>
            <div class="concept-item">
              <p class="concept-en">PROSPERITY</p>
              <p class="concept-ja">発展と繁栄</p>
              <p class="concept-desc">関西の不動産業界の発展と、<br>会員企業の共存共栄をめざして。</p>
            </div>
            <div class="concept-item">
              <p class="concept-en">COMPLIANCE</p>
              <p class="concept-ja">モラルの確立</p>
              <p class="concept-desc">高い倫理感を持ち、<br>つねに公正であること。</p>
            </div>
            <div class="concept-item">
              <p class="concept-en">RELIANCE</p>
              <p class="concept-ja">信頼のつながり</p>
              <p class="concept-desc">信頼関係を深め、そして拡げる。<br>そこに新たな英知が集積する。</p>
            </div>
            <div class="concept-item">
              <p class="concept-en">SPECIALITY</p>
              <p class="concept-ja">専門性の追求</p>
              <p class="concept-desc">高い専門知識を習得しながら、<br>つねにスキルを磨く向上心。</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== 理事長挨拶 ========== -->
  <section class="about-chairman" id="chairman">
    <div class="about-chairman-inner">
      <div class="about-chairman-text">
        <h2 class="about-chairman-title">理事長挨拶</h2>
        <p class="about-chairman-body">
          関西不動産情報センターは1969年の設立以来、「競い合うよりも、響き合うこと。」を精神に掲げ、関西の不動産流通の近代化と信頼性向上に努めてまいりました。銀行系列や大手電鉄、地域に根差した老舗など、多彩な会員企業がその垣根を越えて連携し、新たなビジネスチャンスを獲得してきました。実務に直結する研修会や実力アップ講座を定期的に開催するなど、会員のスキル向上にも注力しています。また、大阪・兵庫・京都の各部会や地区懇談会を通じた密な物件情報交換、質の高いノウハウを共有し、迅速かつ安全な共同仲介制度を構築しております。私たちは高度な専門知識の共有を通じて、お客様や地域社会の期待に応え、安心・安全な不動産取引を実現する社会的使命を担っています。これからも伝統を大切にしながら、時代の変化に応じた新たな価値を創造し、業界の健全な発展と共存共栄を目指してまいります。
        </p>
      </div>
      <div class="about-chairman-photo">
        <img src="<?php echo get_template_directory_uri(); ?>/images/ab_seo.png" alt="理事長">
      </div>
    </div>
  </section>

  <!-- ========== お問い合わせ ========== -->
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
