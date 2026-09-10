<?php get_header(); ?>

<div id="bg_body">
  <?php include( TEMPLATEPATH . '/headerNavi.php' ); ?>
  <div id="wp">
    <div class="concept_h2"> <img src="<?php bloginfo('stylesheet_directory'); ?>/images/works_h2_img.png" width="30" height="22" alt="" />
      <h2>
        <?php wp_title(''); ?>
      </h2>
    </div>
    <?php
if(function_exists('bcn_display'))
{
// Display the breadcrumb
echo '<div id="pankuzu" align="right"><h1>';
bcn_display();
echo '</h1></div><div class="mgBottom45"></div>';
}
?>
    <div id="search">
      <div class="post">
        <p>
          <?php if (have_posts()) : ?>
          お探しのキーワード '
          <?php the_search_query(); ?>
          ' で以下の通り該当ありました
        <ul>
          <?php while (have_posts()) : the_post(); ?>
          <li><a href="<?php the_permalink() ?>" class="title">
            <?php the_title(); ?>
            </a> </li>
          <?php endwhile; ?>
        </ul>
        <p>
          <?php previous_posts_link('&laquo; 前の項目へ') ?>
          <?php next_posts_link('次の項目へ &raquo;') ?>
        </p>
        <?php else : ?>
        お探しのキーワード '
        <?php the_search_query(); ?>
        ' では該当ありませんでした
        <?php endif; ?>
        </p>
      </div>
    </div>
    <div class="mgBottom40 clear"></div>
    <a href="<?php bloginfo('url'); ?>/contact.html"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/index_link_03.jpg" width="960" height="40" alt="医院･クリニックの設計に関するご相談を受付中。" class="css-hover" /></a> </div>
</div>
<?php include( TEMPLATEPATH . '/footer.php' ); ?>
