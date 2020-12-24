<?php

namespace App\Services;

use App\Models\CartItem;
use Auth;

class CartService
{
    public function add($request)
    {
        $user = $request->user();
        $skuId  = $request->input('sku_id');
        $amount  = $request->input('amount');

        //判断购物车是否有该商品
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
    }

    public function remove($skuIds)
    {
        // 可以传单个 ID，也可以传 ID 数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->where('product_sku_id', $skuIds)->delete();
    }
}
