<?php
namespace Marion\Router;

use Marion\Traits\ApiResponse;
use Marion\Core\Context;
use Illuminate\Database\Capsule\Manager as DB;
class Router{
	use ApiResponse;
	public $_admin_side = false;
	public $_ctrl;
	public $_mod;

	private $http_accept;

    private static $routes = [];
	private static $positions = [];
	private static $count = 0;

	/**
	 * Add route to routing
	 *
	 * @param Route $route
	 * @return void
	 */
    public static function addRoute(Route $route){
		self::$count++;
		$route_path = $route->buildRoute();
		self::$positions[$route_path] = self::$count;
        self::$routes[$route_path] = $route;
    }

	/**
	 * Get all active routes
	 *
	 * @return array
	 */
    public static function getRoutes(): array{
        return self::$routes;
    }

	public static function recursive_change_key($arr, $set) {
        if (is_array($arr) && is_array($set)) {
    		$newArr = array();
    		foreach ($arr as $k => $v) {
    		    $key = array_key_exists( $k, $set) ? $set[$k] : $k;
    		    $newArr[$key] = is_array($v) ? self::recursive_change_key($v, $set) : $v;
    		}
    		return $newArr;
    	}
    	return $arr;    
    }

	private static function loadRoutes(){
		if( DB::schema()->hasTable('routes') ){
			$routes = DB::table('routes')->get()->toArray();
			foreach($routes as $route){
				
				$methods = unserialize($route->methods);
				$params = unserialize($route->params);
				
				$route = Route::match($methods,$route->route,$route->action)
					->priority(1)
					->setId($route->id);
				if( okArray($params) ){
					foreach($params as $param => $regex){
						//debugga($params);
						$route->where($param,$regex);
					}
				}
			}
		}

		
	}

	public static function buildRoutes(): void{
		self::loadRoutes();
		
		/*$change_routes = [];
		if( okArray(self::$routes) ){
			foreach(self::$routes as $key => $route){
				if($prefix = $route->getPrefix()){
					$change_routes[$key] = "/{$prefix}{$key}";
				}
			}
			if( okArray($change_routes) ){
				self::$routes = self::recursive_change_key(self::$routes,$change_routes);
			}
		}*/
		
		uasort(self::$routes, function($a,$b){
			if ($a->getPriority() == $b->getPriority() ) {
				$routing_path_a = $a->getRoutingPath();
				$routing_path_b = $b->getRoutingPath();
				return (self::$positions[$routing_path_a] < self::$positions[$routing_path_b]) ? -1 : 1;
			}
			return ($a->getPriority()  < $b->getPriority() ) ? -1 : 1;
		});
		//debugga(self::$routes);exit;
		/*$keys = array_keys(self::$routes);
		$routes = array_values(self::$routes);
		foreach( $routes as $ind => $route){
			if( $path_after_to = $route->getAfterTo() ){
				unset($routes[$ind]);
				$pos = array_search($path_after_to,$keys);
				if( array_key_exists($pos+1,$routes ) ){
					$tmp = $routes[$pos+1];
					$routes[$pos+1] = $route;

				}
				
				
			}
		}
		debugga(self::$routes);exit;*/
    }

	/**
	 * Set routes of routing
	 *
	 * @param array $routes
	 * @return void
	 */
    public static function setRoutes(array $routes): void{
        self::$routes = $routes;
    }


	public static $redirections = [];

