<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Repositories\ProductsPositoryInterface;
use App\Repositories\ProductsPository;
use App\Exceptions\InvalidRequestException;
use Auth;
use App\Models\OrderItem;

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
        // 判断商品是否已经上架，如果没有上架则抛出异常。
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        //取消收藏
        $favored = false;

        if ($user = $request->user()) {
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        return view('products.show', ['product' => $product, 'favored' => $favored, 'reviews' => $reviews]);
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

    public function favoritesList(Request $request)
    {
        $favorites = Auth::user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['favorites' => $favorites]);
    }
}
