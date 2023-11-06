var edit_home_page = false;
var widget_copy_array=[];

function removeFromCopied(id){
	
	widget_copy_array = jQuery.grep(widget_copy_array, function(value) {
	  return value != id;
	});
}

function copy_widget(el,id){
	if( $.inArray(id,widget_copy_array) != -1 ){
		removeFromCopied(id);
		el.closest('.element-child').removeClass('element-copied');
		el.find('i').removeClass('fa-undo').addClass('fa-copy');
	}else{
		widget_copy_array.push(id);
		$('.incolla-widget').show();
		el.closest('.element-child').addClass('element-copied');
		el.find('i').removeClass('fa-copy').addClass('fa-undo');
	}
	if( widget_copy_array.length == 0 ){
		$('.incolla-widget').hide();
	}
	/*if( id == id_widget_copy ){
		id_widget_copy = null;
		$('.incolla-widget').hide();
		el.find('i').removeClass('fa-undo').addClass('fa-copy');
		el.closest('.element-child').removeClass('element-copied');
	}else{
		id_widget_copy = id;

		
	}*/
}




function incolla_widget(parent){
	$.ajax({
		  type: "GET",
		  url: "/admin/content.php",
		  data: { action: "paste_box",parent:parent,ids_box:JSON.stringify(widget_copy_array)},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					reload_list();
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}
function mostra_btn_save(){
	if( edit_home_page ){
		$('#btn_save_modify').show();
	}
}

function confirm_save_edit_home(){
	MarionConfirm('Conferma operazione','Sicuro di volere applicare le modifiche alla pagina?',function(){
		document.location.href="index.php?action=edit_page_ok&id="+js_id_home+"&block="+js_block;
	});
}

function confirm_reset_edit_page(){
	MarionConfirm('Conferma operazione','Sicuro di volere resettare le modifiche della pagina?',function(){
		document.location.href="index.php?action=reset_edit_page&id="+js_id_home+"&block="+js_block;
	});
}

function get_widgets(id_row,position){
	//MarionConfirm('Conferma operazione','Sicuro di volere eliminare questa blocco dalla home?',function(){
		$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { action: "get_widgets",id_row:id_row,position:position,id:js_id_home,block:js_block},
			  dataType: "json",
			  cache: true,
			  success: function(data){
					if(data.result == 'ok'){
						$('#content_widgets').html(data.html);
						
					}else{
						//MarionAlert(js_error_title_alert,data.error);
					}
			  }
			 
		});
	//});
	//var t = confirm('Sicuro di volere leiminare questo pagina?');
	//if(t) document.location.href="/admin/content.php?action=del_page&id="+id;
}
function elimina_blocco_home(id){
	//MarionConfirm('Conferma operazione','Sicuro di volere eliminare questa blocco dalla home?',function(){
		$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { action: "del_block", id: id},
			  dataType: "json",
			  cache: true,
			  success: function(data){
					if(data.result == 'ok'){
						edit_home_page = true;
						mostra_btn_save();
						reload_list();
						//swal.close();
						//$('#block_'+id).remove()
						
						
					}else{
						//MarionAlert(js_error_title_alert,data.error);
					}
					
			  }
			 
		});
	//});
	//var t = confirm('Sicuro di volere leiminare questo pagina?');
	//if(t) document.location.href="/admin/content.php?action=del_page&id="+id;
}

