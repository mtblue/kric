<?php get_header(); ?>

  <!-- ========== KVラッパー（ヒーロー＋KRICについて共通背景） ========== -->
  <div class="kv-wrapper">
    <div class="kv-bg" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/back.png')"></div>
    <div class="kv-overlay"></div>

    <!-- ========== HERO ========== -->
    <section class="hero" id="top">
      <div class="hero-content">
        <img src="<?php echo get_template_directory_uri(); ?>/images/top_logo.png" alt="競い合うより、響き合う。KRIC" class="hero-top-logo">
      </div>
      <div class="hero-lower">
        <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_99406975_M.png" alt="" class="hero-photo">
        <img src="<?php echo get_template_directory_uri(); ?>/images/top_logo2.png" alt="PRIDE of MEMBERSHIP" class="hero-top-logo2">
      </div>
    </section>

    <!-- ========== KRICについて ========== -->
    <section class="about-pride" id="about">
      <div class="about-pride-photo-sp">
        <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_99406975_M.png" alt="">
      </div>
      <div class="about-pride-inner">
        <div class="about-pride-photos">
          <div class="pride-photo pride-photo--top-left">
            <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_89681447_S.jpg" alt="">
          </div>
          <div class="pride-photo pride-photo--right">
            <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_98523184_M.png" alt="">
          </div>
          <div class="pride-photo pride-photo--bottom-left">
            <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_92814973_L.png" alt="">
          </div>
        </div>
        <div class="about-pride-text">
          <div class="about-pride-body">
            <div class="about-pride-heading">
              <img src="<?php echo get_template_directory_uri(); ?>/images/list_icon.png" class="sec-star-icon" alt="">
              <h2 class="sec-title sec-title--light">KRICについて</h2>
            </div>
            <p class="about-pride-desc">
              関西の不動産流通業界の<br>信頼向上のために<br>不動産会社で組織された<br>グループです。
            </p>
            <a href="<?php echo esc_url(home_url('/kric.html')); ?>" class="btn-outline-light"><span class="btn-text">詳細はこちら</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
          </div>
        </div>
      </div>
    </section>

  </div><!-- /.kv-wrapper -->

  <!-- ========== KRICについて テキスト（SP用） ========== -->
  <div class="about-pride-text-sp">
    <div class="about-pride-heading">
      <img src="<?php echo get_template_directory_uri(); ?>/images/list_icon.png" class="sec-star-icon" alt="">
      <h2 class="sec-title sec-title--light">KRICについて</h2>
    </div>
    <p class="about-pride-desc">
      関西の不動産流通業界の<br>信頼向上のために<br>不動産会社で組織された<br>グループです。
    </p>
    <a href="<?php echo esc_url(home_url('/kric.html')); ?>" class="btn-outline-light"><span class="btn-text">詳細はこちら</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
  </div>

  <!-- ========== KRICメディア ========== -->
  <section class="kric-media" id="media">
    <div class="section-inner">
      <div class="kric-media-heading">
        <img src="<?php echo get_template_directory_uri(); ?>/images/list_icon.png" class="sec-star-icon" alt="">
        <h2 class="sec-title">KRICメディア</h2>
        <div class="sec-nav">
          <a href="<?php echo esc_url(home_url('/news.html')); ?>#media" class="btn-list"><span class="btn-text">一覧へ</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
        </div>
      </div>
      <p class="kric-media-desc">ラジオ番組やYouTubeなど様々なメディアで<br>不動産に関する情報を発信しています。</p>
      <div class="media-list-btn-sp">
        <a href="<?php echo esc_url(home_url('/news.html')); ?>#media" class="btn-list"><span class="btn-text">一覧へ</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
      </div>
      <div class="media-slider-wrap">
        <div class="media-grid" id="mediaGrid">
          <?php
          $media_posts = get_posts([
              'post_type'   => 'kric-media',
              'numberposts' => 4,
              'meta_key'    => 'orderView',
              'orderby'     => 'meta_value_num',
              'order'       => 'DESC',
              'post_status' => 'publish',
          ]);

          foreach ($media_posts as $post) :
              // ACF field values
              $youtube_id  = function_exists('get_field') ? (string)get_field('YouTubeURL', $post->ID) : '';
              $general_url = function_exists('get_field') ? get_field('GeneralURL', $post->ID) : '';
              $media_img   = function_exists('get_field') ? get_field('mediaimg', $post->ID) : '';

              // Broadcast date from orderView field (YYYYMMDD → Y,m,d)
              $date_raw = function_exists('get_field') ? (string)get_field('orderView', $post->ID) : '';
              if ($date_raw && strlen($date_raw) === 8) {
                  $date = substr($date_raw, 0, 4) . ',' . substr($date_raw, 4, 2) . ',' . substr($date_raw, 6, 2);
              } else {
                  $date = get_the_date('Y,m,d', $post);
              }

              // Label: YOUTUBE if YouTubeURL is set
              $is_youtube  = !empty($youtube_id);
              $label_class = $is_youtube ? 'media-card-label--youtube' : 'media-card-label--podcast';
              $label_text  = $is_youtube ? 'YOUTUBE' : 'PODCAST';

              // Link: GeneralURL priority (original home.php logic), then YouTube
              if ($general_url) {
                  $link_url = $general_url;
              } elseif ($youtube_id) {
                  $link_url = 'https://www.youtube.com/watch?v=' . $youtube_id;
              } else {
                  $link_url = get_permalink($post->ID);
              }

              // Image: mediaimg priority; YouTube thumbnail if no mediaimg
              if ($media_img) {
                  $img_url = is_array($media_img) ? ($media_img['url'] ?? '') : (string)$media_img;
              } elseif ($youtube_id) {
                  $img_url = 'https://img.youtube.com/vi/' . $youtube_id . '/mqdefault.jpg';
              } else {
                  $img_url = get_the_post_thumbnail_url($post->ID, 'medium') ?: '';
              }

              $title = get_the_title($post);
          ?>
          <div class="media-card">
            <div class="media-card-img">
              <?php if ($img_url) : ?>
              <a href="<?php echo esc_url($link_url); ?>" target="_blank" rel="noopener">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>">
              </a>
              <?php endif; ?>
            </div>
            <div class="media-card-body">
              <div class="media-card-meta">
                <span class="media-card-label <?php echo esc_attr($label_class); ?>"><?php echo $label_text; ?></span>
                <span class="media-card-date"><?php echo esc_html($date); ?></span>
              </div>
              <a href="<?php echo esc_url($link_url); ?>" class="media-card-link" target="_blank" rel="noopener">視聴はこちら→</a>
            </div>
          </div>
          <?php endforeach; wp_reset_postdata(); ?>
        </div><!-- /#mediaGrid -->
        <div class="media-slider-controls-sp">
          <button class="media-slider-btn" id="mediaSliderPrev" aria-label="前へ">&#8249;</button>
          <div class="media-dots-sp" id="mediaDotsSp"></div>
          <button class="media-slider-btn" id="mediaSliderNext" aria-label="次へ">&#8250;</button>
        </div>
      </div><!-- /.media-slider-wrap -->
    </div>
  </section>

  <!-- ========== NEWS 新着情報 ========== -->
  <section class="news" id="news">
    <div class="section-inner news-inner">
      <div class="news-left">
        <div class="news-heading">
          <img src="<?php echo get_template_directory_uri(); ?>/images/list_icon.png" class="sec-star-icon" alt="">
          <h2 class="sec-title">NEWS</h2>
          <div class="sec-nav">
            <a href="<?php echo esc_url(home_url('/news.html')); ?>" class="btn-list"><span class="btn-text">一覧へ</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
          </div>
        </div>
        <div class="news-list-btn-sp">
          <a href="<?php echo esc_url(home_url('/news.html')); ?>" class="btn-list"><span class="btn-text">一覧へ</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
        </div>
        <ul class="news-list">
          <?php
          $news_posts = get_posts([
              'numberposts'   => 5,
              'category_name' => 'tell',
              'post_status'   => 'publish',
          ]);

          foreach ($news_posts as $post) :
              $cats      = get_the_category($post->ID);
              $cat       = !empty($cats) ? $cats[0] : null;
              $cat_name  = $cat ? $cat->name : 'お知らせ';
              $cat_class = $cat ? kric_news_cat_class($cat->slug) : 'news-cat--info';
              $date      = get_the_date('y,m,d', $post);
              $title     = get_the_title($post);
              $link      = get_permalink($post->ID);
          ?>
          <li class="news-item">
            <a href="<?php echo esc_url($link); ?>" class="news-link">
              <span class="news-date"><?php echo esc_html($date); ?></span>
              <span class="news-title"><?php echo esc_html($title); ?></span>
              <span class="news-arr">→</span>
            </a>
          </li>
          <?php endforeach; wp_reset_postdata(); ?>
        </ul>
      </div>
      <div class="news-right">
        <img src="<?php echo get_template_directory_uri(); ?>/images/room-used-official-event.png" alt="公式イベント" class="news-right-img">
      </div>
    </div>
  </section>

  <!-- ========== 会員紹介（MAP） ========== -->
  <section class="member-intro" id="member-intro">
    <div class="member-city-row">
      <div class="member-city-img" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/pixta_88853599_S.jpg')">
      </div>
      <div class="member-city-img" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/pixta_94685113_S.jpg'); background-position: right center;">
      </div>
      <div class="member-city-img" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/pixta_95785405_S.jpg'); background-position: right center;">
      </div>
    </div>
    <div class="member-intro-body">
      <div class="section-inner">
        <div class="member-intro-inner">
          <div class="member-intro-text">
            <div class="member-intro-heading">
              <img src="<?php echo get_template_directory_uri(); ?>/images/list_icon.png" class="sec-star-icon" alt="">
              <h2 class="sec-title sec-title--light">会員紹介</h2>
            </div>
            <p class="member-intro-lead">大阪を中心に関西一円に<br>広がるネットワーク。</p>
            <p class="member-intro-desc">関西一円の信託銀行・都市銀行の不動産部をはじめ、銀行系列の不動産会社、大手電鉄・商社・ゼネコンなどの直系不動産会社、そして新たな分野で活躍する新進気鋭な不動産会社など、信用と実績を誇る不動産会社で組織された信頼のグループです。</p>
            <a href="<?php echo esc_url(home_url('/kaiin.html')); ?>" class="btn-outline-light"><span class="btn-text">詳細はこちら</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
          </div>
          <div class="member-map-col">
            <div class="member-map">
              <div id="memberMapTop" style="width:100%;height:400px;"></div>
            </div>
          </div>
        </div>
        <div class="member-search" id="member-search">
          <div class="member-search-card">
            <p class="member-search-title">- 会員検索はこちら -</p>
            <form class="member-search-form" id="memberSearchFormTop">
              <div class="member-search-inputs">
                <input type="text" class="member-search-input" id="memberKeywordTop" placeholder="会社名">
                <input type="text" class="member-search-input" id="memberAreaTop" placeholder="エリア・駅">
              </div>
              <div class="member-search-btn-row">
                <button type="submit" class="member-search-btn"><span class="btn-text">検索</span><span class="btn-sep">|</span><img src="<?php echo get_template_directory_uri(); ?>/images/search.png" class="search-icon" alt="検索"></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== 活動について ========== -->
  <section class="activities" id="activities">
    <div class="section-inner activities-inner">
      <div class="activities-text">
        <div class="activities-heading">
          <img src="<?php echo get_template_directory_uri(); ?>/images/list_icon.png" class="sec-star-icon" alt="">
          <h2 class="sec-title sec-title--light">活動について</h2>
        </div>
        <p class="activities-lead">関西の不動産流通業界のために、<br>協会内外で様々な活動を実施。</p>
        <p class="activities-desc">人を起点とした情報交換や研修会を通じて知識と実務力を高めるとともに、親睦活動により会員同士の信頼関係と結束を深め、継続的な成長と価値創出を促進しています。</p>
        <a href="<?php echo esc_url(home_url('/katsudo.html')); ?>" class="btn-outline-light"><span class="btn-text">詳細はこちら</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
      </div>
      <div class="activities-photos">
        <div class="act-photo">
          <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_93680086_M.png" alt="">
        </div>
        <div class="act-photo">
          <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_40524215_M.png" alt="">
        </div>
        <div class="act-photo">
          <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_76785357_L.png" alt="">
        </div>
        <div class="act-photo">
          <img src="<?php echo get_template_directory_uri(); ?>/images/pixta_41350523_S.png" alt="">
        </div>
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

