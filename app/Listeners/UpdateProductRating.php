<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use App\Events\OrderReviewed;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// implements ShouldQueue 代表这个事件处理器是异步的
class UpdateProductRating implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        //取到订单商品
        $items = $event->items()->with(['product'])->get();

        //循环每一个商品
        foreach ($items as $item) {
            //查出评论过切支付过的商品
            $result = OrderItem::query()
                ->where('product_id', $item->product_id)
                ->whereNotNull('reviewed_at')
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    DB::raw('count(*) as review_count'),
                    DB::raw('avg(rating) as rating')
                ]);
            //更新商品评分
            $item->product->update([
                'review_count' => $result->review_count,
                'rating' => $result->rating
            ]);
        }
    }
}
