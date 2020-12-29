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

    //优惠券
    //优惠券列表
    $router->get('coupon_codes', 'CouponCodesController@index');

    //优惠券添加页面
    $router->get('coupon_codes/create', 'CouponCodesController@create');

    //添加优惠提交数据
    $router->post('coupon_codes', 'CouponCodesController@store');

    //修改优惠券
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');

    //删除优惠券
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');

    //无限极分类
    $router->get('categories', 'CategoriesController@index');
    $router->get('categories/create', 'CategoriesController@create');
    $router->get('categories/{id}/edit', 'CategoriesController@edit');
    $router->post('categories', 'CategoriesController@store');
    $router->put('categories/{id}', 'CategoriesController@update');
    $router->delete('categories/{id}', 'CategoriesController@destroy');
    $router->get('api/categories', 'CategoriesController@apiIndex');

    //众筹
    $router->get('crowdfunding_products', 'CrowdfundingProductsController@index');
    $router->get('crowdfunding_products/create', 'CrowdfundingProductsController@create');
    $router->post('crowdfunding_products', 'CrowdfundingProductsController@store');
    $router->get('crowdfunding_products/{id}/edit', 'CrowdfundingProductsController@edit');
    $router->put('crowdfunding_products/{id}', 'CrowdfundingProductsController@update');
});
