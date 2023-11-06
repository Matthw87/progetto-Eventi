var list_products_related = {};
var list_sections_related = {};

var cont_section_related = 0;
var cont_prod_related = 0;
function add_section_related(val){
	if( val != 0 ){
		
		if( list_sections_related[val] ){
			alert('Sezione già selezionata');
			$('#related').val(0);
			$('#related').selectpicker("refresh");
			return false;
		}
		list_sections_related[val] = val;
		
		cont_section_related = cont_section_related+1;
		var t = $('#modello_sezione_correlato').clone().appendTo('#cont_related').attr('id','section_related_'+cont_section_related).show();
		
		t.find('#nome_sezione').html($("#related option[value='"+val+"']").text());
		t.find('#elimina').attr('onclick',"remove_section_related("+cont_section_related+","+val+"); return false;");
		t.find('#product_related_add').attr('id','product_related_add_'+cont_section_related).attr('num_div',cont_section_related).show();
		t.find('#cont_prod_section').attr('id','cont_prod_section_'+cont_section_related).show();
		t.find('#section_related_value').attr('name','formdata[section_related]['+cont_section_related+"][section]").val(val);
		t.find('#limit_related_products').attr('name','formdata[section_related]['+cont_section_related+"][num_products]");
		
		t.find('#box_container_product_related').attr('id','box_container_product_related_'+cont_section_related);
		
		t.find('#search_product').autocomplete({
		  source: function( request, response ) {
                $.ajax({
                    type: 'GET',
                    cache: false,
                    dataType: 'json',
                    crossDomain: true,
                    url: 'index.php',
                    data: {
					 ctrl: 'ProductAdmin',
					 mod:'catalogo',
				     ajax: 1,
                     action: 'get_product_section',
                     section: val,
                     featureClass: "P",
                     style: "full",
                     //maxRows: 6,
                     name: request.term
                    },
                    success: function( data ) {
                     response( $.map( data.data, function( item ) {
                       return {
                         label: item.name, // + (item.sezione ? ", " + item.sezione : ""), //+ ", " + item.countryName,
                         value: item.name,
						 id: item.id,
						 img: item.img,
                       }
                     }));
                     }
                });
              },
              minLength: 2,
              select: function( event, ui ) {
				 
				  $('#product_related_add_'+cont_section_related).attr('product_related_name',ui.item.value);
				  $('#product_related_add_'+cont_section_related).attr('product_related_id',ui.item.id);
				  $('#product_related_add_'+cont_section_related).attr('product_related_img',ui.item.img);
              },
              open: function() {
                $(this).removeClass( "ui-corner-all" ).addClass( "ui-corner-top");
              },
              close: function() {
                $(this).removeClass( "ui-corner-top" ).addClass( "ui-corner-all");
              }
		});
		
		$('#section_related_'+cont_section_related+" .flag_related_selection").each(function(){
			$(this).attr('name','formdata[section_related]['+cont_section_related+"][type]").attr('onchange',"change_related_selection($(this),"+cont_section_related+"); return false;");
		});

		$('#section_related_'+cont_section_related+" #flag_random").attr('id','flag_random_'+cont_section_related);
		$('#section_related_'+cont_section_related+" #flag_specific").attr('id','flag_specific_'+cont_section_related);
	}
	$('#related').val(0);
	$('#related').selectpicker("refresh");
}

function change_related_selection(el,num){
	if(el.prop('checked') == true){
		if( el.val() == 'random' ){
			$('#flag_random_'+num).show();
			$('#flag_specific_'+num).hide();
			$('#box_container_product_related_'+num).hide();
		}else{
			$('#flag_random_'+num).hide();
			$('#flag_specific_'+num).show();
			$('#box_container_product_related_'+num).show();
		}
	}
	
}

