<!--==================================ここからヘッダー=========================================-->
<script type="text/javascript">
$(function(){
	login_bar();
});
function login_bar() {
	$.get('/member/login_bar', function(data) {
//alert(data);
		$("#log_bar").empty().append(data);
	});
}
</script>
<div id="container">

<div id="headerArea">
<header>
	<div id="h_login">
		<div class="header_login_inner" id="log_bar">
		</div>
	</div>
	<div id="h_sns">
		<div class="tweet">
			<a href="https://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-lang="ja">ツイートする</a> 
			<script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script> 
		</div>
		<div class="facebook">
			<iframe src="https://www.facebook.com/widgets/like.php?href=https%3A%2F%2Fkric.co.jp/&amp;layout=button_count" scrolling="no" frameborder="0" style="border:none; width:120px; height:21px;" allowtransparency="true"></iframe>
		</div>
	</div>

	<div id="loginBtn">
		<a href="/iis/login"><img src="/wp-content/themes/default/images/h_login.png" alt="お問い合わせ24時間受付中"></a>
	</div>
	
	<div id="navBtn">
	<div class="btnBody">
		<span class="top"></span>
		<span class="middle"></span>
		<span class="bottom"></span>
	</div>
	</div>
	
	<div id="menu">
	<nav>
		<ul id="nav_main">
			<li><a href="/">トップページ</a></li>
			<li><a href="/kric.html">KRICについて</a></li>
			<li><a href="/activity.html">私たちの活動</a></li>
			<li><a href="/member/">会員紹介</a></li>
			<li><a href="/enter.html">入会をお考えの方</a></li>
			<!--<li><a href="#">KRICラジオ</a></li>-->
			<li><a href="/about.html">お問合せ運営団体</a></li>
			<li><a href="/sitemap.html">サイトマップ</a></li>
			<li><a href="/privacy.html">プライバシーポリシー</a></li>
		</ul>
	</nav>
	<!--
	<div id="menu_inq">
		<p class="tel"><a href="tel:0662927791"><span>TEL</span> 06-6292-7791</a></p>
		<p class="time">受付時間 9:10～16:00<br class="sp" />（土・日・祝日除く）</p>
		<p><a href="contact.html"><img src="/wp-content/themes/default/images/menu_mail.png" alt="お問い合わせ24時間受付中"></a></p>
	</div>
	-->
	</div>

	<div id="overlay"></div>

</header>	
<!-- /#headerArea --></div>
<!--==================================▲ここまでヘッダー=========================================--> 

