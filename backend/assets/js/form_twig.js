function form_twig_lang_view(campo,el,loc){
	var el2 = el.closest('.container-flag-form-multilang');
	
	el2.find('.flag-form-multilang').each(function(){
		$(this).removeClass('active');
	});
	el.addClass('active');
	
	el.closest('.container-lang-input').find('.form-group').each(function(){
		$(this).addClass('hidden');
	});
	el.closest('.container-lang-input').find('#div_'+campo+"_"+loc).removeClass('hidden');


	//console.log('#div_'+campo);
}

function form_twig_lang_view_filemanager(campo,el,loc){
	
	var el2 = el.closest('.container-flag-form-multilang');
	
	el2.find('.flag-form-multilang').each(function(){
		$(this).removeClass('active');
	});
	el.addClass('active');
	el.closest('.container-lang-input').find('.content-lang-input').each(function(){
		$(this).addClass('hidden');
	});
	el.closest('.container-lang-input').find('.content-lang-input-'+loc).removeClass('hidden');

	
}


function form_twig_filemanager_remove_image(e){
	e.stopPropagation();

	//console.log($(this)); $(this).hide(); $('#img_filemanager_{{field}}').attr('src','');$('#{{field}}').val('');
}