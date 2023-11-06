var edit_home_page = false;
var widget_copy_array=[];
var id_widget_to_import = 0;
var enabled_add_widget = true;
function removeFromCopied(id){
	
	widget_copy_array = jQuery.grep(widget_copy_array, function(value) {
	  return value != id;
	});
}


function cancel_copy(){
	widget_copy_array  = [];
	$('.incolla-widget').hide();
	$('.element-copied').removeClass('element-copied');
	$('#btn-cancel-copy').hide();
	$('.edit_buttons').removeClass('btns-disabled');
	$('.pg-btn-copy').show();
	$('.add-widget-btn').show();
	$('.pc-widget-copied').hide();
	
}


function disable_copy_widget(el,id){
	removeFromCopied(id);
	$('.incolla-widget').html('Incolla '+ widget_copy_array.length+' elementi');
	el.parent().find('.pc-widget-copied').hide();

	el.parent().find('.element-child').removeClass('element-copied');
	el.parent().find('.btns-disabled').show();
	$('#btn-copy-'+id).css('display','inline-block');
	if( widget_copy_array.length == 0 ){
		$('.incolla-widget').hide();
		cancel_copy();
	}
	
}


function rebuild_copied_elements(){
	//console.log(widget_copy_array);
	if( widget_copy_array.length > 0 ){
		var tmp = widget_copy_array;
		for( var k in tmp){
			if( $('#btn-copy-'+tmp[k]).length > 0 ){
				element_copied($('#btn-copy-'+tmp[k]));
			}else{
				removeFromCopied(tmp[k]);
			}
		}
		if( widget_copy_array.length > 0 ){
			$('.incolla-widget').html('Incolla '+ widget_copy_array.length+' elementi').show();
		}else{
			cancel_copy();
		}
	}
}


function element_copied(btn){
	//console.log(btn);
	btn.closest('.edit_buttons').parent().find('.pc-widget-copied').show();

}

function copy_widget(el,id){
	//console.log(el);
	if( $.inArray(id,widget_copy_array) != -1 ){
		removeFromCopied(id);
		$('.incolla-widget').html('Incolla '+ widget_copy_array.length+' elementi');
		el.closest('.element-child').removeClass('element-copied');
		el.closest('.edit_buttons').parent().find('.pc-widget-copied').hide();
		//el.find('i').removeClass('fa-undo').addClass('fa-copy');
	}else{
		widget_copy_array.push(id);
		$('.incolla-widget').html('Incolla '+ widget_copy_array.length+' elementi').show();
		el.closest('.element-child').addClass('element-copied');
		el.closest('.edit_buttons').parent().find('.pc-widget-copied').show();
		$('#btn-copy-'+id).hide();
		//el.find('i').removeClass('fa-copy').addClass('fa-undo');
	}
	if( widget_copy_array.length == 0 ){
		$('.incolla-widget').hide();
	}
	$('.edit_buttons').addClass('btns-disabled');
	$('#btn-cancel-copy').show();
	$('.add-widget-btn').hide();

	
	/*if( id == id_widget_copy ){
		id_widget_copy = null;
		$('.incolla-widget').hide();
		el.find('i').removeClass('fa-undo').addClass('fa-copy');
		el.closest('.element-child').removeClass('element-copied');
	}else{
		id_widget_copy = id;

		
	}*/
}
function pagecomposer_edit_name_widget(id){
	$('.pagecomposer_name_element_input').hide();
	$('.pagecomposer_name_element').show();
	
	$('#pagecomposer_name_element_input_'+id).show();
	$('#pagecomposer_name_element_'+id).hide();
	
}

function editor_css(id){
	//console.log("index.php?action=editor_css&id_box="+id+"&ctrl=PageComposerAdmin&mod=pagecomposer");

	$('#modal-editor-css').clone().appendTo('body').attr('id','modal-editor-css'+id);
	$("#modal-editor-css"+id).iziModal({
		iframe: true,
		iframeHeight: 500,
		iframeURL: "index.php?action=editor_css&id_box="+id+"&ctrl=PageComposerAdmin&mod=pagecomposer"
	});

	$("#modal-editor-css"+id).iziModal('open');


}