	function __construct(){
		$this->http_accept = $_SERVER['HTTP_ACCEPT'];
		$this->_ctrl = _var('ctrl') ? _var('ctrl') : 'Index';
		$this->_mod = _var('mod');
		if( !$this->_ctrl ){
			$this->_ctrl = 'IndexAdmin';
		}
		if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_){
			$this->_admin_side = true;
		}
		
	}


	function getPaths(){
		$path = _MARION_ROOT_DIR_;
		
		if( $this->_mod ){
			$path_theme = _MARION_THEME_DIR_._MARION_THEME_;
			$path .= "modules/".$this->_mod;
			$path .= "/controllers";
			
			
			
			if( $this->_admin_side ){
				if( file_exists($path_theme."/modules") ){
					$files[] = $path_theme."/modules/".$this->_mod."/controllers/admin";
				}
				$files[] = $path. "/admin";
			}else{
				$path_theme .= "/modules/".$this->_mod."/controllers";
				$files[] = $path_theme."/front";
				$files[] = $path_theme;
				$files[] = $path. "/front";
			}
			$files[] = $path;
		}else{
			if( $this->_admin_side ){
				$path .= 'backend/';

			}
			$files[] =$path. "controllers";
		}
		
		//debugga($files);exit;
		return $files;
	}

	function resolveRoute(){
		$url = $_SERVER['REQUEST_URI'];
        if( $url == _MARION_BASE_URL_.'index.php' ){
            $url = _MARION_BASE_URL_;
        } 
		$pattern_admin = '/^'.preg_replace('/\//','\/',_MARION_BASE_URL_).'backend/';
		//debugga($pattern_admin);exit;
		if( preg_match($pattern_admin,$url) ){
			return;
		}
        $method = $_SERVER['REQUEST_METHOD'];
        if( okArray(self::$routes) ){
			
			foreach(self::$routes as $route => $obj){

				$accepted_requests =$obj->getAccepts();
				if( $accepted_requests ){
					if( !in_array($this->http_accept,$accepted_requests) ) continue;
				}
				$not_accepted_requests =$obj->getNotAccepts();
				if( $not_accepted_requests ){
					if( in_array($this->http_accept,$not_accepted_requests) ) continue;
				}
				
				$check = preg_match('({\*})',$route);
				if( $check ){
					$route = preg_replace('/{\*}/','(.*)',$route);
				}
				preg_match_all('({[a-zA-z_]+})',$route,$params);


				
				$param_match = [];
				$order_match = [];
				if( okArray($params[0]) ){
					$params = $params[0];
					
					$matching = $obj->getConditions();
					
					foreach($params as $i => $par){
						//$key = preg_replace('/{/','\{',$par);
						$key = "/{$par}/";
						

						$param_match[$key] = '([a-zA-Z0-9_:-]+)';
						$param_name = preg_replace('/([\{\}]+)/','',$par);
						$order_match[$param_name] = $i;
						if( array_key_exists($param_name,$matching)) {
							$param_match[$key] = "({$matching[$param_name]})";
						}
					}
					
					$route = preg_replace_array(array_keys($param_match),array_values($param_match),$route);
				}

				if( $route == '**' ){
					$pattern ="(.*)"; 
				}else{
					$pattern =$route. "(.*)"; 
				}
				
				//if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
				if (preg_match('#^' . $pattern . '#', $url, $matches)) {
                    if( $obj->getPathMatch() == 'full' && $url != $route ){	
						continue;
					}
					$auth = $obj->getAuth();
					$no_auth = $obj->getNoAuth();
					if( $auth && !auth($auth) ){
						if( $this->http_accept == 'application/json'){
							$this->response(_translate('not_auth'),401);
						}
						continue;
					}
					if( $no_auth && authUser() ){
						continue;
					}
					

                    if( $redirect_to = $obj->getRedirectTo() ){	
						$reloadPath = $obj->getRedirectReloadPath();
						
						if( $reloadPath ){
							header('Location: '.$redirect_to);
							die();
						}
						$obj = self::$routes[$redirect_to];
					}
                    
                    if( $route_method = $obj->getMethod() ){
						if(is_array($route_method) ){
							if( !in_array( $method, $route_method ) ) continue;
						}else{
							if( $route_method != $method ) continue;
						}
                        
                    }
					

                    
					$controller = $obj->getController();
					

					

					$scope = 'front';
					if( isset($data['admin']) ){
						$scope = 'admin';
					}
					if( $module = $obj->getModule() ){
                        $controller_override_file =  _MARION_THEME_DIR_._MARION_THEME_."/modules/".$module."/controllers/".$scope."/".$controller.".php";
                        if( file_exists($controller_override_file) ){
                            require_once($controller_override_file);
                        }else{
							if( file_exists(_MARION_MODULE_DIR_.$module."/controllers/".$scope."/".$controller.".php") ){
								require_once(_MARION_MODULE_DIR_.$module."/controllers/".$scope."/".$controller.".php");
							}else{
								if( file_exists(_MARION_THEME_DIR_.$module."/controllers/".$scope."/".$controller.".php") ){
									require_once(_MARION_THEME_DIR_.$module."/controllers/".$scope."/".$controller.".php");
								}
							}
                        }
						
					}else{
						$controller_override_file =  _MARION_THEME_DIR_._MARION_THEME_."/overrides/controllers/".$controller.".php";
						if( file_exists($controller_override_file) ){
                            require_once($controller_override_file);
                        }else{
							require_once(_MARION_ROOT_DIR_."/controllers/".$controller.".php");

                        }
					}
					$options = [
						'from_routing' => 1,
						'module' => $module
					];
					if( array_key_exists($controller,self::$redirections) ){
						$redirectController = self::$redirections[$controller];
						$ctrl = new $redirectController($options);
					}else{
						$ctrl = new $controller($options);
					}
                    
					$params = $matches;
					unset($params[0]);
					
					$params = array_values($params);
					$selected = $obj->getSelectedParams();

					$input_params = [];
					$selected_input_params = [];

					if( okArray($params) ){
						foreach($order_match as $_param => $_order){
							if( okArray($selected) ){
								if( in_array($_param,$selected) ){
									$selected_input_params[$_param] = $params[$_order];
								}
							}else{
								$selected_input_params[$_param] = $params[$_order];
							}
							$input_params[$_param] = $params[$_order];
						}
					}
					

					$obj->setInputParameters($input_params);
					$obj->setSelectedInputParameters($selected_input_params);
					
					if( okArray($selected) ){
						$new_params = [];
						foreach($selected as $s){
							$order = $order_match[$s];
							$new_params[] = $params[$order];
						}
						$params = $new_params;
					}
					
					
					Context::set('route',$obj);
					
					$function = $obj->getFunction()?$obj->getFunction():'display';
					$_ENV['routing'] = true;
					call_user_func_array(array($ctrl, $function),$params);
					exit;

				}
			}
		}

		if( $this->http_accept == 'application/json'){
			$this->response('PAGE NOT FOUND',404);
		}
		
	}


	function dispatch(){
		$this->resolveRoute();
		$class = $this->_ctrl."Controller";
		
		$_paths = $this->getPaths();
		foreach($_paths as $path){
			$file = $path . "/" . $class.".php";
			
			if( file_exists($file) ){
				
				require_once($file);
				break;
			}
		}
		
		if( array_key_exists($class,self::$redirections) ){
			$redirect = self::$redirections[$class];
			if($redirect){
				$class = $redirect;
			}
		}
		//controllo se il modulo è installato ed attivo
		if( $this->_mod && !in_array($this->_mod,Context::getModules()) ){
			throw new \Exception("Controller '{$class}' not founds");
			exit;
		}
		
		if( isset($class) && class_exists($class) ){
			$ctrl = new $class();
		}else{
			throw new \Exception("Controller '{$class}' not founds");
		}
		

	}

	/**
	 * Redirect $ctrl to $redirectCtrl controller
	 *
	 * @param string $ctrl
	 * @param string $redirectCtrl
	 * @return void
	 */
	public static function redirectController(string $ctrl, string $redirectCtrl): void{
		self::$redirections[$ctrl] = $redirectCtrl;
	}


	//metodo che registra un url per il redirect
	public static function registerUrl($match,$redirect){
		
	}
}


?>