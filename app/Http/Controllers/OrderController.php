<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Auth;
use App\Models\UserAddress;
use League\Flysystem\InvalidRootException;

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

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
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
}