function cache_block(id,cache){
		$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { action: "cache_block", id:id,cache:cache},
			  dataType: "json",
			  cache: true,
			  success: function(data){
					if(data.result == 'ok'){
						edit_home_page = true;
						mostra_btn_save();
						reload_list();
						//notify('Blocco messo in cache','success');
					}else{
						//MarionAlert(js_error_title_alert,data.error);
					}
			  }
			 
		});
}
function save_blocco_css(el,id){
	//MarionConfirm('Conferma operazione','Sicuro di volere eliminare questa blocco dalla home?',function(){
		$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { action: "save_block_css", id:id, id_html:$('#id_html').val(),class_html:$('#class_html').val(),animate_css:$('#animate_css').val()},
			  dataType: "json",
			  cache: true,
			  success: function(data){
					if(data.result == 'ok'){
						edit_home_page = true;
						mostra_btn_save();
						el.attr('data-id',$('#id_html').val());
						el.attr('data-id',$('#id_html').val());
						el.attr('data-animate',$('#animate_css').val());
						//swal.close();
						//$('#block_'+id).remove()
						$('#modalStyle').modal('toggle');
						
					}else{
						//MarionAlert(js_error_title_alert,data.error);
					}
			  }
			 
		});
	//});
	//var t = confirm('Sicuro di volere leiminare questo pagina?');
	//if(t) document.location.href="/admin/content.php?action=del_page&id="+id;
}
function add_block_home(el,titolo,tipo,modulo,function_name,id,repeat,parent,position){
	console.log('sto quaaaaaa');
	
	if( !parent ) parent = 0;
	if( !position ) position = 0;
	if( !function_name ) function_name = '';
	
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "add_block_to_page", title: titolo, type: tipo,module: modulo,id:id,id_home:js_id_home,block:js_block,parent:parent,position:position,'function':function_name},
		  dataType: "json",
		  cache: false,
		  success: function(data){
				if(data.result == 'ok'){
					if( !repeat ){
						el.remove();
						


					}
					edit_home_page = true;
					mostra_btn_save();
					reload_list();
					if( parent ){
						$('#modal-widgets').modal('toggle');
					}
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}

function addAccordion(parent){
	var position = 0;
	var modulo = 0;
	var titolo = 'accordion';
	var tipo = 'accordion';
	var id = 0;
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "add_block_to_page", title: titolo, type: tipo,module: modulo,id:id,id_home:js_id_home,block:js_block,parent:parent,position:position},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					
					edit_home_page = true;
					mostra_btn_save();
					reload_list();
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}

function addTab(parent){
	var position = 0;
	var modulo = 0;
	var titolo = 'tab';
	var tipo = 'tab';
	var id = 0;
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "add_block_to_page", title: titolo, type: tipo,module: modulo,id:id,id_home:js_id_home,block:js_block,parent:parent,position:position},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					
					edit_home_page = true;
					mostra_btn_save();
					reload_list();
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}
function copy_accordion(id,parent){
	
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "paste_box",parent:parent,ids_box:JSON.stringify([id])},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					reload_list();
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}

function add_column(titolo,tipo,id,parent){
	if( !parent ) parent = 0;
	

	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "add_block_to_page", title: titolo, type: tipo,module: '',id:id,id_home:js_id_home,block:js_block,parent:parent,position:0},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					
					edit_home_page = true;
					mostra_btn_save();
					reload_list();
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}

function reload_list(){
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "edit_page_ajax",id:js_id_home,block:js_block},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){

					var old_tabs = [];
					var old_accordions = [];
					$('.tab-item').each(function(){
						if( $(this).hasClass('active') ){
							old_tabs.push($(this).attr('id'));
						}
					});
					$('.active_accordion').each(function(){
						
							old_accordions.push($(this).parent().attr('id'));
						
					});
					$('#composizione_home_list').html(data.html);
					load_nestable();
					if(widget_copy_array.length ){
						$('.incolla-widget').show();


						for( var k in widget_copy_array){
							$('#btn-copy-'+widget_copy_array[k]).find('i').removeClass('fa-copy').addClass('fa-undo');
							$('#btn-copy-'+widget_copy_array[k]).closest('.element-child').addClass('element-copied');
							
						}

						
					}
					load_sortable_columns();
					
					for( var k in old_tabs ){
						$('#'+old_tabs[k]).find('a').click();
					}
					for( var k in old_accordions ){
						$('#'+old_accordions[k]).find('.accordion').click();
					}
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
	
}
$(document).ready(function()
{

	load_nestable();
	load_sortable_columns();
	


});


function select_div(el){
	$('.riga-corrente').removeClass('riga-corrente');
	el.addClass('riga-corrente');
}

