<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::any('/index','Weixin\IndexController@index');
Route::get('/token','Weixin\IndexController@token');
Route::get('/mage','Weixin\MaController@mage');
Route::any('/wx','Weixin\IndexController@wx');
Route::get('/tianqi','Weixin\MaController@tianqi');
Route::get('/getuser','Weixin\IndexController@getuser');
Route::get('/ruku','Weixin\IndexController@ruku');







