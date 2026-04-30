$(function() {
	var gnav = $('#menu');
	var overlay = $('#overlay');
	var h = $(window).height();
	gnav.css('display','none');
	overlay.css('display','none');
	overlay.css('height',h);
	$('#navBtn,#ft_menu').on('click', function() {
		gnav.slideToggle(500);
		overlay.slideToggle(500);
		if ($('#navBtn').hasClass('nav_open')) {
			$('#navBtn').removeClass('nav_open');
		} else {
			$('#navBtn').addClass('nav_open');
		}
	});
	$('#menu li a').on('click', function() {
		gnav.slideToggle(100);
		overlay.slideToggle(100);
		if ($('#navBtn').hasClass('nav_open')) {
			$('#navBtn').removeClass('nav_open');
		} else {
			$('#navBtn').addClass('nav_open');
		}
	});
	$(window).on('resize', function() {
		if ($('#navBtn').hasClass('nav_open')) {
			$('#navBtn').removeClass('nav_open');
			gnav.css('display','none');
			overlay.css('display','none');
		}
	});
});

$(function() {
    var swpPoint = 480;
	function imgChange(){
		if($(window).width()<swpPoint){
			$("img.swp").each(function(){
				var changeImg = $(this).attr('src').replace('_pc' , '_sp');
				$(this).attr('src', changeImg);
			});
		}else{
			$("img.swp").each(function(){
				var changeImg = $(this).attr('src').replace('_sp' , '_pc');
				$(this).attr('src', changeImg);
			});
		}
	}

	imgChange()

	$(window).on('resize', function(event) {
		event.preventDefault();
		imgChange();
	});
});

$(function() {
    $('map').imageMapResize();
});

/*
PAGE TOP
*/
$(function(){
    var totop = $('#totop a');
    totop.on('click',function () {
        $('body, html').animate({ scrollTop: 0 }, 500);
        return false;
    });
});

$(function(){
	var swpPoint = 480;
	var showFooter = 700;
	var sticky = $('#nav_footer_sp');
    if($(window).width()<swpPoint){
        $(window).on('load scroll resize',function(){
            if($(window).scrollTop() >= showFooter){
                sticky.fadeIn('normal');
                sticky.fadeIn('normal');
            } else if($(window).scrollTop() < showFooter){
                sticky.fadeOut('normal');
                sticky.fadeOut('normal');
            }
        });
     }
});
