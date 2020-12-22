<?php

use App\Http\Controllers\PagesController;
use Illuminate\Auth\Events\Verified;
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


//收货地址
Route::group(['middleware' => ['auth', 'verified']], function () {

    //购物车
    Route::any('/cart', 'CartController@add')->name('cart.add');
    Route::get('/cart/index', 'CartController@index')->name('cart.index');
    Route::any('/cart/{sku}', 'CartController@remove')->name('cart.remove');


    //收货地址
    Route::get('/user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
    Route::get('/user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::post('/user_addresses/store', 'UserAddressesController@store')->name('user_addresses.store');
    Route::post('/user_addresses/{user_address}/update', 'UserAddressesController@update')->name('user_addresses.update');
    Route::any('/user_addresses/{user_address}/destory', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    Route::get('/user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');

    // Route::resource('')
});
//邮箱验证
Auth::routes(['verify' => true]);

//商品展示
Route::prefix('products')->group(function () {
    Route::get('index', 'ProductsController@index')->name('products.index');
    Route::get('/favoritesList', 'ProductsController@favoritesList')->name('products.favoritesList');
    Route::get('/{product}', 'ProductsController@show')->name('products.show');
    Route::post('/{product}/favor', 'ProductsController@favor')->name('products.favor');
    Route::any('/{product}/disfavor', 'ProductsController@disfavor')->name('products.disfavor');
});

