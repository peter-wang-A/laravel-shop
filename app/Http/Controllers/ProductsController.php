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

        return view('products.show', ['product' => $product]);
    }
}
