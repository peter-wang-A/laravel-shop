<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSku;
use Illuminate\Support\Str;

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
        'rating', 'sold_count', 'review_count', 'price', 'type'
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
}
