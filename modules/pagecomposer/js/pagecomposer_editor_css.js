$(document).mouseup(function(e) 
{

	 $('.top').click(function() {
		$('body').animate({scrollTop: 0}, duration);
	  })

	var container = $(".pagecomposer-editor-css-container");

	// if the target of the click isn't the container nor a descendant of the container
	if (!container.is(e.target) && container.has(e.target).length === 0) 
	{
		pagecomposer_editor_side_hide();
	}


	$('.edit_tabs').on('click',function(){
		const tab = $(this).val();

		switch(tab){
			case 'js-head':
				codemirror_js_head.setCursor(codemirror_js_head.lineCount(), 0);
				break;
			case 'js-end':
				codemirror_js_end.setCursor(codemirror_js_end.lineCount(), 0);
				break;
			case 'css':
				codemirror_css.setCursor(codemirror_css.lineCount(), 0);
				break;
		}
	})
});


var codemirror_css;
var codemirror_js_head;
var codemirror_js_end;
var pxScrolled = 200;
var duration = 500;
$(function() {
	
	

	
	
	  
	  codemirror_css = CodeMirror.fromTextArea(document.getElementById('pagecomposer-textarea-editor-css'), {
			lineNumbers: true,
			styleActiveLine: true,
			matchBrackets: true,
			fullscreen:true,
			mode:'css',
			//theme:'night',
			extraKeys: {
				"F11": function(cm) {
				  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				},
				"Esc": function(cm) {
				  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
				},
			}
		  });
	 codemirror_js_end = CodeMirror.fromTextArea(document.getElementById('pagecomposer-textarea-editor-js-end'), {
			lineNumbers: true,
			styleActiveLine: true,
			matchBrackets: true,
			fullscreen:true,
			//theme:'night',
			extraKeys: {
				"F11": function(cm) {
				  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				},
				"Esc": function(cm) {
				  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
				},
			}
		  });
	  codemirror_js_head =  CodeMirror.fromTextArea(document.getElementById('pagecomposer-textarea-editor-js-head'), {
			lineNumbers: true,
			styleActiveLine: true,
			matchBrackets: true,
			fullscreen:true,
			//theme:'night',
			extraKeys: {
				"F11": function(cm) {
				  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				},
				"Esc": function(cm) {
				  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
				},
			}
		  });

	
	codemirror_css.on("change", function(cMirror) {
		//$('#page_composer_layout_css').html(cMirror.getValue());
		$('#pagecomposer-textarea-editor-css').val(cMirror.getValue());
	 });
	 codemirror_js_end.on("change", function(cMirror) {
		$('#pagecomposer-textarea-editor-js-end').val(cMirror.getValue());
	 });
	 codemirror_js_head.on("change", function(cMirror) {
		 $('#pagecomposer-textarea-editor-js-head').val(cMirror.getValue());
		
	 });

	 

  
}); 


function pagecomposer_save_css(id){
	var val = $('#pagecomposer-textarea-editor-css').val();
	var val2 = $('#pagecomposer-textarea-editor-js-head').val();
	var val3 = $('#pagecomposer-textarea-editor-js-end').val();
	
	$.ajax({
		  type: "POST",
		  url: js_baseurl+"backend/index.php?ctrl=PageComposerAdmin&mod=pagecomposer&ajax=1",
		  data: { action: "save_css",id:id,css:val,js_head:val2,js_end:val3},
		  dataType: "json",
		  cache: false,
		  success: function(data){
				if(data.result == 'ok'){
					alert('Modifiche salvate!');
					$('#page_composer_layout_css').html(data.css);
				}else{
					alert(data.error);
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
	
}
/* Set the width of the side navigation to 250px */
function pagecomposer_editor_side_show() {
  $('.pagecomposer-tabset').show();
  $('.pagecomposer-editor-buttons').show();
  document.getElementById("mySidenav").style.width = "50%";
  document.getElementById("mySidenav").style.padding = "40px 32px";
  $('#pagecomposer-textarea-editor-css').focus();
	setTimeout(function() {
		codemirror_css.refresh();
		codemirror_js_end.refresh();
		codemirror_js_head.refresh();
	},1000);

}

/* Set the width of the side navigation to 0 */
function pagecomposer_editor_side_hide() {
$('.pagecomposer-editor-buttons').hide();
$('.pagecomposer-tabset').hide();
  document.getElementById("mySidenav").style.width = "0";
   document.getElementById("mySidenav").style.padding = "0";
}



  $(window).scroll(function() {
	if ($(this).scrollTop() > pxScrolled) {
	  $('.fab-container').css({'bottom': '0px', 'transition': '.3s'});
	} else {
	  $('.fab-container').css({'bottom': '-72px'});
	} 
  });

 




