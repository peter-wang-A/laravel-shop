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
//错误
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::get('/', 'PagesController@root')->name('root');



Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');

//收货地址
Route::group(['middleware' => ['auth', 'verified']], function () {
    //分期付款
    Route::any('payment/{order}/installment', 'PaymentController@payByInstallment')->name('payment.installment');
    Route::get('installments', 'InstallmentController@index')->name('installments.index');

    //购物车
    Route::any('/cart', 'CartController@add')->name('cart.add');
    Route::get('/cart/index', 'CartController@index')->name('cart.index');
    Route::any('/cart/{sku}', 'CartController@remove')->name('cart.remove');

    //订单
    Route::any('orders', 'OrderController@store')->name('orders.store');
    Route::get('orders/index', 'OrderController@index')->name('orders.index');
    Route::get('orders/{order}', 'OrderController@show')->name('orders.show');


    //支付
    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');

    Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');

    //确认收货
    Route::post('orders/{order}/received', 'OrderController@received')->name('orders.received');

    //退款
    //客户端提交退款理由
    Route::any('/order/{order}/refund', 'OrderController@handleRefund')->name('orders.refund');

    //优惠券
    //查看优惠券
    Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');

    //收货地址
    Route::get('/user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
    Route::get('/user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::post('/user_addresses/store', 'UserAddressesController@store')->name('user_addresses.store');
    Route::post('/user_addresses/{user_address}/update', 'UserAddressesController@update')->name('user_addresses.update');
    Route::any('/user_addresses/{user_address}/destory', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    Route::get('/user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');


    //评论
    Route::get('/orders/{order}/review', 'OrderController@review')->name('orders.review.show');
    Route::post('/orders/{order}/review', 'OrderController@sendReview')->name('orders.review.store');

    //众筹
    Route::any('crowdfunding_orders', 'OrderController@crowdfunding')->name('crowdfunding_orders.store');

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
