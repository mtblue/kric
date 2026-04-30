<div class="cont_sidebox">
  <?php $root_slug = ps_get_root_page( $post ); ?>
  <?php $root_slug = $root_slug->post_name; ?>
  <?php if($root_slug == 'kric' or $root_slug == 'member'): ?>
	  <div class="side_cap_bg">
	    <div class="side_cap_in">
	      <div class="side_cap_h2"> KRICについて </div>
	    </div>
	  </div>
	  <div id="SideNavi">
	    <?php wp_nav_menu( array('menu' => 'side-navi1')); ?>
	  </div>
	  <div class="mgBottom20"></div>
  <?php elseif($root_slug == 'kric-media'): ?>
	  <div class="side_cap_bg">
	    <div class="side_cap_in">
	      <div class="side_cap_h2"> KRICメディア </div>
	    </div>
	  </div>
	  <div id="SideNavi">
		<div class="menu-side-navi-container">
			<ul id="menu-side-media" class="menu">
				<li class="menu-item1"><a href="kric-media.html">KRICメディアTOP</a></li>
				<li class="menu-item2"><a href="kric-media.html?d=240701&k=1">MBSラジオ<br />村瀬哲史とKRIC！不動産の時間</a></li>
				<li class="menu-item2"><a href="kric-media.html?k=2">MBSラジオ 予備校講師<br />村瀬哲史のナニワ地理学</a></li>
				<li class="menu-item2"><a href="kric-media.html?k=3">YouTube<br />ナニワの地理学スピンオフ</a></li>
				<li class="menu-item1"><a href="https://www.facebook.com/p/%E9%96%A2%E8%A5%BF%E4%B8%8D%E5%8B%95%E7%94%A3%E6%83%85%E5%A0%B1%E3%82%BB%E3%83%B3%E3%82%BF%E3%83%BC-100064836692406/?locale=ja_JP" target="_blank">Facebook</a></li>
			</ul>
		</div>
	    <?php //wp_nav_menu( array('menu' => 'side-navi1')); ?>
	  </div>
	  <div class="mgBottom20"></div>
  <?php elseif($root_slug == 'activity'): ?>
	  <div class="side_cap_bg">
	    <div class="side_cap_in">
	      <div class="side_cap_h2"> 私たちの活動 </div>
	    </div>
	  </div>
	  <div id="SideNavi">
	    <?php wp_nav_menu( array('menu' => 'side-navi2')); ?>
	  </div>
	  <div class="mgBottom20"></div>
  <?php endif; ?>
  <?php include( TEMPLATEPATH . '/sd_gnavi.php' ); ?>
</div>
<!--cont_sidebox--> 
