<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableTwoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_two', function (Blueprint $table) {
            $table->unique('string_unique', 'string_unique_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_two', function (Blueprint $table) {
            $table->dropIndex('string_unique_unique');
        });
        Schema::table('table_two', function (Blueprint $table) {
            $table->dropColumn('string_unique');
        });
    }
}
