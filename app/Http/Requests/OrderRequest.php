<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            // 判断用户提交的地址 ID 是否存在于数据库并且属于当前用户
            // 后面这个条件非常重要，否则恶意用户可以用不同的地址 ID 不断提交订单来遍历出平台所有用户的收货地址
            'address_id' => [
                'required',
                Rule::exsits('user_addresses', 'id')->where(function ($query) {
                    $query->where("user_id", $this->user()->id);
                }),
            ],
            'items' => ['required', 'array'],
            'items.*.sku_id' => [ //检查 items 数组下的每一个子数组的 sku_id 参数
                'required',
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        return $fail('请选择商品');
                    }
                    if ($sku->product()->on_sale === 0) {
                        return $fail('商品未上架');
                    };
                    if ($sku->stock === 0) {
                        return $fail('该商品已售完');
                    }

                    //或读取当前索引
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                    $index = $m[0];
                    //根据索引找到用户所提交的购买数量
                    $amount = $this->input('items')[$index]['amount'];
                    if ($amount > 0 && $amount > $this->stock) {
                        return $fail('库存不足');
                    }
                }
            ],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
