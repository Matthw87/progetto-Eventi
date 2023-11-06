<?php
use Marion\Router\Route;
Route::get('/product/{product}/get-next-attributes/{attribute}','catalogo:ProductController:getNextAttributes')
    ->where('product','[0-9]+')
    ->where('attribute','[0-9]+');
    
Route::get('/product/search/{search}','catalogo:ProductListController:search');

Route::get('/product/{id}/{name}','catalogo:ProductController:view')
    ->where('id','[0-9]+')
    ->select(['id']);



Route::get('/category/{id}/{name}','catalogo:ProductListController:category')
    ->where('id','[0-9]+')
    ->select(['id']);
Route::get('/tag/{tag}','catalogo:ProductListController:tag')
    ->where('tag','[0-9a-zA-z]+');

Route::get('/api/v1/catalog/categories/{id}/children','catalogo:ApiController:getCategoryChildren',['prefix' => ''])->where('id',"[0-9]+");
Route::get('/api/v1/catalog/categories/{id}','catalogo:ApiController:getCategory',['prefix' => ''])->where('id',"[0-9]+");
Route::get('/api/v1/catalog/categories','catalogo:ApiController:getCategories',['prefix' => '']);
Route::get('/api/v1/catalog/products/{id}','catalogo:ApiController:getProduct',['prefix' => ''])->where('id',"[0-9]+");
Route::get('/api/v1/catalog/products','catalogo:ApiController:getProducts',['prefix' => '']);
Route::get('/api/v1/catalog/tags','catalogo:ApiController:getTags',['prefix' => '']);



?>