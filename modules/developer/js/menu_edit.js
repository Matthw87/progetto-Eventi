
if( $('#label_static').prop('checked') == true ){
    $('#div_label_text').show();
    $('#div_label_function').hide();
}else{
    $('#div_label_text').hide();
    $('#div_label_function').show();
}

$('#label_static').on('change',function(){
    if( $(this).prop('checked') == true ){
        $('#div_label_text').show();
        $('#div_label_function').hide();
    }else{
        $('#div_label_text').hide();
        $('#div_label_function').show();
    }
})

if( $('#show_label').prop('checked') == true ){
    $('#div_label_static').show();
}else{
    $('#div_label_static').hide();
    $('#div_label_text').hide();
    $('#div_label_function').hide();
}

$('#show_label').on('change',function(){
    if( $(this).prop('checked') == true ){
        $('#div_label_static').show();
    }else{
        $('#div_label_static').hide();
    }
})