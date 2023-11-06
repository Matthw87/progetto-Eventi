$(document).ready(function(){
	var w = $(window).width();
	var bool = false;
	if (w<1024)
	{
		console.log("prova"),
		$("#sezioni-table tbody tr > td").each(function(){
			bool = true;
			var lui = $(this);
			if( lui.find('.check_action_bulk').length != 0 )
			{
				lui.css("min-width", "50px").css("width", "50px");
			}
		});
		if(bool)
		{
			$("#sezioni-table thead tr th:first-child").css("min-width", "50px").css("width", "50px");
			$("#sezioni-table tfoot tr td:first-child").css("min-width", "50px").css("width", "50px");
		}
	}
});

$(window).load(function(){
	var w = $(window).width();
	if (w<1024)
	{
		var hpag = $(window).height();
		var hmenu = $("#main-menu").height();
		var hmax = 0;
		if(hpag>hmenu)
		{
			hmax=hpag;
		}
		else
		{
			hmax=hmenu;
		}
		$("#sidebar").height(hmax);
	}
});