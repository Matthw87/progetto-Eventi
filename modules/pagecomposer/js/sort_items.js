$(document).ready(function()
{

	load_nestable_items();
	

});

function load_nestable_items(){
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
					  data: { 'action': "save_composition_box",'list' : list.nestable('serialize'),id:js_id_box,ctrl:'PageComposerAdmin',mod:'pagecomposer',ajax:1},
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