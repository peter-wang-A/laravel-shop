<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CouponCodesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '优惠券管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode());

        $grid->model()->orderBy('created_at', 'desc');

        $grid->id('ID')->sortable();
        $grid->name('名称');
        $grid->code('优惠码');
        $grid->description('描述');
        $grid->column('usage', '用量')->display(function ($value) {
            return "{$this->used}/{$this->total}";
        });
        // $grid->type('类型')->display(function ($value) {
        //     return CouponCode::$typeMap[$value];
        // });

        // 根据不同的折扣类型用对应的方式来展示
        // $grid->value('折扣')->display(function ($value) {
        //     return $this->type === CouponCode::TYPE_FIXED ? '￥' . $value : $value . '%';
        // });

        // $grid->min_amount('最低金额');
        // $grid->total('总量');
        // $grid->used('已用');
        $grid->enabled('是否启用')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->created_at('创建时间');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CouponCode::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('type', __('Type'));
        $show->field('value', __('Value'));
        $show->field('total', __('Total'));
        $show->field('used', __('Used'));
        $show->field('min_amount', __('Min amount'));
        $show->field('not_before', __('Not before'));
        $show->field('not_after', __('Not after'));
        $show->field('enabled', __('Enabled'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CouponCode());

        $form->display('id', 'ID');
        $form->text('name', '名称')->rules('required');
        $form->text('code', '优惠码')->rules(function ($form) {
            //如果有优惠券 ID 代表是编辑
            if ($id = $form->model()->id) {
                return 'nullable|unique:coupon_codes,code,' . $id . ',id';
            } else {
                return 'nullable|unique:coupon_codes';
            }
        });
        $form->radio('type', '类型')->options(CouponCode::$typeMap)->rules('required')->default(CouponCode::TYPE_FIXED);
        $form->text('value', '折扣')->rules(function ($form) {
            //如果选择了折扣类型，那么折扣范围最好是 1~99
            if (request()->input('type') === CouponCode::TYPE_PERCENT) {
                return 'required|numeric|between:1,99';
            } else {
                //选择的是固定金额则最小是 0.01 元
                return 'required|numeric|min:0.01';
            }
        });
        $form->text('total', '总量')->rules('required|numeric|min:0');
        $form->text('min_amount', '最低金额')->rules('required|numeric|min:0');
        $form->datetime('not_before', '开始时间');
        $form->datetime('not_after', '结束时间');
        $form->radio('enabled', '启用')->options(['1' => '是', '0' => '否']);

        // 定义事件回调，当模型即将保存时会触发这个回调
        $form->saving(function (Form $form) {
            //如果没有优惠券码生成优惠券码，并保存到数据库
            if (!$form->code) {
                $form->code = CouponCode::findAvailableCode();
            }
        });

        return $form;
    }
}
