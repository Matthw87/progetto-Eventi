<?php
use Marion\Core\Marion;

use Marion\Entities\Cms\LinkMenuFrontend;
use Illuminate\Database\Capsule\Manager as Capsule;
use Marion\Core\Context;

require_once (dirname(__FILE__).'/../vendor/autoload.php');
require (dirname(__FILE__).'/env.php');


//$dotenv = new Dotenv2();
//carico le variabili d'ambiente
//$dotenv->load(_MARION_ROOT_DIR_.'.env');


$dotenv = Dotenv\Dotenv::createImmutable(_MARION_ROOT_DIR_);
$dotenv->load();



/*
echo "<pre>";
print_r($dotenv);exit;
*/

if( defined('_MARION_MEMORY_LIMIT_') ){
	ini_set('memory_limit', _MARION_MEMORY_LIMIT_);
}
if( defined('_MARION_MAX_EXECUTION_TIME_') ){
	ini_set('max_execution_time', _MARION_MAX_EXECUTION_TIME_);
}

if( defined('_MARION_DEFAULT_TIMEZONE_') ){
	date_default_timezone_set(_MARION_DEFAULT_TIMEZONE_);
}


$_MARION_ENV = array(
	"DATABASE" => array(
		"options" => array(
			"host" => $_ENV["DB_HOST"],
			"nome" => $_ENV["DB_NAME"],
			"password" => $_ENV["DB_PASS"],
			"user" => $_ENV["DB_USER"],
			"port" => $_ENV["DB_PORT"],
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			"cache" => 0,
			"cache_file" => 1,
			"pathcache" => "cache",
			"lifetime" => 10000000,
			"log" => 0,
		),
	),
	"CACHE" => array(
		"active" => 0,
		"time" => 1000000,
		"storage" => "files",
		"path" => _MARION_ROOT_DIR_."cache",
		"securityKey" => "aGt784=nuovo",
	),
	"ACCESSO" => array(
		"restrected" => 0
	),
	"TWIG" => array(
		"cache" => 0
	),
	
);


if( _MARION_DISPLAY_ERROR_ ){
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
	$whoops->register();
}

/** ELOQUENT ORM */
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_MARION_ENV["DATABASE"]['options']["host"],
    'database'  => $_MARION_ENV["DATABASE"]['options']["nome"],
    'username'  => $_MARION_ENV["DATABASE"]['options']["user"],
    'password'  => $_MARION_ENV["DATABASE"]['options']["password"],
    'charset'   => $_MARION_ENV["DATABASE"]['options']["charset"],
    'collation' => $_MARION_ENV["DATABASE"]['options']["collation"],
    'prefix'    => '',
]);


// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
$capsule->bootEloquent();

/** END ELOQUENT */


require_once(_MARION_LIB_."functions.php");
$GLOBALS['setting']['default'] = $_MARION_ENV;

if (_MARION_ENABLE_SSL_ && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}


/**
 * Load modules
 */
Marion::loadModules();

/**
 * Load hooks
 */
Marion::loadHooks();

/**
 * Load frontend routes
 */
Marion::loadRoutes(); 

/**
 * Call hooks on boot application
 */
Marion::do_action('action_on_boot'); // carico tutti gli hook registrati su boot (FUNZIONA SOLO PER LE ACTION MEMORIZZATE NEL DB)


if( !defined('_MARION_CONSOLE_')){
	session_start(); //avvio la sessione
}


/**
 * Load languages
 */
Marion::loadLang();

/**
 * Load Theme
 */
Marion::loadTheme();

/**
 * Load translations
 */
Marion::loadTranslations();

/**
 * Detect client device: mobile, tablet or desktop
 */
Marion::detectClient();





/*if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ){
	$_current_user = Marion::getUser();
	if( is_object($_current_user) && $_current_user->locale ){
		
		$GLOBALS['activelocale'] = $_current_user->locale;
	}
	
}*/

/**
 * Load configuration site
 */
Marion::read_config();


/**
 * override setting current lang
 */

Marion::do_action('action_override_set_language');
define('_MARION_LANG_',Context::getLang());

//registro le url delle pagine come url disponibili per i link menu frontend
LinkMenuFrontend::registerItem(\Marion\Entities\Cms\PageItemFrontend::class);

//Regola per la password
$GLOBALS['PASSWORD_FORM_RULE'] = (new \Marion\Support\Form\Rule())->validate(function($value, \Marion\Support\Form\FormData $formdata){
	if( $value ){
		if( strlen($value) > 50 ){
			return _translate('password_validation_errors.min_lenght');
		}
		if( strlen($value) < 6 ){
			return _translate('password_validation_errors.max_lenght');
		}
		if( !preg_match('/[A-Z]/',$value) ){
			return _translate('password_validation_errors.uppercase');
		}
		if( !preg_match('/[a-z]/',$value) ){
			return _translate('password_validation_errors.lowercase');
		}
		if( !preg_match('/[0-9]/',$value) ){
			return _translate('password_validation_errors.number');
		}
		if( !preg_match('/[%\-_|\#\*\?]/',$value) ){
			return _translate('password_validation_errors.special_character');
		}
	}
});


/**
 * Call hooks after init application
 */
Marion::do_action('action_after_init');

?>