/*function show_buttons(){
	$('.edit_buttons').hide();
	$('.edit_buttons').each(function(){
		el = $(this);
		
		el.parent().on('mouseenter',function(){
			$('.edit_buttons').hide();
			var el2 = $(this).find('.edit_buttons').first().show();	
			select_div($(this));
			//show_parent(el2);
		})

		el.parent().on('mouseleave',function(e){
			
			$(this).find('.edit_buttons').first().hide();	

			var new_element = e.relatedTarget|| event.toElement;
			if( typeof new_element != 'undefined' && new_element != null){
				
				var next = $(new_element).find('.edit_buttons');
				if( next ){
					select_div(new_element);
					$('.edit_buttons').hide();
					next.first().show();
				}
			}
			//new_element.find('.edit_buttons').first().hide();	
		})
	});
}*/

function load_nestable(){
	var updateOutput = function(e)
	{
		
		edit_home_page = true;
		mostra_btn_save();
		var list   = e.length ? e : $(e.target),
			output = list.data('output');
			if (window.JSON) {
				


					
					$.ajax({
					  // definisco il tipo della chiamata
					  type: "POST",
					  // specifico la URL della risorsa da contattare
					  url: "index.php",
					  // passo dei dati alla risorsa remota
					  data: { 'action': "save_composition",'list' : list.nestable('serialize'),id:js_id_home,block:js_block},
					  // definisco il formato della risposta
					  dataType: "json",
					  // imposto un'azione per il caso di successo
					  success: function(data){
							
					  },
					  // ed una per il caso di fallimento
					  error: function(){
						alert("Chiamata fallita!!!");
					  }
					});


			   
			} else {
				output.val('JSON browser support required for this demo.');
			}
	};

	// activate Nestable for list 1
	$('#nestable').nestable({
		maxDepth: 1,
	})
	.on('change', updateOutput);

}


function open_modal_widget(id,position){
	$('#modal-widgets').modal('toggle');
	get_widgets(id,position);
}




function open_modal_style(el,id){
	$('#modalStyle').find('#id_html').val(el.attr('data-id'));
	//alert(el.attr('data-animate'));
	//console.log($('#modalStyle').find('#animate_css'));
	$('#modalStyle').find('#animate_css').val(el.attr('data-animate'));
	$('#modalStyle').find('#class_html').val(el.attr('data-class'));
	$('#modalStyle').find('#id_row').val(id);
	$('#modalStyle').modal('toggle');
	$( "#btn_save_css" ).unbind( "click" );
	$('#btn_save_css').on('click',function(){
		save_blocco_css(el,id);
	});
	
}


function open_edit_page2(id,url){
	$('#btn-save-wiget-conf').attr('id_box',id);
	$.ajax({
		  type: "GET",
		  url: url+"&id_box="+id,
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					 //reload_list()
					$('#modal-widget-conf').modal('toggle');
					$('#content_widget_conf').html(data.html);
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
		});
}

function open_edit_page(id,url){

	edit_home_page = true;
	mostra_btn_save();

	$.magnificPopup.open({
	  items: {
		src: url+"&id_box="+id
	  },
	  type: 'iframe'
	}, 0);
}

function sort_items(id){
	
	$.magnificPopup.open({
	  items: {
		src: "index.php?action=sort_items&id_box="+id
	  },
	  type: 'iframe',
	  callbacks: {
		  close: function(){
			 reload_list()
		  }
	  },
	}, 0);

}


function load_sortable_columns(){
	$('.cont-columns').sortable({

	  
	  update: function( event, ui ) {

			if(event.sender == null){
				// call ajax here
			
				var list = []
				
				$(this).find('.sortable').each(function(){
					if( $(this).attr('id') ){
						list.push($(this).attr('id'))
					}
				});

				$.ajax({
				  type: "GET",
				  url: "index.php",
				  data: { action: "save_order_row", items:JSON.stringify(list)},
				  dataType: "json",
				  cache: true,
				  success: function(data){
						if(data.result == 'ok'){
							 //reload_list()
							
						}else{
							//MarionAlert(js_error_title_alert,data.error);
						}
				  }
				 
				});
			}
			
	  }
	});
	load_sortable_tabs();
	load_sortable_items();
	load_accordions();
	show_buttons();
	

}

