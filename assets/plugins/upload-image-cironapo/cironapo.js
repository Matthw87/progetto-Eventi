(function($) {

	$.fn.cironapo = function(options) {
	   var defaults = {
		locale : 'it',
		box_small: false,
		resize: null,//"thumbnail,small,medium,large"
		check_extension: true,
		extensions: 'jpeg,jpg,png,gif',
		dimension_resize: null,
		max_width: null,
		max_height: null,
		baseurl: '/',
		text_locale : 
		{
			  'it' : {
					text_no_image: 'Upload image',
					text_error_max_width: "L'ampiezza dell'immagine supera quella massima consentita",
					text_error_max_height: "L'altezza dell'immagine supera quella massima consentita",
					text_extension_not_aviable: "Estensione file non consentita",
					text_confirm_delete: "Sicuro di volere eliminare l'immagine?",
					text_delete_success: "Immagine eliminata con successo!!",
					text_error_upload : "Si è verificato un errore nell'upload dell'immagine.",
					text_aborted_upload : "Upload dell'immagine annullato!!",
					text_success_upload : "Immagine caricata con successo!!",
					text_attention_upload: "Eliminare prima l'immagine corrente",
					text_upload_button: 'carica',
					text_cancel_button: 'annulla',
					text_remove_button: 'rimuovi',
			  },
			  'en' : {
				   text_no_image: 'Upload Image',
				   text_error_max_width: "L'ampiezza dell'immagine supera quella massima consentita",
					text_error_max_height: "L'altezza dell'immagine supera quella massima consentita",
					text_extension_not_aviable: "Estensione file non consentita",
					text_confirm_delete: "Confirm operation?",
					text_delete_success: "Image deleted!!",
					text_error_upload : "An error occurred while transferring the file.",
					text_aborted_upload : "Upload aborted!!",
					text_success_upload : "Uploaded image with successs!!",
					text_attention_upload: "Eliminare prima l'immagine corrente",
					text_upload_button: 'upload',
					text_cancel_button: 'cancel',
					text_remove_button: 'delete',
			  }
  
		},
		id_wrapper: 'wrapper-cironapo',
		class_wrapper: '',
		show_alert_message: false, //stabilisce se mostrare a video come popup le informazioni dell'upload
		url_upload: 'index.php?ctrl=Media&type=img_single&ajax=1',
		url_remove: 'index.php?ctrl=Media&type=delete_image&ajax=1',
		data: null,
		type_url_image: "or-nw",
		id_field_img: 'image_field'
	  };
	  options = $.extend(defaults, options);
	  //console.log(options);
	  //nascondo l'input file
	  $(this).hide();
	  var element = $(this);
	  var check = true;
	  var send = false;
	  var xhr;
  
	  if(options.check_extension == true){
			options.extensions =  options.extensions.split(',');
	   }
  
  
  
	  if( $('#'+options.id_field_img).val() != '' ){
		  options.id = $('#'+options.id_field_img).val();
	  }
	  //creo il box
	  if( options.box_small ){
		  if( options.id != null ){
			  var html = '<div class="cironapo_container">'+
			  '<label class="cironapo_fileinput_sm" for="'+$(this).attr('id')+'">'+
			  '<span id="cironapo_no_image" style="display:none"><i class="fa fa-plus-circle"></i></span>'+
			  '<img id="ciornapo_img_preview" class="ciornapo_img_preview_sm" src='+options.baseurl+'img/'+options.id+'/'+options.type_url_image+'/logo.png" alt="" /><br>'+
			  '<img style="display:none;" id="cironapo_loader" src="'+options.baseurl+'plugins/upload-image-cironapo/img/ajax-load.gif" alt="" />'+
			  '<span id="cironapo_upload_message" style="display:none"></span>'+
			  '</label>'+
			  '<div class="text-center">'+
			  '<button class="btn btn-success btn-xs btn-cironapo" type="button" id="cironapo_upload" style="display:none"><i class="fa fa-upload"></i></button>'+
			  '<button class="btn btn-warning btn-xs btn-cironapo" type="button" id="cironapo_cancel" style="display:none"><i class="fa fa-reply"></i></button>'+
			  '<button class="btn btn-danger btn-xs btn-cironapo" type="button" id="cironapo_remove"><i class="fa fa-times"></i></button>'+
			  '</div>'+
			  '</div>';
			  $('#'+options.id_field_img).val(options.id);
		  }else{
			  var html = '<div class="cironapo_container">'+
			  '<label class="cironapo_fileinput_sm" for="'+$(this).attr('id')+'">'+
			  '<span id="cironapo_no_image"><i class="fa fa-plus-circle"></i></span>'+
			  '<img style="display:none;" id="ciornapo_img_preview" class="ciornapo_img_preview_sm" src="" alt="" /><br>'+
			  '<img style="display:none;" id="cironapo_loader" src="'+options.baseurl+'plugins/upload-image-cironapo/img/ajax-load.gif" alt="" />'+
			  '<span id="cironapo_upload_message" style="display:none"></span>'+
			  '</label>'+
			  '<div class="text-center">'+
			  '<button class="btn btn-success btn-xs btn-cironapo" type="button" id="cironapo_upload" style="display:none"><i class="fa fa-upload"></i></button>'+
			  '<button class="btn btn-warning btn-xs btn-cironapo" type="button" id="cironapo_cancel" style="display:none"><i class="fa fa-reply"></i></button>'+
			  '<button class="btn btn-danger btn-xs btn-cironapo" type="button" id="cironapo_remove" style="display:none"><i class="fa fa-times"></i></button>'+
			  '</div>'+
			  '</div>';
  
		  }
	  }else{
		  if( options.id != null ){
			  var html = '<div class="cironapo_container">'+
			  '<label class="cironapo_fileinput" for="'+$(this).attr('id')+'">'+
			  '<span id="cironapo_no_image" style="display:none"><i class="fa fa-plus-circle"></i></span>'+
			  '<img id="ciornapo_img_preview" class="ciornapo_img_preview" src='+options.baseurl+'img/'+options.id+'/'+options.type_url_image+'/logo.png" alt="" /><br>'+
			  '<img style="display:none;" id="cironapo_loader" src="'+options.baseurl+'plugins/upload-image-cironapo/img/ajax-load.gif" alt="" />'+
			  '<span id="cironapo_upload_message" style="display:none"></span>'+
			  '</label>'+
			  '<div class="text-center">'+
			  '<button class="btn btn-success btn-sm" type="button" id="cironapo_upload" style="display:none"><i class="fa fa-upload"></i> '+get_locale_text('text_upload_button')+'</button>'+
			  '<button class="btn btn-warning btn-sm" type="button" id="cironapo_cancel" style="display:none"><i class="fa fa-reply"></i> '+get_locale_text('text_cancel_button')+'</button>'+
			  '<button class="btn btn-danger btn-sm" type="button" id="cironapo_remove"><i class="fa fa-times"></i> '+get_locale_text('text_remove_button')+'</button>'+
			  '</div>'+
			  '</div>';
			  $('#'+options.id_field_img).val(options.id);
		  }else{
			  var html = '<div class="cironapo_container">'+
			  '<label class="cironapo_fileinput" for="'+$(this).attr('id')+'">'+
			  '<span id="cironapo_no_image"><i class="fa fa-plus-circle"></i> '+get_locale_text('text_no_image')+'</span>'+
			  '<img style="display:none;" id="ciornapo_img_preview" class="ciornapo_img_preview" src="" alt="" /><br>'+
			  '<img style="display:none;" id="cironapo_loader" src="'+options.baseurl+'plugins/upload-image-cironapo/img/ajax-load.gif" alt="" />'+
			  '<span id="cironapo_upload_message" style="display:none"></span>'+
			  '</label>'+
			  '<div class="text-center">'+
			  '<button class="btn btn-success btn-sm" type="button" id="cironapo_upload" style="display:none"><i class="fa fa-upload"></i> '+get_locale_text('text_upload_button')+'</button>'+
			  '<button class="btn btn-warning btn-sm" type="button" id="cironapo_cancel" style="display:none"><i class="fa fa-reply"></i> '+get_locale_text('text_cancel_button')+'</button>'+
			  '<button class="btn btn-danger btn-sm" type="button" id="cironapo_remove" style="display:none"><i class="fa fa-times"></i> '+get_locale_text('text_remove_button')+'</button>'+
			  '</div>'+
			  '</div>';
  
		  }
	  }
	  
	  if( options.class_wrapper){
		  $('#'+options.id_wrapper).addClass(options.class_wrapper);
	  }
	  $('#'+options.id_wrapper).html(html);
	  
	  
	  
	  //determino il testo nella lingua specificata
	  function get_locale_text(text){
			  return options.text_locale[options.locale][text];
		  
	  }
	  
  
  
	  function reset(){
		  $('#'+options.id_wrapper).find('#cironapo_upload').hide();
		  $('#'+options.id_wrapper).find('#cironapo_cancel').hide();
		  $('#'+options.id_wrapper).find('#cironapo_remove').hide();
		  $('#'+options.id_wrapper).find('#cironapo_no_image').show();
		  
		  $('#'+options.id_wrapper).find('#ciornapo_img_preview').attr('src','').hide();
		  $('#'+options.id_field_img).val('');
		  element.val('');
	  }
  
  
	  function delete_img(){
		  $.ajax({
								
			type: "POST",
			url: options.url_remove,
			data: {'id' : $('#'+options.id_field_img).val()},
			dataType: "json",
			success: function(data){
				  if(data.result == 'ok'){
					  reset();
					  if( options.show_alert_message ){
						  alert_message(get_locale_text('text_delete_success'));
					  }
				  }else{
					  
				  }
				  return false;
			},
			error: function(){
			  
			}
		  });
	  }
	  
	  $( this).click( function() {
		  if( $('#'+options.id_field_img).val() ){
			  alert_message(get_locale_text('text_attention_upload'));
			  return false;
		  }
		  
	  });
	  
	  //callback change image
	  $( this).change( function() {
		  if( $('#'+options.id_field_img).val() ){
			  return false;
		  }else{
			  var file = $(this).context.files[0];
			  
			  if(!checkExtension(file.name )){
				  if( options.show_alert_message ){
					  alert_message(get_locale_text('text_extension_not_aviable'));
				  }
				  //console.log(get_locale_text('text_extension_not_aviable'));
				  show_message(get_locale_text('text_extension_not_aviable'),'warning');
				  return false;
			  }
			  
			  send = false;
			  xhr = new window.XMLHttpRequest();
			  var img = $('#'+options.id_wrapper).find("#ciornapo_img_preview");
			  var fr = new FileReader();
			  var loaded = false;
			  fr.onload = function () {
				   var image = new Image();
  
				  image.src = fr.result;
				  if( options.max_width || options.max_height ){
					  image.onload = function() {
						  if( options.max_width && options.max_height && ((image.height > options.max_height) || (image.width > options.max_width)) ){
							  if(image.width > options.max_width){
								  if( options.show_alert_message ){
									  alert_message(get_locale_text('text_error_max_width'));
								  }
								  show_message(get_locale_text('text_error_max_width'),'warning');
								  return false;
							  }else if( image.height > options.max_height ){
								  if( options.show_alert_message ){
									  alert_message(get_locale_text('text_error_max_width'));
								  }
								  show_message(get_locale_text('text_error_max_width'),'warning');
								  return false;
							  }else{
								  img.attr('src',fr.result);
								  $('#'+options.id_wrapper).find('#cironapo_no_image').hide();
								  $('#'+options.id_wrapper).find('#ciornapo_img_preview').show();
								  $('#'+options.id_wrapper).find('#cironapo_upload').show();
								  $('#'+options.id_wrapper).find('#cironapo_cancel').show();
								  $('#'+options.id_wrapper).find('#cironapo_remove').hide();
							  }
						  }else if(options.max_width && image.width > options.max_width){
							  if( options.show_alert_message ){
								  alert_message(get_locale_text('text_error_max_width'));
							  }
							  show_message(get_locale_text('text_error_max_width'),'warning');
							  return false;
						  }else if (options.max_height && image.height > options.max_height){
							  if( options.show_alert_message ){
								  alert_message(get_locale_text('text_error_max_height'));
							  }
							  show_message(get_locale_text('text_error_max_height'),'warning');
							  return false;
						  }else{
							  
							  img.attr('src',fr.result);
							  $('#'+options.id_wrapper).find('#cironapo_no_image').hide();
							  $('#'+options.id_wrapper).find('#ciornapo_img_preview').show();
							  $('#'+options.id_wrapper).find('#cironapo_upload').show();
							  $('#'+options.id_wrapper).find('#cironapo_cancel').show();
							  $('#'+options.id_wrapper).find('#cironapo_remove').hide();
						  }
  
					  };
				  }else{
					  
					  img.attr('src',fr.result);
					  $('#'+options.id_wrapper).find('#cironapo_no_image').hide();
					  $('#'+options.id_wrapper).find('#ciornapo_img_preview').show();
					  $('#'+options.id_wrapper).find('#cironapo_upload').show();
					  $('#'+options.id_wrapper).find('#cironapo_cancel').show();
					  $('#'+options.id_wrapper).find('#cironapo_remove').hide();
				  }
				  
			  }
			  fr.readAsDataURL(file);
  
  
			  
		  }
		  
	  });
  
  
	  
  
	  
  
	  //callback click upload-button
	  $('#'+options.id_wrapper).find('#cironapo_upload').click(function(){
		  
		  xhr.addEventListener("progress", updateProgress, false);
				 
		  xhr.addEventListener("load", transferComplete, false);
		  xhr.addEventListener("error", transferFailed, false);
		  xhr.addEventListener("abort", transferCanceled, false);								
		  xhr.open("POST", options.url_upload, true)
		  xhr.responseType = "json";
		  
		  var formData = new FormData();
		  send = true;
		  
		  formData.append('image', element[0].files[0]); 
		  formData.append('resize', options.resize); 
		  
		  if( options.data != null ){
			  for(var item in options.data){
				  formData.append(item, options.data[item]); 
			  }
		  }
		  
		  xhr.send(formData);
		  $('#'+options.id_wrapper).find('#cironapo_upload').hide();
		  $('#'+options.id_wrapper).find('#cironapo_loader').show();
		  
		  
	  });
  
	  //callback click cancel-button
	  $('#'+options.id_wrapper).find('#cironapo_cancel').click(function(){
		  if( !send ){
			  reset();
		  }else{
			  xhr.abort();
		  }
	  });
	  
  
	  //callback click remove-button
	  $('#'+options.id_wrapper).find('#cironapo_remove').click(function(){
		  var t = confirm(get_locale_text('text_confirm_delete'));
		  if( t ){
			  delete_img();	
		  }
		  
	  });
  
  
  
	  // progress on transfers from the server to the client (downloads)
	  function updateProgress (oEvent) {
	   
	  }
	  
	  function transferComplete(evt) {
		  var response = evt.target.response;
		  
		  if( response.result == 'ok' ){
			  if( options.show_alert_message ){
				  alert_message(get_locale_text('text_success_upload'));
			  }
			  $('#'+options.id_field_img).val(response.id);
			  $('#'+options.id_wrapper).find('#cironapo_remove').show();
			  $('#'+options.id_wrapper).find('#cironapo_cancel').hide();
			  $('#'+options.id_wrapper).find('#cironapo_loader').hide();
			  $('#'+options.id_wrapper).find('#upload_message').show();
			  show_message(get_locale_text('text_success_upload'),'success');
  
			  
		  }
	  }
	  
	  function transferFailed(evt) {
		  reset();
		  if( options.show_alert_message ){
			  alert_message(get_locale_text('text_error_upload'));
		  }
		  show_message(get_locale_text('text_error_upload'),'error');
  
	  }
	  
	  function transferCanceled(oEvent) {
		  $('#cironapo_loader').hide();
		  reset();
		  if( options.show_alert_message ){
			  alert_message(get_locale_text('text_aborted_upload'));
		  }
		  show_message(get_locale_text('text_aborted_upload'),'warning');
		
	  }
  
  
  
  
	  function alert_message(message){
		  alert(message);
  
	  }
  
  
	  function show_message(text,type){
		  //if( ! options.box_small ){
			  $('#'+options.id_wrapper).find('#cironapo_upload_message').removeClass('cironapo_error');
			  $('#'+options.id_wrapper).find('#cironapo_upload_message').removeClass('cironapo_success');
			  if( type == 'error' ){
				  $('#'+options.id_wrapper).find('#cironapo_upload_message').addClass('cironapo_error');
			  }else if(type == 'success'){
				  $('#'+options.id_wrapper).find('#cironapo_upload_message').addClass('cironapo_success');
			  }else if(type == 'warning'){
				  $('#'+options.id_wrapper).find('#cironapo_upload_message').addClass('cironapo_warning');
			  }
			  console.log($('#'+options.id_wrapper).find('#cironapo_upload_message'));
			  $('#'+options.id_wrapper).find('#cironapo_upload_message').html(text);
			  $('#'+options.id_wrapper).find('#cironapo_upload_message').fadeIn();
			  setTimeout(function(){
				  $('#'+options.id_wrapper).find('#cironapo_upload_message').fadeOut();
			  },2000);
		  //}
  
	  }
  
  
	   function checkExtension(filename){
		  var ext = filename.split('.').pop().toLowerCase();
			if( !options.check_extension ){ 
				return true;
			}else{
			  if( $.inArray( ext, options.extensions ) == -1){
				   return false; 	
				}else{
				   return true; 	
				}
			}
	   }
  
  
  
	
	};
  
  
  
  
  })(jQuery);