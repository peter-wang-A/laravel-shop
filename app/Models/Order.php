<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Order extends Model
{
    //众筹状态常量
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNFING = 'crowdfunding';

    public static $typeMap = [
        self::TYPE_NORMAL => '普通商品订单',
        self::TYPE_CROWDFUNFING => '众筹商品订单'
    ];
    //退款状态
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_APPLIED    => '已申请退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
    ];

    //物流状态
    const SHIP_STATUS_PENDING = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED = 'received';

    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING   => '未发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED  => '已收货',
    ];

    protected $fillable = [
        'type',
        'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
    ];

    //确定再短存储类型
    protected $casts = [
        'closed'    => 'boolean',
        'reviewed'  => 'boolean',
        'address'   => 'json',
        'ship_data' => 'json',
        'extra'     => 'json',
    ];

    //时间类型字段
    protected $dates = [
        'paid_at',
    ];

    //监听存入事件, 自动生成订单的流水号，静态类
    protected static function boot()
    {
        //重写父类方法
        parent::boot();

        // 在写入数据库之间触发
        static::creating(function ($model) {
            if (!$model->no) {
                //调用 findAvailable 类生成流水订单号
                $model->no = static::findAvailable();

                //如果生成失败，停止执行程序
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    //一个订单只属于一个用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //一个订单有多个商品
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    //一个订单一个优惠券
    public function coupon()
    {
        return $this->belongsTo(CouponCode::class);
    }

    public static function getAvailableRefundNo()
    {
        do {
            // Uuid类可以用来生成大概率不重复的字符串
            $no = Uuid::uuid4()->getHex();
            // 为了避免重复我们在生成之后在数据库中查询看看是否已经存在相同的退款订单号
        } while (self::query()->where('refund_no', $no)->exists());

        return $no;
    }

    public static function findAvailable()
    {
        //订单流水号前缀
        $prefix = date('YmdHis');

        //随机生成6位数字,循环10词选一
        for ($i = 0; $i < 10; $i++) {
            // str_pad() 函数把字符串填充为新的长度
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            //判断模型是否存在某个记录，返回 boolean,find 返回模型对象
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }

        \Log::warning('find order no failed');
        return false;
    }
}
