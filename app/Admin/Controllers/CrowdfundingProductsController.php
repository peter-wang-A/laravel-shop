<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\CrowdfundingProduct;
use App\Models\ProductSku;
use App\Models\Category;
use App\Services\CategoryService;


class CrowdfundingProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '众筹商品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

     protected function customGrid(Grid $grid)
     {
         // 只展示 type 为众筹类型的商品
        $grid->model()->where('type', Product::TYPE_CROWDFUNDING);

        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');
        //展示众筹字段
        $grid->column('crouwdfunding.target_amount', '目标金额');
        $grid->column('crouwdfunding.total_amount', '目前金额');
        $grid->column('crouwdfunding.end_at', '结束时间');
        $grid->column('crouwdfunding.status', '状态')->display(function ($value) {
            return CrowdfundingProduct::$statusMap[$value];
        });

     }
     protected function customForm(Form $form){

       // 添加众筹相关字段
        $form->text('crouwdfunding.target_amount', '众筹目标金额')->rules('required|numeric|min:0.01');
        $form->datetime('crouwdfunding.end_at', '众筹截至时间')->rules('required|date');

     }
}
