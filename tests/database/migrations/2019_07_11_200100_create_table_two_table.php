<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTwoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_two', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('table_one_id');
            $table->string('string_unique')->default('a');
            $table->timestamps();
            $table->foreign('table_one_id')->references('id')->on('table_one')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_two');
    }
}
