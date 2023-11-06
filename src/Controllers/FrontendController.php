<?php
namespace Marion\Controllers;
use MatthiasMullie\Minify;
use Marion\Core\Marion;
use Marion\Controllers\Controller;
//use Shop\{Cart,Eshop};
use Marion\Entities\Cms\LinkMenuFrontend;

class FrontendController extends Controller{
	public $_auth = '';
	public $_required_access = false;
	public $_user;
	public $_ajax;
	public $_tmpl_obj;
	public $_ctrl;
	public $_action;
	public $_module;

	// TWIG VARS
	public $_twig = true;
	public $_twig_vars = array();
	public $_twig_functions = array();
	public $_twig_templates_dir = array();
	public $_twig_global_vars = array();


	public $_compressed_css = ''; //variabile che contiene il css compresso


	public function init($options=array()){
		parent::init($options);
		if( isset($options['module']) ){
			$this->_module = $options['module'];
		}else{
			$this->_module = _var('mod');
		}
		
	}


	function notLogged(){
		header('Location: '._MARION_BASE_URL_."login");
		exit;
	}




	function initTemplateDir(){}


	function setMedia(){
		$this->registerCSS('assets/plugins/bootstrap/bootstrap.min.css');
		$this->registerCSS('assets/flag-icon-css/css/flag-icon.css');
		$this->registerJS('assets/plugins/jquery.js','head',10);
		$this->registerJS('assets/plugins/bootstrap/bootstrap.min.js','head',11);
		$this->registerJS('backend/js/function.js','head');


		//carico il css del tema
		if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/theme.min.css") ){
			$this->registerCSS('themes/'._MARION_THEME_.'/theme.min.css');
		} else {
			if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/theme.css") ){
				$this->registerCSS('themes/'._MARION_THEME_.'/theme.css');
			} 
		}

		//carico il js del tema
		if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/theme.min.js") ){
			$this->registerJS('themes/'._MARION_THEME_.'/theme.min.js');
		} else {
			if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/theme.js") ){
				$this->registerJS('themes/'._MARION_THEME_.'/theme.js');
			} 
		}

		

		
		Marion::do_action('action_register_media_front',array($this));



	}

	function error($message=''){
		$this->setVar('message',$message);
		$this->output('error.htm');
		exit;
	}


	//metodo che comprime il css in un'unica stringa stamapata nella pagina
	function compressCSS(){
		$css = '';
		$css_files = $this->_css;

		$css_file_name = '';

		$files_get_data = array();

		//debugga($css_files);exit;
		if(okArray($css_files)){
			foreach($css_files as $k => $file){
				if( file_exists(_MARION_ROOT_DIR_.$file) ){
					$files_get_data[] = _MARION_ROOT_DIR_.$file;
					unset($this->_css[$k]);
					$css_file_name .= Marion::slugify($file);
					
				}
			}
		}
		$file_css_min = _MARION_COMPRESSED_DIR_.md5(base64_encode($css_file_name)).".css";
		if( !file_exists($file_css_min)  ){
			$minifier = new Minify\CSS();
			if( okArray($files_get_data) ){
				foreach($files_get_data as $file){
					$css = file_get_contents($file);
					
					$minifier->add($css);
						
					
				}
			}
			
			

			
			unlink($file_css_min);
			
			$minifier->minify($file_css_min);
			$css_min = file_get_contents($file_css_min);
			
		}else{
			$css_min = file_get_contents($file_css_min);
			
			
		}
		
		$this->setVar('compressed_css', $css_min);
		
	}

	function cacheFileJS($file){
		$filename = Marion::slugify($file);
		$file_js = _MARION_COMPRESSED_DIR_.md5(base64_encode($filename)).".js";
		if( file_exists($file_js) ) {
			$js_content_min = file_get_contents($file_js);
			return $js_content_min;
		}
		if( file_exists(_MARION_ROOT_DIR_.$file)  ){
			
			$file = _MARION_ROOT_DIR_.$file;
			$js_content = file_get_contents($file);
			
			$minifier = new Minify\JS();
			$minifier->add($js_content);
			$minifier->minify($file_js);
			$js_content_min = file_get_contents($file_js);
			//debugga($js_content_min);
			
			return $js_content_min;
			/*$js_content .= file_get_contents(_MARION_ROOT_DIR_.$file);
			if( preg_match('/\.min\.js$/',$file) ){
				
				$js_content_min .= $js_content;
			}else{
				require_once dirname(__FILE__)."/MinifyCssJs.class.php";
				$minify = new MinifyJs();
				$js_content_min .= $minify->minimizeJavascriptSimple($js_content);

			}
			file_put_contents($file_js,$js_content_min);
			return $js_content_min;*/

		}

		return false;

	}

	//metodo che comprime il css in un'unica stringa stamapata nella pagina
	function compressJS(){

		$head_js_files = array();
		$end_js_files = array();
		if(array_key_exists('head',$this->_javascript)){
			$head_js_files = $this->_javascript['head'];
		}

		if(array_key_exists('end',$this->_javascript)){
			$end_js_files = $this->_javascript['end'];
		}

		
		$_js_head = array();
		$_js_end = array();

		if(okArray($head_js_files)){
			foreach($head_js_files as $p => $files){
				foreach($files as $k => $file){	
					$data = $this->cacheFileJS($file);
					if($data){
						$_js_head[] = array(
							'type' => 'data',
							'data' => $data
						);
					}else{
						$_js_head[] = array(
							'type' => 'file',
							'url' => $file
						);
					}
				}
			}
		}
		if(okArray($end_js_files)){
			foreach($end_js_files as $p => $files){
				foreach($files as $k => $file){

					$data = $this->cacheFileJS($file);
					if($data){
						$_js_end[] = array(
							'type' => 'data',
							'data' => $data
						);
					}else{
						$_js_end[] = array(
							'type' => 'file',
							'url' => $file
						);
					}
				}
			}
		}


		$this->setVar('compressed_js_head', $_js_head);
		$this->setVar('compressed_js_end', $_js_end);


		
	}

	function setTemplateVariables(){

		if( _MARION_COMPRESSED_CSS_ ){
			$this->compressCSS();
		}

		

		if( _MARION_COMPRESSED_JS_ ){
			$this->compressJS();
		}
		
		parent::setTemplateVariables();
		$this->setVar('url_admin',$this->adminUrl());
		$this->setVar('THEME_DIR',_MARION_BASE_URL_.'themes/');
		$this->setVar('LANG',_MARION_LANG_);
		
	}



		
	//restituisce l'url a cui deve essere reindirizzato l'amministratore dopo il login
	function adminUrl(){
		if( Marion::getConfig('generale','redirect_admin_side') == 1 && authAdminUser() ){
			return $this->getBaseUrl()."backend/index.php";
		}else{
			return $this->getBaseUrl()."account/home.htm";
			
			/*if( isMultilocale()){
				return $this->getBaseUrl()."{$GLOBALS['activelocale']}/account/home.htm";
			}else{
				return $this->getBaseUrl()."account/home.htm";
			}*/
		}

	}



	

	function initTwingTemplate(){
		
		$directories_templates_twig = array();
				
		$directories_templates_twig[] = _MARION_THEME_DIR_._MARION_THEME_."/templates/";
		

		
		if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/templates/".$GLOBALS['activelocale']) ){
			$directories_templates_twig[] = _MARION_THEME_DIR_._MARION_THEME_."/templates/".$GLOBALS['activelocale'];
		}

		
		
		
		
		
		if( $this->_module ){
			
			
			
			if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/modules/".$this->_module."/templates") ){
				

				$directories_templates_twig[] = [_MARION_THEME_DIR_._MARION_THEME_."/modules/".$this->_module."/templates",$this->_module];

				if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/modules/".$this->_module."/templates/front") ){
					$directories_templates_twig[] = [_MARION_THEME_DIR_._MARION_THEME_."/modules/".$this->_module."/templates/front",$this->_module];
				}
			}

			if( file_exists('modules/'.$this->_module."/templates") ){

				if( file_exists('modules/'.$this->_module."/templates/front")){
					$directories_templates_twig[] = ['modules/'.$this->_module."/templates/front",$this->_module];
				}
				$directories_templates_twig[] = ['modules/'.$this->_module."/templates",$this->_module];
			}
		}
		Marion::do_action('action_register_twig_templates_dir',array(&$directories_templates_twig));
		$loader = new \Twig\Loader\FilesystemLoader();
		$loader->addPath(_MARION_ROOT_DIR_.'src/Twig');
		foreach($directories_templates_twig as $path ){
			if( is_array($path) ){
				$loader->addPath($path[0],$path[1]);
			}else{
				$loader->addPath($path);
			}
		}
		
		if( okArray($this->_twig_templates_dir) ){
			foreach($this->_twig_templates_dir as $dir ){
				if( is_array($dir) ){
					$loader->addPath($dir[0],$dir[1]);
				}else{
					$loader->addPath($dir);
				}
			}
		}
		//debugga('qua');exit;
		$options_twig = array(
			'debug' => _MARION_DISPLAY_ERROR_,
		);
		global $_MARION_ENV;
		if( $_MARION_ENV['TWIG']['cache'] ){
			//$options_twig['cache'] = ".."._MARION_TMP_DIR_;
		}
		

		$twig = new \Twig\Environment($loader,$options_twig);
		$twig->addExtension(new \Twig\Extension\StringLoaderExtension());
		
		
		
		$this->loadTemplateVariables($twig);
		$this->loadTemplateFunctions();

		

		
		if( okArray($this->_twig_functions) ){
			foreach($this->_twig_functions as $func){
				$twig->addFunction($func);
			}
		}

		if( okArray($this->_twig_global_vars) ){
			foreach($this->_twig_global_vars as $k => $v){
				$twig->addGlobal($k,$v);
			}
		}

		
		
		$filter = new \Twig\TwigFilter('serialize', function ($array) {
			return serialize($array);
		});

		$twig->addFilter($filter);
		
		$this->_tmpl_obj = $twig;

		

		
		$this->linkMenuFrontend();

		/*if( Marion::isActiveModule('ecommerce') ){
			$this->cartNumberProduct();
			$this->underCart();
			$this->switchCurrency();
		}*/
		$this->switchLocale();
		

		

		
		
		
	}

	//carica le funzioni di template di base
	function loadTemplateFunctions(){

		parent::loadTemplateFunctions();



		$twig_functions = array();
			 
		/*$twig_functions[] = new \Twig\TwigFunction('cartTotalFormatted', function () {
			if( _var('recurrent_payment') ){
	
				$total =  Cart::getCurrentTotalRecurrentPaymentOrder(false);
			}else{
				//$total = $this->cartTotal();
				$total =  Cart::getCurrentTotal(false);
				
			}
			return Eshop::formatMoney($total);
		});	
		
		

		
				
		$twig_functions[] =new \Twig\TwigFunction('cartEmpty', function () {
					
			
			if( $GLOBALS['cart_number_product'] ){
				return false;
			}else{
				return true;
			}

		});
		*/
		
		$twig_functions[] = new \Twig\TwigFunction('isMultilocale', function () {
			return Marion::isMultilocale();
		});
		
		$twig_functions[] = new \Twig\TwigFunction('authUser', function () {
			return authUser();
		});
		
		foreach($twig_functions as $function){
			$this->addTemplateFunction($function);
		}
		
		//Marion::do_action('load_twig_frontend_functions'); // da eliminare

		//carico le funzioni di front end per TWIG
		Marion::do_action('action_register_twig_function_front',array($this));


		
		
  

		
	}







	//FUNZIONI SOLO PER LA CLASSE FRONTEND
	function linkMenuFrontend(){
		$tree = LinkMenuFrontend::getTree();
		$this->setVar('items_link_menu',$tree);
	}





	function cartNumberProduct(){
		
		$this->setVar('num_products_in_cart', $GLOBALS['cart_number_product']);
		return $GLOBALS['cart_number_product'];
		

	}

	/*function underCart($ob_start=false){
		return true;
		if( isset($_SESSION['ADMIN_CART_USER_MODIFY']) && $_SESSION['ADMIN_CART_USER_MODIFY'] ){
			$cart = Cart::withId($_SESSION['ADMIN_CART_USER_MODIFY']);
			$ordini = $cart->getOrders();
		}else{
			$ordini = Cart::getCurrentOrders();
		}
	
	
		foreach($ordini as $k => $ord){
			$prodotto = $ord->getProduct();
			
			if(is_object($prodotto)){
				$ordini[$k]->productname = $prodotto->getName();
				$ordini[$k]->productname_title = $prodotto->getName(null,false);
				$ordini[$k]->link = $prodotto->getUrl();
				$ordini[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
			}
		}
		$this->setVar('ordini_under',$ordini);
		if( $ob_start ){
			ob_start();
			$this->output('partials/undercarrello.htm');
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}
		
	}

	*/


	function  switchLocale(){
        if (okArray(Marion::getConfig('locale','supportati'))) {
            $locales = Marion::getConfig('locale','supportati');
        } else {
            $locales = array('it', 'en', 'es', 'fr', 'de', 'ru', 'ro', 'pl');
        }

		
		$icon['en'] = 'gb';
		
		$toreturn = array();
        $array = $_GET;
		$routing = isset($_ENV['routing']) ? true: false;
		
		
        if(isset($_SERVER['PATH_INFO'])) {
            $vardata = explode('/', $_SERVER['PATH_INFO']);
            array_shift($vardata);
            if ($vardata[0] == 'do') {
                array_shift($vardata);
                array_shift($vardata);
                foreach ($locales AS $locale) {
                    $temp = $_SERVER['SCRIPT_NAME'].'/do/'.$locale.'/'.implode('/', $vardata);
                    //$var = 'url_lang_'.$locale;
                    $toreturn[$locale]['url'] = $temp;
					$toreturn[$locale]['icon'] = "flag-icon flag-icon-".(isset($icon[$locale])?$icon[$locale]:$locale);
                }
            } else {
                $vardata = explode('/', $_SERVER['PATH_INFO']);
                $num_param = count($vardata);
                foreach ($locales AS $locale) {
                    for ($i=0;$i<$num_param;$i++) {
                        if ($vardata[$i] == 'lang') $vardata[$i+1] = $locale;
                    }
                    $temp = $_SERVER['SCRIPT_NAME'].implode('/', $vardata);
                    if( $locale == $GLOBALS['activelocale'] ){
						$this->setVar('switch_locale_current_icon', "flag-icon flag-icon-".(isset($icon[$locale])?$icon[$locale]:$locale));
					}else{

						$toreturn[$locale]['url'] = $routing? _MARION_BASE_URL_.'index.php?lang='.$locale."&_redirect_route=".urlencode($_SERVER['REQUEST_URI'])  :$temp;
						$toreturn[$locale]['icon'] = "flag-icon flag-icon-".$locale;
					}
					
                }
            }
        } else {
            foreach ($locales AS $locale) {
                $array['lang'] = $locale;
                $query = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($array);
                if( $locale == $GLOBALS['activelocale'] ){
					$this->setVar('switch_locale_current_icon', "flag-icon flag-icon-".(isset($icon[$locale])?$icon[$locale]:$locale));
				}else{
					$toreturn[$locale]['url'] = $routing? _MARION_BASE_URL_.'index.php?lang='.$locale."&_redirect_route=".urlencode($_SERVER['REQUEST_URI']): $query;
						
					$toreturn[$locale]['icon'] = "flag-icon flag-icon-".(isset($icon[$locale])?$icon[$locale]:$locale);
				}
			
            }
        }

		
		
		$this->setVar('switch_locale_flags', $toreturn);
        
	}


	/*function switchCurrency(){
		
		
		
		$url_current = $_SERVER['SCRIPT_NAME'];
		if( $_SERVER['QUERY_STRING'] ){
			$url_current.="?".$_SERVER['QUERY_STRING'];
		}
		
		
	
		if(preg_match('/\?currency=([a-z][a-z][a-z])/i',$url_current)){
			$url = preg_replace('/\?currency=([a-z][a-z][a-z])/i','',$url_current);
		}elseif(preg_match('/\&currency=([a-z][a-z][a-z])/i',$url_current)){
			$url = preg_replace('/\&currency=([a-z][a-z][a-z])/i','',$url_current);
		}else{
			$url = $url_current;
		}
		
		if(preg_match('/\?/',$url)){
			$separator = "&";
		}elseif(preg_match('/\&/',$url)){
			$separator = "?";
		}else{
			$separator = "?";
		}
		$currencies_supportati = getConfig('currency','supported');
		
		foreach($currencies_supportati as $v){

			if( $v == $GLOBALS['activecurrency'] ){
				$this->setVar('switch_currency_current',$v);
				continue;
			}
			$select[$v] = "{$url}{$separator}currency={$v}";
		}
		$this->setVar('switch_currencies', $select);
		
		//$this->select_currency = $select;

		

	}
	*/

	function notAuth(){
		$this->output('access/not_auth.htm');
		exit;
	}






	

}
?>