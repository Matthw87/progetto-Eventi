function copyToClipboard(id) {
	 var element = $('#val_'+id);
	  var $temp = $("<textarea>");
	  
	  $("body").append($temp);
	  $temp.val($(element).val()+"\r\n").select();
	  document.execCommand("copy");
	  $temp.remove();
	  alert('HTML campo copiato!');
	}

	/*function add_field_form(id){
		
		$.magnificPopup.open({
		  items: {
			src: "index.php?action=add_field&form="+id
		  },
		  type: 'iframe',
		  callbacks: {
			  close: function(){
				 reload_form_fields(js_id_form);
			  }
		  },
		}, 0);

	}*/

function save_field_form(id){
	$('#form_field_error').html('');
	var formdata = $('#form_field').serialize();
	$.ajax({
	  type: "POST",
	  url: "index.php",
	  dataType: "json",
	  data: {ctrl:'EditorForm',mod:'form_builder',action:'save_field',ajax:1,formdata:formdata,id_box:id},
	  success: function(data){
			if(data.result	== 'ok'){
				$('#other_fields').html(data.html).show();
				$('#form_campo').html('');
			}else{
				$('#form_field_error').html(data.error);
			}
	  },
	 
	});
	

}

function confirm_delete_field(id,k){
	var t = confirm("Sicuro di volere eliminare questo campo");
	if( t ){
		
		$.ajax({
		  type: "POST",
		  url: "index.php?ctrl=EditorForm&mod=form_builder&action=del_field&ajax=1&id_box="+id+"&indice="+k,
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$('#other_fields').html(data.html);
				}else{
					
				}
		  },
		 
		
		});
	}
}


function add_field_form(id,k){
	$.ajax({
	  type: "POST",
	  url: "index.php?ctrl=EditorForm&mod=form_builder&action=add_field&ajax=1&id_box="+id+"&indice="+k,
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				$('#other_fields').hide();
				$('#form_campo').html(data.html);
			}else{
				
			}
	  },
	 
	});
	

}
	function edit_field_form(id){
		
		$.magnificPopup.open({
		  items: {
			src: "index.php?action=mod_field&id="+id
		  },
		  type: 'iframe',
		  callbacks: {
			  close: function(){
				 reload_form_fields(js_id_form);
			  }
		  },
		}, 0);

	}

	function reload_form_fields(id){
		$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: { action: "get_form_fields",id : id},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$('#campi_form').html(data.html);
					$('#campi_html').html(data.html2);
					$('#campi_html2').html(data.html3);
				}else{
					
				}
		  },
		 
		});
	}