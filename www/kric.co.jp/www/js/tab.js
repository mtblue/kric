


jQuery(document).ready(function() {
// .tablist直下の全li要素の中から最初のli要素に.selectを追加
		$(".tab > .tablist > li.sell > a").addClass("select");
		// .tablist直下の全li要素にマウスオーバーしたらリンク要素に偽装
		$(".tab > .tablist > li > a").click(function(){
			$(this).css("cursor","pointer");
		},function(){
			$(this).css("cursor","default");
		});
		
  jQuery(".tab > .tabArea").hide();
  jQuery(".tabDefault").show();
  //
  var tabIndex = jQuery(".tab > .tabArea");
  var tabNum = jQuery(tabIndex).length-1;
  
  //
  jQuery(".tab > .tablist > li > a").click(function() {
	  // .hovers下の.tablist直下の全li要素のclass属性を削除
		$(".tab > .tablist > li > a").filter("a").removeClass("select");
		// マウスオーバーしたli要素に.selectを追加
		$(this).addClass("select");
    var targetID = jQuery(this).attr("href")+("_s");
    for (i=0; i<=tabNum; i++) {
      jQuery(tabIndex[i]).hide();
      }
    jQuery(targetID).show();
	jQuery(targetID).fadeOut(0);
	jQuery(targetID).fadeIn(2000);
    return false;
  });
});
