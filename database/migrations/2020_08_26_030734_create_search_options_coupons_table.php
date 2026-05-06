<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchOptionsCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_options_coupons', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('search_option_id')->nullable();
            $table->foreign('search_option_id')->references('id')->on('search_options')->onDelete('cascade');

            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');

            $table->unsignedBigInteger('store_id')->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->index(['store_id',  'search_option_id'], 'search_options_coupons_store_option_idx');
            $table->index(['coupon_id', 'search_option_id'], 'search_options_coupons_coupon_option_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_options_coupons');
    }
}
