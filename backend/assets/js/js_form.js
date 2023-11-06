$(document).ready(function(){
	
	
	
	
	/****  Pickadate  ****/
	if ($('.pickadate').length && $.fn.pickadate) {
	  
		if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
			
			 
			if( javascript_activelocale == 'it' ){
				 
				$('.pickadate').each(function () {
			        $(this).pickadate(
			        	{
			        	monthsFull: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
						monthsShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
						weekdaysFull: ['Domenica', 'Lunedi', 'Martedi', 'Mercoledi', 'Giovedi', 'Venerdi', 'Sabato'],
						weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
						showMonthsShort: '',
						showWeekdaysFull: '',
			        	format: 'd/mm/yyyy',
			        	
			        	today: 'Oggi',
						clear: 'Annulla',
						close: 'Chiudi',
			    	});

			    });
			}else{
				$('.pickadate').each(function () {
			        $(this).pickadate({format: 'd/mm/yyyy'});
			    });	
			}	
		}else{
			$('.pickadate').each(function () {
		        $(this).pickadate({format: 'd/mm/yyyy'});
		    });	
			
		}
		
	}
	
	/****  Pickatime  ****/
	if ($('.pickatime').length && $.fn.pickatime) {
		if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
			if( javascript_activelocale == 'it' ){
			    $('.pickatime').each(function () {
			        $(this).pickatime({
				        format: 'H:i',
				        clear: 'Annulla',
				        });
			    });
		    }else{
			     $('.pickatime').each(function () {
			        $(this).pickatime({
				        format: 'H:i',
				        });
			    });
			    
		    }
	    }else{
		    $('.pickatime').each(function () {
		        $(this).pickatime({
			        format: 'H:i',
			        });
		    });
	    }
	}
	
	/****  Datetimepicker Only date  ****/
	
	if ($('.datetimepicker_date').length && $.fn.datetimepicker) {
	   	 if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
			 $('.datetimepicker_date').each(function () {
		        
		        $(this).datetimepicker(
		        	{
			        format: 'd/m/Y',
			        lang: javascript_activelocale,
			        timepicker:false,
					allowBlank: true
		        	}
		        );
		    });
	    }else{
		    $('.datetimepicker_date').each(function () {
		        $(this).datetimepicker(
		        	{
			        format: 'd/m/Y',
			        timepicker:false,
					allowBlank: true
		        	}
		        );
		    });
	    }
	}
	
	/****  Datetimepicker Only Time  ****/
	if ($('.datetimepicker_time').length && $.fn.datetimepicker) {
	   	 if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
			 $('.datetimepicker_time').each(function () {
		        
		        $(this).datetimepicker(
		        	{
			        format: 'H:i',
			        lang: javascript_activelocale,
			        datepicker:false,
					allowBlank: true
		        	}
		        );
		    });
	    }else{
		    $('.datetimepicker_date').each(function () {
		        $(this).datetimepicker(
		        	{
			        format: 'H:i',
			        datepicker:false,
					allowBlank: true
		        	}
		        );
		    });
	    }
	}
	
	
	/****  Datetimepicker ****/
	if ($('.datetimepicker').length && $.fn.datetimepicker) {
	   	 if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
			 $('.datetimepicker').each(function () {
		        
		        $(this).datetimepicker(
		        	{
			        format: 'd/m/Y H:i',
			        lang: javascript_activelocale,
					allowBlank: true
			        }
		        );
		    });
	    }else{
		    $('.datetimepicker').each(function () {
		        $(this).datetimepicker(
		        	{
			        format: 'd/m/Y H:i',
					allowBlank: true
			        }
		        );
		    });
	    }
	}

	
	
	
	/****  Datepicker  ****/
	if ($('.datepicker').length && $.fn.datepicker) {
		if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
		    $('.datepicker').each(function () {
		        $(this).datepicker({
		            format: 'dd/mm/yyyy',
		            language: 'it'
		        });
		    });
	    }else{
		    $('.datepicker').each(function () {
		        $(this).datepicker({
		            format: 'dd/mm/yyyy',
		        });
		    });
		    
	    }
	}
	
	
	
	
	
	/****  TinyMCE  ****/
	if( typeof javascript_activelocale != 'undefined' && javascript_activelocale != null ){
		if ($('.mce-textarea-full').length ) {
			tinymce.init({
			    language : javascript_activelocale,
				selector: ".mce-textarea-full",
				file_browser_callback: function(field, url, type, win) {
						tinyMCE.activeEditor.windowManager.open({
							file: '/assets/plugins/kcfinder-3.12/browse.php?opener=tinymce4&field=' + field + '&type=' + type +"&langCode="+javascript_activelocale,
							title: 'KCFinder',
							width: 700,
							height: 500,
							inline: true,
							close_previous: false
						}, {
							window: win,
							input: field
						});
						return false;
					},
			    theme: "modern",
			    plugins: [
			        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
			        "searchreplace wordcount visualblocks visualchars code fullscreen",
			        "insertdatetime media nonbreaking save table contextmenu directionality",
			        "emoticons template paste textcolor colorpicker textpattern imagetools"
			    ],
			    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
			    toolbar2: "print preview media | forecolor backcolor emoticons",
			    image_advtab: true,
			    templates: [
			        {title: 'Test template 1', content: 'Test 1'},
			        {title: 'Test template 2', content: 'Test 2'}
			    ]
			});
		}
		
		if ($('.mce-textarea-base').length ) {
			tinymce.init({
				language : javascript_activelocale,
			    selector: ".mce-textarea-base",
			    plugins: [
			        "advlist autolink lists link image charmap print preview anchor",
			        "searchreplace visualblocks code fullscreen",
			        "insertdatetime media table contextmenu paste"
			    ],
			    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
			});
		}
		
		
		
		if ($('.mce-textarea-classic').length ) {
			tinymce.init({
					language : javascript_activelocale,
			        selector: ".mce-textarea-classic",
					file_browser_callback: function(field, url, type, win) {
						tinyMCE.activeEditor.windowManager.open({
							file: '/assets/plugins/kcfinder-3.12/browse.php?opener=tinymce4&field=' + field + '&type=' + type +"&langCode="+javascript_activelocale,
							title: 'KCFinder',
							width: 700,
							height: 500,
							inline: true,
							close_previous: false
						}, {
							window: win,
							input: field
						});
						return false;
					},
			        plugins: [
			                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
			                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			                "table contextmenu directionality emoticons template textcolor paste fullpage textcolor colorpicker textpattern"
			        ],
			
			        toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
			        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
			        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",
			
			        menubar: false,
			        toolbar_items_size: 'medium',
			
			        style_formats: [
			                {title: 'Bold text', inline: 'b'},
			                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
			                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
			                {title: 'Example 1', inline: 'span', classes: 'example1'},
			                {title: 'Example 2', inline: 'span', classes: 'example2'},
			                {title: 'Table styles'},
			                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
			        ],
			
			        templates: [
			                {title: 'Test template 1', content: 'Test 1'},
			                {title: 'Test template 2', content: 'Test 2'}
			        ]
			});
		}
	}else{
		if ($('.mce-textarea-full').length ) {
			tinymce.init({
				selector: ".mce-textarea-full",
			    theme: "modern",
				file_browser_callback: function(field, url, type, win) {
						tinyMCE.activeEditor.windowManager.open({
							file: '/assets/plugins/kcfinder-3.12/browse.php?opener=tinymce4&field=' + field + '&type=' + type +"&langCode=it",
							title: 'KCFinder',
							width: 700,
							height: 500,
							inline: true,
							close_previous: false
						}, {
							window: win,
							input: field
						});
						return false;
					},
			    plugins: [
			        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
			        "searchreplace wordcount visualblocks visualchars code fullscreen",
			        "insertdatetime media nonbreaking save table contextmenu directionality",
			        "emoticons template paste textcolor colorpicker textpattern imagetools"
			    ],
			    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
			    toolbar2: "print preview media | forecolor backcolor emoticons",
			    image_advtab: true,
			    templates: [
			        {title: 'Test template 1', content: 'Test 1'},
			        {title: 'Test template 2', content: 'Test 2'}
			    ]
			});
		}
		
		if ($('.mce-textarea-base').length ) {
			tinymce.init({
			    selector: ".mce-textarea-base",
			    plugins: [
			        "advlist autolink lists link image charmap print preview anchor",
			        "searchreplace visualblocks code fullscreen",
			        "insertdatetime media table contextmenu paste"
			    ],
			    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
			});
		}
		
		
		
		if ($('.mce-textarea-classic').length ) {
			tinymce.init({
			        selector: ".mce-textarea-classic",
					file_browser_callback: function(field, url, type, win) {
						tinyMCE.activeEditor.windowManager.open({
							file: '/assets/plugins/kcfinder-3.12/browse.php?opener=tinymce4&field=' + field + '&type=' + type +"&langCode=it",
							title: 'KCFinder',
							width: 700,
							height: 500,
							inline: true,
							close_previous: false
						}, {
							window: win,
							input: field
						});
						return false;
					},
			        plugins: [
			                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
			                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
			                "table contextmenu directionality emoticons template textcolor paste fullpage textcolor colorpicker textpattern"
			        ],
			
			        toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
			        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
			        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",
			
			        menubar: false,
			        toolbar_items_size: 'small',
			
			        style_formats: [
			                {title: 'Bold text', inline: 'b'},
			                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
			                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
			                {title: 'Example 1', inline: 'span', classes: 'example1'},
			                {title: 'Example 2', inline: 'span', classes: 'example2'},
			                {title: 'Table styles'},
			                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
			        ],
			
			        templates: [
			                {title: 'Test template 1', content: 'Test 1'},
			                {title: 'Test template 2', content: 'Test 2'}
			        ]
			});
		}
		
		
	}
	/****  CKE Editor  ****/
	if ($('.cke-editor-advanced').length && $.fn.ckeditor) {
	    $('.cke-editor-advanced').each(function () {
			$(this).ckeditor({
				imageUploadUrl : '/upload_file.php?plugin=cke&type=img&action=upload',
				codeSnippetGeshi_url: '../lib/colorize.php',
				extraPlugins : 'lineheight,dialogadvtab',
				//imageBrowser_listUrl: '/upload_file.php?plugin=cke&type=img&action=view',
				//filebrowserBrowseUrl: '/upload_file.php?plugin=cke&type=img&action=view',
			   filebrowserBrowseUrl : '/assets/plugins/kcfinder-3.12/browse.php?opener=ckeditor&type=files',
			   filebrowserImageBrowseUrl : '/assets/plugins/kcfinder-3.12/browse.php?opener=ckeditor&type=images',
			   filebrowserFlashBrowseUrl : '/assets/plugins/kcfinder-3.12/browse.php?opener=ckeditor&type=flash',
			   filebrowserUploadUrl : '/assets/plugins/kcfinder-3.12/upload.php?opener=ckeditor&type=files',
			   filebrowserImageUploadUrl : '/assets/plugins/kcfinder-3.12/upload.php?opener=ckeditor&type=images',
			   filebrowserFlashUploadUrl : '/assets/plugins/kcfinder-3.12/upload.php?opener=ckeditor&type=flash',
				
			});
	       /*CKEDITOR.replace($(this).attr('id'), {
				//toolbar:'Basic',
				//extraPlugins : 'imagebrowser,youtube,cleanuploader',
				//imageBrowser_listUrl : "/page/list_files.htm"
			});*/
	    });
	}

	

	$('.btn-filemanger-marion').each(function(){
		var field_id = $(this).attr('data-input');
		
		responsive_filemanager_callback(field_id);
	});
	
	
});

