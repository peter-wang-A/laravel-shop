<?php

namespace App\Http\Controllers;

use App\App\Moldes\CouponCode;
use App\Events\OrderReviewed;
use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InternalException;
use App\Http\Requests\AppplyRefundRequest;
use App\Http\Requests\CrowdfundingOrderRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Auth;
use App\Models\UserAddress;
use League\Flysystem\InvalidRootException;
use Carbon\Carbon;

class OrderController extends Controller
{
    // protected $ordSer;
    // public function __construct(OrderService $ordSer)
    // {
    //     $this->ordSer = $ordSer;
    // }
    //创建订单
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        $coupon  = null;

        //如果用户提交了优惠券信息
        if ($code = $request->input('coupon_code')) {
            //查出优惠码
            $coupon = CouponCode::where('code', $code)->first();

            if (!$coupon) {
                throw new CouponCodeUnavailableException('优惠券不存在');
            }
        }

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'), $coupon);
    }
    //订单页面
    public function index(Request $request)
    {
        $order = Order::query()
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->paginate();

        return view('orders.index', ['orders' => $order]);
    }

    //订单详情页面，
    public function show(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    //确认收货
    public function received(Order $order)
    {
        //判断商品是否已经发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRootException('订单状态错误');
        }

        //更新为发货
        $order->update([
            'ship_status' => Order::SHIP_STATUS_RECEIVED
        ]);

        return $order;
    }

    //评论页面
    public function review(Order $order)
    {
        //检验权限
        $this->authorize('own', $order);

        if (!$order->paid_at) {
            throw new InvalidRootException('订单未支付不可评论');
        }

        return view('orders.review', ['order' => $order->load('items.product', 'items.productSku')]);
    }

    //评论
    public function sendReview(Order $order, SendReviewRequest $request)
    // public function sendReview(Order $order, Request $request)
    {
        //检验权限
        $this->authorize('own', $order);

        if (!$order->paid_at) {
            throw new InvalidRootException('该订单未支付不可评论');
        }

        //判断是否已经评论
        if ($order->reviewed) {
            throw new InvalidRootException('该订单已经评论，不可重复添加');
        }

        //获取评论数据
        $reviews = $request->input('reviews');

        //开启事务
        \DB::transaction(function () use ($reviews, $order) {
            //遍历提交的数据
            foreach ($reviews as $review) {
                //找到orderItem
                $orderItem = $order->items()->find($review['id']);

                //保存评论数据
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now()
                ]);
            }
            //将订单标记为已经评论
            $order->update([
                'reviewed' => true
            ]);
        });
        event(new OrderReviewed($order));

        return redirect()->back();
    }

    //退款
    public function applyRefund(Order $order, AppplyRefundRequest $request)
    {
        //判断订单是否支付
        if (!$order->paid_at) {
            throw new InternalException('该用户未付款，不可退款');
        }

        //判断退款状态是否为未退款
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InternalException('该订单已申请过退款，请勿重复退款');
        }


        //将用输入的退款理由放到订单的 extra 字段中
        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->input(['reason']);

        //将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra' => $extra
        ]);

        return response()->json([
            'code' => 200,
            'msg' => $order
        ]);
    }

    //众筹商品下单请求
    public function crowdfunding(CrowdfundingOrderRequest $request, OrderService $orderService)
    {
        // dd($request->input('sku_id'));
        //获取当前用户
        $user = $request->user();
        //获取 Sku
        $sku = ProductSku::find($request->input('sku_id'));
        //获取地址
        $address = UserAddress::find($request->input('address_id'));
        //获取数量
        $amount = $request->input('amount');

        return $orderService->crowdfunding($user, $address, $sku, $amount);
    }
}
