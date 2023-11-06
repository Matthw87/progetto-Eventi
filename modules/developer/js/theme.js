function active_theme(dir){


           
    $.ajax({
        // definisco il tipo della chiamata
        type: "GET",
        // specifico la URL della risorsa da contattare
        url: "index.php",
        // passo dei dati alla risorsa remota
        data: { ctrl:"ThemeAdmin",mod:'developer',ajax:1,action: "active", theme : dir,ajax:1},
        // definisco il formato della risposta
        dataType: "json",
        // imposto un'azione per il caso di successo
        success: function(data){
            document.location.reload();
        },
        // ed una per il caso di fallimento
        error: function(){
        alert("Chiamata fallita!!!");
        }
    });
}