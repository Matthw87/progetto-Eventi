$(() => {
	if ($('.datetimepicker_date').length > 0)
		$('.datetimepicker_date').datepicker({
			closeText: 'Chiudi',
			prevText: 'Prec',
			nextText: 'Succ',
			currentText: 'Oggi',
			monthNames: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
			monthNamesShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
			dayNames: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'],
			dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
			dayNamesMin: ['Do', 'Lu', 'Ma', 'Me', 'Gio', 'Ve', 'Sa'],
			dateFormat: 'dd/mm/yy',
			firstDay: 1,
			isRTL: false
		});
});


function form_builder_submit(id) {

	let formData = new FormData($('#form_builder_' + id).get(0));
	$('#form_builder_' + id).find('.error_form_builder').each(function(){
		$(this).removeClass('error_form_builder');
	});
	$.ajax({
		type: 'POST',
		url: 'index.php?ctrl=Submit&mod=form_builder&ajax=1',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		dataType: 'json',
		success: data => {
			if( $('#recaptcha_'+id).length > 0 ){
				grecaptcha.reset();
			}

			if (data.ok) {
				if( data.redirect_url ){
					window.location.href=data.redirect_url;
				}else{
					
					$('#form_builder_success_'+id).html('');
					$('#form_builder_error_'+id).css({ 'display': 'none ' });
					$('#form_builder_success_'+id).css({ 'display': 'block' });
					$('#form_builder_success_'+id).html(data.message);
				}
				

			} else {
				
				$('#form_builder_error_'+id).html('');
				$('#form_builder_success_'+id).css({ 'display': 'none ' });
				$('#form_builder_error_'+id).css({ 'display': 'block' });

				data.errors.forEach(error => {
					$('#form_builder_error_'+id).append(error);
				});

				data.field_errors.forEach(field => {
					$('#form_builder_' + id).find("input[name="+field+"]").addClass('error_form_builder');
					$('#form_builder_' + id).find("select[name="+field+"]").addClass('error_form_builder');
					$('#form_builder_' + id).find("textarea[name="+field+"]").addClass('error_form_builder');
				});

				

			}

		}
	});
}