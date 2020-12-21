<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Repositories\ProductsPositoryInterface;
use App\Repositories\ProductsPository;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{

    protected $repo;

    public function __construct(ProductsPositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    // 商品首页
    public function index(Request $request)
    {
        $products =   $this->repo->productsData($request);
        return view('products.index', [
            'products' => $products['products'],
            'filters' => [
                'search' => $products['search'],
                'order' => $products['order']
            ]
        ]);
    }

    //商品详情页面
    public function show(Product $product, Request $request)
    {
        // dd($product->skus);
        // 判断商品是否已经上架，如果没有上架则抛出异常。
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        //取消收藏
        $favored = false;

        if ($user = $request->user()) {
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        return view('products.show', ['product' => $product, 'favored' => $favored]);
    }

    //收藏
    public function favor(Product $product, Request $request)
    {
        $this->repo->favor($product, $request);
        // dd($responce);
        return response()->json([
            'msg' => '收藏成功', 'code' => 200
        ]);
    }

    //取消收藏
    public function disfavor(Product $product, Request $request)
    {
        $this->repo->disfavor($product, $request);
        return response()->json([
            'msg' => '取消成功', 'code' => 200
        ]);
    }
}
