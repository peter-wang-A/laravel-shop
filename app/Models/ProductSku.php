<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductSku extends Model
{
    protected $fillable = [
        'title', 'description', 'price', 'stock'
    ];

    public function product()
    {
        $this->belongsTo(Product::class);
    }
}
