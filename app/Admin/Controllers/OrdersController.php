<?php

namespace App\Admin\Controllers;

use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
// use GuzzleHttp\Psr7\Request;
use App\Models\User;
use League\Flysystem\InvalidRootException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Models\CrowdfundingProduct;

class OrdersController extends AdminController
{
    use ValidatesRequests;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        // 只展示已支付的订单，并且默认按支付时间倒序排序
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');

        $grid->no('订单流水号');
        //展示关联关系的字段，使用 column 方法
        $grid->column('user.name', '卖家');

        $grid->total_amount('总金额')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->ship_status('物流')->display(function ($value) {
            return Order::$shipStatusMap[$value];
        });

        $grid->refund_status('退款状态')->display(function ($value) {
            return Order::$refundStatusMap[$value];
        });

        // 禁用创建按钮，后台不需要创建订单
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            // 禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });

        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    public function show($id, Content $content)
    {
        return $content
            ->header('查看订单')
            // body 方法可以接受 Laravel 的视图作为参数
            ->body(view('admin.orders.show', ['order' => Order::find($id)]));
        // ->body(view('admin.orders.show', ['order' => Order::find($id)]));
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */

    //发货接口
    // public function ship(User $order, Request $request)
    public function ship(Order $order, Request $request)
    {
        // return 'bb';
        //判断是否支付
        if (!$order->paid_at) {
            throw new InvalidRootException('该订单未支付');
        }

        //判断该订单状态是否为未发货
        if ($order->ship_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRootException('该订单已发货');
        }

        //如果该订单还是众筹订单，但未众筹成功不能发货
        if ($order->type === Order::TYPE_CROWDFUNDING && $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS) {
            throw new InvalidRequestException('众筹订单只能在众筹成功之后发货');
        }


        // Laravel 5.5 之后 validate 方法可以返回校验过的值
        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ], [], [
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);

        //把数据库改为已发货
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            'ship_data' => $data,
        ]);

        return redirect()->back();
    }

    //退款处理接口
    // public function handleRefund(Order $order, HandleRefundRequest $request)
    // {
    //     //判断订单状态是否正常
    //     if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
    //         throw new InternalException('订单状态不正确');
    //     };

    //     if ($request->input('agree')) {
    //         //同意退款
    //     } else {
    //         //将拒绝退款的理由写到订单 extra 字段中
    //         $extra = $order->extra ?: [];
    //         $extra['refund_disagree_reson'] = $order->input['reson'];
    //         $order->update([
    //             'extra' => $extra,
    //             'refund_status' => Order::REFUND_STATUS_PENDING
    //         ]);
    //     }
    //     return $order;
    // }

    public function handleRefund(Order $order, HandleRefundRequest $request)
    {
        // 判断订单状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InternalException('订单状态不正确');
        }
        // 是否同意退款
        if ($request->input('agree')) {
            // 同意退款的逻辑这里先留空
            // todo
        } else {
            // 将拒绝退款理由放到订单的 extra 字段中
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            // 将订单的退款状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }

        return $order;
    }

    // protected function detail($id)
    // {
    //     $show = new Show(Order::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('no', __('No'));
    //     $show->field('user_id', __('User id'));
    //     $show->field('address', __('Address'));
    //     $show->field('total_amount', __('Total amount'));
    //     $show->field('remark', __('Remark'));
    //     $show->field('paid_at', __('Paid at'));
    //     $show->field('payment_method', __('Payment method'));
    //     $show->field('payment_no', __('Payment no'));
    //     $show->field('refund_status', __('Refund status'));
    //     $show->field('refund_no', __('Refund no'));
    //     $show->field('closed', __('Closed'));
    //     $show->field('reviewed', __('Reviewed'));
    //     $show->field('ship_status', __('Ship status'));
    //     $show->field('ship_data', __('Ship data'));
    //     $show->field('extra', __('Extra'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    // protected function form()
    // {
    //     $form = new Form(new Order());

    //     $form->text('no', __('No'));
    //     $form->number('user_id', __('User id'));
    //     $form->textarea('address', __('Address'));
    //     $form->decimal('total_amount', __('Total amount'));
    //     $form->textarea('remark', __('Remark'));
    //     $form->datetime('paid_at', __('Paid at'))->default(date('Y-m-d H:i:s'));
    //     $form->text('payment_method', __('Payment method'));
    //     $form->text('payment_no', __('Payment no'));
    //     $form->text('refund_status', __('Refund status'))->default('pending');
    //     $form->text('refund_no', __('Refund no'));
    //     $form->switch('closed', __('Closed'));
    //     $form->switch('reviewed', __('Reviewed'));
    //     $form->text('ship_status', __('Ship status'))->default('pending');
    //     $form->textarea('ship_data', __('Ship data'));
    //     $form->textarea('extra', __('Extra'));

    //     return $form;
    // }
}
