function pagecomposer_accordions() {
	$(".faq-composer .tit-faq").click(function(){
		var lui = $(this);

		var img_plus = lui.closest('.pagecomposer-accordion-container').attr('img_plus');
		var img_minus = lui.closest('.pagecomposer-accordion-container').attr('img_minus');
		if(lui.parents(".faq-composer").hasClass("active"))
		{
			lui.parents(".faq-composer").removeClass("active");
			lui.parents(".faq-composer").find(".content-faq").slideToggle();
			lui.find("img").attr("src", img_plus);
		}
		else
		{
			$(".faq-composer").each(function(){
				if($(this).hasClass("active"))
				{
					$(this).removeClass("active");
					$(this).find(".content-faq").slideToggle();
					$(this).find(".tit-faq").find("img").attr("src", img_plus);
				}
			});
			lui.parents(".faq-composer").find(".content-faq").slideToggle();
			lui.parents(".faq-composer").addClass("active");
			lui.find("img").attr("src", img_minus);
		}
	});
}

$(document).ready(function(){
	pagecomposer_accordions();
});