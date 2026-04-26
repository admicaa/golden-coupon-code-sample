<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponPageIdToMetaTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_page_meta_tags', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('coupon_page_id')->nullable();
            $table->foreign('coupon_page_id')->references('id')->on('coupon_pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_page_meta_tags', function (Blueprint $table) {
            //
        });
    }
}
