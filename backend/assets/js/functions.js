function crea_select(el, newOptions, selectedOption) {
 
   
   if(el.prop) {
      var options = el.prop('options');
   }
   else {
     var options = el.attr('options');
   }
   
   $('option', el).remove();

   $.each(newOptions, function(val, text) {
       options[options.length] = new Option(text, val);
   });
   if( selectedOption ){
	el.val(selectedOption);  
   }

   el.selectpicker("refresh");
  
   
}


Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };


if (typeof swal !== "undefined") { 
	 function MarionAlert(title,text,callback){
		if (typeof callback === 'function') {
			swal({
				title:title, 
				text: text,
				html: true,
				confirmButtonColor: "#18a689",
				allowOutsideClick: false,
				},
				callback
				)
		}else{
			swal({
				title:title, 
				text: text,
				html: true,
				allowOutsideClick: false,
				confirmButtonColor: "#18a689",
				})
		}
	 }
	/*
		title: titolo del popup
		text: testo del popup
		callback: funzione di callback
		textButtonCancel: testo sul button cancel
	*/

	function MarionConfirm(title,text,callback,confirmButtonText,cancelButtonText){
		if( !confirmButtonText ){
			confirmButtonText = js_confirm_ok_alert;
		}
		if( !cancelButtonText ){
			cancelButtonText = js_confirm_cancel_alert;
		}
		if( !text ){
			text = js_confirm_text_alert;
		}
		if (typeof callback === 'function') {
			swal({
			  title: title,
			  text: text,
			  html: true,
			  //type: "warning",
			  showCancelButton: true,
			  confirmButtonColor: "#18a689",
			  confirmButtonText: confirmButtonText,
			  cancelButtonText: cancelButtonText,
			  closeOnConfirm: false,
			  allowOutsideClick: false,
			},
			callback
			);
		}else{
			swal({
			  title: title,
			  text: text,
			  //type: "warning",
			  showCancelButton: true,
			  confirmButtonColor: "#18a689",
			  confirmButtonText: textButtonCancel,
			  cancelButtonText: js_confirm_cancel_alert,
			  closeOnConfirm: false,
			  allowOutsideClick: false,
			});
		}
	}
}

if (typeof jError !== "undefined" && typeof jSuccess !== "undefined") { 
	function notify(text,type){
		if( !type) type = 'info';
		if( type == 'error'){
				jError(
					text, {
						HorizontalPosition: 'right',
						VerticalPosition: 'bottom',
						ShowOverlay: true,
						TimeShown: 3000,
						OpacityOverlay:  0.5,
						MinWidth:  250
				});
		}else if(type == 'success'){
			jSuccess(
					text, {
						HorizontalPosition: 'right',
						VerticalPosition: 'bottom',
						ShowOverlay: true,
						TimeShown: 3000,
						OpacityOverlay:  0.5,
						MinWidth:  250
				});
		}else{
			jNotify(
					text, {
						HorizontalPosition: 'right',
						VerticalPosition: 'bottom',
						ShowOverlay: true,
						TimeShown: 3000,
						OpacityOverlay:  0.5,
						MinWidth:  250
				});
		}
	}

	function notify_top(text,type){
		if( !type) type = 'info';
		if( type == 'error'){
				jError(
					text, {
						HorizontalPosition: 'right',
						VerticalPosition: 'top',
						ShowOverlay: true,
						TimeShown: 3000,
						OpacityOverlay:  0.5,
						MinWidth:  250
				});
		}else if(type == 'success'){
			jSuccess(
					text, {
						HorizontalPosition: 'right',
						VerticalPosition: 'top',
						ShowOverlay: true,
						TimeShown: 3000,
						OpacityOverlay:  0.5,
						MinWidth:  250
				});
		}else{
			jNotify(
					text, {
						HorizontalPosition: 'right',
						VerticalPosition: 'top',
						ShowOverlay: true,
						TimeShown: 3000,
						OpacityOverlay:  0.5,
						MinWidth:  250
				});
		}
	}
}

function show_loader(){
	$('.cs-loader').show();
}

function hide_loader(){
	$('.cs-loader').hide();
}

function executeCopy(text) {
	var input = document.createElement('textarea');
	document.body.appendChild(input);
	input.value = text;
	//input.focus();
	input.select();
	document.execCommand('Copy');
	input.remove();
}


