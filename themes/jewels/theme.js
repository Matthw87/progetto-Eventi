$(document).ready(function() {
	
	//lingua2
	$(".lingua-corrente").click(function() {
		$(".box-lingue").slideToggle();
	});

	//valuta
	$(".valuta-corrente").click(function() {
		$(".box-valute").slideToggle();
	});

	$(".top .search").on("click",function(){
		$(this).addClass("active");
		$(".barraricerca").addClass("active");
	});

	$(".top .close-search").on("click",function(e){
		e.preventDefault();
		$(".top .search").removeClass("active");
		$(".barraricerca").removeClass("active");
	});

	$(".top .user .logged").on("click",function(e){
		e.preventDefault();
		$(".underuser").slideToggle();
		$(".undercart").slideUp();
	});

	$(".selfiltri").on("click",function(){
		$(this).toggleClass("active");
		$(".colsx").toggleClass("visible");
		$(".coldx").toggleClass("resize");
	});

	$(".titcolsx").on("click",function(){
		$(this).toggleClass("active");
		$(this).closest(".filtri_ricerca").find(".metismenu, .cont_filtro").slideToggle();
	});

	$(".tittab").on("click",function(){
		var padre = $(this).parent();
		padre.find(".content").slideToggle();
		padre.toggleClass("aperto");
		if(padre.hasClass("aperto")) {
			padre.find("img").attr("src", "/themes/jewels/images/ico_arrow_up_accordion.png")
		}
		else {
			padre.find("img").attr("src", "/themes/jewels/images/ico_arrow_down_accordion.png");
		}
	});
	positionInfo();

	$(".nav-menu").on("click",function(){
		$(".top .nav-menu").toggleClass("open");
		$(".top .menu").toggleClass("active");
	});

	$(".user").on("click",function(){
		$(".guest").removeClass("active");
		$(this).toggleClass("active");
		$(".logindata").show();
		$(".nologindata").hide();
	});
	$(".guest").on("click",function(){
		$(".user").removeClass("active");
		$(this).addClass("active");
		$(".logindata").hide();
		$(".nologindata").show();
	});

	if($(".colsx-backend").is(":visible")) {
		$("body").addClass("backend");
	}

	if( ($(".addcart").is(":visible")) || ($(".cart-wish").is(":visible")) ) {
		$(".cont_btn").show();
	}
	else {
		$(".cont_btn").hide();
	}
});

$(window).resize(function(){
	positionInfo();
});

function positionInfo(){
	var w = $(window).width();
	/*if(w > 767){
		var hinfo = $(".prod .info").height();
		$(".col-foto").css("min-height",hinfo);
	}*/
	if(w > 1024){
		var hfancyone = $(".fancyone").height();
		var hfoto = $(".prod .foto").height();
		if(hfancyone !== hfoto){
			var wscreen = $(window).width();
			var wprod = $(".prod").width();
			var right = (wscreen-wprod)/2;

			$(".prod .info").addClass("fixed").css("right", right);
		}
	}
	else {
		$(".prod .info").removeClass("fixed").css("right", "0").css("margin-top","0");
	}
}

$(window).scroll(function(){
	var w = $(window).width();
	scroll = $(window).scrollTop();
	if (scroll >= 40) {
		$(".top").addClass("bannerh");
		$(".colsx-backend").addClass("upside");
	} else {
		 $(".top").removeClass("bannerh");
		 $(".colsx-backend").removeClass("upside");
	}
	if(w > 1024){
		var hfancyone = $(".fancyone").height();
		var hfoto = $(".prod .foto").height();
		if(hfancyone !== hfoto){
			
			if (scroll >= hfancyone) {
				$(".prod .info").addClass("nofixed").css("margin-top",hfancyone);
			} else {
				$(".prod .info").removeClass("nofixed").css("margin-top","0");
			}
		}
	}
});

$.fn.isInViewport = function() {
    var elementTop = $(this).offset().top;
    var elementBottom = elementTop + $(this).outerHeight();

    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
};

$(window).on('resize scroll', function() {
	var wscreen = $(window).width();
	if(wscreen > 991){
		var hcontainer = $('.container-small').height();
		
		if ($('.footer').isInViewport()) {
			$('.colsx-backend').removeClass('sticky');
			$('.colsx-backend').addClass('notsticky');
			$('.colsx-backend').css('height' , hcontainer);
		} else {
			$('.colsx-backend').removeClass('notsticky');
			$('.colsx-backend').addClass('sticky');
		}
	}
});