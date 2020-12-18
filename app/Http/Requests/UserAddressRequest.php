<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
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
            'province'=>'required',
            'city'=>'required',
            'district'=>'required',
            'address'=>'required',
            'contact_name'=>'required',
            'contact_phone'=>'required',
        ];
    }

    public function attributes()
    {
        return [
            'province'=>'省',
            'city'=>'市',
            'district'=>'区',
            'address'=>'详细地址',
            'contact_name'=>'收货人',
            'contact_phone'=>'电话',
        ]
    }
}
