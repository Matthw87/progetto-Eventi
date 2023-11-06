<?php
use Marion\Router\Route;
Route::get('/{module}.json','api:IndexController:json')->where('module',"([a-zA-z\_]+)");
Route::get('/swagger','api:IndexController:swagger')->where('module',"([a-zA-z\_]+)");
Route::get('/{module}/swagger','api:IndexController:swagger')->where('module',"([a-zA-z\_]+)");


Route::post('/auth/login','api:AuthController:login',['prefix'=> 'api/v1']);
Route::post('/auth/forgot-password','api:AuthController:forgotPassword',['prefix'=> 'api/v1']);
Route::get('/auth/me','api:AuthController:me',['prefix'=> 'api/v1'])->auth('base');

?>