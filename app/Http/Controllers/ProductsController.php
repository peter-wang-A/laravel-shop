<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Repositories\ProductsPositoryInterface;
use App\Repositories\ProductsPository;
use App\Exceptions\InvalidRequestException;
use Auth;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Services\CartService;
use App\Services\CategoryService;
use App\SearchBuilders\ProductsSearchBuilder;

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
            ],
            'category' => $products['category'],
            'properties' => $products['properties'],
            'propertyFilters' => $products['propertyFilters'],
        ]);
    }

    //商品详情页面
    public function show(Product $product, Request $request)
    {
        // 商品推荐
        // 创建一个查询构造器，只搜索上架的商品，取搜索结果的前 4 个商品
        $builder = (new ProductsSearchBuilder())->onSale()->paginate(4, 1);

        //遍历当前商品属性
        foreach ($product->properties as $property) {

            $builder->propertyFilter($property->name, $property->value, 'should');
        }
        //至少匹配一半的属性，ceil 向上舍入证数
        $builder->miniShouldMatch(ceil(count($product->properties) / 2));

        $params = $builder->getParams();

        // 同时将当前商品的 ID 排除
        $params['body']['query']['bool']['must_not'] = [['term' => ['_id' => $product->id]]];

        //搜索数据
        $result = app('es')->search($params);

        //取出返回数据
        $similarProductIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // dd(join(',',$similarProductIds));
        $similarProducts = Product::query()
            ->where('id', $similarProductIds)
            //使用原生方法差排序
            ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $similarProductIds)))
            ->get();


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
        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews,
            'similar' => $similarProducts,
        ]);
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
