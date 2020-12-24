<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Auth;



class OrderController extends Controller
{
    protected $ordSer;
    public function __construct(OrderService $ordSer)
    {
        $this->ordSer = $ordSer;
    }
    //创建订单
    public function store(OrderRequest $request)
    {
        $this->ordSer->store($request);
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
    public function show(Order $order)
    {

        // $this->authorize('own', $order);

        return view('orders.show', ['order' => $order->load(['items.product', 'items.productSku'])]);
    }
}
