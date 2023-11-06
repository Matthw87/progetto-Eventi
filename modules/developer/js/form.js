$(document).ready(function(){	
    
	$('#checklunghezza').on('change',function(){
		
		if( $(this).prop('checked') == true){
			$('#div_lunghezzamin').show();
			$('#div_lunghezzamax').show();
		}else{
			$('#div_lunghezzamin').hide();
			$('#div_lunghezzamax').hide();
		}
	});
	
	
	$('#dimension_resize_default').on('change',function(){
		
		if( $(this).prop('checked') == true){
			$('#custom_dimension').show();
		}else{
			$('#custom_dimension').hide();
		}
    });
    
    $('#unique_value').on('change',function(){
         //caso della checkbox
        if( $('#type').val() == 4 ){
        
            if( $(this).prop('checked') == true ){
          
               
                $('#div_function_template').hide();
                $('#function_template').val('');
            
            }else{
                $('#div_function_template').show();
               
            }
        }
        
		
	});

	

	$('#tipo_file').on('change',function(){
		var val = $(this).val();
		
		if( val == 1){
			$('#div_ext_image').show();
			$('#div_dimension_resize_default').show();
			$('#div_resize_image').show();
			$('#div_ext_attach').hide();
			if( $('#dimension_resize_default').prop('checked') == true){
				$('#custom_dimension').show();
			}else{
				$('#custom_dimension').hide();
			}
		}

		if( val == 2){
			
			$('#custom_dimension').hide();
			$('#div_dimension_resize_default').hide();
			$('#div_resize_image').hide();
			$('#div_ext_image').hide();
			$('#div_ext_attach').show();
		}
		
	});
	
	$('#tipo').on('change',function(){
		var val = $(this).val();
		
		
		if( val == 7){
			$('#div_tipo_data').show();
		}else{
			$('#tipo_data').val('0');
			$('#div_tipo_data').hide();	
		}

		if( val == 8){
			$('#div_tipo_timestamp').show();
		}else{
			$('#tipo_timestamp').val('0');
			$('#div_tipo_timestamp').hide();	
		}

		if( val == 9){
			$('#div_tipo_time').show();
		}else{
			$('#tipo_time').val('0');
			$('#div_tipo_time').hide();	
		}
	});


	if( $('#checklunghezza').prop('checked') == true){

		$('#div_lunghezzamin').show();
		$('#div_lunghezzamax').show();
	}else{
		$('#div_lunghezzamin').hide();
		$('#div_lunghezzamax').hide();
	}



	var val_tipo = $('#tipo').val();
	//alert(val_tipo);
	
	if( val_tipo == 7){
		$('#div_tipo_data').show();
	}else{
		$('#tipo_data').val('0');
		$('#div_tipo_data').hide();	
	}

	if( val_tipo == 8){
		$('#div_tipo_timestamp').show();
	}else{
		$('#tipo_timestamp').val('0');
		$('#div_tipo_timestamp').hide();	
	}

	if( val_tipo == 9){
		$('#div_tipo_time').show();
	}else{
		$('#tipo_time').val('0');
		$('#div_tipo_time').hide();	
	}
	var val_tipo_file = $('#tipo_file').val();
	
	
	
	if( val_tipo_file == 1){
		$('#div_ext_image').show();
		$('#div_resize_image').show();
		$('#div_dimension_resize_default').show();
		
		$('#div_ext_attach').hide();
	}

	if( val_tipo_file == 2){
		$('#div_dimension_resize_default').hide();
		$('#div_resize_image').hide();
		$('#div_ext_image').hide();
		$('#div_ext_attach').show();
    }
    
    if( $('#type').val() == 4 && $('#unique_value').prop('checked') == true){
        //caso della checkbox
		$('#div_function_template').hide();
        $('#function_template').val('');
    
	}

	var dimension_resize_default = $('#dimension_resize_default').val();
		
	if( $('#dimension_resize_default').prop('checked') == true){
		$('#custom_dimension').show();
	}else{
		$('#custom_dimension').hide();
	}

	if($('#tipo_valori').prop('checked') == true){
		$('#static_fields').show();
		$('#div_function_template').hide();
	}else{
		$('#static_fields').hide();
		$('#div_function_template').show();
	}


	$('#tipo_valori').on('change',function(){
		if($(this).prop('checked') == true){
			$('#static_fields').show();
			$('#div_function_template').hide();
		}else{
			$('#static_fields').hide();
			$('#div_function_template').show();
		}
	})

	if($('#multilocale').prop('checked') == true){
		$('.multilocale').show();
		$('.no_multilocale').hide();
	}else{
		$('.multilocale').hide();
		$('.no_multilocale').show();
	}


	$('#multilocale').on('change',function(){
		if($(this).prop('checked') == true){
			$('.multilocale').show();
			$('.no_multilocale').hide();
		}else{
			$('.multilocale').hide();
			$('.no_multilocale').show();
		}
	})

	
		
	
	
});

function add_option(){
	var multilocale = 0
	if($('#multilocale').prop('checked')){
		multilocale=1;
	}
	var id_campo= $('#codice').val();
	
	$('<div data-iziModal-fullscreen="true"  data-iziModal-title="Opzione" data-iziModal-icon="icon-home"></div>').appendTo('body').attr('id','modal-option');
	$("#modal-option").iziModal({
		
		iframe: true,
		iframeHeight: 500,
		iframeURL: "index.php?ctrl=FormFieldAdmin&action=add_field&multilocale="+multilocale+"&campo="+id_campo,
		onClosed: function(){
			$("#modal-option").remove();
		}
	});

	$("#modal-option").iziModal('open');
}


function change_mandatory(id){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "FormFieldAdmin",mod:'developer',action:'mandatory','id':id,'ajax':1},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
							
					if( data.status == 1 ){
						$('#field_'+id+'_online').show();
						$('#field_'+id+'_offline').hide();
					}else{
						$('#field_'+id+'_online').hide();
						$('#field_'+id+'_offline').show();
					}
			
				}else{
					
				}
		  },
	 
	});
}

