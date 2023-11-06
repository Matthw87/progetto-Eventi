
$(document).ready(function(){
	if( $('.variationsProduct').length > 0){
		$('.variationsProduct').each(function(index,value){
			if( index != 0 ){
				var attribute = $(this).attr('attribute');
				if( $(this).attr('type_option') == 'radio' ){
					$('.'+attribute).each(function(){
						$(this).attr('disabled','disabled').closest('div').find('label').addClass('not_aviable');
					});
				}else{
					var select = $('#'+attribute);
					select.attr('disabled','disabled');
				}
			}
			
		});
	}

	
	$('.variationsProduct input[type=radio]').on('change',function(){
		var el = $(this);
		$(this).closest('ul').find('label').each(function(){
			$(this).removeClass('attribute_selected');
		});
		if( el.prop('checked') == true ){
			
			el.closest('li').find('label').addClass('attribute_selected');
		}
	})
		
	
});

function order_catalog(return_location,val){
	
	if(!val) return false;
	if( val == 'hight' || val == 'low' ){ 
		orderkey = 'price';
	}else{
		orderkey = val;
		val = 'asc';
	}

	

	var res = return_location.split("&orderkey");
	return_location = res[0];
	var res = return_location.split("?orderkey"); 
	return_location = res[0];
	
	
	var matches = return_location.match(/\?/);
	
	if( matches != null ){
		document.location.href=return_location+"&orderkey="+orderkey+"&ordervalue="+val;
	}else{
		document.location.href=return_location+"?orderkey="+orderkey+"&ordervalue="+val;
	}
	
}

function pagenumber_catalog(return_location,val){
	
	if(!val) return false;
	
	var matches = return_location.match(/pagenumber/);
	if( matches != null ){
		return_location = return_location.replace(/pagenumber\=([0-9]+)/,'pagenumber='+val);
		document.location.href=return_location;
		return;
	}
	

	var res = return_location.split("&pagenumber"); 
	
	return_location = res[0];
	
	
	if( return_location == '/catalog.php'){
		document.location.href=return_location+"?pagenumber="+val;
	}else{
		var matches = return_location.match('/?/');
		
		if( matches != null ){
			document.location.href=return_location+"&pagenumber="+val;
		}else{
			document.location.href=return_location+"&pagenumber="+val;
		}
	}
}


function change_images_product(formdata){
	if( typeof js_prodotto_popup != 'undefined' && js_prodotto_popup != null ){
		var popup = 1
	}else{
		var popup = 0;
	}
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'Catalogo',mod:'catalogo',ajax:1,action: "getImagesProduct", formdata: formdata, prodotto_popup:popup},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					if( $('.foto').length > 0 ){
						$('.foto').replaceWith(data.html);
						$(".netta").click(function(){
							$(".netta").removeClass("active_netta");
							var path = $(this).attr("src");
							$(".imgbig").attr("src", path);
							$(".zoomImg").attr("src", path);
							$(".fancybox").attr("href", path);
							$(this).toggleClass("active_netta");
						});
						if( typeof data.price_box != 'undefined' && data.price_box != null){
							$('.price').replaceWith(data.price_box);
						}
					}
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}


function reset_next_attributes(element){
	
	var el = element.closest('.variationsProduct')
	if( el ) {
		el.nextAll().each(function(){
			
			var attribute = $(this).attr('attribute');
			if( attribute ){
				if( $(this).attr('type_option') == 'radio' ){

						$('.'+attribute).each(function(){
							$(this).attr('disabled','disabled').closest('div').find('label').addClass('not_aviable');
							$(this).prop('checked',false).trigger('change');
						});
				}else{
					$('#'+attribute).val(0);
				
				}
			}
		});
	}
}

