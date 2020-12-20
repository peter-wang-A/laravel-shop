<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSku;

class ProductsSeender extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //创建30个商品
        $products = factory(Product::class, 30)->create();

        //创建3个 sku,每个 sku 的 'product_id' 字段设为当前循环的商品Id

        foreach ($products as $product) {

            $skus = factory(ProductSku::class, 3)->create([
                'product_id' => $product->id
            ]);

            //找出最低价格的 sku 把商品价格设置为改价格
            $product->update(['price' => $skus->min('price')]);
        }
    }
}
