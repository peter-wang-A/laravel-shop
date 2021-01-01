<?php

namespace App\Console\Commands\Cron;

use App\Jobs\RefundCrowdfundingOrders;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FinishCrowdfunding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:finish-crowdfunding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结束众筹';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CrowdfundingProduct::query()
            //众筹时间早已当前时间
            ->where('end_at', '<=', Carbon::now())
            // 众筹状态为众筹中
            ->where('status', CrowdfundingProduct::STATUS_FUNDING)
            ->get()
            ->each(function (CrowdfundingProduct $crowdfunding) {
                // 如果众筹目标金额大于实际众筹金额
                if ($crowdfunding->total_amount < $crowdfunding->target_amount) {
                    // 调用众筹失败逻辑
                    $this->crowdfundingFailed($crowdfunding);
                } else {
                    // 否则调用众筹成功逻辑
                    $this->crowdfundingSucceed($crowdfunding);
                }
            });
    }

    //众筹成功逻辑
    public function crowdfunfingSucceed($crowdfunding)
    {
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_SUCCESS
        ]);
    }

    //众筹失败逻辑
    public function crowdfundingFailed($crowdfunding)
    {
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_FAIL
        ]);
        dispatch(new RefundCrowdfundingOrders($crowdfunding));
    }
}
