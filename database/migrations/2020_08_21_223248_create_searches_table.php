<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('searches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');

            for ($i = 1; $i < 6; $i++) {
                $table->text('stage_' . $i)->nullable();
            }

            $table->string('language')->default(language());
            $table->foreign('language')->references('shortcut')->on('languages')->onDelete('cascade');
            $table->timestamps();

            $table->index(['coupon_id', 'store_id'], 'searches_coupon_store_idx');
            $table->index(['store_id', 'coupon_id'], 'searches_store_coupon_idx');
        });
        $fullStageArray  = [];
        for ($i = 1; $i < 6; $i++) {
            DB::statement('ALTER TABLE searches ADD FULLTEXT(stage_' . $i . ')');
            array_push($fullStageArray, 'stage_' . $i);
        }
        $fullStageText = implode(',', $fullStageArray);
        DB::statement('ALTER TABLE searches ADD FULLTEXT INDEX `all_index` (' . $fullStageText . ')');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('searches');
    }
}
