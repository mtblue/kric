<?php get_header(); ?>
<div id="titleArea">
	<h1><a href="/"><img src="/wp-content/themes/default/images/site_title1.png" alt="関西不動産情報センター、KRIC"></a></h1>
	<p class="pride"><img src="/wp-content/themes/default/images/site_title2.png" alt="PRIDE OF MEMBERSHIP 競い合うことよりも、響き合うこと。それが、メンバーシップの誇り。"></p>
</div>

<div class="cont_wrap">
	
<div class="cont_box">
  <div class="cont_main">
    <div class="txt_h2_bg">
      <div class="txt_h2_in">
        <h2>
          <div class="txt_h2">
            <?php
         $parents_title = get_the_title($post->post_parent);
         echo $parents_title;
?>
          </div>
        </h2>
      </div>
    </div>
    <?php if(is_page('kric-media')): ?>
		<?php if(isset($_GET['k'])){
						if($_GET['k']=='1' || $_GET['k']==''){ ?>
		<script>
			$(function() {
				$url = "https://kric.co.jp/member/login_status";
				$.getJSON($url , function(data) {
				    //console.log(data);
				    //alert(data.member_id + data.login_id);
				    //$('#lists').text(data.login_id);
				    const lists = data.login_id;
				    //$('#lists').text(lists);
				    window.lists = lists;
				    if (lists){
						$('.YouTubeFALSE').remove();
				    }else{
						$('.YouTubeTRUE').remove();
				    }
		  		});
			});
		</script>
		<?php } } ?>

    	<div>
			<ul class="media-list">
			<?php 
			if ($_GET['d'] == ''){
				//$ov1 = date("Ymd", strtotime(date('Ymd') . "-6 month"));
				//$ov1 = date("20241001", strtotime(date('Ymd') . "-6 month"));
				$ov1 = date("20251101", strtotime(date('Ymd') . "-6 month"));
			}else{
				//$aa = str_split($_GET["d"], 2);
				//ov1 = '20'.$aa[0].'-'.$aa[1].'-'.$aa[2];
				$ov1 ='20'.$_GET["d"];
			}
			if($_GET['d']=='240701'){
				$ov2 = date("Ymd", strtotime($ov1 . "+3 month"));
			}elseif($_GET['d']=='250401'){
				$ov2 = date("Ymd", strtotime($ov1 . "+6 month"));
			}else{
				$ov2 = date("Ymd", strtotime($ov1 . "+6 month"));
			}

			if(isset($_GET['k'])){
				if($_GET['k']=='1' || $_GET['k']==''){
					//$k = 'MBSラジオ 村瀬哲史とKRIC！不動産の時間';
					$k = '';
					echo "<div class=\"txt_h3_bg\">\n";
					echo "<div class=\"txt_h3_in\">\n";
					echo "<div class=\"txt_h3\"> MBSラジオ 村瀬哲史とKRIC！不動産の時間 </div>\n";
					echo "</div>\n";
					echo "</div>\n";
				}elseif($_GET['k']=='2') {
					$k = 'MBSラジオ 予備校講師 村瀬哲史のナニワ地理学';
					echo "<div class=\"txt_h3_bg\">";
					echo "<div class=\"txt_h3_in\">\n";
					echo "<div class=\"txt_h3\"> MBSラジオ 予備校講師 村瀬哲史のナニワ地理学 </div>\n";
					echo "</div>\n";
					echo "</div>\n";
				}elseif($_GET['k']=='3') {
					$ov1 = '20241025';
					$k = 'YouTube ナニワの地理学スピンオフ';
					echo "<div class=\"txt_h3_bg\">";
					echo "<div class=\"txt_h3_in\">\n";
					echo "<div class=\"txt_h3\"> YouTube ナニワの地理学スピンオフ </div>\n";
					echo "</div>\n";
					echo "</div>\n";
				}
			}else{
				$k = '';
			}
			//echo $ov1;
			//echo $ov2;
			//date('Y-m-d', strtotime('+6 month'));
			//$ov2 = $_GET["ov2"];
			if($k ==''){
				$args = array(
					'offset' => '0',
					'post_type' => 'kric-media',
					'meta_key' => 'orderView',
					'orderby' => 'meta_value',
					'posts_per_page' => '100',
					'order' => 'DESC',
					'meta_query' => array(
				    	'relation' => 'AND',
				    	array(
				      		'key' => 'orderView',
				      		'value' => $ov1,
				      		'compare' => '>=',
				    	),
				    	array(
				      		'key' => 'orderView',
				      		'value' => $ov2,
				      		'compare' => '<',
				    	)
					)
				);
			}else{
				$args = array(
					'offset' => '0',
					'post_type' => 'kric-media',
					'meta_key' => 'orderView',
					'orderby' => 'meta_value',
					'posts_per_page' => '100',
					'order' => 'DESC',
					'meta_query' => array(
				    	'relation' => 'AND',
				    	array(
				      		'key' => 'orderView',
				      		'value' => $ov1,
				      		'compare' => '>=',
				    	),
				    	array(
				      		'key' => 'orderView',
				      		'value' => $ov2,
				      		'compare' => '<',
				    	),
				    	array(
				      		'key' => 'kind',
				      		'value' => $k,
				    	)
					)
				);
			}
			$medias = get_posts($args);
			  //$radios = get_posts('&offset=0 & post_type=kric-radio & meta_key=orderView & orderby=meta_value & posts_per_page=100 & order=DESC');
			  $i=0;
			  foreach($medias as $post){
			  	//echo $post->ID;
			  	$GeneralURL = get_field('GeneralURL', $post->ID);
			  	$YouTubeURL = get_field('YouTubeURL', $post->ID);
			  	$topView = get_field('topView', $post->ID);
			  	$mediaimg   = get_field('mediaimg', $post->ID);

			  	$i++;
			  	//echo "123:$i:".$YouTubeURL;
				//if($topView[0] == 'on' and $GeneralURL){
				//	echo '<li><a href="' . $GeneralURL .'" ';
				//	echo ' target="_blank">';
				//	echo '<img src="' . $radioimg.'" ';
				//	echo ' alt="" style="height:150px;" /></a></li>';
				//}elseif($YouTubeURL){
				if($YouTubeURL){
					echo '<li class="YouTubeTRUE">';
					echo '<a href="https://www.youtube.com/watch?v=' . $YouTubeURL .'" target="_blank">';
					echo '<img src="http://img.youtube.com/vi/' . $YouTubeURL .'/mqdefault.jpg" alt="" style="height:150px;" />';
					echo '</a>';
					echo '</li>';

					if(isset($_GET['k'])){
						if($_GET['k']=='1' || $_GET['k']==''){
							echo '<li class="YouTubeFALSE">';
							echo '<img src="http://img.youtube.com/vi/' . $YouTubeURL .'/mqdefault.jpg" alt="" style="height:150px;" />';
							echo '<img src="/images/radio/key.png" alt="" style="height:150px;" />';
							echo '</li>';
						}
					}
				}elseif($GeneralURL){
			?>
			  	  <li><a href="<?php the_field('GeneralURL', $post->ID);?>" target="_blank"><img src="<?php the_field('mediaimg', $post->ID);?>" alt="" /></a></li>
			<?php }else{ ?>
			  	  <li><img src="<?php the_field('mediaimg', $post->ID);?>" alt="" /></li>
			<?php }	} ?>

			</ul>
				<div style="clear:both;"></div>
				<div style="margin: 20px 58px 20px 56px;">
					<!--
					<a href="/kric-media.html?k=2" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第1回～ (2024/10/07 ～ )</a>
					-->
					<?php if($_GET['k'] == '1'){ ?>
					<a href="/kric-media.html?k=1&d=240701" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第92回～第105回 (2024/07/01 ～ 2024/9/30)</a>
					<a href="/kric-media.html?k=1&d=240101" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第66回～第91回 (2024/01/06 ～ 2024/06/24)</a>
					<a href="/kric-media.html?k=1&d=230701" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第40回～第65回 (2023/07/03 ～ 2023/12/25)</a>
					<a href="/kric-media.html?k=1&d=230101" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第14回～第39回 (2023/01/02 ～ 2023/06/26)</a>
					<a href="/kric-media.html?k=1&d=220701" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第1回～第13回 (2022/10/03 ～ 2022/12/26)</a>
					<?php }elseif($_GET['k'] == '2'){ ?>
					<a href="/kric-media.html?k=2&d=251001" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第37回～ (2025/10/06 ～)</a>
					<a href="/kric-media.html?k=2&d=250401" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第19回～第36回 (2025/04/07 ～2025/09/15)</a>
					<a href="/kric-media.html?k=2&d=241001" style="display: block; padding: 10px; background: #785d30; color: #fff; text-align: center; margin-top: 10px;">第1回～第18回 (2024/10/14 ～ 2025/03/17)</a>

					<?php } ?>
					</div>
    	</div>


    <?php endif; ?>
    <?php if(is_page('contact')): ?>
    <?php if($_POST['inqirymode']=='input'): ?>
    <!--//確認画面での処理を書く-->
    <div class="txt_h3_bg">
      <div class="txt_h3_in">
        <div class="txt_h3"> お電話でのお問合せはこちらから </div>
      </div>
    </div>
    <div class="mgBottom10"></div>
    <img src="/wp-content/themes/default/images/contact_img_01.png" width="710" height="100" alt="お電話でのお問合せはこちらから" class="pc" />
    <img src="/wp-content/themes/default/images/contact_sp.png" alt="お気軽にお問合せ下さい" class="sp">
    <div class="mgBottom50"></div>
    <div class="txt_h3_bg">
      <div class="txt_h3_in">
        <div class="txt_h3"> メールでのお問合せはこちらから </div>
      </div>
    </div>
    <?php elseif($_POST['inquirymode']=='confirm'): ?>
    <!--//送信完了画面での処理を書く-->
    <div class="txt_h3_bg">
      <div class="txt_h3_in">
        <div class="txt_h3"> お電話でのお問合せはこちらから </div>
      </div>
    </div>
    <div class="mgBottom10"></div>
    <img src="/wp-content/themes/default/images/contact_img_01.png" width="710" height="100" alt="お電話でのお問合せはこちらから" class="pc" />
    <img src="/wp-content/themes/default/images/contact_sp.png" alt="お気軽にお問合せ下さい" class="sp">
    <div class="mgBottom50"></div>
    <div class="txt_h3_bg">
      <div class="txt_h3_in">
        <div class="txt_h3"> メールでのお問合せはこちらから </div>
      </div>
    </div>
    <?php else: ?>
    <!--//初期入力画面での処理を書く-->
    <div class="txt_h3_bg">
      <div class="txt_h3_in">
        <div class="txt_h3"> お電話でのお問合せはこちらから </div>
      </div>
    </div>
    <img src="/wp-content/themes/default/images/contact_img_01.png" width="710" height="100" alt="お電話でのお問合せはこちらから" class="pc" />
    <img src="/wp-content/themes/default/images/contact_sp.png" alt="お気軽にお問合せ下さい" class="sp">
    <div class="mgBottom50"></div>
    <div class="txt_h3_bg">
      <div class="txt_h3_in">
        <div class="txt_h3"> メールでのお問合せはこちらから </div>
      </div>
    </div>
    <p>下記にお問合せ内容をご入力の上、<span class="contact_bold">［確認画面］</span>ボタンを押してください。<br />
      お問合せ完了後、確認メールが届かない場合は、念のため迷惑メールフォルダをご確認ください。</p>
    <p>※マークのついた項目は必須項目となっております。ご入力漏れのないようお願い致します。<br />
      お問合せいただいた個人情報の取扱いに関しては<a href="/privacy.html">「プライバシーポリシー」</a>のページをご確認ください。</p>
    <?php endif; ?>
    <?php endif; ?>
    <!--edit-->
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php echo post_custom('page-contents'); ?>
    <?php if (wp_list_pages("child_of=".$post->ID."&echo=0")) { ?>
    <?php } ?>
    <?php the_content(__('» 続きを詳しく読む')); ?>
    <?php wp_link_pages(); ?>
    <?php endwhile; endif; ?>
    <!--edit end-->
    <?php //include( TEMPLATEPATH . '/cont_underbox.php' ); ?>
    </div>
  <!--cont_main-->
  
  <?php get_sidebar(); ?>
</div>
<!--cont_box--> 

<!--==================▲ここまでコンテンツ=======================-->
<?php include( TEMPLATEPATH . '/footer.php' ); ?>
