<?php

namespace App\Http\Controllers;

use App\App\Moldes\CouponCode;
use App\Models\Order;
use Carbon\Carbon;
use Encore\Admin\Form\Field\Number;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;

class CouponCodesController extends Controller
{
    public function show($code)
    {
        //判断优惠券是否存在
        if (!$record = CouponCode::where('code', $code)->first()) {
            abort(404);
        }

        //如有优惠券没有启用则等于优惠券不存在
        if (!$record->enabled) {
            abort(404);
        }

        // dd($record->total);
        if ((int)$record->value - $record->used <= 0) {
            return response()->json(['msg' => '该优惠券已被兑完'], 403);
        }


        if ($record->not_before && $record->not_before->gt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券现在还不能使用'], 403);
        }

        if ($record->not_after && $record->not_after->lt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券已过期'], 403);
        }

        return $record;
    }
}