<script>
(function () {
  var mapEl    = document.getElementById('memberMapTop');
  var form     = document.getElementById('memberSearchFormTop');
  var kwInput  = document.getElementById('memberKeywordTop');
  var areaInput= document.getElementById('memberAreaTop');

  var gmap = null;
  var markers = [];
  var infoWindow = null;

  window.initMemberMapTop = function () {
    gmap = new google.maps.Map(mapEl, {
      center: { lat: 34.70, lng: 135.50 },
      zoom: 10,
    });
    infoWindow = new google.maps.InfoWindow();
    fetch('/intro/dir_json?all=1')
      .then(function (r) { return r.json(); })
      .then(function (data) { updateMarkers(data); })
      .catch(function () {});
  };

  function updateMarkers(data) {
    markers.forEach(function (m) { m.setMap(null); });
    markers = [];
    if (!gmap) { return; }
    data.forEach(function (item) {
      if (item.lat == null || item.lng == null) { return; }
      var svgIcon = {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
          '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="42" viewBox="0 0 30 42">' +
          '<path fill-rule="evenodd" fill="#b8954a" stroke="#ffffff" stroke-width="2" ' +
          'd="M15 0C6.716 0 0 6.716 0 15c0 10.5 15 27 15 27S30 25.5 30 15C30 6.716 23.284 0 15 0Z ' +
          'M15 15m-6 0a6 6 0 1 0 12 0a6 6 0 1 0-12 0"/>' +
          '</svg>'
        ),
        scaledSize: new google.maps.Size(30, 42),
        anchor: new google.maps.Point(15, 42),
      };
      var marker = new google.maps.Marker({
        position: { lat: item.lat, lng: item.lng },
        map: gmap,
        title: item.name,
        icon: svgIcon,
      });
      marker.addListener('click', function () {
        infoWindow.setContent(
          '<div style="font-size:13px;line-height:1.6;"><strong>' + item.name + '</strong></div>'
        );
        infoWindow.open(gmap, marker);
      });
      markers.push(marker);
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var kw   = kwInput.value.trim();
    var area = areaInput.value.trim();
    var params = [];
    if (kw)   { params.push('keyword=' + encodeURIComponent(kw)); }
    if (area) { params.push('area='    + encodeURIComponent(area)); }
    var url = '/kaiin.html' + (params.length ? '?' + params.join('&') : '');
    window.location.href = url;
  });
})();
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDduY_Gqdho8bKF9ZSSR3DAPX5C63yqtPo&callback=initMemberMapTop" async defer></script>

<?php get_footer(); ?>
