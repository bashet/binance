<?php

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

Route::get('/', 'HomeController@welcome');
Route::get('registration-done', function (){
    flash()->success('Registration has been successfully completed!');
    auth()->logout();
    return redirect('/');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('trading', 'TradingController@index')->name('trading');
Route::post('start-scanning', 'TradingController@start_scanning');
Route::get('adaeth', 'HomeController@adaeth');
Route::post('adaeth', 'HomeController@get_adaeth');

Route::post('get-current-price', 'HomeController@get_current_price');
