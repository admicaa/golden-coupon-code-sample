<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchOptionsPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_options_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('search_option_id')->nullable();
            $table->foreign('search_option_id')->references('id')->on('search_options')->onDelete('cascade');

            $table->string('language');
            $table->foreign('language')->references('shortcut')->on('languages')->onDelete('cascade');

            $table->string('name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_options_pages');
    }
}
