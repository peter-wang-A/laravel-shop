<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Installment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_REPAYING = 'repaying';
    const STATUS_FINISHED = 'finished';

    public static $statusMap = [
        self::STATUS_PENDING => '未执行',
        self::STATUS_REPAYING => '还款中',
        self::STATUS_FINISHED => '已完成'
    ];

    protected $fillable = ['no', 'total_amount', 'count', 'fee_rate', 'fine_rate', 'status'];

    protected static function boot()
    {

         parent::boot();
        //监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            //如果模型的 no 字段为空
            if (!$model->no) {
                //调用 helpers findAvailableNo 生成分期流水号
                $model->no = findAvailableNo(static::query());
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(InstallmentItem::class);
    }
}
