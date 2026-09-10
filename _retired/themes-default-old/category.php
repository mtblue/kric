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
        <div class="txt_h2"> KRICからお伝えしたいこと </div>
      </div>
    </div>
    <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
    <div class="tell_box">
      <p class="tell_left">
        <?php the_time('Y.m.d') ?>
      </p>
      <p class="tell_right"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'kubrick'), the_title_attribute('echo=0')); ?>">
        <?php the_title(); ?>
        </a></p>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
    <?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>
    <?php //include( TEMPLATEPATH . '/cont_underbox.php' ); ?>
  </div>
  <!--cont_main-->

  <?php get_sidebar('tell'); ?>

</div>
<!--cont_box--> 

<!--==================▲ここまでコンテンツ=======================-->
<?php include( TEMPLATEPATH . '/footer.php' ); ?>
