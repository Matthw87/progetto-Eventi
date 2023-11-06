$('.pagecomposer-popup-close-click').on('click',function(){
	$(this).closest('.pagecomposer-popup').hide();
});

$(window).load(function(){
	
	/*$('.pagecomposer-popup').each(function(){
		if( $(this).attr('show') ){
			var delay = parseInt($(this).attr('delay'));
			var el = $(this);
			if( delay ){
				setTimeout(function(){ 
					
					el.fadeIn();
				}, delay);
			}else{

				$(this).fadeOut();
			}
		}
	});*/
});

$(document).ready(function (e) {
	 $('.pagecomposer-popup').each(function(){
	  
	  var pc_conf = jQuery.parseJSON( $(this).attr('pc_conf') );
	  //console.log(pc_conf);
		
	  var options_slick_modals = {};
	  console.log($(this));

	  if( pc_conf ){
		  
		   //TIPOLOGIA POPUP
		  if( pc_conf.popup_type != 'none' ){
				options_slick_modals.popup_type = pc_conf.popup_type;


				switch(pc_conf.popup_type){
					case 'delayed':
						options_slick_modals.popup_delayedTime = ''+parseInt(pc_conf.popup_delayed_time)+'s'; //RITARDO DEL POPUP DELAYED
						break;
				}
		  }
		  if( pc_conf.open_hash != '' ){
			 options_slick_modals.popup_openWithHash = pc_conf.open_hash; // APERTURA CON HASH
		  }

		   //RIAPERTURA POPUP
		  if( pc_conf.popup_reopenClass ){
			 //classe che permette la riapertura del popup (di default al click dell'oggetto con la classe specificata)
			 options_slick_modals.popup_reopenClass = pc_conf.popup_reopenClass;

			 if( pc_conf.popup_reopenClassTrigger ){
				 //stabilisce quando si deve aprire il popup: valore ammessi 'click','hover'
				 options_slick_modals.popup_reopenClassTrigger = pc_conf.popup_reopenClassTrigger;
			  }
		  }
		 

		  //CHIUSURA AUTOMATICA
		  
		  if( parseInt(pc_conf.auto_close) == 1 ){
				options_slick_modals.popup_autoClose = true;
		  }else{
			    options_slick_modals.popup_autoClose = false;
		  }
		 
		  //REDIRECT ALLA CHIUSURA
		  if( parseInt(pc_conf.redirect_close) == 1 ){
				options_slick_modals.popup_redirectOnClose = true; //ABILITA REDIRECT
				options_slick_modals.popup_redirectOnCloseUrl = pc_conf.popup_redirectOnCloseUrl //URL REDIRECT ALLA CHIUSURA
		  }
		

		  //BOTTONE CHIUSURA
		  if( parseInt(pc_conf.enable_close_button_popup) == 1 ){
				options_slick_modals.popup_closeButtonEnable = true; // ABILITA
				options_slick_modals.popup_closeButtonAlign = pc_conf.close_button_align; //ALLINEAMENTO
				options_slick_modals.popup_closeButtonStyle = pc_conf.close_button_style; //STILE
				options_slick_modals.popup_closeButtonPlace = pc_conf.close_button_place; //POSIZIONE
				if( pc_conf.popup_closeButtonText != '' ){
					options_slick_modals.popup_closeButtonText = pc_conf.popup_closeButtonText; //TESTO
				 }
		  }else{
			   options_slick_modals.popup_closeButtonEnable = false; // DISABILITA
		  }
		 
		  

		  

		 
		  //ANIMAZIONE POPUP
		  options_slick_modals.popup_animation = pc_conf.popup_animation;
		  //POSIZIONE POPUP
		  options_slick_modals.popup_position = pc_conf.popup_position;
		 
		  //CSS POPUP
		  if(  typeof $(this).attr('popup_css') != 'undefined'  && $(this).attr('popup_css') != '' ){
			 
			  options_slick_modals.popup_css = jQuery.parseJSON($(this).attr('popup_css'));
			 
		  }

		  //OVERLAY
		  if( parseInt(pc_conf.overlay_visibility) == 1 ){
				options_slick_modals.overlay_isVisible = true; // ABILITA
				options_slick_modals.overlay_animation = pc_conf.overlay_animation; //ANIMAZIONE
				if( parseInt(pc_conf.overlay_close_popup) == 1 ){
					options_slick_modals.overlay_closesPopup = true; //AL CLICK SU OVERLAY IL POPUP VIENE CHIUSO
				}else{
					options_slick_modals.overlay_closesPopup = false;
				}
				if( typeof $(this).attr('overlay_css') != 'undefined'  && $(this).attr('overlay_css') != '' ){
				  options_slick_modals.overlay_css = jQuery.parseJSON($(this).attr('overlay_css'));
			    }
				
		  }else{
			   options_slick_modals.overlay_isVisible = false; // DISABILITA
		  }


		  //MOBILE
		  if( parseInt(pc_conf.show_mobile) == 1 ){
			   options_slick_modals.mobile_show = true; //ABILITA
			   options_slick_modals.mobile_position = pc_conf.popup_position_mobile; // POSIZIONE
			   if( pc_conf.mobile_breakpoint ){
				 options_slick_modals.mobile_breakpoint = pc_conf.mobile_breakpoint; // POSIZIONE
			   }

			   if( typeof $(this).attr('mobile_css') != 'undefined' && $(this).attr('mobile_css') != '' ){
				  //console.log($(this).attr('mobile_css'));
				  
				  options_slick_modals.mobile_css = jQuery.parseJSON($(this).attr('mobile_css'));
				 
			   }
			   
		  }else{
			   options_slick_modals.mobile_show = false; //DISABILITA
		  }
		  

		  //COOKIE
		  if( parseInt(pc_conf.restrict_cookieSet) == 1 ){
				options_slick_modals.restrict_cookieSet = true; //ABILITO I COOKIE
				options_slick_modals.restrict_cookieName = pc_conf.restrict_cookieName; //IMPOSTO IL NOME AI COOKIE
				options_slick_modals.restrict_cookieDays = pc_conf.restrict_cookieDays; ////IMPOSTO LA DURATA DEI COOKIE

		  }


		  //INTERVALLO DATE
		  if( parseInt(pc_conf.restrict_dateRange) == 1 ){
				options_slick_modals.restrict_dateRange = true; //ABILITO IL RANGE TEMPORALE
				if( pc_conf.restrict_dateRangeStart != '' ){
					options_slick_modals.restrict_dateRangeStart = pc_conf.restrict_dateRangeStart; //IMPOSTO LA DATA DI INIZIO
				}
				if( pc_conf.restrict_dateRangeEnd != '' ){
					options_slick_modals.restrict_dateRangeEnd = pc_conf.restrict_dateRangeEnd; ////IMPOSTO LA DATA DI FINE
				}

		  }

		  




		  //CONTENUTO ANIMATO?
		  /*if( parseInt(pc_conf.content_animate) == 1 ){
				options_slick_modals.content_animate = true; //AL CLICK SU OVERLAY IL POPUP VIENE CHIUSO

				options_slick_modals.content_animation = 'rotateOut';
		  }else{
				options_slick_modals.content_animate = false;
		  }*/

		  
		  
		  
		  console.log(options_slick_modals);
			
		  $(this).SlickModals(
				options_slick_modals
			);

	  }else{
		  $(this).SlickModals({
				popup_type: 'delayed',
				popup_delayedTime: '1s',
				popup_animation: 'slideTop',
				popup_position: 'rightCenter',
			});
	  }

	
  })
});
/*

$(document).mouseup(function (e) {
  $('.pagecomposer-popup').each(function(){
	  
	  
	  /*if( $(this).attr('close-out') ){
	 
		  var container = $(this).find('.pagecomposer-popup-content'); // YOUR CONTAINER SELECTOR
			
		  if (!container.is(e.target) // if the target of the click isn't the container...
			  && container.has(e.target).length === 0) // ... nor a descendant of the container
		  {
			  
			container.closest('.pagecomposer-popup').fadeOut();
		  }
	  }
	
	  

	
  })
});
*/
