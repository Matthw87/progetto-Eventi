function add_user(id,profile){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "ProfileAdmin",action:'add_profile_user','id':id,'profile':profile,ajax:1},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
							
					var el = $('#button_add_'+id);
					el.remove();
					$('#user_'+id).addClass('success');
			
				}else{
					
				}
		  },
	 
	});
}