function load_sortable(classe){
	$('#'+classe).sortable({

	  
	  update: function( event, ui ) {

			if(event.sender == null){
				// call ajax here
			
				var list = []
				
				$(this).find('.element-child').each(function(){
					if( $(this).attr('id') ){
						list.push($(this).attr('id'))
					}
				});

				$.ajax({
				  type: "GET",
				  url: "index.php",
				  data: { action: "save_order_row", items:JSON.stringify(list)},
				  dataType: "json",
				  cache: true,
				  success: function(data){
						if(data.result == 'ok'){
							 //reload_list()
							
						}else{
							//MarionAlert(js_error_title_alert,data.error);
						}
				  }
				 
				});
			}
			
	  }
	});
}

function load_sortable_items(){
	//load_sortable('cont-elements');
	//load_sortable('cont-div-row');
	$('.cont-elements').sortable({

	  
	  update: function( event, ui ) {

			if(event.sender == null){
				// call ajax here
			
				var list = []
				
				$(this).find('.element-child').each(function(){
					if( $(this).attr('id') ){
						list.push($(this).attr('id'))
					}
				});

				$.ajax({
				  type: "GET",
				  url: "index.php",
				  data: { action: "save_order_row", items:JSON.stringify(list)},
				  dataType: "json",
				  cache: true,
				  success: function(data){
						if(data.result == 'ok'){
							 //reload_list()
							
						}else{
							//MarionAlert(js_error_title_alert,data.error);
						}
				  }
				 
				});
			}
			
	  }
	});
	load_sortable('cont-div-row');

}



function load_sortable_tabs(){
	$('.cont-tabs').sortable({

	  
	  update: function( event, ui ) {

			if(event.sender == null){
				// call ajax here
			
				var list = []
				
				$(this).find('.tab-element').each(function(){
					if( $(this).attr('id') ){
						list.push($(this).attr('id'))
					}
				});

				$.ajax({
				  type: "GET",
				  url: "index.php",
				  data: { action: "save_order_row", items:JSON.stringify(list)},
				  dataType: "json",
				  cache: true,
				  success: function(data){
						if(data.result == 'ok'){
							 //reload_list()
							
						}else{
							//MarionAlert(js_error_title_alert,data.error);
						}
				  }
				 
				});
			}
			
	  }
	});
	$('.tabs-list').sortable({
	  cancel: ".add_tab",
	 // items : ':not(.add_tab)',
	  update: function( event, ui ) {

			if(event.sender == null){
				// call ajax here
			
				var list = []
				
				$(this).find('li').each(function(){
					if( $(this).attr('id') ){
						list.push($(this).attr('id'))
					}
				});

				$.ajax({
				  type: "GET",
				  url: "index.php",
				  data: { action: "save_order_row", items:JSON.stringify(list),type:'tab'},
				  dataType: "json",
				  cache: true,
				  success: function(data){
						if(data.result == 'ok'){
							 //reload_list()
							
						}else{
							//MarionAlert(js_error_title_alert,data.error);
						}
				  }
				 
				});
			}
			
	  }
	});
}


function load_accordions(){

	var acc = document.getElementsByClassName("accordion");
	var i;
	
	for (i = 0; i < acc.length; i++) {
	  acc[i].addEventListener("click", function() {
		/* Toggle between adding and removing the "active" class,
		to highlight the button that controls the panel */
		this.classList.toggle("active_accordion");

		/* Toggle between hiding and showing the active panel */
		var panel = this.nextElementSibling;
		if (panel.style.display === "block") {
		  panel.style.display = "none";
		} else {
		  panel.style.display = "block";
		}
	  });
	}

	/*/$('.accordion-item').sortable({
	 // items : ':not(.add_tab)',
	  update: function( event, ui ) {

			if(event.sender == null){
				// call ajax here
			
				var list = []
				
				$('.accordion-item').each(function(){
					if( $(this).attr('id') ){
						list.push($(this).attr('id'))
					}
				});

				$.ajax({
				  type: "GET",
				  url: "/admin/content.php",
				  data: { action: "save_order_row", items:JSON.stringify(list)},
				  dataType: "json",
				  cache: true,
				  success: function(data){
						if(data.result == 'ok'){
							 //reload_list()
							
						}else{
							//MarionAlert(js_error_title_alert,data.error);
						}
				  }
				 
				});
			}
			
	  }
	});*/

}
