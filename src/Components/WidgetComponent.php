<?php
namespace Marion\Components;
use Marion\Core\Marion;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\FilesystemLoader;
class WidgetComponent{
	
	public Environment $_twig;
	public array $_twig_vars;
	public array $_twig_templates_dir;
	public array $_twig_functions;

	public string $module = '';
	
	function __construct(string $module=''){
		$this->module = $module;
		$this->_twig_functions = [];
		$this->_twig_vars = [];
		$this->_twig_templates_dir = [];
	
	}
	
	/**
	 * Set module param
	 *
	 * @param string $module
	 * @return void
	 */
	function setModule(string $module): void{
		$this->module = $module;
	}

	/**
	 * Check if client is Mobile
	 *
	 * @return boolean
	 */
	function isMobile(): bool {
		return _MARION_DEVICE_ == 'MOBILE';
	}

	/**
	 * Check if client is Tablet
	 *
	 * @return boolean
	 */
	function isTablet(): bool {
		return _MARION_DEVICE_ == 'TABLET';
	}

	
	/**
	 * Output template
	 *
	 * @param string $tmpl
	 * @param boolean $string
	 * @return void
	 */
	function output(string $tmpl,$string = false){
		$this->initTemplateTwig();
		
		if( $string ){
			$output = '';
			if( okArray($this->_twig_vars) ){
				$output = $this->_twig->render($tmpl, $this->_twig_vars);
			}else{
				$output = $this->_twig->render($tmpl);
			}
			return $output;
		}else{
			if( okArray($this->_twig_vars) ){
				echo $this->_twig->render($tmpl, $this->_twig_vars);
			}else{
				echo $this->_twig->render($tmpl);
			}
		}
	}
	

	/**
	 * imposta una variabile nel template
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	function setVar($key,$value): void{
		$this->_twig_vars[$key] = $value;
	}

	/**
	 * Restituisce l'elenco delle cartelle di templates
	 *
	 * @return array
	 */
	function getTemplatesDirectories(): array{
		$path = [];
		if( $this->module && $this->module != _MARION_THEME_){
			$path_theme = _MARION_THEME_DIR_._MARION_THEME_;
			if( file_exists($path_theme."/modules/".$this->module."/templates")){
				$path_theme .= "/modules/".$this->module."/templates";
				$path[] = [$path_theme,$this->module];
			}
			$path[] = [_MARION_MODULE_DIR_.$this->module."/templates",$this->module];
		}else{
			$path[] = _MARION_THEME_DIR_._MARION_THEME_."/templates";
		}
		return $path;
	}


	/**
	 * Init twig template engine
	 *
	 * @return void
	 */
	function initTemplateTwig(): void{
		
		$paths = $this->getTemplatesDirectories();
		//debugga($paths);exit;
		$loader = new FilesystemLoader();
		foreach($paths as $path){
			
			if( is_array($path) ){
				if( file_exists( $path[0] ) ){
					$loader->addPath($path[0],$path[1]);
				}
			}else{
				if( file_exists( $path ) ){
					$loader->addPath($path);
				}
			}
		}
		foreach($this->_twig_templates_dir as $path){
			if( is_array($path) ){
				if( file_exists( $path[0] ) ){
					$loader->addPath($path[0],$path[1]);
				}
			}else{
				if( file_exists( $path ) ){
					$loader->addPath($path);
				}
			}
		}
		$twig = new Environment($loader, [
			//'cache' =>  ".."._MARION_TMP_DIR_,
		]);
		$this->loadTemplateVariables($twig);
		$this->loadTemplateFunctions();

		
		if( okArray($this->_twig_functions) ){
			foreach($this->_twig_functions as $func){
				$twig->addFunction($func);
			}
		}
		
		$this->_twig = $twig;
	}

	/**
	 * Carica le funzioni di template di base
	 *
	 * @return void
	 */
	function loadTemplateFunctions(): void{
		$this->_twig_functions[] = new TwigFunction('auth', function ($type) {
			return Marion::auth($type);
		});
		$this->_twig_functions[] = new TwigFunction('okArray', function ($array) {
			return okArray($array);
		});
		$this->_twig_functions[] = new TwigFunction('tr', function ($string,$module=null) {
			return _translate($string,$module);
		});
		$this->_twig_functions[] = new TwigFunction('getConfig', function ($group=NULL,$key=null,$value=null) {	
			return Marion::getConfig($group,$key,$value);
		});
	}

	/**
	 * Carica le variabili di template di base
	 *
	 * @param \Twig\Environment $twig
	 * @return void
	 */
	function loadTemplateVariables(Environment $twig): void{
		
		//$_global_vars['activecurrency'] = _MARION_CURRENCY_;
		$_global_vars = [];
		$_global_vars['activelocale'] = _MARION_LANG_;
		
		//$_global_vars['currencyLabel'] = Marion::getHtmlCurrency(_MARION_CURRENCY_);
		if( $user = Marion::getUser() ){
			$_global_vars['userdata'] = $user;
		}
		$locales = Marion::getConfig('locale','supportati');
		$_global_vars['locales'] = array($_global_vars['activelocale']);
		
		foreach($locales as $loc){
			if( !in_array($loc,$_global_vars['locales'])){
				$_global_vars['locales'][] = $loc;
			}
		}
		
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$_protocollo = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}else{
			$_protocollo = !empty($_SERVER['HTTPS']) ? "https" : "http";
		}
		
		$_global_vars['baseurl'] = _MARION_BASE_URL_;
		if( isset($_SERVER['REDIRECT_QUERY_STRING']) && $_SERVER['REDIRECT_QUERY_STRING'] ){
			$_global_vars['return_location'] = $_SERVER['SCRIPT_NAME']."?".$_SERVER['REDIRECT_QUERY_STRING'];
		}elseif( $_SERVER['QUERY_STRING']){
			$_global_vars['return_location'] = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
		}else{
			$_global_vars['return_location'] = $_SERVER['SCRIPT_NAME'];
		}

		foreach($_global_vars as $k => $v){
			$twig->addGlobal($k, $v);
		}
	}


	/**
	 * associa una funzione di template a TWIG
	 *
	 * @param \Twig\TwigFunction $function
	 * @return void
	 */
	function addTemplateFunction(TwigFunction $function=NULL): void{
		$this->_twig_functions[] = $function;
	}

	/**
	 * Aggiunge una cartella di templates
	 *
	 * @param string $dir
	 * @param string $namespace
	 * @return void
	 */
	function addTwingTemplatesDir(string $dir, string $namespace=null): void{
		if( $namespace ){
			$this->_twig_templates_dir[] = [$dir,$namespace];
		}else{
			$this->_twig_templates_dir[] = $dir;
		}
		
	}



}




?>