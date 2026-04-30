<?php
/*
Template Name:ホーム
*/
?>
<?php get_header(); ?>
<div id="mainArea">
	<section id="main">
		<div id="titleArea">
			<h1><a href="https://kric.co.jp"><img src="/wp-content/themes/default/images/site_title1.png" alt="関西不動産情報センター、KRIC"></a></h1>
			<p class="pride"><img src="/wp-content/themes/default/images/site_title2.png" alt="PRIDE OF MEMBERSHIP 競い合うことよりも、響き合うこと。それが、メンバーシップの誇り。"></p>
		</div>
		<figure><img class="swp" src="/wp-content/themes/default/images/main_pc.jpg" alt="人×想い× 絆 私たちだから、できることがある。"></figure>
		<div id="mediaArea">
			<h2><img src="/wp-content/themes/default/images/ico_kric-media.png" alt="KRIC メディア"></h2>
			<ul class="slider">
<?php 
  $medias = get_posts('&offset=0 & post_type=kric-media & meta_key=orderView & orderby=meta_value & posts_per_page=200 & order=ASC');
  $stack = array();
  foreach($medias as $post){
  	//echo $post->ID;
  	$topView = get_field('topView', $post->ID);
  	$GeneralURL = get_field('GeneralURL', $post->ID);
  	$YouTubeURL = get_field('YouTubeURL', $post->ID);
  	$mediaimg   = get_field('mediaimg', $post->ID);
	if($topView and $GeneralURL){
		  	echo "<li><div>";
			echo "<a href=". $GeneralURL ." target='_blank'>";
		  	echo "<img src='";
		  	echo the_field('mediaimg', $post->ID) . "' alt='' />";
		  	echo "</a>";
		 	echo "</div></li>";
	}else{
		if($topView){
		  	if($YouTubeURL){
			echo '<li><div><a href="https://www.youtube.com/watch?v=' . $YouTubeURL .'" ';
			echo ' target="_blank">';
			echo '<img src="http://img.youtube.com/vi/' . $YouTubeURL .'/mqdefault.jpg" ';
			echo ' alt="" style="height:150px;" /></a></li>';
			}else{
			  	echo "<li><div>";
			  	if($GeneralURL){

					echo "<a href=". $GeneralURL ." target='_blank'>";
				}
			  	echo "<img src='";
			  	echo the_field('mediaimg', $post->ID) . "' alt='' />";
			  	echo "</a>";
			 	echo "</div></li>";
			}
		}else{
	}}} ?>
			</ul>

			<script>
				$(window).on('load',function(){
					var slide_list = $('.slider').slick({
						autoplay: false,
						autoplaySpeed: 4000,
						speed: 1000,
						centerMode: true,
						centerPadding: '20px',
						slidesToShow: 3,
						infinite: true,
						arrows: true,
						prevArrow: '<button class="slide-arrow prev-arrow"></button>',
						nextArrow: '<button class="slide-arrow next-arrow"></button>',
						responsive: [{
						breakpoint: 1000,
						settings: {
							slidesToShow: 3
							}
						},{
						breakpoint: 640,
						settings: {
							slidesToShow: 2
							}
						},{
						breakpoint: 480,
						settings: {
							slidesToShow: 1
							}
						}]
					});
					$(window).on('resize',function() {
						slide_list.slick('setPosition');
					});
				});
			</script>
			<a href="/kric-media.html" class="link_list">一覧はこちら</a>
		</div>
	<!-- /#main --></section>
<!-- /#mainArea --></div>

