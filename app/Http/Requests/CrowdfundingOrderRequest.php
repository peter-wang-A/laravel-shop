<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\CrowdfundingProduct;
use App\Models\ProductSku;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CrowdfundingOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sku_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        return $fail('该商品不存在');
                    }

                    if ($sku->product->type !== Product::TYPE_CROWDFUNDING) {
                        return $fail("该商品不支持众筹");
                    }

                    if (!$sku->product->on_sale) {
                        return $fail('该商品未上架');
                    }

                    if ($sku->product->crowdfunding->status !== CrowdfundingProduct::STATUS_FUNDING) {
                        return $fail('该商品众筹已经结束');
                    }
                    if ($sku->stock == 0) {
                        return $fail('该商品已售完');
                    }
                    if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                        return $fail('该商品库存不足');
                    }
                }
            ],
            'amount' =>['required','integer','min:1'],
            'address_id'=>[
                'required',
                Rule::exists('user_addresses','id')->where('user_id',$this->user()->id) //把 address_id 与 user_addresses 表的 id 作比较，如果存在，在于当前登录的 用户 id 做比较，成功则通过验证
            ]
        ];
    }
}
