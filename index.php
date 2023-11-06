<?php
require ('config/include.inc.php');
if( _MARION_ADMIN_REDIRECT_ENABLED_ ){
    if( $_SERVER['HTTP_ACCEPT'] != 'application/json' ){
        header('Location: '._MARION_BASE_URL_.'backend/index.php');
        exit;
    }
}
//richiamo il router per smistare la richiesta al controller di competenza
$router = new Marion\Router\Router();
$router->dispatch();
?>
