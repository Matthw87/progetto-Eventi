$(document).ready(function(){
		
    $('.col2').click(function(){
        var el = $(this);
        $('.col2').removeClass('active');			
        el.find('input').prop('checked',true);
        el.addClass("active");
        $(".wireframe1").attr("src","assets/images/full.png");
        $(".wireframe2").attr("src","assets/images/top-content.png");
        $(".wireframe3").attr("src","assets/images/sidebar-dx.png");
        $(".wireframe4").attr("src","assets/images/sidebar-sx.png");
        el.find(".wireframe1").attr("src","assets/images/full-active.png?v=1");
        el.find(".wireframe2").attr("src","assets/images/top-content-active.png");
        el.find(".wireframe3").attr("src","assets/images/sidebar-dx-active.png");
        el.find(".wireframe4").attr("src","assets/images/sidebar-sx-active.png");
    });
    if( typeof js_layout != 'undefined' && js_layout != null && js_layout != '' ){
        
        $('#layout_'+js_layout).closest('.col2').trigger('click');

        $('#layout_div').addClass('overlay_layout');
    }
});