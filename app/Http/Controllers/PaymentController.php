<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use League\Flysystem\InvalidRootException;
use Carbon\Carbon;
use App\Events\OrderPaid;


class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    {
        //判断订单是否是当用户
        $this->authorize('own', $order);

        //订单已支付获知已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRootException('订单状态不正确');
        }

        //调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, //订单编号，要保证唯一
            'total_amount' => $order->total_amount, //订单金额，单位元，支持小数后两位。
            'subject' => '支付 Laravel Shop 的订单' . $order->no, //订单标题
        ]);
    }

    // 前端回调页面
    public function alipayReturn()
    {
        // 校验提交的参数是否合法
        $data = app('alipay')->verify();
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }
        return view('pages.success', ['msg' => '付款成功']);
    }

    // 服务器端回调
    public function alipayNotify()
    {
        //校验输入参数
        $data = app('alipay')->verify();

        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        // 所有交易状态：https://docs.open.alipay.com/59/103672
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();

        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }

        //如果这笔订单状态已经是支付
        if ($order->pai_at) {
            //返回数据给支付宝
            return app('alipay')->success();
        }

        $order->update([
            'paid_at' => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no' => $data->trade_no, //支付宝订单号
        ]);


        //触发事件
        $this->afterPaid($order);

        return app('alipay')->success();
    }
}
