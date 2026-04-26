<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryPageIdToStorePageMetaTags extends Migration
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
            $table->unsignedBigInteger('country_name_id')->nullable();
            $table->foreign('country_name_id')->references('id')->on('country_names')->onDelete('cascade');
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
