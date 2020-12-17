<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserAddress extends Model
{
    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_userd_at',
    ];


    //定义时间日期关系，返回 Carbon
    protected $dates = ['last_used_at'];

    //定义数据模型关系，一个用户有多个地址
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //定义一个访问器，访问全部地址，避免每次都拼接
    public function getFullAddressAttribute()
    {
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }
}
