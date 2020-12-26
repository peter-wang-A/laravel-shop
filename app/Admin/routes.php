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
    $router->put('products/{id}', 'ProductsController@update');

    //订单列表
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');

    //订单详情
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');

    //发货接口
    // $router->post('orders/{order}/ship', function () {
    //     return 'a';
    // })->name('admin.orders.ship');
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');

    //处理客户退款
    $router->post('orders/{order}/refund', 'OrdersController@ship')->name('admin.orders.handle_refund');
});
