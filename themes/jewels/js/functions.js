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

   el.val(selectedOption);  
  
   
}

function reload_captcha(code){
	$.ajax({
	  type: "GET",
	  url: "/mail.php",
	  data: { action: "reload_captcha",code: code},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				if( $('#img_captcha').length > 0){
					$('#img_captcha').attr('src',data.link);
				}

				if( $('#button_reload_captcha').length > 0){
					$('#button_reload_captcha').attr('code',data.code);
				}
				
				//alert('Operazione eseguita con successo!');
			}else{
				alert(data.error);
			}
	  },
	 
	});
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



 function MarionAlert(title,text,callback){
	if (typeof callback === 'function') {
		swal({
			title:title, 
			text: text,
			html: true,
			confirmButtonColor: "#C1C1C1",
			},
			callback
			)
	}else{
		swal({
			title:title, 
			text: text,
			html: true,
			confirmButtonColor: "#C1C1C1",
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
		  confirmButtonColor: "#C1C1C1",
		  confirmButtonText: confirmButtonText,
		  cancelButtonText: cancelButtonText,
		  closeOnConfirm: false
		},
		callback
		);
	}else{
		swal({
		  title: title,
		  text: text,
		  //type: "warning",
		  showCancelButton: true,
		  confirmButtonColor: "#C1C1C1",
		  confirmButtonText: textButtonCancel,
		  cancelButtonText: js_confirm_cancel_alert,
		  closeOnConfirm: false
		});
	}
}



function disable_mod_user(id){
	
	

	$.ajax({
	  type: "GET",
	  url: "/admin/ecommerce.php",
	  data: { action: "manage_order_user",id: id,type:'disabled'},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				$('#mod_user_'+id).remove();
				
				//alert('Operazione eseguita con successo!');
			}else{
				alert(data.error);
			}
	  },
	 
	});
	
}


