<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSku;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'discription', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price'
    ];

    protected $casts = [
        'on_sale' => 'boolean'
    ];

    //定义模型关系,一个商品有多个 SKU
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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
