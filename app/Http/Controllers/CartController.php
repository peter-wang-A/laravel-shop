<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartSer;
    public function __construct(CartService $cartSer)
    {
        $this->cartSer = $cartSer;
    }

    //购物车页面
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get();
        $cartItems  = $request->user()->cartItems()->with('productSku.product')->get();

        return view('cart.index', ['cartItems' => $cartItems, 'addresses' => $addresses]);
    }

    public function add(AddCartRequest $request)
    {
        $this->cartSer->add($request);
        return response()->json([
            'msg' => '加入成功',
            'code' => 200
        ]);
    }

    public function remove(ProductSku $sku, Request $request)
    {
        $this->cartSer->remove($sku->id);
        return;
    }
}