function export_row(id){
	//console.log("index.php?action=editor_css&id_box="+id+"&ctrl=PageComposerAdmin&mod=pagecomposer");
	 $("#modal-widget-export").iziModal();
	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: {action:'export_row',ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,id:id,id_page:js_id_home},
		  //dataType: "json",
		  cache: true,
		  success: function(data){
			  //$("#content_export_widget").html(data);
			  var html = jQuery('<div />').text(data).html()
			   var modal = $("#modal-widget-export").iziModal();
			   modal.iziModal('setContent', "<div style='padding:10px;'><textarea id='textarea_export_"+id+"' style='width:100%; height:200px;' readonly>'"+html+"'</textarea><button style='margin-top:10px; margin-bottom:10px;' class='btn btn-sm btn-primary pull-right' id='btn-save-import' onclick='coypy_export("+id+"); return false;'>COPIA</button></div>")
			   modal.iziModal('open');
			  
			 
				//alert(data);
				/*if(data.result == 'ok'){
					alert(data.data);
					 $("#content_export_widget").html(data.data);
					 $("#modal-widget-export").iziModal('open');
									 
					 
					 //reload_list()
					//console.log(data.id);
					//close_modal_editor_css(data.id);
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}*/
		  }
		 
		});


}


function coypy_export(id){
	var text = $('#textarea_export_'+id).val();
	console.log(text);
	var input = document.createElement('textarea');
	document.body.appendChild(input);
	input.value = text;
	//input.focus();
	input.select();
	document.execCommand('Copy');
	input.remove();
	alert('Contenuto copiato negli appunti!');
}

function import_row(id){



	id_widget_to_import = id;
	//console.log("index.php?action=editor_css&id_box="+id+"&ctrl=PageComposerAdmin&mod=pagecomposer");
	 //$("#modal-widget-export").iziModal();
	 var modal = $("#modal-widget-import").iziModal();
	 if( id == 0 ){
		 modal.iziModal('setContent', "<div style='padding:10px;'><textarea style='width:100%; height:200px;' id='import-widget-data'></textarea><button style='margin-top:10px; margin-bottom:10px;' class='btn btn-sm btn-primary pull-right' id='btn-save-import' onclick='import_row_ok(1); return false;'>IMPORTA E SOSTITUISCI</button><button style='margin-top:10px; margin-bottom:10px;' class='btn btn-sm btn-primary pull-right' id='btn-save-import' onclick='import_row_ok(); return false;'>IMPORTA</button></div>");
	 }else{
		 modal.iziModal('setContent', "<div style='padding:10px;'><textarea style='width:100%; height:200px;' id='import-widget-data'></textarea><button style='margin-top:10px; margin-bottom:10px;' class='btn btn-sm btn-primary pull-right' id='btn-save-import' onclick='import_row_ok(); return false;'>IMPORTA</button></div>");
	 }
	
	 // modal.iziModal('setContent', "<div style='padding:10px;'><textarea style='width:100%; height:200px;' readonly>'"+data+"'</textarea></div>")
	 modal.iziModal('open');

}

function import_row_ok(sostituisci){
	if( !sostituisci ) sostituisci = 0;
	var t = true;
	if( !id_widget_to_import && sostituisci == 1){
		t = confirm("Effettuando questa operazione i contenuti inseriti nellabozza verranno eliminati. Procedere?");
	}
	if( t ){
		$.ajax({
			  type: "POST",
			  url: "index.php",
			  data: {action:'import_row',ctrl:'PageComposerAdmin',mod:'pagecomposer',id:js_id_home,ajax:1,id_box:id_widget_to_import,data:$('#import-widget-data').val(),sostituisci:sostituisci},
			  dataType: "json",
			  cache: true,
			  success: function(data){
				  if(data.result == 'ok'){
					$("#modal-widget-import").iziModal('close');
					reload_list();
				  }else{
					alert(data.error);
				  }
				  //$("#content_export_widget").html(data);
				   /*var modal = $("#modal-widget-export").iziModal();
				   modal.iziModal('setContent', "<div style='padding:10px;'><textarea style='width:100%; height:200px;' readonly>'"+data+"'</textarea></div>")
				   modal.iziModal('open');*/
				  
				 
					//alert(data);
					/*if(data.result == 'ok'){
						alert(data.data);
						 $("#content_export_widget").html(data.data);
						 $("#modal-widget-export").iziModal('open');
										 
						 
						 //reload_list()
						//console.log(data.id);
						//close_modal_editor_css(data.id);
					}else{
						//MarionAlert(js_error_title_alert,data.error);
					}*/
			  }
			 
			});
	}

}


function editor_css_save(formdata){
	
	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: {action:'editor_css',formdata:formdata,ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					 //reload_list()
					console.log(data.id);
					close_modal_editor_css(data.id);
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
		});
}

function close_modal_editor_css(id){
	reload_list();
	
	$("#modal-editor-css"+id).iziModal('close');
	$('#modal-editor-css'+id).iziModal('destroy');
	$('#modal-editor-css'+id).remove();
}

