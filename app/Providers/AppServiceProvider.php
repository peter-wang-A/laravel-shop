<?php

namespace App\Providers;

use Monolog\Logger;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; //add fixed sql
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('alipay', function () {
            $config               = config('pay.alipay');
            $config['notify_url'] =ngrok_url('payment.alipay.notify');
            $config['return_url'] = route('payment.alipay.return');

            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
                $config['notify_url'] = route('payment.alipay.notify');
                $config['notify_url'] = 'http://requestbin.net/r/121pc5s1';
                $config['return_url'] = route('payment.alipay.return');
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            //调用 Yansongda\Pay 来创建一个支付宝对象
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat', function () {
            $config = config('pay.wechat');

            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }

            //调用 Yansongda\Pay 来创建一个支付宝对象
            return Pay::wechat($config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191); //add fixed sql

        // 当 Laravel 渲染 products.index 和 products.show 模板时，就会使用 CategoryTreeComposer 这个来注入类目树变量
        // 同时 Laravel 还支持通配符，例如 products.* 即代表当渲染 products 目录下的模板时都执行这个 ViewComposer
        \View::composer('products.*', \App\Http\ViewComposers\CategoryTreeComposer::class);
        // \View::composer(['products.index', 'products.show'], \App\Http\ViewComposers\CategoryTreeComposer::class);
    }
}
