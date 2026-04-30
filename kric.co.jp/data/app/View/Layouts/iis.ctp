<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />


<meta name="viewport" id="viewport" />
<script type="text/javascript">
if(navigator.userAgent.indexOf('iPhone') != -1 || navigator.userAgent.indexOf('iPod') > -1){
    ( function(){
		document.getElementById( 'viewport' ).content = "width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" ;} ) ();
}
else if(navigator.userAgent.indexOf('Android') != -1 || (navigator.userAgent.indexOf('Android') != -1 && navigator.userAgent.indexOf('EB-WX1GJ') != -1)){
    ( function(){
		document.getElementById( 'viewport' ).content = "width=device-width, initial-scale=0.95, minimum-scale=0.95, maximum-scale=0.95, user-scalable=no" ;} ) ();
}
else{
    ( function(){
      var contentWidth = 1010; 
      var min_scale = 
          Math.round( ( screen.width / contentWidth ) * 10000 , 5 ) / 10000;
      var max_scale = 
          Math.round( ( screen.height / contentWidth ) * 10000 , 5 ) / 10000;
      document.getElementById( 'viewport' ).content =
                   'width=' + contentWidth + ' , ' +
                   'minimum-scale=' + min_scale + ' , ' +
                   'maximum-scale=' + max_scale + ' , ' +
                   'user-scalable=no' ;
      } ) () ;
}
</script>



<title>関西不動産情報センター／KRIC（クリック）</title>
<link rel="stylesheet" href="/css/format.css" type="text/css" media="screen" />
<link rel="stylesheet" href="/css/iis.css" type="text/css" media="screen" />

<meta name="Keywords" content="近畿,関西,京阪神,大阪,兵庫,京都,奈良,滋賀,不動産,土地,ビル,事務所,収益,店舗,工場,倉庫,駐車場,住宅,戸建て,マンション,仲介,売買,分譲,賃貸,有効利用,コンサルティング,リフォーム,鑑定,測量,保険,信託,証券化,ファンド,SPC,REIT" />
<meta name="Description" content="当サイトは、関西一円の信託銀行・都市銀行不動産部・銀行系列の不動産会社、大手電鉄系・ゼネコンなどの直系会社、老舗の不動産会社など「信用と実績」を誇る不動産会社で組織された関西不動産情報センター（KRIC）が運営するサイトです。" /><link rel="canonical" href="https://kric.co.jp/" />
<!-- /all in one seo pack -->

<script type="text/javascript" src="/js/jquery-1.8.0.min.js"></script>
<script type="text/javascript" src="/js/smoothscroll.js"></script>
<script language="javascript" type="text/javascript" src="/js/breakpoints.js"></script>
<script language="javascript" type="text/javascript" src="/js/respond.min.js"></script>

<script language="javascript" type="text/javascript" src="/js/menu.js"></script>

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