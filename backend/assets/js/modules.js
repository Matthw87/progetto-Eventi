var modules_checked_flag = [];
var modules_submitted = [];



function execute_action(module,action,reload,from_theme){


	$.ajax({
		type: "GET",
		url: "index.php",
		data : {module:module,ctrl:'ModuleAdmin',action:action,ajax:1,from_theme: from_theme},
		dataType: "json",
		success: function(data){
			if(data.result == 'ok'){
				if( reload ){
					document.location.reload();
				}else{
					modules_checked_flag.push(module);
					if( modules_checked_flag.length == modules_submitted.length){
						document.location.reload();
					}
				}
			}else{
				if( reload ){
					alert(data.errore);
				}else{
					modules_checked_flag.push(module);
					if( modules_checked_flag.length == modules_submitted.length){
						document.location.reload();
					}
				}
			}

				
		},
			
	});


	
}


function module_action(module,action,from_theme){
	switch(action){
		case 'active':
			msg = "Sicuro di volere attivare questo modulo?";
			break;
		case 'disable':
			msg = "Sicuro di volere disattivare questo modulo?";
			break;
		case 'install':
			msg = "Sicuro di volere installare questo modulo?";
			break;
		case 'uninstall':
			msg = "Verranno cancellati tutte le informazioni memorizzate dal modulo nel database.\n Sicuro di volere rimuovere questo modulo?";
			break;

	}
	var t = confirm(msg);

	if( t ){
		execute_action(module,action,true,from_theme);
	}
}




function submit_bulk_action_modules(action){
	var t = confirm('Sicuro di volere procedere con questa operazione?');
	if(t){ 
		$('.module_check').each(function(){
			if( $(this).prop('checked') == true ){
				var active = parseInt($(this).attr('active'));
				console.log(active);
				if( active && action == 'disable'){
					var module = $(this).val();
					modules_submitted.push(module);
				}
				if( !active && action == 'active'){
					var module = $(this).val();
					modules_submitted.push(module);
				}
				
			}
		});
		if(  modules_submitted.length ){
			for( var k in modules_submitted ){
				
					execute_action(modules_submitted[k],action,false);
				
			};
		}else{
			alert("L'operazione indicata non può essere applicata a nessun modulo selezionato.")
		}
	}
}



function select_all_module(el){
	$('.module_check').prop('checked',el.prop('checked'));
	
}