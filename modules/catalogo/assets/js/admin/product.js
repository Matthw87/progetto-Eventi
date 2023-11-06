$(document).ready(function(){
	
	$('#qnt_stock_bulk').on('change',function(){
		var val = $(this).val();
		
		$('#list_child_product tr').each(function(){
			if($(this).find('.check_stock_bulk').prop('checked') ){
				$(this).find('.qnt_child').val(val);
			}
		});
	});

	$('#weight_stock_bulk').on('change',function(){
		var val = $(this).val();
		
		$('#list_child_product tr').each(function(){
			if($(this).find('.check_stock_bulk').prop('checked') ){
				$(this).find('.weight_child').val(val);
			}
		});
	});

	$('#check_stock_bulk').on('change',function(){
		
		if( $(this).prop('checked') ){
			$('.check_stock_bulk').prop('checked',true);
		}else{
			$('.check_stock_bulk').prop('checked',false);
		}
	});

	$('#check_action_bulk').on('change',function(){
		
		if( $(this).prop('checked') ){
			$('.check_action_bulk').prop('checked',true);
		}else{
			$('.check_action_bulk').prop('checked',false);
		}
	});


	
	
	$('#minorder_stock_bulk').on('change',function(){
		var val = $(this).val();
		$('#list_child_product tr').each(function(){
			if($(this).find('.check_stock_bulk').prop('checked') ){
				$(this).find('.minorder_child').val(val);
			}
		});
	});
	
	$('#maxorder_stock_bulk').on('change',function(){
		var val = $(this).val();
		$('#list_child_product tr').each(function(){
			if($(this).find('.check_stock_bulk').prop('checked') ){
				$(this).find('.maxorder_child').val(val);
			}
		});
	});
	
	if(  $.fn.inputmask ){
		$('.solointeri').inputmask("integer",
			{allowPlus: true,
			allowMinus: false,
			rightAlign:false}
		);

		$('.solodouble').inputmask("numeric",
			{allowPlus: true,
			allowMinus: false,
			rightAlign:false}
		);
	}
	
	if( typeof js_form_veloce_stock != 'undefined' && js_form_veloce_stock != null ){
		for( var k in js_form_veloce_stock){
			$('#img_'+k).cironapo({
				id_field_img:'image_'+k,
				id_wrapper: 'wrapper-upload_'+k, 
				box_small: true,
				resize:"thumbnail,small,medium,large",
				type_url_image: "th-nw",
			});
		}
	}



	if( typeof js_id_price_list != 'undefined' && js_id_price_list != null ){

		$('#pricelist_'+js_id_price_list).css('border','1px solid red');
		
	}

	



});

function change_visibility(id){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "ProductAdmin",action:'change_visibility','id':id,'ajax':1,mod:'catalogo'},
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





$(document).ready(function(){
	$('.variation-attr').on('click',function(){
		
		$(this).closest('.cont-var-prod').find('.var-attributo').removeClass('selected');
		$(this).closest('.var-attributo').addClass('selected');
	});

	/*if( typeof js_new_product_with_child != 'undefined' && js_new_product_with_child != null ){
		console.log(js_new_product_with_child);
		notify_top('Aggiungi le variazioni per il prodotto!','success');
	}*/	
});


function salva_variazione(){
	var form = $('#form_var_prod').serialize();
	

	$.ajax({
	  type: "POST",
		  url: "index.php",
		  data: { action: "add_child_rapid_ok",'formdata':form,ctrl: "ProductAdmin",ajax:1,mod:'catalogo'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$.ajax({
					  type: "GET",
						  url: "index.php",
						  data: { action: "get_children_stock",'id':data.id,ctrl: "ProductAdmin",ajax:1,mod:'catalogo'},
						  dataType: "json",
						  success: function(data){
								if(data.result == 'ok'){
									$('#list_child_product').append(data.html)
									$('#figli-table').show();
									$('#nessuna_variazione').hide();
									
									//notify_top('Variazione aggiunta con successo!','success');
								}else{
									//notify(data.errore,'error');
								}
						  },
					 
					});
				}else{
					//notify(data.errore,'error');
				}
		  },
	 
	});
}
function salva(){
	var form = $('#form').serialize();
	show_loader();
	
	$.ajax({
	  type: "POST",
		  url: "index.php",
		  data: { action: $('#action').val(),'formdata':form,ctrl: "ProductAdmin",ajax_request:1,mod:'catalogo'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
					document.location.href=data.redirect;
				}else{
					hide_loader();
					
					notify_top(data.error,'error');
				}
		  },
	 
	});
}



function salva_e_rimani(){
	var form = $('#form').serialize();
	show_loader();
	
	$.ajax({
	  type: "POST",
		  url: "index.php",
		  data: { action: $('#action').val(),'formdata':form,ctrl: "ProductAdmin",ajax_request:1,mod:'catalogo'},
		  dataType: "json",
		  success: function(data){
				hide_loader();
				if(data.result == 'ok'){
					if( data.force_redirect ){
						notify_top('Prodotto salvato con successo!','success');
						document.location.href=data.redirect;
					}else{
						if( data.modules ){
							for( var k in data.modules ){
								$('#tab_'+k).find('.col-md-12').html(data.modules[k]);
							}
						}
						notify_top('Prodotto salvato con successo!','success');
					}
				}else{
					if( data.tab != '' ){
						
						$('.'+data.tab).find('a').trigger('click');
					}
					notify_top(data.error,'error');
				}
		  },
	 
	});
}


function submit_bulk_action_products(url){
	
	var fd = {};
	var cont = 0;
	var check = false;
	$('.check_action_bulk').each(function(i){
		if( $(this).prop('checked') ){
			check = true;
			fd[cont] = $(this).val();
			cont = cont+1;
		}
	})
	if( !check ){
		alert('Nessun prodotto selezionato');
	}else{
		document.location.href=url+"&id="+JSON.stringify(fd);
	}
	

}
