<?php

namespace App\Repositories;

use App\Models\Product;

interface  ProductsPositoryInterface
{
    public function productsData($request);
    public function favor($product,$request);
    public function disfavor($product,$request);
}
