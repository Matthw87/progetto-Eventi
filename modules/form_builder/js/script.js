function widget_developer_submit(id) {
	$('#widget_developer_form_error_' + id).hide();
	$('#widget_developer_form_success_' + id).hide();
	var formdata = $('#widget_developer_form_' + id).serialize();
	$.ajax({
		type: "POST",
		url: "/modules/widget_developer/index.php",
		data: { action: "send", id: id, formdata: formdata, captcha: grecaptcha.getResponse() },
		dataType: "json",
		success: function (data) {
			if (data.result == 'ok') {
				$('#widget_developer_form_success_' + id).html(data.message).show();
				$('#widget_developer_form_' + id).find('input').each(function () {
					//$(this).val('');
				});
				$('#widget_developer_form_' + id).find('select').each(function () {
					//$(this).val(0);
				});
				grecaptcha.reset();
			} else {
				grecaptcha.reset();
				$('#widget_developer_form_error_' + id).html(data.error).show();

			}
		},

	});
	return false;
}