<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSku;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Product extends Model
{
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';

    public static $typeMap = [
        self::TYPE_NORMAL => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品'
    ];

    protected $fillable = [
        'title', 'discription', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price', 'type', 'long_title'
    ];

    //众筹商品模型关系，一对一
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    protected $casts = [
        'on_sale' => 'boolean'
    ];

    //定义模型关系,一个商品有多个 SKU
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //定义一个查询构造器，把相同的属性名的属性值单独取出
    public function getGroupPropertiesAttribute()
    {
        return $this->properties
            ->groupBy('name')
            ->map(function ($properties) {
                return $properties->pluck('value')->all();
            });
    }

    //查看器，把相对路径改为绝对路径
    public function getImageUrlAttribute()
    {

        if (Str::startsWith($this->attributes['image'], ['http', 'https'])) {
            return $this->attributes['image'];
        }


        return \Storage::disk('public')->url($this->attributes['image']);
    }

    //把数据写入 es
    public function toESArray()
    {
        $arr = Arr::only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        // 如果商品有类目，则 category 字段为类目名数组，否则为空字符串
        $arr['category'] = $this->category ? explode('-', $this->category->item_name) : '';

        // 类目的 PATH 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';

        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->discription);

        //取出需要的 sku 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return Arr::only($sku->toArray(), [
                'title', 'description', 'price'
            ]);
        });

        //取出需要的属性属性
        $arr['properties'] = $this->properties->map(function (ProductProperty $pro) {
            return array_merge(Arr::only($pro->toArray(), ['name', 'value']), ['search_value' => $pro->name . ':' . $pro->value]);
        });

        return $arr;
    }
}