function changeAttribute(el){
	if( (el.attr('type') == 'radio' && el.prop('checked') == true) || el.attr('type') == 'select' ){
		reset_next_attributes( el );
		$('.loading-variations').removeClass('loading-variations');
		var formdata = $('#addCart').serialize();
		let product_id = $('#product_id').val();
		if( el ){

			if( el.attr('type') == 'radio' ){
				if( el.attr('name_option') ){
					el.closest('.variationsProduct').find('#name_variation_selected').html(": "+el.attr('name_option'));
				}
			}

			var current = el.closest('.variationsProduct');
			
			var current_val = el.val();
			
			var next = current.next();
			change_images_product(formdata);
			if( current_val && next && next.hasClass('variationsProduct')){
				next.find('.radiocolor').addClass('loading-variations');
				attribute = next.attr('attribute');
				$.ajax({
					  type: "GET",
					  url: `/catalogo/product/${product_id}/get-next-attributes/${attribute}`,
					  data: { attribute : attribute, formdata: formdata},
					  dataType: "json",
					  success: function(data){
							$('.loading-variations').removeClass('loading-variations');
							if(data.result == 'ok'){
								if( next.attr('type_option') == 'radio' ){
									$('.'+attribute).each(function(){
										$(this).prop('checked',false).attr('disabled','disabled').closest('div').find('label').addClass('not_aviable');
									});
									for( var k in data.options ){
										if( k != 0 ){
											$('#'+attribute+"_"+k).removeAttr('disabled').closest('div').find('label[for="'+attribute+'_'+k+'"]').removeClass('not_aviable');
										}
									}
								}else{
									var select = $('#attribute_'+attribute);
									console.log(select);
									crea_select(select,data.options,0);
									select.removeAttr('disabled');
								}
								
							}else{
								MarionAlert(js_error_title_alert,data.error);
							}
					  }
					 
				});

			}
		}else{

			$.ajax({
				  type: "GET",
				  url: "index.php",
				  data: { ctrl:'Catalogo',mod:'catalogo',ajax:1,action: "getNextAttributeValues", formdata: formdata},
				  dataType: "json",
				  success: function(data){
						if(data.result == 'ok'){
							
							if( data.type == 'radio' ){
								$('.'+data.attribute).each(function(){
									$(this).prop('checked',false).attr('disabled','disabled').closest('div').find('label').addClass('not_aviable');
								});
								for( var k in data.options ){
									if( k != 0 ){
										$('#'+data.attribute+"_"+k).removeAttr('disabled').closest('div').find('label[for="'+data.attribute+'_'+k+'"]').removeClass('not_aviable');
									}
								}
							}else{
								var select = $('#'+data.attribute);
								crea_select(select,data.options,0);
								select.removeAttr('disabled');
							}
							
						}
				  }
				 
			});

		}
	}
	

}
//CARICAMENTO AJAX
var loading_ajax_scroll = false;
function show_other_products(el,url,page){
	$('.end_list_page').hide();
	loading_ajax_scroll = true;
	var next = parseInt(page) +1; 
	if( $('#loader-ajax').length > 0 ){
		$('#loader-ajax').show();
	}
	$.ajax({
		  type: "GET",
		  url: url,
		  data: { page: next,ajax_pager:1},
		  dataType: "json",
		  success: function(data){
				loading_ajax_scroll = false;
				if(data.result == 'ok'){
					if( $('#loader-ajax').length > 0 ){
						$('#loader-ajax').hide();
					}


					$('.contprod').append(data.html);//.hide().fadeIn(500);
					var check = parseInt(data.other_products);
					
					if( check == 1 ){
						el.attr('onclick',"show_other_products($(this),'"+url+"',"+next+")");
						$('.end_list_page').show();
						
					}else{
						el.hide();
					}
					LL.update();
				}
		  }
		 
	});

}

function scrolling_ajax_catalog(){
	
	if( $('.end_list_page').length > 0 ){
		$('.end_list_page').on('inview', function(event, isInView) {

			//console.log('dentro inview');
			if (isInView) {
				//console.log('scrolling ajax');
				if( !loading_ajax_scroll ){
					$('#btn-showmore').trigger('click');
				}
				// element is now visible in the viewport

			} else {

				// element has gone out of viewport

			}
		});
	}
	
}

$(document).ready(function(){
	scrolling_ajax_catalog();
});




function plus_add_cart(el){
	var el2 = el.closest('form').find(':text');

	console.log(el2);
	var val = parseInt(el2.val());
	
	if( val <= 0 ){
		val = 1
	}else{
		val = val +1;
	}
	el2.val(val);
	
}

function minus_add_cart(el){
	var el2 = el.closest('form').find(':text');
	var val = parseInt(el2.val());
	
	if( val <= 0 ){
		val = 1
	}else{
		val = val -1;
	}
	el2.val(val);
}

