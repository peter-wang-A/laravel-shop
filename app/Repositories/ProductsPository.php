<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Pagination\LengthAwarePaginator;
use App\SearchBuilders\ProductsSearchBuilder;

class  ProductsPository implements ProductsPositoryInterface
{

    public function productsData($request)
    {
        // 创建一个查询构造器
        // $builder =  Product::query()->where('on_sale', true);

        // //判断是否有传参数
        // if ($search = $request->input('search', '')) {
        //     /**
        //      * 如果有传参数则进行查询
        //      * 设置为模糊查询，包含字段的都查出来
        //      * orWhereHas 关联查询
        //      * 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
        //      */
        //     $like = "%" . $search . "%";

        //     $builder->where(function ($query) use ($like) {
        //         $query->where('title', 'like', $like)
        //             ->orWhere('discription', 'like', $like)
        //             ->orWhereHas('skus', function ($query) use ($like) {
        //                 $query->where('title', 'like', $like)
        //                     ->orWhere('description', 'like', $like);
        //             });
        //     });
        // }

        // $req_category_id = $request->input('category_id');

        // if ($req_category_id && $category = Category::find($req_category_id)) {
        //     if ($category->is_directory) {
        //         //把子目录商品全部查出来
        //         $builder->whereHas('category', function ($query) use ($category) {
        //             $query->where('path', 'like', $category->path . $category->id . '-');
        //         });
        //     } else {
        //         $builder->where('category_id', $category->id);
        //     }
        // }
        // /**
        //  * 是否有order 参数，如果有就给 $order 变量
        //  */
        // if ($order = $request->input('order', '')) {

        //     if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
        //         // dd($m[1]);
        //         if (in_array($m[1], ['sold_count', 'rating', 'price'])) {
        //             //根数传入的参数来查询
        //             $builder->orderBy($m[1], $m[2]);
        //         }
        //     }
        // }

        // $products = $builder->orderBy('id', 'desc')->paginate(16);

        // $data = [
        //     'products' => $products,
        //     'search' => $search,
        //     'order' => $order,
        //     'category' => $category ?? null,

        // ];
        // return $data;


        // 使用 ES 查询
        $page = $request->input('page', 1);
        $perPage = 16; // 每页显示多少条数据

        //  新建查询构造器对象，设置只搜索上架商品，设置分页
        $builder = (new ProductsSearchBuilder())->onSale()->paginate($perPage, $page);

        //类目筛选
        $categoryId = $request->input('category_id');
        if ($categoryId && $category = Category::find($categoryId)) {
            // 调用查询构造器的类目筛选
            $builder->category($category);
        }

        //关键字搜索
        if ($search = $request->input('search', '')) {
            //把空值除外
            $keywords = array_filter(explode(' ', $search));
            $builder->keywords($keywords);
        }

        // 调用查询构造器的分面搜索
        if ($search || isset($category)) {
            $builder->aggregatePropterties();
        }

        //属性筛选
        $propertyFilters  = [];
        // 从用户请求参数获取 filters
        if ($filterString = $request->input('filters')) {
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                list($name, $value) = $filter;
                // 将用户筛选的属性添加到数组中
                $propertyFilters[$name] = $value;
                $builder->propertyFilter($name, $value);
            }
        }

        //排序
        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    $builder->orderBy($m[1], $m[2]);
                }
            }

        }

        //执行搜索
        $result = app('es')->search($builder->getParams());

        //把聚合的属性传给前端
        $properties = [];
        //isset() 判断数组中的元素是否为 nu'l'l
        if (isset($result['aggregations'])) {
            //将聚合的值转成 collect 集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])->map(function ($bucket) {
                return [
                    'key' => $bucket['key'],
                    'values' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                ];
            })
                ->filter(function ($property) use ($propertyFilters) {
                    // 过滤掉只剩下一个值 或者 已经在筛选条件里的属性
                    return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]);
                });
        }


        // 通过 collect 函数将返回结果转为集合，并通过集合的 pluck 方法取到返回的商品 ID 数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // 通过 whereIn 方法从数据库中读取商品数据
        $products = Product::query()
            ->whereIn('id', $productIds)
            // orderByRaw 可以让我们用原生的 SQL 来给查询结果排序
            ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $productIds)))
            ->get();

        // 返回一个 LengthAwarePaginator 分页对象
        /**
         * 参数
         * 1、所有商品集合 2、商品总数 3每页显示多少个 4、当前第几页 5、是哪个页面的分页
         */
        $pager = new LengthAwarePaginator($products, $result['hits']['total']['value'], $perPage, $page, ['path' => route('products.index', false)]);

        $data = [
            'products' => $pager,
            'search' => $search,
            'order' => $order,
            'order' => $order,
            'category' => $category ?? null,
            'properties' => $properties,
            'propertyFilters' => $propertyFilters,
        ];
        return $data;
    }

    //收藏商品
    public function favor($product, $request)
    {
        //获取当前用户
        $user = $request->user();
        //判断用户是否收藏了此商品，如果收藏了什么都不做
        if ($user->favoriteProducts()->find($product->id)) {
            return;
        }
        //否则收藏此商品
        $user->favoriteProducts()->attach($product);
    }

    //取消收藏
    public function disfavor($product, $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product->id);
    }
}
