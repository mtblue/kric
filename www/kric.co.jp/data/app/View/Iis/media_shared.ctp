  <!-- ========== メインコンテンツ ========== -->
  <main class="mem-main">
    <div class="mem-card-wrap">
      <div class="mem-card">

        <?php
          $channel = isset($tpl_disp['channel']) ? $tpl_disp['channel'] : 'mbs1';
          $groupList = isset($tpl_disp['group_list']) ? $tpl_disp['group_list'] : array();
          $currentGroupId = isset($tpl_disp['current_group_id']) ? $tpl_disp['current_group_id'] : '';
          $actionBase = APP_NAME . '/media_' . $channel;

          $tabs = array(
            'mbs1'    => array('label' => '【MBSラジオ】', 'name' => '村瀬哲史とKRIC！<br>不動産の時間'),
            'mbs2'    => array('label' => '【MBSラジオ】', 'name' => '予備校講師<br>村瀬哲史のナニワ地理学'),
            'youtube' => array('label' => '【YouTube】',   'name' => 'ナニワの地理学<br>スピンオフ'),
          );
        ?>

        <p class="mem-breadcrumb mem-breadcrumb--pc"><a href="/">関西不動産情報センターTOP</a> &gt; <a href="/iis/index">会員専用ページ</a> &gt; KRICメディア</p>

        <div class="kric-media-head">
          <h2 class="mem-sec-title">─ KRICメディア ─</h2>
          <a href="https://www.facebook.com/p/%E9%96%A2%E8%A5%BF%E4%B8%8D%E5%8B%95%E7%94%A3%E6%83%85%E5%A0%B1%E3%82%BB%E3%83%B3%E3%82%BF%E3%83%BC-100064836692406/?locale=ja_JP" target="_blank" rel="noopener" class="kric-media-fb-btn">
            <span class="btn-text">Facebook</span>
            <span class="btn-sep"></span>
            <img src="/wp-content/themes/default/images/link.png" alt="" class="kric-media-fb-icon">
          </a>
        </div>

        <div class="kric-media-tabs">

          <?php foreach ($tabs as $ch => $tab): ?>
          <a href="/<?php echo APP_NAME; ?>/media_<?php echo $ch; ?>" class="kric-media-tab<?php echo ($channel === $ch) ? ' is-active' : ''; ?>">
            <span class="kric-media-tab-body">
              <span class="kric-media-tab-label"><?php echo $tab['label']; ?></span>
              <span class="kric-media-tab-name"><?php echo $tab['name']; ?></span>
            </span>
            <span class="kric-media-tab-arr">▼</span>
          </a>

          <?php if ($channel === $ch && !empty($groupList)): ?>
          <div class="kric-media-subtabs">
            <?php foreach ($groupList as $group): ?>
            <?php $isActive = ($currentGroupId !== '' && $currentGroupId == $group['range_label']); ?>
            <a href="/<?php echo APP_NAME; ?>/media_<?php echo $channel; ?>/group=<?php echo rawurlencode($group['range_label']); ?>" class="kric-media-subtab<?php echo $isActive ? ' is-active' : ''; ?>">
              <span class="kric-media-subtab-range"><?php echo h($group['range_label']); ?></span>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php endforeach; ?>

        </div>

        <?php if (!empty($tpl_disp['main_list'])): ?>
        <div class="kric-media-grid">
          <?php foreach ($tpl_disp['main_list'] as $item): ?>
          <?php $imgSrc = (isset($item['image_url']) && $item['image_url'] != '') ? h($item['image_url']) : ''; ?>
          <a href="<?php echo h($item['url']); ?>" target="_blank" rel="noopener" class="kric-media-card">
            <?php if ($imgSrc != ''): ?>
            <img src="<?php echo $imgSrc; ?>" alt="<?php echo h($item['post_title']); ?>" class="kric-media-card-img">
            <?php else: ?>
            <span class="kric-media-card-img kric-media-card-img--noimg"></span>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>

        <hr class="mem-divider mem-divider--no-bottom">

        <?php if (!empty($tpl_disp['navi']['direct_navi'])): ?>
        <nav class="mem-pagination" aria-label="ページ">
          <?php echo $tpl_disp['navi']['navigator_before']; ?>
          <div class="mem-pg-pages">
            <?php echo $tpl_disp['navi']['direct_navi_first']; ?>
            <?php echo $tpl_disp['navi']['direct_navi']; ?>
            <?php echo $tpl_disp['navi']['direct_navi_last']; ?>
          </div>
          <?php echo $tpl_disp['navi']['navigator_next']; ?>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p style="text-align:center; margin:60px 0;">コンテンツはありません。</p>
        <?php endif; ?>

      </div>
    </div>
  </main>
