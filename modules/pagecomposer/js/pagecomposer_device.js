var pagecomposer_mobile_detect = new MobileDetect(window.navigator.userAgent);
var pagecomposer_mobile_detect_type = 'desktop';


if( pagecomposer_mobile_detect.mobile()){
	pagecomposer_mobile_detect_type = 'mobile';
}

if( pagecomposer_mobile_detect.tablet()){
	pagecomposer_mobile_detect_type = 'tablet';
}

$('.pagecomposer-detect-mobile').each(function(){
	if( !$(this).hasClass('pagecomposer-'+pagecomposer_mobile_detect_type+'-device') ){

		$(this).hide();
	}
});