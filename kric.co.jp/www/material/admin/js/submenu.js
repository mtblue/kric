// JavaScript Document

$(function(){
    $("ul.menu li").hover(function(){
	$(">ul:not(:animated)",this).slideDown("slow");
    },function(){
	$(">ul",this).slideUp("slow");
    });
});