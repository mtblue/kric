<?php
    $ua = $_SERVER['HTTP_USER_AGENT'];

    if (!(preg_match("/\/Windows/", $ua) && preg_match("/\/MSIE 6/", $ua))) {
         echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    }
?>

<?php
//if(is_page('member')) {
//	header("Location: /member/");
//}elseif(is_page('member_search_name')){
//	header("Location: /member/?Mode=list-name&Name=a");
//}elseif(is_page('member_search_area')){
//	header("Location: /member/?Mode=list-area&Area=100");
//}elseif(is_page('member_search_work')){
//	header("Location: /member/?Mode=list-industry&Category=100");
//}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<title><?php if (function_exists('seo_title_tag')) { seo_title_tag(); } else { bloginfo('name'); wp_title();} ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/css/format.css?<?php echo time(); ?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/css/common.css?<?php echo time(); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="stylesheet" href="/wp-content/themes/default/css/ress.css?<?php echo time(); ?>">
<link rel="stylesheet" href="/wp-content/themes/default/css/base.css?<?php echo time(); ?>">
<link rel="stylesheet" href="/wp-content/themes/default/css/top.css?<?php echo time(); ?>">
<link rel="stylesheet" href="/wp-content/themes/default/css/slick.css?<?php echo time(); ?>">
<link rel="stylesheet" href="/wp-content/themes/default/css/contents.css?<?php echo time(); ?>">
<link rel="pingback" href="<?php bloginfo('pingback_url') ?>" />
<?php wp_head(); ?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46389103-1', 'kric.co.jp');
  ga('send', 'pageview');

</script>


<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-TK4JNG0SP8"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-TK4JNG0SP8');

</script>
<script src="http://f1.nakanohito.jp/lit/index.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">try { var lb = new Vesicomyid.Bivalves("125941"); lb.init(); } catch(err) {} </script>

<script src="/wp-content/themes/default/js/jquery-3.6.0.min.js"></script>
<script src="/wp-content/themes/default/js/imageMapResizer.min.js"></script>
<script src="/wp-content/themes/default/js/slick.js"></script>
<script src="/wp-content/themes/default/js/main.js"></script>

<script type="text/javascript" src="https://apis.google.com/js/plusone.js"> {lang: 'en'} </script>
<?php if(is_front_page()): ?>
<script type="text/javascript">
$(document).ready(function() {
    $('.slideshow').cycle({
        fx:'fade',
        speed:1000,
        timeout:2000,
        autostop:1,
        autostopCount:3
    });
});
</script>
<?php elseif(is_page('contact')): ?>
<script type="text/javascript">
$(function(){
	$('table.iqfm-table tr').each(function(i){
		$(this).attr('class','number' + (i + 1));
	});
});
</script>
<?php endif; ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-42383364-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-42383364-1');
</script>
</head>
<?php if(is_page()): ?>
<body id="<?php echo $parent_slug = get_page_uri($post->post_parent); ?>">
<?php else: ?>
<body <?php body_class(); ?>>
<?php endif; ?>
<a id="top" name="top"></a>
<?php include( TEMPLATEPATH . '/headerNavi.php' ); ?>
<?php //include( TEMPLATEPATH . '/hd_bt.php' ); ?>
<!--==================================▼ここからコンテンツ=========================================-->
<main>
