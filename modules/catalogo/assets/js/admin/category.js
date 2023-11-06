function change_visibility(id){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "CategoryAdmin",action:'change_visibility','id':id,'ajax':1, mod:'catalogo'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
							
					if( !data.status ){
						$('#section_'+id+'_label_offline').show();
						$('#section_'+id+'_online').show();
						$('#section_'+id+'_offline').hide();
					}else{
						$('#section_'+id+'_label_offline').hide();
						$('#section_'+id+'_online').hide();
						$('#section_'+id+'_offline').show();
					}
			
				}else{
					
				}
		  },
	 
	});
}


$(document).ready(function()
{

	var updateOutput = function(e)
	{
		
		
		var list   = e.length ? e : $(e.target),
			output = list.data('output');
			if (window.JSON) {
				


					
					$.ajax({
					  // definisco il tipo della chiamata
					  type: "POST",
					  // specifico la URL della risorsa da contattare
					  url: "index.php",
					  // passo dei dati alla risorsa remota
					  data: { 'action': "save_order_sections",'list' : list.nestable('serialize'),'ajax':1,ctrl:'CategoryAdmin',mod:'catalogo'},
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
		maxDepth: 4,
	})
	.on('change', updateOutput);
	

});

function executeCopy(text) {
	var input = document.createElement('textarea');
	document.body.appendChild(input);
	input.value = text;
	//input.focus();
	input.select();
	document.execCommand('Copy');
	input.remove();
}