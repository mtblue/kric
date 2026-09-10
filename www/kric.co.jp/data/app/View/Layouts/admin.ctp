<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理画面 | KRIC</title>
<link rel="stylesheet" href="/wp-content/themes/default/css/style.css">
<link rel="stylesheet" href="/wp-content/themes/default/css/member.css">
<link rel="stylesheet" href="/wp-content/themes/default/css/admin.css">
<script src="/material/admin/js/jquery.min.js"></script>
</head>
<body class="is-member">

  <!-- ========== HEADER ========== -->
  <header id="header" class="is-scrolled">
    <div class="header-inner">
      <div class="header-logo">
        <a href="/" class="logo-text">
          <img src="/wp-content/themes/default/images/hd_logo.png" alt="KRIC" class="logo-img">
          <span class="logo-ja">関西不動産情報センター</span>
        </a>
      </div>
      <div class="header-right">
        <div class="header-btns">
          <a href="/<?php echo APP_NAME; ?>/logout" class="btn-hd btn-hd--member">
            <img src="/wp-content/themes/default/images/hd_login.png" class="btn-hd-icon" alt="">
            <span class="btn-text">会員専用｜ログアウト</span>
            <span class="btn-sep">|</span>
            <span class="btn-arrow">&#x25B6;&#xFE0E;</span>
          </a>
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
      <li><a href="/<?php echo APP_NAME; ?>/index">インフォメーション管理 <span class="gnav-arrow gnav-arrow-link">＞</span></a></li>
      <li><a href="/<?php echo APP_NAME; ?>/company">会社管理 <span class="gnav-arrow gnav-arrow-link">＞</span></a></li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">ライブラリ管理 <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="/<?php echo APP_NAME; ?>/library">ライブラリ管理</a></li>
          <li><a href="/<?php echo APP_NAME; ?>/category">カテゴリ管理</a></li>
        </ul>
      </li>
    </ul>
  </nav>

  <!-- ========== ページタイトルバー ========== -->
  <?php $adm_current = isset($this->request->params['action']) ? $this->request->params['action'] : ''; ?>
  <div class="mem-page-bar">
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; 管理画面<?php echo ($adm_current === 'login') ? ' ログイン' : ''; ?></p>
    <div class="mem-page-bar-inner">
      <h1 class="mem-page-title">［管理画面］</h1>
    </div>
  </div>

  <!-- ========== ナビタブ ========== -->
  <nav class="mem-nav" aria-label="管理メニュー">
    <div class="mem-nav-inner">
      <ul class="mem-nav-list">
        <li class="mem-nav-item<?php echo in_array($adm_current, array('index','information_edit')) ? ' is-active' : ''; ?>">
          <a href="/<?php echo APP_NAME; ?>/index" class="mem-nav-link">インフォメーション管理 <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item<?php echo in_array($adm_current, array('company','company_edit','company_view','member','member_edit')) ? ' is-active' : ''; ?>">
          <a href="/<?php echo APP_NAME; ?>/company" class="mem-nav-link">会社管理 <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item has-sub<?php echo in_array($adm_current, array('library','library_edit','library_view','category','category_edit')) ? ' is-active' : ''; ?>">
          <a href="/<?php echo APP_NAME; ?>/library" class="mem-nav-link">ライブラリ管理 <span class="mem-nav-arr">▼</span></a>
          <ul class="mem-nav-sub">
            <li<?php echo in_array($adm_current, array('library','library_edit','library_view')) ? ' class="is-active"' : ''; ?>><a href="/<?php echo APP_NAME; ?>/library">ライブラリ管理</a></li>
            <li<?php echo in_array($adm_current, array('category','category_edit')) ? ' class="is-active"' : ''; ?>><a href="/<?php echo APP_NAME; ?>/category">カテゴリ設定</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

<?php echo $content_for_layout; ?>

  <!-- ========== FOOTER ========== -->
  <footer class="footer mem-footer">
    <div class="footer-inner">
      <div class="footer-left">
        <a href="/" class="logo-text logo-text--footer">
          <img src="/wp-content/themes/default/images/ft_logo.png" alt="KRIC" class="logo-img logo-img--footer">
          <span class="logo-ja logo-ja--footer">関西不動産情報センター</span>
        </a>
        <div class="footer-address">
          <p>〒530-0012 大阪市北区芝田2丁目1番18号 西阪急ビル7階</p>
        </div>
        <a href="https://www.google.com/maps/search/?api=1&query=34.7055664%2C135.4967281" target="_blank" rel="noopener" class="footer-map-btn">
          <span class="btn-text">google map</span>
          <span class="btn-sep">|</span>
          <span class="btn-arrow">&#x25B6;&#xFE0E;</span>
        </a>
      </div>
      <div class="footer-right">
        <nav class="footer-nav">
          <div class="footer-nav-cols mem-ft-nav-cols adm-ft-nav">
            <div class="footer-nav-col">
              <a href="/<?php echo APP_NAME; ?>/index" class="footer-nav-parent">インフォメーション管理</a>
            </div>
            <div class="footer-nav-col">
              <a href="/<?php echo APP_NAME; ?>/company" class="footer-nav-parent">会社管理</a>
            </div>
            <div class="footer-nav-col">
              <a href="/<?php echo APP_NAME; ?>/library" class="footer-nav-parent">ライブラリ管理</a>
              <ul>
                <li><a href="/<?php echo APP_NAME; ?>/category">カテゴリ管理</a></li>
                <li><a href="/<?php echo APP_NAME; ?>/library">ライブラリ管理</a></li>
              </ul>
            </div>
          </div>
        </nav>
        <div class="footer-privacy">
          <a href="/privacy.html">プライバシーポリシー</a>
        </div>
      </div>
    </div>
    <div class="footer-copy">
      <p>Copyright &copy; KRIC 関西不動産情報センター All Rights Reserved.</p>
    </div>
  </footer>

  <!-- フローティングページトップボタン -->
  <a href="#" class="page-top-fixed" aria-label="ページトップへ" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">∧</a>
  <script>
  (function(){
    var btn = document.querySelector('.page-top-fixed');
    if (!btn) return;
    window.addEventListener('scroll', function(){
      if (window.scrollY > 300) { btn.classList.add('is-visible'); }
      else { btn.classList.remove('is-visible'); }
    }, { passive: true });
  })();
  </script>

  <script src="/wp-content/themes/default/js/main.js"></script>
</body>
</html>
