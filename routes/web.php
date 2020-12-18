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
    Route::get('/user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    // Route::resource('')
});



Auth::routes(['verify' => true]);
