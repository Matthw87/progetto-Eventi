$(document).ready(function(){
	
	$(".carrellino img").click(function(){
		$(".undercart").slideToggle();
	});


	$(".open_sub").click(function(){
		var lui = $(this);
		var controllo = lui.find(".sub-menu").hasClass("activissimo");
		if(controllo)
		{
			lui.find(".sub-menu").removeClass("activissimo");
			lui.find(".sub-menu").slideToggle();
		}
		else
		{
			$(".sub-menu").each(function(){
				var temp = $(this).hasClass("activissimo");
				if(temp)
				{
					$(this).slideToggle();
					$(this).removeClass("activissimo");
				}
			});
			lui.find(".sub-menu").slideToggle();
			lui.find(".sub-menu").addClass("activissimo");
		}
		$( ".sub-menu li" ).click(function( event ) {
			event.stopPropagation();
		});
	});
});

$(window).resize(function(){
	aggiustacarrellino();
});



function aggiustacarrellino()
{
	var w = $(window).width();
	var h = $(window).height();
	if(w >= 768){
		var hfixtop = $(".fixtop").height();
		var maxh = h-hfixtop-50;
		var hcart = $(".fixedcart").height();
		console.log(maxh);
		if( hcart> maxh)
		{
			$(".fixedcart").css("height" , maxh).css("overflow-y" , "scroll");
		}
		else
		{
			$(".fixedcart").css("height" , "auto").css("overflow-y" , "hidden");
		}
		
	}
}
