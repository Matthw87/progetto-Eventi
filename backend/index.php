<?php
use Marion\Router\Router;
define('_MARION_ADMIN_DIR',basename(getcwd()));
define('_MARION_ADMIN_SIDE_',1); //constante che stabilisce la navigazione nel back office
require ('../config/include.inc.php');



if( !_MARION_ADMIN_SIDE_ENABLED_ ){
    header('Location: '._MARION_BASE_URL_.'index.php');
}


//richiamo il router per smistare la richiesta al controller di competenza
$router = new Router();
$router->dispatch();





?>