<div id="overviewArea">
	<section id="overview">
		
		<article>
			<div class="ttl">
				<h2><div><img src="/wp-content/themes/default/images/about_ttl.png" alt="KRICについて｜ABOUT"></div><a href="/kric.html" class="link_list sp">一覧はこちら</a></h2>
				<p>「関西不動産情報センター」を英文で「Kansai Real estate Information Center」と表示し、略称をKRIC＝クリックと称します。</p>
				<a href="/kric.html" class="link_list pc">一覧はこちら</a>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/about1_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">KRICの歴史</p>
				<a href="/kric/history.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item rv">
				<figure><img class="swp" src="/wp-content/themes/default/images/about2_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">設立の⽬的と<br class="sp">社会的使命</p>
				<a href="/kric/object.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/about3_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">組織図</p>
				<a href="kric/formation.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
		</article>

		<article>
			<div class="ttl">
				<h2><div><img src="/wp-content/themes/default/images/active_ttl.png" alt="私たちの活動｜ACTIVE"></div><a href="/activity/communication.html" class="link_list sp">一覧はこちら</a></h2>
				<p>会員をサポートするために様々な活動が実施されております。</p>
				<a href="/activity/communication.html" class="link_list pc">一覧はこちら</a>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/active1_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">人から始まる<br class="sp">情報交換</p>
				<a href="/activity/communication.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item rv">
				<figure><img class="swp" src="/wp-content/themes/default/images/active2_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">想いをかたちにする<br class="sp">研修会</p>
				<a href="/activity/training.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/active3_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">絆を深める<br class="sp">親睦活動</p>
				<a href="/activity/friendship.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
		</article>

		<article>
			<div class="ttl">
				<h2><div><img src="/wp-content/themes/default/images/member_ttl.png" alt="会員紹介｜MEMBER"></div><a href="/member/" class="link_list sp">一覧はこちら</a></h2>
				<p>会員名、エリア、業務種目から目的の不動産会社を検索することができます。</p>
				<a href="/member/" class="link_list pc">一覧はこちら</a>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/member1_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">会員名で検索<br class="sp"><span>(五十音検索)</span></p>
				<a href="/member/?Mode=list-name&Name=a" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item rv">
				<figure><img class="swp" src="/wp-content/themes/default/images/member2_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">エリアで検索</p>
				<a href="/member/?Mode=list-area&Area=100" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/member3_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">業務種目で検索</p>
				<a href="/member/?Mode=list-industry&Category=100" class="link_detail">詳細はこちら</a>
				</div>
			</div>
		</article>

		<article>
			<div class="ttl">
				<h2><div><img src="/wp-content/themes/default/images/guidance_ttl.png" alt="入会をお考えの方｜GUIDANCE"></div><a href="/enter.html" class="link_list sp">一覧はこちら</a></h2>
				<a href="/enter.html" class="link_list pc">一覧はこちら</a>
			</div>
			<div class="item">
				<figure><img class="swp" src="/wp-content/themes/default/images/guidance1_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">入会のご案内</p>
				<a href="/enter/guide.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
			<div class="item rv">
				<figure><img class="swp" src="/wp-content/themes/default/images/guidance2_pc.jpg" alt="image photo"></figure>
				<div class="cnt">
				<p class="ttl_sub">会員のメリット</p>
				<a href="/enter.html" class="link_detail">詳細はこちら</a>
				</div>
			</div>
		</article>
		
	<!-- /#overview --></section>
<!-- /#overviewArea --></div>

<div id="serviceArea">
	<section id="service">
		
		<h2>KRICがあなたにできること</h2>
		
		<p>関西不動産情報センター（略称：KRIC）は、会員の利益に資する活動のみならず、これまで以上に地域社会との連携を図り、不動産に関わる様々な支援活動に積極的に取り組んでいこう、という目標を掲げました。そこで、あなたのためにKRICができることをご紹介させていただきます。</p>
		
		<a href="/service.html" class="link_list">一覧はこちら</a>	
		
	<!-- /#service --></section>
<!-- /#serviceArea --></div>

<div id="infoArea">
	<section id="info">
		
		<div class="title">
			<h2>KRICからお伝えしたいこと</h2>
			<a href="/archives/category/tell" class="link_list">一覧はこちら</a>
		</div>
		<ul>
        <?php
 $lastposts = get_posts('numberposts=10&offset=0 & category_name=tell');
 foreach($lastposts as $post) :
 setup_postdata($post);
 ?>
			<li><span class="date"><?php the_time('Y.m.d') ?></span><a href="<?php the_permalink(); ?>" id="post-<?php the_ID(); ?>"><p class="tell_right"><?php echo mb_substr($post->post_title, 0, 60); ?>… </p></a></li>
      <?php endforeach; ?>
		</ul>
		
	<!-- /#info --></section>
<!-- /#infoArea --></div>


<!--==================▲ここまでコンテンツ=======================-->
<?php include( TEMPLATEPATH . '/footer.php' ); ?>