function add_product_related(el,num){
	
		
	el.closest('.flag_specific').find('input').val('');


	
	if( el.attr('product_related_id') ){
		var val = parseInt(el.attr('product_related_id'));
		if( list_products_related[val] ){
			alert('Articolo già presente');
			return false;
		}
		console.log(num);
		if( num == null ){
			num = cont_section_related;
		}
		
		
		list_products_related[val] = val;
		cont_prod_related = cont_prod_related +1;
		var temp =$('#modello_prodotto_correlato').clone().appendTo('#cont_prod_section_'+num).attr('id','prod_related_'+cont_prod_related)
		temp.find('#prodotto_correlato_name').html(el.attr('product_related_name'));
		if( el.attr('product_related_img') != 'false' ){
			$('#prod_related_'+cont_prod_related).find('#prodotto_correlato_img').attr('src',el.attr('product_related_img'));
		}
		temp.find('#elimina').addClass('elimina_prodotto_correlato_'+num).attr('onclick',"remove_product_related($(this),"+el.attr('product_related_id')+"); return false;");
		temp.find('#product_related_value').attr('name','formdata[section_related]['+num+"][products][]").val(el.attr('product_related_id'));
		el.removeAttr('product_related_id');
		temp.show();
	}
}

function remove_product_related(el,id){
	el.closest('.panel').remove();
	delete list_products_related[id]; 
}

function remove_section_related(num,id){
	
	delete list_sections_related[id]; 
	console.log($(".elimina_prodotto_correlato_"+num));
	$(".elimina_prodotto_correlato_"+num).each(function(){
		$(this).trigger('click');
	});
	$('#section_related_'+num).remove();
	
}


$(document).ready(function(){
	
	if( typeof js_num_products_related != 'undefined' && js_num_products_related != null ){
		cont_prod_related = parseInt(js_num_products_related);
		
	}

	if( typeof js_num_sections_related != 'undefined' && js_num_sections_related != null ){
		cont_section_related = parseInt(js_num_sections_related);
		
	}
	
	if( typeof js_list_sections_related != 'undefined' && js_list_sections_related != null ){

		var tmp_js_list_sections_related = jQuery.parseJSON( js_list_sections_related );
		
		for( var j in tmp_js_list_sections_related){
			list_sections_related[tmp_js_list_sections_related[j]] = tmp_js_list_sections_related[j];
		}
		
	}

	if( typeof js_list_products_related != 'undefined' && js_list_products_related != null ){
		var tmp_js_list_products_related = jQuery.parseJSON( js_list_products_related );
		for( var j in tmp_js_list_products_related){
			list_products_related[tmp_js_list_products_related[j]] = tmp_js_list_products_related[j];
		}
	}



	if( $('.relatedProductSearch').length > 0 ) {
		$('.relatedProductSearch').each(function(){
			var val = $(this).attr('section');
			var num = $(this).attr('num');
			$(this).autocomplete({
			  source: function( request, response ) {
					$.ajax({
						type: 'GET',
						cache: false,
						dataType: 'json',
						crossDomain: true,
						url: 'index.php',
						data: {
						 ctrl: 'ProductAdmin',
						 ajax: 1,
						 mod:'catalogo',
						 action: 'get_product_section',
						 section: val,
						 featureClass: "P",
						 style: "full",
						 //maxRows: 6,
						 name: request.term
						},
						success: function( data ) {
						 response( $.map( data.data, function( item ) {
						   return {
							 label: item.name, // + (item.sezione ? ", " + item.sezione : ""), //+ ", " + item.countryName,
							 value: item.name,
							 id: item.id,
							 img: item.img,
						   }
						 }));
						 }
					});
				  },
				  minLength: 2,
				  select: function( event, ui ) {
					  $('#product_related_add_'+num).attr('product_related_name',ui.item.value);
					  $('#product_related_add_'+num).attr('product_related_id',ui.item.id);
					  $('#product_related_add_'+num).attr('product_related_img',ui.item.img);
				  },
				  open: function() {
					$(this).removeClass( "ui-corner-all" ).addClass( "ui-corner-top");
				  },
				  close: function() {
					$(this).removeClass( "ui-corner-top" ).addClass( "ui-corner-all");
				  }
			});
				
		});
		

	}
	
});
