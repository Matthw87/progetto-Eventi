$(document).ready(function(){
	if( $('#reset_password').length > 0){
		if( $('#reset_password').prop('checked') == false){
			$('#password_row').hide();
		}

		$('#reset_password').on('change',function(){
			if( $(this).prop('checked') == false){
				$('#password_row').hide();
			}else{
				$('#password_row').show();
			}
		})
	}
})

function change_visibility(id){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "UserAdmin",action:'change_visibility','id':id,'ajax':1},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
							
					var el = $('#status_'+id);
					if( data.status ){
						el.removeClass('label-danger').addClass('label-success').html(data.text);
					}else{
						el.removeClass('label-success').addClass('label-danger').html(data.text);
					}
			
				}else{
					
				}
		  },
	 
	});
}

