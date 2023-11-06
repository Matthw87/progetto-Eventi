$(document).ready(function(){
    // Activate Carousel
	var w = $(window).width();
	
	/*if(w>767){
		$('.mobile_sliderfull').hide();
		$('.desktop_sliderfull').show();
	}else{
		$('.mobile_sliderfull').show();
		$('.desktop_sliderfull').hide();

	}*/

	$(".desktop_sliderfull").each(function(){
		$('#'+$(this).attr('id')).carousel();	
	});

	$(".mobile_sliderfull").each(function(){
		$('#'+$(this).attr('id')).carousel();	
	});
    
	//$(".mobile_sliderfull").carousel();
});