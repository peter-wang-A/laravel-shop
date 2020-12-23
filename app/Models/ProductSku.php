<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description', 'price', 'stock', 'sku_category'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //减库存
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于 0 ');
        }

        return $this->where('id', $this->id)->where('stock', '>=', $amount)->descrement('stock', $amount);
    }


    //加库存
    public function addStock($amount){
        if($amount<0){
            throw new InternalException('加库存不可小于 0 ');

        }

        return $this->increment('stock', $amount);
    }
}
