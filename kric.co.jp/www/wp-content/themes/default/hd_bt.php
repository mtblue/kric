<!--==================================▼ここからぱんくずとSNSボタン=========================================-->

<div class="pankuzu_wrap">
  <div class="pan_sns">
    <div class="pankuzu"> <img src="<?php bloginfo('stylesheet_directory'); ?>/images/index_img_05.png" width="12" height="12" alt="" class="img_float" />
      <?php
if(function_exists('bcn_display'))
{
// Display the breadcrumb
echo '<h1>';
bcn_display();
echo '</h1>';
}
?>
    </div>
    <div class="sns">
      <div class="tweet"> 
        <!--twitter--> 
        <a href="https://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-lang="ja">ツイートする</a> 
        <script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script> 
      </div>
      <div class="google_plus">
        <!--google＋-->
        <g:plusone size="medium" width="50px" href="https://kric.co.jp/"></g:plusone>
      </div>
      <div class="facebook">
        <!--facebook-->
        <iframe src="https://www.facebook.com/widgets/like.php?href=https%3A%2F%2Fkric.co.jp/&amp;layout=button_count" scrolling="no" frameborder="0" style="border:none; width:120px; height:21px;" allowtransparency="true"></iframe>
      </div>
    </div>
  </div>
</div>
<!--==================================▲ここまでぱんくずとsnsボタン=========================================-->
<div class="mgBottom20"></div>
