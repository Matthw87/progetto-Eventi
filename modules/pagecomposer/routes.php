<?php
use Marion\Router\Route;
Route::get('/preview/{id}','pagecomposer:PreviewController:preview')
    ->where('id','[0-9]+');
?>