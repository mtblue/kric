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
    <?php if(is_page('kric-radio')): ?>
    	<div>
			<ul class="radio-list">
			<?php 
			  $radios = get_posts('&offset=0 & post_type=kric-radio & meta_key=orderView & orderby=meta_value & posts_per_page=100 & order=DESC');
			  $i=0;
			  foreach($radios as $post){
			  	//echo $post->ID;
			  	$GeneralURL = get_field('GeneralURL', $post->ID);
			  	$YouTubeURL = get_field('YouTubeURL', $post->ID);
			  	$topView = get_field('topView', $post->ID);
			  	$radioimg   = get_field('radioimg', $post->ID);
			  	$i++;
			  	//echo "123:$i:".$YouTubeURL;
				//if($topView[0] == 'on' and $GeneralURL){
				//	echo '<li><a href="' . $GeneralURL .'" ';
				//	echo ' target="_blank">';
				//	echo '<img src="' . $radioimg.'" ';
				//	echo ' alt="" style="height:150px;" /></a></li>';
				//}elseif($YouTubeURL){
				if($YouTubeURL){
					echo '<li><a href="https://www.youtube.com/watch?v=' . $YouTubeURL .'" ';
					echo ' target="_blank">';
					echo '<img src="http://img.youtube.com/vi/' . $YouTubeURL .'/mqdefault.jpg" ';
					echo ' alt="" style="height:150px;" /></a></li>';
				}elseif($GeneralURL){
			?>
			  	  <li><a href="<?php the_field('GeneralURL', $post->ID);?>" target="_blank"><img src="<?php the_field('radioimg', $post->ID);?>" alt="" /></a></li>
			<?php }else{ ?>
			  	  <li><img src="<?php the_field('radioimg', $post->ID);?>" alt="" /></li>
			<?php }	} ?>

			</ul>
				<div style="clear:both;"></div>
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
