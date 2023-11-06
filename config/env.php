<?php
define('_MARION_VERSION_','3.1.1'); //versione di Marion

if( defined('_MARION_CONSOLE_') ){
  define('_MARION_DOCUMENT_ROOT_',getcwd()); //document root del sito
}else{
  define('_MARION_DOCUMENT_ROOT_',$_SERVER["DOCUMENT_ROOT"]); //document root del sito
}

define('_MARION_ROOT_DIR_',_MARION_DOCUMENT_ROOT_."/"); //percorso della cartella contente il sito

define('_MARION_MODULE_DIR_',_MARION_ROOT_DIR_."modules/"); //percorso della cartella contente i moduli
define('_MARION_MEDIA_DIR_',_MARION_ROOT_DIR_."media/"); //percorso della cartella contente i media
define('_MARION_THEME_DIR_',_MARION_ROOT_DIR_."themes/"); //percorso della cartella contente i temi
define('_MARION_TMP_DIR_',_MARION_ROOT_DIR_."tmp"); //cartella dove vengono messi i file temporanei
define('_MARION_LIB_',_MARION_ROOT_DIR_.'lib/'); //cartella dove risiedono le librerie
define('_MARION_UPLOAD_DIR_',_MARION_MEDIA_DIR_."upload/"); //cartella in cui vengono caricate le immagini e gli allegati


define('_MARION_DEV_MODE_',1); //stabilisce se stiamo in modalità sviluppo

define('_MARION_BASE_URL_',"/"); //url base del sito
define('_MARION_ENABLE_SSL_',0); //abilita l'https su sito


/*** VARIABILI PHP ******/
if( _MARION_DEV_MODE_ ){
  define('_MARION_DISPLAY_ERROR_',1);
}else{
  define('_MARION_DISPLAY_ERROR_',0);
}

define('_MARION_MEMORY_LIMIT_','1024M'); //imposta il memory limit di php
define('_MARION_MAX_EXECUTION_TIME_',0); //imposta il tempo massimo di esecuzione di uno script. Se 0 il tempo è da considerasi infinito
define('_MARION_DEFAULT_TIMEZONE_','Europe/Rome'); //Sets the default timezone used by all date/time functions in a script



/* OTTIMIZZAZIONE PAGE SPEED */
define('_MARION_COMPRESSED_CSS_',0); //comprime i files css in un unico file
define('_MARION_COMPRESSED_JS_',0); //comprime i files js in un unico file
define('_MARION_CACHE_IMAGES_',1); //


/* STORAGE */
define('_MARION_COOKIE_SESSION_',1); //memorizza i dati nei cookie e non nella sessione

/* JWT */
define('_MARION_JWT_KEY_','marion_site'); //chiave utilizzata per la creazione del token JWT
define('_MARION_JWT_EXPIRATION_TIME_','1 hours'); //durata del token

/* WEBP IMAGES */
define('_MARION_QUALITY_WEBP_CONVERT_',80); //stabilisce la qualità delle immagini webp qunado viene fatta la conversione. Valore compreso tra 1 a 100
define('_MARION_COMPRESSED_DIR_',_MARION_ROOT_DIR_."cache/minimized/"); //comprime i files css in un unico file



define('_MARION_ADMIN_SIDE_ENABLED_',1); //abilita il backend
define('_MARION_ADMIN_REDIRECT_ENABLED_',0); //abilita redirect al back office




?>