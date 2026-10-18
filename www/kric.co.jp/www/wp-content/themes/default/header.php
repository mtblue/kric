<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google tag (gtag.js) - Google Analytics 4 -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q35S7Z24WR"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-Q35S7Z24WR');
  </script>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

  <!-- ========== HEADER ========== -->
  <header id="header">
    <div class="header-inner">
      <div class="header-logo">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-text">
          <img src="<?php echo get_template_directory_uri(); ?>/images/hd_logo.png" alt="KRIC" class="logo-img">
          <span class="logo-ja">関西不動産情報センター</span>
        </a>
      </div>
      <div class="header-right">
        <div class="header-btns">
<a href="/iis/login" class="btn-hd btn-hd--member"><img src="<?php echo get_template_directory_uri(); ?>/images/hd_login.png" class="btn-hd-icon" alt=""><span class="btn-text">会員専用｜ログイン</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
        </div>
        <button class="menu-btn" id="menuBtn" aria-label="メニューを開く">
          <span class="menu-btn-lines">
            <span></span><span></span><span></span>
          </span>
          <span class="menu-btn-label">MENU</span>
        </button>
      </div>
    </div>
  </header>

  <!-- ========== GLOBAL NAV ========== -->
  <div class="gnav-overlay" id="gnavOverlay"></div>
  <nav class="gnav" id="gnav" aria-hidden="true">
    <div class="gnav-header">
      <button class="gnav-close" id="gnavClose" aria-label="閉じる">
        <span class="gnav-close-icon">✕</span>
        <span class="gnav-close-label">MENU</span>
      </button>
    </div>
    <ul class="gnav-list">
      <li><a href="<?php echo esc_url(home_url('/')); ?>">TOP</a></li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">KRICについて <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="<?php echo esc_url(home_url('/kric.html')); ?>">KRICについて</a></li>
          <li><a href="<?php echo esc_url(home_url('/kric.html')); ?>#chairman">理事長挨拶</a></li>
        </ul>
      </li>
      <li><a href="<?php echo esc_url(home_url('/katsudo.html')); ?>">活動内容 <span class="gnav-arrow gnav-arrow-link">＞</span></a></li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">NEWS <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="<?php echo esc_url(home_url('/news.html')); ?>">NEWS</a></li>
          <li><a href="<?php echo esc_url(home_url('/news.html')); ?>">新着情報</a></li>
          <li><a href="<?php echo esc_url(home_url('/news.html')); ?>#media">KRICメディア</a></li>
        </ul>
      </li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">会員紹介 <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="<?php echo esc_url(home_url('/kaiin.html')); ?>">会員紹介</a></li>
          <li><a href="<?php echo esc_url(home_url('/kaiin.html')); ?>#map">マップ検索</a></li>
          <li><a href="<?php echo esc_url(home_url('/kaiin.html')); ?>#search">フリーワード検索</a></li>
        </ul>
      </li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">入会案内 <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="<?php echo esc_url(home_url('/nyukai.html')); ?>">入会案内</a></li>
          <li><a href="<?php echo esc_url(home_url('/nyukai.html')); ?>#flow">入会審査の流れ</a></li>
          <li><a href="<?php echo esc_url(home_url('/nyukai.html')); ?>#qualification">入会資格</a></li>
        </ul>
      </li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">団体概要 <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>">団体概要</a></li>
          <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>#management">関西不動産情報センター（KRIC事務局）</a></li>
          <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>#orgchart">組織図</a></li>
          <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>#history">KRICの歴史</a></li>
        </ul>
      </li>
    </ul>
  </nav>
