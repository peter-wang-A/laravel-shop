<?php

use App\Http\Controllers\PagesController;
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

Route::get('/', 'PagesController@root')->name('root');

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
    Route::get('/user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::post('/user_addresses/store', 'UserAddressesController@store')->name('user_addresses.store');
    Route::post('/user_addresses/{user_address}/update', 'UserAddressesController@update')->name('user_addresses.update');
    Route::any('/user_addresses/{user_address}/destory', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    Route::get('/user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');

    // Route::resource('')
});


Auth::routes(['verify' => true]);

//商品展示
Route::prefix('products')->group(function () {
    Route::get('index', 'ProductsController@index')->name('products.index');
});
