<?php

namespace App\Repositories;

use App\Models\Product;

class  ProductsPository implements ProductsPositoryInterface
{

    public function productsData($request)
    {
        //创建一个查询构造器
        $builder =  Product::query()->where('on_sale', true);

        //判断是否有传参数
        if ($search = $request->input('search', '')) {
            /**
             * 如果有传参数则进行查询
             * 设置为模糊查询，包含字段的都查出来
             * orWhereHas 关联查询
             * 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
             */
            $like = "%" . $search . "%";

            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('discription', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }
        /**
         * 是否有order 参数，如果有就给 $order 变量
         */
        if ($order = $request->input('order', '')) {

            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // dd($m[1]);
                if (in_array($m[1], ['sold_count', 'rating', 'price'])) {
                    //根数传入的参数来查询
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);

        $data = ['products' => $products, 'search' => $search, 'order' => $order];
        return $data;
    }
}
