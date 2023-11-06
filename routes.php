<?php
use Marion\Router\Route;

Route::match(['GET','POST'],'/login','AccessController:login');
Route::get('/logout','AccessController:logout');
Route::match(['GET','POST'],'/activation/{*}','AccessController:activation')->noAuth();
Route::match(['GET','POST'],'/forgot-password','AccessController:forgotPassword')->noAuth();
Route::match(['GET','POST'],'/reset-password/{*}','AccessController:resetPassword')->noAuth();
Route::get('/reset-password-success','AccessController:resetPasswordSuccess');
Route::match(['GET','POST'],'/signup','AccessController:signup')->noAuth();
Route::get('/account/home','AccountController:home')->auth();
Route::match(['GET','POST'],'/account/me','AccountController:personalData')->auth();
Route::get('/p/{page}','IndexController:getPage');
Route::get('/','IndexController:getPage')->pathMatch('full');
Route::redirect('/account/personal-data','/account/me')->auth();
//Route::redirect('**','/p/404',true)->last();
?>