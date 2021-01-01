<?php
function test_helper()
{
    return 'OK';
}

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function ngrok_url($routeName, $parameters = [])
{
    // 开发环境，并且配置了 NGROK_URL
    if (app()->environment('local') && $url = config('app.ngrok_url')) {
        // route() 函数第三个参数代表是否绝对路径
        return $url . route($routeName, $parameters, false);
    }

    return route($routeName, $parameters);
}


//生成
function findAvailableNo($query)
{
    // 分期流水号前缀
    $prefix = date('YmdHis');
    for ($i = 0; $i < 10; $i++) {
        // 随机生成 6 位的数字
        $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // 判断是否已经存在
        if (!$query->where('no', $no)->exists()) {
            return $no;
        }
    }
    \Log::warning('find installment no failed');

    return false;
}


function big_number($number, $scale = 2)
{
    return new \Moontoast\Math\BigNumber($number, $scale);
}
