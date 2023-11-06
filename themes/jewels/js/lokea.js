$(document).ready(function() {
	$(".title_faq").click(function(event) {
		var sel = $(this);
		if($(this).parent().find(".text_faq").is(':hidden')) {
			$(".text_faq").slideUp();
			$(".title_faq").removeClass("active_faq");
			$(this).parent().find(".text_faq").slideDown();
			sel.addClass("active_faq");
		}
		else {
			$(this).parent().find(".text_faq").slideUp();
			sel.removeClass("active_faq");
		}
	});
	
	//lingua
	$(".lingua-corrente").click(function() {
		$(".box-lingue").slideToggle();
	});

	//valuta
	$(".valuta-corrente").click(function() {
		$(".box-valute").slideToggle();
	});
});

$(window).load(function(){
	$(".cont_imgprodsez .imgprod").each(function(){
		var lui = $(this);
		var wimg = lui.width();
		var himg = lui.height();
		wimg = (wimg-(wimg*2))/2;
		himg = (himg-(himg*2))/2;
		lui.css("margin-left", wimg).css("left","50%").css("margin-top", himg).css("top","50%");
		setTimeout(function(){
			lui.css("visibility","visible"); 
		}, 100);
	});
})