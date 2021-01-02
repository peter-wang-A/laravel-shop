<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    //分期付款列表
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', ['installments' => $installments]);
    }

    //分期付款详细页面
    public function show(Installment $installment)
    {
        $this->authorize('own', $installment);
        //取出所有还款计划，s
        $items = $installment->items()->orderBy('sequence', 'desc')->get();

        // dd($installment->order_id);
        return view('installments.show', [
            'installment' => $installment,
            'items' => $items,
            'nextItem' => $items->where('paid_at', null)->first(), //下一个未完成的还款计划
        ]);
    }
}
