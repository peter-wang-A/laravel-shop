<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Pagination\LengthAwarePaginator;

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

        //构建查询
        $params = [
            'index' => 'products',
            'body' => [
                'from' => ($page - 1) * $perPage, // 通过当前页数与每页数量计算偏移值
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]]
                        ],
                    ],
                ],
            ],
        ];

        //是否有提交 Order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            //是否以字符串 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'rating', 'sold_count'])) {
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
        }

        //类目筛选
        $categoryId = $request->input('category_id');
        if ($categoryId && $category = Category::find($categoryId)) {
            if ($category->is_directory) {
                // 如果是一个父类目，则使用 category_path 来筛选
                $params['body']['query']['bool']['filter'][] = [
                    'prefix' => ['category_path' => $category->path . $category->id . '-']
                ];
            } else {
                // 否则直接通过 category_id 筛选
                $params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category->id]];
            }
        }

        //关键字搜索
        if ($search = $request->input('search', '')) {

            $keywords = array_filter(explode(' ', $search));
            $params['body']['query']['bool']['must'] = [];

            foreach ($keywords as $keyword) {
                $params['body']['query']['bool']['must'] = [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^2',
                            'long_title^2',
                            'description_title',
                            'properties_value',
                            'skus_title',
                            'skus_description',
                            'category^2',
                        ]
                    ]
                ];
            }
        }


        if ($search || isset($category)) {
            $params['body']['aggs'] = [
                'properties' => [
                    'nested' => [
                        'path' => 'properties',
                    ],
                    'aggs' => [
                        'properties' => [
                            'terms' => [
                                'field' => 'properties.name'
                            ],
                            'aggs' => [
                                'value' => [
                                    'terms' => [
                                        'field' => 'properties.value'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }

        //执行搜索
        $result = app('es')->search($params);

        //按条件筛选
        // $propertyFilters = [];
        // if ($filterString = $request->input('filters')) {
        //     //将获取到的字符串，用 | 切割成数组
        //     $filterArray = explode('|', $filterString);
        //     // 将字符串用符号 : 拆分成两部分并且分别赋值给 $name 和 $value 两个变量
        //     foreach ($filterArray as $filter) {
        //         list($name, $value) = explode(':', $filter);
        //         // 将用户筛选的属性添加到数组中
        //         $propertyFilters[$name] = $value;
        //         // 添加到 filter 类型中
        //         $params['body']['query']['bool']['filter'][] = [
        //             // 由于我们要筛选的是 nested 类型下的属性，因此需要用 nested 查询
        //             'nested' => [
        //                 // 指明 nested 字段
        //                 'path' => 'properties',
        //                 'query' => [
        //                     ['term' => ['properties.name' => $name]],
        //                     ['term' => ['properties.value' => $value]],
        //                 ]
        //             ]
        //         ];

        //     }
        //     // dd($params);
        // }

        $propertyFilters  = [];
             // 从用户请求参数获取 filters
             if ($filterString = $request->input('filters')) {
                // 将获取到的字符串用符号 | 拆分成数组
                $filterArray = explode('|', $filterString);
                foreach ($filterArray as $filter) {
                    // 将字符串用符号 : 拆分成两部分并且分别赋值给 $name 和 $value 两个变量
                    list($name, $value) = explode(':', $filter);

                      // 将用户筛选的属性添加到数组中
                        $propertyFilters[$name] = $value;

                    // 添加到 filter 类型中
                    $params['body']['query']['bool']['filter'][] = [
                        // 由于我们要筛选的是 nested 类型下的属性，因此需要用 nested 查询
                        'nested' => [
                            // 指明 nested 字段
                            'path'  => 'properties',
                            'query' => [
                                ['term' => ['properties.name' => $name]],
                                ['term' => ['properties_value' => $value]],
                            ],
                        ],
                    ];
                }
            }

        // dd($params);
        //把聚合的属性传给前端
        $properties = [];
        //isset() 判断数组中的元素是否为 nu'l'l
        if (isset($result['aggregations'])) {
            //将聚合的值转成 collect 集合
            $properties = collect($result['aggregations']['properties']
            ['properties']['buckets'])->map(function ($bucket) {
                return [
                    'key' => $bucket['key'],
                    'values' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                ];
            })
            ->filter(function ($property) use ($propertyFilters) {
                // 过滤掉只剩下一个值 或者 已经在筛选条件里的属性
                return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]) ;
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
            'properties' => $properties
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
