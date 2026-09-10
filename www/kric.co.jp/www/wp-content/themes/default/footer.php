  <!-- ========== FOOTER ========== -->
  <footer class="footer" id="member">
    <div class="footer-inner">
      <div class="footer-left">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-text logo-text--footer">
          <img src="<?php echo get_template_directory_uri(); ?>/images/ft_logo.png" alt="KRIC" class="logo-img logo-img--footer">
          <span class="logo-ja logo-ja--footer">関西不動産情報センター</span>
        </a>
        <div class="footer-address">
          <p>〒530-0012 大阪市北区芝田2丁目1番18号 西阪急ビル7階</p>
        </div>
        <a href="https://www.google.com/maps/search/?api=1&query=34.7055664%2C135.4967281" target="_blank" rel="noopener" class="footer-map-btn"><span class="btn-text">google map</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
      </div>
      <div class="footer-right">
        <nav class="footer-nav">
          <div class="footer-nav-cols">
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-nav-parent">TOP</a>
            </div>
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/kric.html')); ?>" class="footer-nav-parent">KRICについて</a>
              <ul>
                <li class="footer-nav-self"><a href="<?php echo esc_url(home_url('/kric.html')); ?>">KRICについて</a></li>
                <li><a href="<?php echo esc_url(home_url('/kric.html')); ?>#chairman">理事長挨拶</a></li>
              </ul>
            </div>
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/katsudo.html')); ?>" class="footer-nav-parent">活動内容</a>
            </div>
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/news.html')); ?>" class="footer-nav-parent">NEWS</a>
              <ul>
                <li class="footer-nav-self"><a href="<?php echo esc_url(home_url('/news.html')); ?>">NEWS</a></li>
                <li><a href="<?php echo esc_url(home_url('/news.html')); ?>">新着情報</a></li>
                <li><a href="<?php echo esc_url(home_url('/news.html')); ?>#media">KRICメディア</a></li>
              </ul>
            </div>
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/kaiin.html')); ?>" class="footer-nav-parent">会員紹介</a>
              <ul>
                <li class="footer-nav-self"><a href="<?php echo esc_url(home_url('/kaiin.html')); ?>">会員紹介</a></li>
                <li><a href="<?php echo esc_url(home_url('/kaiin.html')); ?>#map">マップ検索</a></li>
                <li><a href="<?php echo esc_url(home_url('/kaiin.html')); ?>#search">フリーワード検索</a></li>
              </ul>
            </div>
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/nyukai.html')); ?>" class="footer-nav-parent">入会案内</a>
              <ul>
                <li class="footer-nav-self"><a href="<?php echo esc_url(home_url('/nyukai.html')); ?>">入会案内</a></li>
                <li><a href="<?php echo esc_url(home_url('/nyukai.html')); ?>#flow">入会審査の流れ</a></li>
                <li><a href="<?php echo esc_url(home_url('/nyukai.html')); ?>#qualification">入会資格</a></li>
              </ul>
            </div>
            <div class="footer-nav-col">
              <a href="<?php echo esc_url(home_url('/dantai.html')); ?>" class="footer-nav-parent">団体概要</a>
              <ul>
                <li class="footer-nav-self"><a href="<?php echo esc_url(home_url('/dantai.html')); ?>">団体概要</a></li>
                <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>#management">関西不動産情報センター<span class="footer-kric-suffix">（KRIC事務局）</span></a></li>
                <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>#orgchart">組織図</a></li>
                <li><a href="<?php echo esc_url(home_url('/dantai.html')); ?>#history">KRICの歴史</a></li>
              </ul>
            </div>
          </div>
        </nav>
        <div class="footer-privacy">
          <a href="<?php echo esc_url(home_url('/privacy.html')); ?>">プライバシーポリシー</a>
        </div>
      </div>
    </div>
    <div class="footer-copy">
      <p>Copyright &copy; KRIC 関西不動産情報センター All Rights Reserved.</p>
    </div>
  </footer>

  <!-- SP固定フッターボタン -->
  <div class="sp-footer-fixed">
    <a href="/iis/login" class="sp-ft-btn sp-ft-btn--contact"><img src="<?php echo get_template_directory_uri(); ?>/images/hd_login.png" class="btn-hd-icon" alt=""><span class="btn-text">会員専用｜ログイン</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
  </div>

  <!-- フローティングページトップボタン -->
  <a href="#" class="page-top-fixed" aria-label="ページトップへ" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">∧</a>
  <script>
  (function(){
    var btn = document.querySelector('.page-top-fixed');
    if (!btn) return;
    window.addEventListener('scroll', function(){
      if (window.scrollY > 300) {
        btn.classList.add('is-visible');
      } else {
        btn.classList.remove('is-visible');
      }
    }, { passive: true });
  })();
  </script>

  <?php wp_footer(); ?>
</body>
</html>
