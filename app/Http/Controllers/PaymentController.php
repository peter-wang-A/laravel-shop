<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use League\Flysystem\InvalidRootException;
use Carbon\Carbon;
use App\Events\OrderPaid;
use App\Models\Installment;
use App\Models\InstallmentItem;
use Illuminate\Validation\Rule;


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

    //分期付款
    public function payByInstallment(Order $order, Request $request)
    {
        //判断订单是否属于当前用户
        $this->authorize('own', $order);

        //订单已支付或已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        //订单低于分期金额
        if ($order->total_amount < config('app.min_installment_amount')) {
            throw new InvalidRequestException('订单金额低于最低分期金额');
        }

        // 校验用户提交的还款月数，数值必须是我们配置好费率的期数
        $this->validate($request, [
            'count' => [
                'required',
                Rule::in(array_keys(config('app.installment_fee_rate')))
            ]
        ]);

        // 删除同一笔商品订单发起过其他的状态是未支付的分期付款，避免同一笔商品订单有多个分期付款
        Installment::query()
            ->where('order_id', $order->id)
            ->where('status', Installment::STATUS_PENDING)
            ->delete();

        //接收分期福付款期数
        $count = $request->input('count');

        //创建一个新的分期付款对象
        $installment = new Installment([
            //订单的本金作为分期付款对象
            'total_amount' => $order->total_amount,
            //分期期数
            'count' => $count,
            //从配置中读取相应的期数费率
            'fee_rate' => config('app.installment_fee_rate')[$count],
            //从配置中读取逾期的费率
            'fine_rate' => config('app.installment_fine_rate')
        ]);
        //与订单和用户关联
        $installment->user()->associate($request->user());
        $installment->order()->associate($order);
        $installment->save();


        // dd($installment->created_at);

        //第一期的还款截至日期为明天凌晨 0 点
        $dueDate = Carbon::tomorrow();
        //计算每一期的本金
        $base = big_number($installment->total_amount)->divide($installment->count)->getValue();
        //计算每一期的手续费
        $fee = big_number($base)->multiply($installment->fee_rate)->divide(100)->getValue();

        //根据用户选择的还款期数，创建敌营数量的还款计划
        for ($i = 0; $i < $count; $i++) {
            //最后一期的金额等于总金额减去前面几期的本金
            if ($i === $count - 1) {
                $base = big_number($order->total_amount)->subtract(big_number($base)->multiply($count - 1));
            }
            $installment->items()->create([
                'sequence' => $i,
                'base' => $base,
                'fee' => $fee,
                'due_date' => $dueDate,
            ]);

            //还款日期加 30 天
            $dueDate = $dueDate->copy()->addDays(30);
        }

        return $installment;
    }
}
