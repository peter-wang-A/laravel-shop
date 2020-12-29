<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrowdfundingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crowdfunding_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id')->comment('产品 ID');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->decimal('target_amount', 10, 2)->comment('目标金额');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('当前已筹金额');
            $table->unsignedInteger('user_count')->default(0)->comment('当前参与众筹用户数');
            $table->dateTime('end_at')->comment('众筹结束时间');
            $table->string('status')->default(\App\Models\CrowdfundingProduct::STATUS_FUNDING)->comment('当前筹款状态');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crowdfunding_products');
    }
}
