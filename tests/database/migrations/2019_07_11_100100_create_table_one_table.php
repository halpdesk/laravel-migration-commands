<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_one', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('string_nullable')->nullable();
            $table->integer('integer_default')->default(5);
            $table->float('float_index', 8, 2)->index('float_index_index');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_one');
    }
}