function responsive_filemanager_callback(field_id){
	var type_image = false;
	
	if( $('#img_filemanager_'+field_id).length > 0 ){
		
		if( $('#'+field_id).val() ){
			

			var file = $('#'+field_id).val();
			
			var image = '';
			var extension = file.substr( (file.lastIndexOf('.') +1) );
			switch(extension) {
				case 'jpg':
				case 'png':
				case 'gif':
				case 'jpeg':
				case 'webp':
				case 'svg':
					image = file;
					type_image = true;
					  // There's was a typo in the example where
				break;                         // the alert ended with pdf instead of gif.
				default:
					image = js_baseurl+'assets/images/file-icons/512px/'+extension+".png";
					break;
			}
			if( type_image ){
				$('#img_filemanager_'+field_id).attr('src',js_baseurl+'media/filemanager/'+image).show();
			}else{
				$('#img_filemanager_'+field_id).attr('src',image).show();
			}

			$('#img_filemanager_'+field_id).closest('.preview_box_filemanager').show();
			$('#img_filemanager_'+field_id).show();
			$('#img_filemanager_'+field_id+"_remove").show();

			$('#img_filemanager_'+field_id).closest('.filemanager_editor_form').find('.preview_box_filemanager').addClass('no-image');
			$("#modal_filemanager_"+field_id).iziModal('close');
		}else{
			
			$('#img_filemanager_'+field_id+"_remove").hide();
			
			$('#img_filemanager_'+field_id).closest('.filemanager_editor_form').find('.preview_box_filemanager').removeClass('no-image');
		}
	}
}



