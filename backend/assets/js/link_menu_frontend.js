$(document).ready(function(){
	
    
    $('#static_url').on('change',function(){
        if( $(this).prop('checked') == true ){
            $('#url_type').closest('.row').hide();
            $('#div_url').show();
        }else{
            $('#url_type').closest('.row').show();
            $('#div_url').hide();
        }
    })

    if( $('#static_url').prop('checked') == true ){
        $('#url_type').closest('.row').hide();
        $('#div_url').show();
    }else{
        $('#url_type').closest('.row').show();
        $('#div_url').hide();

        if( $('#url_type').val() ){
            const old_value = js_formdata['id_url_page'];
            $.ajax({
                type: "GET",
                url: "index.php",
                data: { 
                    ctrl:'LinkMenuFrontend',
                    ajax:1,
                    action: "get_link_dinamic_menu", 
                    type:$('#url_type').val() 
                },
                dataType: "json",
                success: function(data){
                    if(data.result == 'ok'){
                
                        crea_select($('#id_url_page'),data.options,old_value );
                    }
                },
                
            });
        }
    }

    $('#url_type').on('change',function(){
        const val = $(this).val();
        $.ajax({
            type: "GET",
            url: "index.php",
            data: { 
                ctrl:'LinkMenuFrontend',
                ajax:1,
                action: "get_link_dinamic_menu", 
                type:val 
            },
            dataType: "json",
            success: function(data){
                if(data.result == 'ok'){
                    crea_select($('#id_url_page'),data.options);
                }
            },
            
        });
    })
    

});