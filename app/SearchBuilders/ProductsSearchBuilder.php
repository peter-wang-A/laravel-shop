<?php
// namespace App\SearchBuilders;
namespace App\SearchBuilders;

use App\Models\Category;
use Dotenv\Loader\Value;
use PhpParser\Builder\Property;

class ProductsSearchBuilder
{
    //初始化查询
    protected $params = [
        'index' => 'products',
        'type' => '_doc',
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => [],
                    'must' => []
                ],
            ],
        ],
    ];

    //添加分页查询
    public function paginate($size, $page)
    {
        $this->params['body']['from'] = ($page - 1) * $size;
        $this->params['body']['size'] =  $size;

        return $this;
    }

    //筛选上架商品
    public function onSale()
    {
        $this->params['body']['query']['bool']['filter'][] = ['term' => ['on_sale' => true]];

        return $this;
    }

    //按类目筛选商品
    public function category(Category $category)
    {
        if ($category->is_directory) {
            $this->params['body']['query']['bool']['filter'][] = [
                'prefix' => ['category_path' => $category->path . $category->id . '-']
            ];
        } else {
            $this->params['body']['query']['bool']['filter'][] = [
                'term' => ["category_id" => $category->id]
            ];
        }

        return $this;
    }

    //添加搜索词
    public function keywords($keywords)
    {
        $keywords = is_array($keywords) ? $keywords : [$keywords];
        foreach ($keywords as $keyword) {
            $this->params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $keyword,
                    'fields' => [
                        'title^3',
                        'long_title^2',
                        'category^2',
                        'description',
                        'skus_title',
                        'skus_description',
                        'properties_value',
                    ]
                ]
            ];
        }
        return $this;
    }

    //聚合属性
    public function aggregatePropterties()
    {
        $this->params['body']['aggs'] = [
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
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this;
    }

    //添加一个商品属性筛选条件
    public function propertyFilter($name, $value, $type)
    {
        $this->paramss['body']['query']['bool'][$type][] = [
            'nested' => [
                'path' => 'properties',
                'query' => [
                    'term' => ['properties.search_value' => $name . ':' . $value]
                ]
            ]
        ];

        return $this;
    }

    // 设置 minimum_should_match 参数
    public function miniShouldMatch($count)
    {
        $this->params['body']['query']['bool']['minimum_should_match'] = (int)$count;

        return $this;
    }

    //添加排序
    public function orderBy($filed, $direction)
    {
        //panduans是否有排序
        if (!isset($this->params['body']['sort'])) {
            $this->params['body']['sort'] = [];
        }

        $this->params['body']['sort'] = [$filed => $direction];
    }

    //返回构造好的查询参数
    public function getParams()
    {
        return $this->params;
    }
}
