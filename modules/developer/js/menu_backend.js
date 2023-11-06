function edit_menu(event,id){
    console.log(event);
    event.stopPropagation();
    document.location.href='index.php?mod=developer&ctrl=MenuBackend&action=edit&id='+id;
}

function elimina_voce(id){

}
	

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
                    data: { ctrl:"MenuBackend",mod:'developer',ajax:1,action: "save_order", lista : list.nestable('serialize')},
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
    maxDepth: 2,
})
.on('change', updateOutput);

		

	