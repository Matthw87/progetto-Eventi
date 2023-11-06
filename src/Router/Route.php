<?php
namespace Marion\Router;

const ROUTE_FIRST_PRIORITY = -1;
const ROUTE_LAST_PRIORITY = 10000;
const ROUTE_MIN_PRIORITY=1;
const ROUTE_MAX_PRIORITY=9999;

class Route{
    private int $id; //for database route
    private string $route = '';
    private string $function = '';
    private $method = '';
    private string $path = '';
    private string $routingPath = '';
    private string $controller = '';
    private string $prefix = '';
    private string $pathMatch = '';
    private string $module = '';
    private string $redirectTo = '';
    private bool $redirectReloadPath = false;
    private int $priority = 50;
    private string $before_to = '';
    private string $after_to = '';
    private array $selected_params;
    private array $matching = [];
    private string $auth = '';
    private bool $no_auth = false;
    private $accepted_requests;
    private $not_accepted_requests;
    private mixed $data = null;


    private array $selected_input_parameters = [];
    private array $input_parameters = [];

    /**
     * contructor
     *
     * @param string $route
     */
    function __construct(string $route)
    {
        $this->route = $route;
        $this->selected_params = [];
    }

    public function getRoute(){
        return $this->route;
    }

    public function getData(){
        return $this->data;
    }

    public function buildRoute(){
        
        if( $this->prefix ) {
            $route = "/".$this->prefix.$this->route;
        }else{
            $route = $this->route;
        }
        $this->routingPath($route);
        return $route;
    }

    public function getModule(): string{
        return $this->module;
    }
    public function getController(): string{
        return $this->controller;
    }

    /**
     * return input parameters from url
     *
     * @return array
     */
    public function getInputParameters(): array{
        return $this->input_parameters;
    }

    /**
     * return selected input parameters from url
     *
     * @return array
     */
    public function getSelectedInputParameters(): array{
        return $this->selected_input_parameters;
    }

    /**
     * Get allowed server request method for route
     *
     * @return string|array
     */
    public function getMethod(){
        return $this->method;
    }

    public function getRoutingPath(): string{
        return $this->routingPath;
    }

    public function getPathMatch(): string{
        return $this->pathMatch;
    }

    public function getRedirectTo(): string{
        return $this->redirectTo;
    }

    public function getRedirectReloadPath(): bool{
        return $this->redirectReloadPath;
    }

    public function getFunction(): string{
        return $this->function;
    }

    public function getId(): int{
        return $this->id;
    }

    public function setId(int $id){
        $this->id = $id;
        return $this;
    }

    public function getPriority(): int{
        return $this->priority;
    }

    public function getAfterTo(): string{
        return $this->after_to;
    }

    public function getBeforeTo(): string{
        return $this->before_to;
    }

    public function getAccepts(): ?array{
        return $this->accepted_requests;
    }

    public function getNotAccepts(): ?array{
        return $this->not_accepted_requests;
    }

    public function getPrefix(): string{
        return $this->prefix;
    }

    public function auth(?string $auth=null): self{
        if( !$auth ) $auth = 'base';
        $this->auth = $auth;
        return $this;
    }

    public function noAuth(): self{
        $this->no_auth = true;
        return $this;
    }
    
    private function module(string $module): self{
        $this->module = $module;
        return $this;
    }

    
    public function accepts(array $accepts): self{
        $this->accepted_requests = $accepts;
        return $this;
    }

    public function notAccepts(array $not_accepts): self{
        $this->not_accepted_requests = $not_accepts;
        return $this;
    }

    private function prefix(string $prefix): self{
        $this->prefix = $prefix;
        return $this;
    }

    public function redirectTo(string $redirectTo, bool $reloadPath): self{
        $this->redirectTo = $redirectTo;
        $this->redirectReloadPath = $reloadPath;
        return $this;
    }

    /**
     * set Input paramaters form url
     *
     * @param array $parameters
     * @return self
     */
    public function setInputParameters($parameters = []): self{
        $this->input_parameters = $parameters;
        return $this;
    }
    
