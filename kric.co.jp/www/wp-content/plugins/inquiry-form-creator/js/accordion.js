$(document).ready(function() {
	$(".tabContents dt").hover(function(){
		$(this).css("cursor","pointer"); 
	},function(){
		$(this).css("cursor","default"); 
		});
	$(".tabContents dd").css("display","none");
	$(".tabContents dt").click(function(){
		$(this).next().slideToggle("fast");
		});
});
