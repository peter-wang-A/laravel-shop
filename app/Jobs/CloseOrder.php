<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $order;
    public function __construct(Order $order, $delay)
    {
        $this->order = $order;
        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //判断订单是否已经支付,如果已经支付就不需要关闭订单
        if ($this->order->paid_at) {
            return;
        }


        \DB::transaction(function () {
            //将库存加到 stock 中去
            $this->order->update([
                'closed' => true
            ]);
        });

        //加库存,循环出每一个 SKU ，将 SKU 的数量加到库存中去
        foreach ($this->order->items() as $item) {
            $item->ProductSku->addStock($item->amount);
        }
    }
}