function incolla_widget(parent){
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "paste_box",parent:parent,ids_box:JSON.stringify(widget_copy_array)},
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
	var t = confirm('Sicuro di volere applicare le modifiche alla pagina?');
	//MarionConfirm('Conferma operazione','Sicuro di volere applicare le modifiche alla pagina?',function(){
	if( t ){
		document.location.href="index.php?ctrl=PageComposerAdmin&mod=pagecomposer&action=edit_ok&id="+js_id_home+"&block="+js_block;
	}
	//});
}

function confirm_reset_edit_page(){

	//MarionConfirm('Conferma operazione','Sicuro di volere applicare le modifiche alla pagina?',function(){
	var t = confirm('Sicuro di volere resettare le modifiche della pagina?');
	if( t ){
		document.location.href="index.php?action=reset&ctrl=PageComposerAdmin&mod=pagecomposer&id="+js_id_home+"&block="+js_block;
	}
	
}

function get_widgets(id_row,position){
	//MarionConfirm('Conferma operazione','Sicuro di volere eliminare questa blocco dalla home?',function(){
		$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "get_widgets",id_row:id_row,position:position,id:js_id_home,block:js_block},
			  dataType: "json",
			  cache: true,
			  success: function(data){
					if(data.result == 'ok'){
						$('#content_widgets').html(data.html);

						$('.pagecomposer-ricerca-widgets').keyup(function(){
							
							const that = $(this);
							const key = that.val();
							const re = new RegExp(key, "gi");
							that.closest('.row').next().find('.colonna-widget').each(function(){
								$(this).show();
								title = $(this).attr('title');
								
								if( !title.match(re) ){
									$(this).hide();
								}
							})
							
						});
						
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
			  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "del_block", id: id},
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
			  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "cache_block", id:id,cache:cache},
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
			  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "save_block_css", id:id, id_html:$('#id_html').val(),class_html:$('#class_html').val(),animate_css:$('#animate_css').val()},
			  dataType: "json",
			  cache: true,
			  success: function(data){
					if(data.result == 'ok'){
						edit_home_page = true;
						/*mostra_btn_save();
						el.attr('data-id',$('#id_html').val());
						el.attr('data-id',$('#id_html').val());
						el.attr('data-animate',$('#animate_css').val());
						//swal.close();
						//$('#block_'+id).remove()
						$('#modalStyle').modal('toggle');*/
						reload_list();
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
	//console.log(enabled_add_widget,'qui');
	if( !parent ) parent = 0;
	if( !position ) position = 0;
	if( !function_name ) function_name = '';
	if( !enabled_add_widget ) return;
	enabled_add_widget = false;
	
	
	$('.img-righe').each(function(){
		$(this).removeClass('active');
		$(this).find('img').attr('src',$(this).find('img').attr('img_default'));
	});
	
	el.find('img').attr('src',el.find('img').attr('img_active'));
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "add_block_to_page", title: titolo, type: tipo,module: modulo,id:id,id_home:js_id_home,block:js_block,parent:parent,position:position,'function':function_name},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					if( !repeat ){
						//el.remove();
						


					}
					edit_home_page = true;
					mostra_btn_save();
					reload_list(data.id);
					if( parent ){
						$('#modal-widgets').modal('toggle');
					}
					enabled_add_widget = true;
					$('.sidebar-composer').removeClass('visible');
					if( parseInt(parent) == 0 ){
						$('html, body').animate({ 
							scrollTop: $(document).height()}, 
							800
						);
					}
					$('.img-righe').each(function(){
						$(this).removeClass('active');
						$(this).find('img').attr('src',$(this).find('img').attr('img_default'));
					});
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
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "add_block_to_page", title: titolo, type: tipo,module: modulo,id:id,id_home:js_id_home,block:js_block,parent:parent,position:position},
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
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "add_block_to_page", title: titolo, type: tipo,module: modulo,id:id,id_home:js_id_home,block:js_block,parent:parent,position:position},
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
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "paste_box",parent:parent,ids_box:JSON.stringify([id])},
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
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "add_block_to_page", title: titolo, type: tipo,module: '',id:id,id_home:js_id_home,block:js_block,parent:parent,position:0},
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

