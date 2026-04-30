<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>KRIC ⅡS 管理画面</title>
<meta name="keywords" content=""/>
<meta name="description" content=""/>
<?php
	$menu = mb_split("/",$_SERVER['REQUEST_URI']);
?>
<?php if($menu[2] != 'company_view' && $menu[2] != 'information_view' && $menu[2] != 'library_view'): ?>
<link href="/material/admin/css/reset.css" rel="stylesheet" type="text/css" />
<link href="/material/admin/css/top.css" rel="stylesheet" type="text/css" />
<link href="/material/admin/css/page.css" rel="stylesheet" type="text/css" />
<link href="/material/admin/css/layout.css" rel="stylesheet" type="text/css" />
<link href="/material/admin/css/advanced.css" rel="stylesheet" type="text/css" />
<?php endif; ?>
<script type="text/javascript" src="/material/admin/js/jquery.min.js"></script>
<script type="text/javascript" src="/material/admin/js/submenu.js"></script>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-TK4JNG0SP8"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-TK4JNG0SP8');
</script>

</head>
<?php echo $this->fetch('content'); ?>
</html>