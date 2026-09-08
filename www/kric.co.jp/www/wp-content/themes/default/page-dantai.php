<?php
/*
 * Template Name: 団体概要
 */
?>
<?php get_header(); ?>

  <!-- ========== ページKV ========== -->
  <section class="page-kv--bg">
    <div class="page-kv-placeholder" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/fm_bg.png')"></div>
    <div class="page-kv-overlay">
      <h1 class="page-kv-title">団体概要</h1>
    </div>
  </section>

  <!-- パンくず -->
  <div class="page-breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">関西不動産情報センター TOP</a>
    <span class="page-breadcrumb-sep">&gt;</span>
    団体概要
  </div>

  <!-- ========== 関西不動産センター（KRIC事務局） ========== -->
  <section class="page-sec" id="management">
    <h2 class="page-sec-heading">関西不動産センター（KRIC事務局）</h2>
    <div class="dantai-inner">
      <div class="dantai-info-wrap">
        <div class="dantai-dl">
          <div class="dantai-dl-row">
            <span class="dantai-dl-dt">住所</span>
            <span class="dantai-dl-dd">〒530-0012 大阪市北区芝田2丁目1番18号 西阪急ビル7階</span>
          </div>
          <div class="dantai-dl-row">
            <span class="dantai-dl-dt">TEL</span>
            <span class="dantai-dl-dd">06-6292-7791（代）</span>
          </div>
          <div class="dantai-dl-row">
            <span class="dantai-dl-dt">FAX</span>
            <span class="dantai-dl-dd">06-6292-7799</span>
          </div>
          <div class="dantai-dl-row">
            <span class="dantai-dl-dt">E-mail</span>
            <span class="dantai-dl-dd">info@kric.co.jp</span>
          </div>
          <div class="dantai-dl-row">
            <span class="dantai-dl-dt">営業日</span>
            <span class="dantai-dl-dd">月〜金 午前10時〜午後4時</span>
          </div>
          <div class="dantai-dl-row">
            <span class="dantai-dl-dt">休業日</span>
            <span class="dantai-dl-dd">土曜・日曜・祝日・盆休（8月15日）<br>年末年始（12月29日〜1月4日）</span>
          </div>
        </div>
        <div class="dantai-photos">
          <div class="dantai-photo dantai-photo--top-left">
            <img src="<?php echo get_template_directory_uri(); ?>/images/fm_img1.png" alt="">
          </div>
          <div class="dantai-photo dantai-photo--top-right">
            <img src="<?php echo get_template_directory_uri(); ?>/images/fm_img2.png" alt="">
          </div>
          <p class="dantai-symbol-copy">会員シンボルマークは私たちの誇りであり、お客様への「信用と信頼」をお約束する"証し"でもあります。</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== 組織図 ========== -->
  <section class="page-sec page-sec--alt" id="orgchart">
    <h2 class="page-sec-heading">組織図</h2>
    <div class="dantai-inner">
      <div class="dantai-orgchart-wrap">
        <img src="<?php echo get_template_directory_uri(); ?>/images/formation.png" alt="組織図" class="dantai-orgchart-img">
      </div>
    </div>
  </section>

  <!-- ========== 社員および役員企業一覧 ========== -->
  <section class="page-sec" id="members">
    <h2 class="page-sec-heading">
      社員および役員企業一覧
      <span class="page-sec-heading-note">社員会社 17社 ★は役員会社</span>
    </h2>
    <div class="dantai-inner">
      <div class="dantai-members-inner">
        <p class="dantai-member-list">
          ●泉建設(株)　●関電不動産開発(株)★　●近鉄不動産(株)★　●京阪神興業(株)★<br>
          ●京阪電鉄不動産(株)★　●住商レジデンシャル(株)★　●住友林業ホームサービス(株)<br>
          ●但土(株)　●東洋不動産(株)★　●日鉄興和不動産(株)　●ハウジングサービス(株)<br>
          ●阪急阪神不動産(株)★　●みずほ信託銀行(株)★　●三井住友信託銀行(株)★<br>
          ●三菱地所ハウスネット(株)　●三菱UFJ信託銀行(株)★　●(株)りそな銀行★
        </p>
      </div>
    </div>
  </section>

  <!-- ========== 役員会社一覧 ========== -->
  <section class="page-sec page-sec--alt" id="officers">
    <h2 class="page-sec-heading">役員会社 22社（五十音順）</h2>
    <div class="dantai-inner">
      <div class="dantai-members-inner">
        <p class="dantai-members-sub">理事長会社</p>
        <p class="dantai-member-list">●(株)田村商会</p>

        <p class="dantai-members-sub">副理事長会社</p>
        <p class="dantai-member-list">●近鉄不動産(株)　●(株)甲子園商会　●丸順不動産(株)</p>

        <p class="dantai-members-sub">理事会社</p>
        <p class="dantai-member-list">
          ●(株)城戸産業　●(株)クレイド　●京阪神興業(株)　●京阪電鉄不動産(株)<br>
          ●(株)サンワコーポレーション　●(株)神英不動産　●新日本住建(株)　●(株)天マ屋<br>
          ●東洋不動産(株)　●阪急阪神不動産(株)　●みずほ信託銀行(株)　●三井住友信託銀行(株)<br>
          ●三井住友トラスト不動産(株)　●三菱UFJ信託銀行(株)　●(株)平安コーポレーション
        </p>

        <p class="dantai-members-sub">監事会社</p>
        <p class="dantai-member-list">●関電不動産開発(株)　●住商レジデンシャル(株)　●(株)りそな銀行</p>

        <p class="dantai-member-note">なお、会員各社名については、「会員紹介」をご覧ください。</p>
        <a href="<?php echo esc_url(home_url('/kaiin.html')); ?>" class="btn-dantai"><span class="btn-text">会員紹介ページ</span><span class="btn-sep">|</span><span class="btn-arrow">&#x25B6;&#xFE0E;</span></a>
      </div>
    </div>
  </section>

  <!-- ========== 50周年 YouTube バナー ========== -->
  <section class="page-sec dantai-yt-sec">
    <div class="dantai-inner">
      <p class="dantai-yt-lead">KRICは、令和元年（2019年）に創立50周年を迎えました。<br>これまでの歩みにつきましては、こちらよりご覧いただけます。</p>
      <a href="https://www.youtube.com/watch?si=bHc4ek4LS_5MQtdO&v=FpBD4AyTAAQ&feature=youtu.be" class="dantai-yt-banner" target="_blank" rel="noopener">
        <img src="<?php echo get_template_directory_uri(); ?>/images/fm_50th_anniversary.png" alt="KRIC創立50周年の歩み 公式YouTubeチャンネルへ" class="dantai-yt-img">
      </a>
    </div>
  </section>

  <!-- ========== KRICの歴史 ========== -->
  <section class="page-sec" id="history">
    <h2 class="page-sec-heading">KRICの歴史</h2>
    <div class="dantai-inner">
      <div class="dantai-history">

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和44年4月28日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">設立</p>
            <p class="dantai-hist-desc">昭和44年4月28日 設立総会　名称大阪不動産取引センター</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和44年6月10日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">業務開始</p>
            <p class="dantai-hist-desc">情報検索機（タナック）を導入し、業務開始</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和46年8月25日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">模写電送装置の開通</p>
            <p class="dantai-hist-desc">業界初の電送を開始</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和48年1月26日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">兵庫不動産取引センターの設立</p>
            <p class="dantai-hist-desc">神戸市に設立</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和50年1月16日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">単独受託制度の発足（略称マルタン）</p>
            <p class="dantai-hist-desc">約2年半に亘る検討を経て発足<br>（昭和57年5月20日媒介契約制度発足時に廃止）</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和51年11月25日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">コンピューターの導入</p>
            <p class="dantai-hist-desc">1年10ヵ月に亘る準備期間を経て、コンピューターによるオンライン化を図る（情報検索機タナックを廃止）</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和52年10月1日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">大阪・兵庫両センターの統合</p>
            <p class="dantai-hist-desc">同センターを統合　名称を関西不動産情報センターと改め、内部組織を改革<br>運営委員会、大阪部会、兵庫部会、京都部会、20の地区懇談会を設置</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和53年4月1日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">通信体系の大変換</p>
            <p class="dantai-hist-desc">昭和46年8月より約6年半に亘り実施してきた専用回線による模写電送方式を廃止、業界初の電話ファックス方式に転換、コンピューターと連動する設備体制を完了</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和46年5月9日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">事務所の移転</p>
            <p class="dantai-hist-desc">大阪市西区土佐堀1-1大同生命ビルに事務所を移転</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">昭和57年3月27日</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">青年部会の発足</p>
            <p class="dantai-hist-desc">センター会員の若い力を結集し発足、需要家に対し安心と便益を与えるため、「手付金の保証」等の制度化に取り組む</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成3年5月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">不動産売買契約特約文例集発刊</p>
            <p class="dantai-hist-desc">不動産売買契約特約文例集を発刊、全会員に配布</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成5年9月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">「重要事項説明書作成の手引き」発刊</p>
            <p class="dantai-hist-desc">「重要事項説明書作成の手引き」を発刊、全会員に配布</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成7年11月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">会員同好会の「クラブ活動」を発足</p>
            <p class="dantai-hist-desc">会員同好会のクラブ活動として軽音楽クラブ、映画クラブ、釣りクラブ、囲碁・将棋クラブが発足</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成9年2月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">インターネットホームページを開設</p>
            <p class="dantai-hist-desc">インターネットによるKRICホームページを開設、全会員の営業概要、店舗写真、案内図等の紹介を開始</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成9年9月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">KRIC標準重要事項説明書雛形等を発刊</p>
            <p class="dantai-hist-desc">重要事項説明書雛形、同補足資料等が完成、印刷刊行物とCD-ROMを会員に配布</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成10年1月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">KRIC IISの運営開始</p>
            <p class="dantai-hist-desc">新KRICシステムをバージョンアップしたKRIC IIS（インターネットインフォメーションサービス）システムを開発、インターネットホームページによる物件情報の一般ユーザー向け公開を開始</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成17年9月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">「KRICかわら版」創刊</p>
            <p class="dantai-hist-desc">会員間情報共有のため、「KRICかわら版」の発刊を開始</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">平成29年12月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">KRICパートナーズ創設</p>
            <p class="dantai-hist-desc">宅建業者以外の協力企業との連携強化のため「KRICパートナーズ」を新設</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">令和元年11月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">50周年記念式典</p>
            <p class="dantai-hist-desc">ヒルトン大阪にて50周年記念式典を開催</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">令和4年10月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">KRICラジオ番組放送開始</p>
            <p class="dantai-hist-desc">MBSラジオにて毎週月曜日KRICラジオ番組を放送開始<br>令和4年11月にMBSラジオ秋まつりにて初の公開収録を実施</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">令和5年11月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">MBSラジオ秋まつり ブース出展</p>
            <p class="dantai-hist-desc">長居公園で開催のMBSラジオ秋まつりにて、KRICブースを出展</p>
          </div>
        </div>

        <div class="dantai-history-item">
          <span class="dantai-hist-date">令和8年9月</span>
          <div class="dantai-hist-body">
            <p class="dantai-hist-title">KRICホームページのリニューアル</p>
            <p class="dantai-hist-desc">一般公開サイトと会員専用ページを合わせて全面リニューアル</p>
          </div>
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

<?php get_footer(); ?>
