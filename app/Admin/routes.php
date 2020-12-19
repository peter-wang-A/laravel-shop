<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    //用户后台
    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index');

    //商品后台
    $router->get('products', 'ProductsController@index');
    $router->get('products/create', 'ProductsController@create');
    $router->post('products', 'ProductsController@store');

    //修改商品
    $router->get('products/{id}/edit', 'ProductsController@edit');

    // $router->get('products/{id}/edit', 'ProductsController@edit');
$router->put('products/{id}', 'ProductsController@update');

});
