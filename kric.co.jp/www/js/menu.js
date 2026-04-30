$(window).setBreakpoints({
    distinct: true, 
    breakpoints: [
		1,
        641
    ] 
});    

$(document).ready(function(){
  $(".subNaviRight").css("display","none");
  $(".spNavi").click(function(){
	  if($("+.subNavi",this).css("display")=="none"){
	         $("+.subNavi",this).slideDown("normal");
	         $(this).addClass("active");
	  }else{
	    $("+.subNavi",this).slideUp("normal");
	    $(this).removeClass("active");
	  }
	  if($("+.subNaviRight",this).css("display")=="none"){
	         $("+.subNaviRight",this).slideDown("normal");
	         $(this).addClass("active");
	  }else{
	    $("+.subNaviRight",this).slideUp("normal");
	    $(this).removeClass("active");
	  }
  });
});


var menu_flg = true;
	
function sp_menu_slide() {
	if(menu_flg){
		menu_flg = false;
		$("#anime_wrap").animate({right:"260px"},
			{queue:false,  
			duration:500
		});
		$("#spMenu_btn a").html("<img src='/images/iis/menu_btn_close.png'>");
	}
	else{
		menu_flg = true;
		$("#anime_wrap").animate({right:"0px"},
			{queue:false,  
			duration:500
		});
		$("#spMenu_btn a").html("<img src='/images/iis/menu_btn.png'>");
	}
}