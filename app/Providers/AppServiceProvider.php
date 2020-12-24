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
            $config = config('pay.alipay');

            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
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
    }
}
