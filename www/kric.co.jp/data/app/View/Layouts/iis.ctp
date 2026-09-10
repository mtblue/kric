<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KRIC 会員専用ページ</title>
  <link rel="stylesheet" href="/wp-content/themes/default/css/style.css">
  <link rel="stylesheet" href="/wp-content/themes/default/css/member.css">
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
          <a href="/iis/logout" class="btn-hd btn-hd--member">
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
      <li><a href="/iis/index">インフォメーション<span class="gnav-arrow gnav-arrow-link">＞</span></a></li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">ビジネスダイレクト <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="/iis/bd">売りたい</a></li>
          <li><a href="/iis/bd02">買いたい</a></li>
          <li><a href="/iis/bd03">貸したい</a></li>
          <li><a href="/iis/bd04">借りたい</a></li>
        </ul>
      </li>
      <li><a href="/<?php echo APP_NAME; ?>/library">ライブラリ <span class="gnav-arrow gnav-arrow-link">＞</span></a></li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">KRICメディア <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="/<?php echo APP_NAME; ?>/media_mbs1">【MBSラジオ】村瀬哲史とKRIC！不動産の時間</a></li>
          <li><a href="/<?php echo APP_NAME; ?>/media_mbs2">【MBSラジオ】予備校講師村瀬哲史のナニワ地理学</a></li>
          <li><a href="/<?php echo APP_NAME; ?>/media_youtube">【YouTube】ナニワの地理学スピンオフ</a></li>
        </ul>
      </li>
      <li><a href="/iis/dir">登録者名簿 <span class="gnav-arrow gnav-arrow-link">＞</span></a></li>
      <li class="gnav-has-child">
        <button class="gnav-toggle" aria-expanded="false">登録情報 <span class="gnav-arrow">＋</span></button>
        <ul class="gnav-child">
          <li><a href="/iis/company_edit">会社概要</a></li>
          <li><a href="/<?php echo APP_NAME; ?>/member_edit/id=<?php echo isset($tpl_disp['sessionInfo']['member_id']) ? h($tpl_disp['sessionInfo']['member_id']) : ''; ?>">個別設定</a></li>
          <li><a href="/iis/member">メンバー追加／削除</a></li>
        </ul>
      </li>
    </ul>
  </nav>

  <!-- ========== 会員ページタイトルバー ========== -->
  <?php $currentAction = $this->request->action; ?>
  <div class="mem-page-bar">
    <?php
      $bdLabels = array('bd' => '売りたい', 'bd02' => '買いたい', 'bd03' => '貸したい', 'bd04' => '借りたい');
    ?>
    <?php if ($currentAction == 'login'): ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; 会員専用ページ ログイン</p>
    <?php elseif ($currentAction == 'reminder'): ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/<?php echo APP_NAME; ?>/login">会員専用ページ ログイン</a></p>
    <?php elseif ($currentAction == 'library'): ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/iis/index">会員専用ページ</a> &gt; ライブラリ</p>
    <?php elseif ($currentAction == 'library_list'): ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/iis/index">会員専用ページ</a> &gt; <a href="/<?php echo APP_NAME; ?>/library">ライブラリ</a> &gt; <?php echo isset($tpl_disp['opener_category']['category_name']) ? h($tpl_disp['opener_category']['category_name']) : ''; ?></p>
    <?php elseif ($currentAction == 'library_detail'): ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/iis/index">会員専用ページ</a> &gt; <a href="/<?php echo APP_NAME; ?>/library">ライブラリ</a><?php if (!empty($tpl_disp['opener_name'])): ?> &gt; <a href="/<?php echo APP_NAME; ?>/library_list/cg=<?php echo h($tpl_disp['opener_id']); ?>"><?php echo h($tpl_disp['opener_name']); ?></a><?php endif; ?></p>
    <?php elseif (strpos($currentAction, 'media_') === 0): ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/iis/index">会員専用ページ</a> &gt; KRICメディア</p>
    <?php else: ?>
    <p class="mem-breadcrumb mem-breadcrumb--sp"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/iis/index">会員専用ページ</a><?php if ($currentAction == 'information_detail'): ?> &gt; <a href="/iis/index">インフォメーション</a> &gt; 詳細<?php elseif (isset($bdLabels[$currentAction])): ?> &gt; <a href="/iis/bd">ビジネスダイレクト</a> &gt; <?php echo $bdLabels[$currentAction]; ?><?php elseif ($currentAction == 'bd_detail'): ?> &gt; <a href="/iis/bd">ビジネスダイレクト</a> &gt; 詳細<?php elseif ($currentAction == 'dir'): ?> &gt; 登録者名簿<?php elseif (in_array($currentAction, array('company', 'company_edit'))): ?> &gt; <a href="/iis/company_edit">登録情報</a> &gt; 会社概要<?php elseif (in_array($currentAction, array('member', 'member_new'))): ?> &gt; <a href="/iis/company_edit">登録情報</a> &gt; メンバー追加／削除<?php elseif ($currentAction == 'member_edit'): ?> &gt; <a href="/iis/company_edit">登録情報</a> &gt; 個別設定<?php endif; ?></p>
    <?php endif; ?>
    <div class="mem-page-bar-inner">
      <h1 class="mem-page-title">[会員専用ページ]</h1>
      <?php if ($currentAction !== 'login' && $currentAction !== 'reminder' && $currentAction !== 'authmail'): ?>
      <p class="mem-page-user">
        <span class="mem-page-company"><?php echo isset($tpl_disp['sessionInfo']['company_name']) ? h($tpl_disp['sessionInfo']['company_name']) : ''; ?></span>
        <span class="mem-page-sep">｜</span>
        <span class="mem-page-name"><?php echo isset($tpl_disp['sessionInfo']['name']) ? h($tpl_disp['sessionInfo']['name']) : ''; ?>　様</span>
      </p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ========== 会員ナビタブ ========== -->
  <nav class="mem-nav" aria-label="会員メニュー">
    <div class="mem-nav-inner">
      <ul class="mem-nav-list">
        <li class="mem-nav-item<?php echo (in_array($currentAction, array('index', 'information_detail'))) ? ' is-active' : ''; ?>">
          <a href="/iis/index" class="mem-nav-link">インフォメーション <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item<?php echo (strpos($currentAction, 'bd') === 0) ? ' is-active' : ''; ?>">
          <a href="/iis/bd" class="mem-nav-link">ビジネスダイレクト <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item<?php echo (strpos($currentAction, 'library') === 0) ? ' is-active' : ''; ?>">
          <a href="/<?php echo APP_NAME; ?>/library" class="mem-nav-link">ライブラリ <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item<?php echo (strpos($currentAction, 'media_') === 0) ? ' is-active' : ''; ?>">
          <a href="/<?php echo APP_NAME; ?>/media_mbs1" class="mem-nav-link">KRICメディア <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item<?php echo ($currentAction === 'dir') ? ' is-active' : ''; ?>">
          <a href="/iis/dir" class="mem-nav-link">登録者名簿 <span class="mem-nav-arr">▼</span></a>
        </li>
        <li class="mem-nav-item has-sub<?php echo (in_array($currentAction, array('company', 'company_edit', 'member', 'member_edit', 'member_new'))) ? ' is-active' : ''; ?>">
          <a href="/iis/company_edit" class="mem-nav-link">登録情報 <span class="mem-nav-arr">▼</span></a>
          <ul class="mem-nav-sub">
            <li<?php echo (in_array($currentAction, array('company', 'company_edit'))) ? ' class="is-active"' : ''; ?>><a href="/iis/company_edit">会社概要</a></li>
            <li<?php echo ($currentAction == 'member_edit') ? ' class="is-active"' : ''; ?>><a href="/<?php echo APP_NAME; ?>/member_edit/id=<?php echo isset($tpl_disp['sessionInfo']['member_id']) ? h($tpl_disp['sessionInfo']['member_id']) : ''; ?>">個別設定</a></li>
            <li<?php echo (in_array($currentAction, array('member', 'member_new'))) ? ' class="is-active"' : ''; ?>><a href="/iis/member">メンバー追加／削除</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <?php echo $this->fetch('content'); ?>

  <!-- ========== FOOTER（会員版） ========== -->
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
        <a href="https://www.google.com/maps/search/?api=1&query=34.7055664%2C135.4967281" target="_blank" rel="noopener" class="footer-map-btn"><span class="btn-text">google map</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
      </div>
      <div class="footer-right">
        <nav class="footer-nav">
          <div class="footer-nav-cols mem-ft-nav-cols">
            <div class="footer-nav-col">
              <a href="/iis/index" class="footer-nav-parent">インフォメーション</a>
            </div>
            <div class="footer-nav-col">
              <a href="/iis/bd" class="footer-nav-parent">ビジネスダイレクト</a>
              <ul>
                <li><a href="/iis/bd">売りたい</a></li>
                <li><a href="/iis/bd02">買いたい</a></li>
                <li><a href="/iis/bd03">貸したい</a></li>
                <li><a href="/iis/bd04">借りたい</a></li>
              </ul>
            </div>
            <div class="footer-nav-col">
              <a href="/<?php echo APP_NAME; ?>/library" class="footer-nav-parent">ライブラリ</a>
            </div>
            <div class="footer-nav-col">
              <a href="/<?php echo APP_NAME; ?>/media_mbs1" class="footer-nav-parent">KRICメディア</a>
              <ul>
                <li><a href="/<?php echo APP_NAME; ?>/media_mbs1">【MBSラジオ】<br>村瀬哲史とKRIC！<br>不動産の時間</a></li>
                <li><a href="/<?php echo APP_NAME; ?>/media_mbs2">【MBSラジオ】<br>予備校講師村瀬哲史の<br>ナニワ地理学</a></li>
                <li><a href="/<?php echo APP_NAME; ?>/media_youtube">【YouTube】<br>ナニワの地理学<br>スピンオフ</a></li>
              </ul>
            </div>
            <div class="footer-nav-col">
              <a href="/iis/dir" class="footer-nav-parent">登録者名簿</a>
            </div>
            <div class="footer-nav-col">
              <a href="/iis/company_edit" class="footer-nav-parent">登録情報</a>
              <ul>
                <li><a href="/iis/company_edit">会社概要</a></li>
                <li><a href="/<?php echo APP_NAME; ?>/member_edit/id=<?php echo isset($tpl_disp['sessionInfo']['member_id']) ? h($tpl_disp['sessionInfo']['member_id']) : ''; ?>">個別設定</a></li>
                <li><a href="/iis/member">メンバー追加／削除</a></li>
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

  <!-- 登録者情報モーダル -->
  <div class="dir-modal-overlay" id="dirModalOverlay">
    <div class="dir-modal">
      <button class="dir-modal-close" id="dirModalClose">&times;</button>
      <div class="dir-modal-content" id="dirModalContent"></div>
    </div>
  </div>

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
  <script>
  (function () {
    var overlay = document.getElementById('dirModalOverlay');
    var content = document.getElementById('dirModalContent');
    var closeBtn = document.getElementById('dirModalClose');
    if (!overlay) return;
    function closeModal() {
      overlay.classList.remove('is-open');
      content.innerHTML = '';
    }
    document.addEventListener('click', function (e) {
      var link = e.target.closest('.js-lightbox-link');
      if (!link) return;
      e.preventDefault();
      content.innerHTML = '<p style="padding:40px;text-align:center;">読み込み中...</p>';
      overlay.classList.add('is-open');
      fetch(link.href)
        .then(function (r) { return r.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var body = doc.querySelector('body');
          content.innerHTML = body ? body.innerHTML : html;
        })
        .catch(function () {
          content.innerHTML = '<p style="padding:40px;">データを取得できませんでした。</p>';
        });
    });
    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeModal();
    });
  })();
  </script>
</body>
</html>