function reload_list(id_box){
		
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',id:js_id_home,block:js_block,fromAjax:1},
		  dataType: "json",
		  cache: true,
		  success: function(data){
				if(data.result == 'ok'){
					if( $('#composizione_home_list').length == 0 ){
						document.location.reload();
						return;
					}
					var old_tabs = [];
					var old_accordions = [];
					$('.tab-item').each(function(){
						if( $(this).hasClass('active') ){
							old_tabs.push($(this).attr('id'));
						}
					});

					$('.accordion').each(function(){
						if( $(this).hasClass('active_accordion') ){
							old_accordions.push($(this).closest('.accordion-item').attr('id'));
						}
					});


					
					//console.log(old_accordions);
					
					$('#composizione_home_list').html(data.html).removeClass('inizia-layout');//.css('margin-top', '120px');
					load_nestable();
					if(widget_copy_array.length ){
						$('.incolla-widget').show();
						
						$('.edit_buttons').addClass('btns-disabled');


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

					if( id_box ){
						$('#'+id_box).find('.edit_buttons').find('.edit_btn_widget').click();
					}
					add_label_widgets()

					rebuild_copied_elements();
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
	
}


function add_label_widgets(){
	$('.pagecomposer_name_element_input').on('keypress',function(e) {
		if(e.which == 13) {
			var id_widget = $(this).attr('id_widget');
			var name = $(this).val();
			var el = $(this);
			$.ajax({
			  // definisco il tipo della chiamata
			  type: "POST",
			  // specifico la URL della risorsa da contattare
			  url: "index.php",
			  // passo dei dati alla risorsa remota
			  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,'action': "save_custom_name",'name' : name,id:js_id_home,block:js_block,id_widget:id_widget,ajax:1},
			  // definisco il formato della risposta
			  dataType: "json",
			  // imposto un'azione per il caso di successo
			  success: function(data){
					el.hide();
					$('#pagecomposer_name_element_'+id_widget).html(name).show();
			  },
			  // ed una per il caso di fallimento
			  error: function(){
				alert("Chiamata fallita!!!");
			  }
			});
			
		}
	});
}
$(document).ready(function()
{	


	add_label_widgets();




	
	load_nestable();
	load_sortable_columns();
	


});

$(document).click(function(event) { 
  $target = $(event.target);
  if(!$target.closest('.pagecomposer_name_element_input').length && 
  $('.pagecomposer_name_element_input').is(":visible")) {
   
	$('.pagecomposer_name_element_input').hide();
	$('.pagecomposer_name_element').show();
  }        
});


function select_div(el){
	if( el.length > 0 ){
		$('.riga-corrente').removeClass('riga-corrente');
		if( el ){
			
			el.addClass('riga-corrente');
		}
	}
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
					  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,'action': "save_composition",'list' : list.nestable('serialize'),id:js_id_home,block:js_block},
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
	$('<div data-iziModal-fullscreen="true"  data-iziModal-title="Editor Widget"  data-iziModal-subtitle="Modifica i parametri del widget"  data-iziModal-icon="icon-home"></div>').appendTo('body').attr('id','modal-editor-widget'+id);
	$("#modal-editor-widget"+id).iziModal({
		title: 'Widget',
		subtitle: 'Modifica i parametri del widget',
		iframe: true,
		iframeHeight: 500,
		iframeURL: url+"&id_box="+id,
		onClosed: function(){
			reload_list();
		}
	});
	//cicciobello
	$("#modal-editor-widget"+id).iziModal('open');

}
/*function open_edit_page(id,url){

	edit_home_page = true;
	mostra_btn_save();

	$.magnificPopup.open({
	  items: {
		src: url+"&id_box="+id
	  },
	  type: 'iframe'
	}, 0);
}*/

function sort_items(id){
	


	$('<div data-iziModal-fullscreen="true"  data-iziModal-title="Ordina widget"  data-iziModal-subtitle="Modifica l\'ordine di visualizzazione dei widget"  data-iziModal-icon="icon-home"></div>').appendTo('body').attr('id','modal-editor-widget-sort');
	$("#modal-editor-widget-sort").iziModal({
		title: 'Ordina widget',
		subtitle: 'Ordina widget',
		iframe: true,
		iframeHeight: 500,
		iframeURL: "index.php?action=sort_items&id_box="+id+"&ctrl=PageComposerAdmin&mod=pagecomposer",
		onClosed: function(){
			 reload_list();
			 $('#modal-editor-widget-sort').iziModal('destroy');
		
		},
	});
	$("#modal-editor-widget-sort").iziModal('open');
	/*$.magnificPopup.open({
	  items: {
		src: 
	  },
	  type: 'iframe',
	  callbacks: {
		  close: function(){
			 reload_list()
		  }
	  },
	}, 0);*/

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
				  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "save_order_row", items:JSON.stringify(list)},
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
	//show_buttons();
	

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
				  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "save_order_row", items:JSON.stringify(list)},
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
				  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "save_order_row", items:JSON.stringify(list)},
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
				  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "save_order_row", items:JSON.stringify(list)},
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
				  data: { ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1,action: "save_order_row", items:JSON.stringify(list),type:'tab'},
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
