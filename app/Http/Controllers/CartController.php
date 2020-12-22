<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{

    public function add(AddCartRequest $request)
    {
        $user = $request->user();
        $skuId  = $request->input('sku_id');
        $amount  = $request->input('amount');

        //判断购物车是否有商品
        if ($cart = $user->cartItems->where('product_sku_id', $skuId)->first()) {

            $cart->update([
                'amoubt' => $amount + $cart->amount
            ]);
        }

        //没有则新增一条记录
        $cart = new CartItem(['amount' => $amount]);
        // dd($cart);
        $cart->user()->associate($user);
        $cart->productSku()->associate($skuId);
        $cart->save();

        return response()->json([
            'msg' => '加入成功',
            'code' => 200
        ]);
    }

    public function index(Request $request)
    {
        $cartItems  = $request->user()->cartItems()->with('productSku.product')->get();

        return view('cart.index', ['cartItems' => $cartItems]);
    }

    public function remove(ProductSku $sku, Request $request)
    {
        // dd($sku->id);
        $request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();

        return;
    }
}