    /**
     * set selected input paramaters form url
     *
     * @param array $parameters
     * @return self
     */
    public function setSelectedInputParameters($parameters = []): self{
        $this->selected_input_parameters = $parameters;
        return $this;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return self
     */
    public function controller(string $controller): self{
        $this->controller = $controller;
        return $this;
    }


    /**
     * Set data
     *
     * @param string $controller
     * @return self
     */
    public function data(mixed $data): self{
        $this->data = $data;
        return $this;
    }


    public function pathMatch(string $pathMatch): self{
        $this->pathMatch = $pathMatch;
        return $this;
    }

    public function routingPath(string $routingPath): self{
        $this->routingPath = $routingPath;
        return $this;
    }

    

    /**
     * Add method to route
     *
     * @param string|array $method
     * @return self
     */
    public function method($method): self{
        $this->method = $method;
        return $this;
    }

    /**
     * Add contition an specifc params
     *
     * @param string $param
     * @param string $match
     * @return self
     */
    public function where(string $param,string $match): self{
        $this->matching[$param] = $match;
        return $this;
    }

    public function select(array $params): self{
        $this->selected_params = $params;
        return $this;
    }
    
    public function getAuth(): string{
        return $this->auth;
    }

    public function getNoAuth(): bool{
        return $this->no_auth;
    }

    public function getConditions(): array{
        return $this->matching;
    }

    public function getSelectedParams(): array{
        return $this->selected_params;
    }

    /**
     * Set function callback
     *
     * @param string $function
     * @return self
     */
    public function function(string $function): self{
        $this->function = $function;
        return $this;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return self
     */
    public function priority(int $priority): self{
        if( $priority >= ROUTE_MIN_PRIORITY && $priority <= ROUTE_MAX_PRIORITY ){
            $this->priority = $priority;
            return $this;
        }else{
            throw new \Exception('PATH PRIORITY ROUTE NOT VALID');
        }
        
        
    }

    public function before(string $route): self{
        $this->before_to = $route;
        return $this;
    }

    public function after(string $route): self{
        $this->after_to = $route;
        return $this;
    }

    /**
     * Set priority to -1
     *
     * @return self
     */
    public function first(): self{
        $this->priority = ROUTE_FIRST_PRIORITY;
        return $this;
    }

    /**
     * Set priority to 99999999
     *
     * @return self
     */
    public function last(): self{
        $this->priority = ROUTE_LAST_PRIORITY;
        return $this;
    }

    /**
     * Set path route
     *
     * @param string $path
     * @return self
     */
    public function path(string $path): self{
        $this->path = $path;
        $list = explode(':',$this->path);
        if( count($list) == 3){
            $this->module($list[0]);
            $this->prefix($list[0]);
            $this->controller($list[1]);
            $this->function($list[2]);
        }else if(count($list) == 2 ){
            $this->controller($list[0]);
            $this->function($list[1]);
        }else{
            throw new \Exception('ERROR PATH ROUTE');
            
        }
        return $this;
    }
    /**
     * Create route instance
     *
     * @param string|array $method
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function instance($method, string $route, string $path, array $options=[]): self{
        $obj = (new Route($route))->method($method)->path($path);
        if( okArray( $options) ){
            if( isset($options['prefix'] ) ){
                $obj->prefix($options['prefix']);
            }
        }
        Router::addRoute($obj);
        return $obj;
    }

    /**
     * Create route instance with method GET
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function get(string $route, string $path, array $options=[]): self{
        return self::instance('GET', $route, $path, $options);
    }

    /**
     * Create route instance with method POST
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function post(string $route, string $path, array $options=[]): self{
        return self::instance('POST', $route, $path, $options);
    }

    /**
     * Create route instance with method PUT
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function put(string $route, string $path, array $options=[]): self{
        return self::instance('PUT', $route, $path, $options);
    }

    /**
     * Create route instance with method DELETE
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function delete(string $route, string $path, array $options=[]): self{
        return self::instance('DELETE', $route, $path);
    }

    /**
     * Create route instance with method OPTIONS
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function options(string $route, string $path, array $options=[]): self{
        return self::instance('OPTIONS', $route, $path);
    }

    /**
     * Create route instance with generic method
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    public static function any(string $route, string $path, array $options=[]): self{
        return self::instance('', $route, $path, $options);
    }

    /**
     * Create route instance with specific methods
     *
     * @param string $route
     * @param string $path
     * @param array $options
     * @return self
     */
    /** */
    public static function match( array $methods, string $route, string $path, array $options=[]): self{
        return self::instance($methods, $route, $path, $options);
    }

    

    public static function redirect(string $route, string $redirecTo, bool $reloadPath=false){
        $obj = (new Route($route))->redirectTo($redirecTo,$reloadPath);
        Router::addRoute($obj);
        return $obj;
    }

}