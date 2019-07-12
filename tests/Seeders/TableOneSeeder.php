<?php

namespace Halpdesk\LaravelMigrationCommands\Tests\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableOneSeeder extends Seeder
{
    public static $data = [
        [
            'string_nullable' => null,
            'integer_default' => 1,
            'float_index' => 0.5
        ],
        [
            'string_nullable' => 'foo',
            'integer_default' => 2,
            'float_index' => 0.7
        ]
    ];

    public function run()
    {
        DB::table('table_one')->insert(static::$data[0]);
        DB::table('table_one')->insert(static::$data[1]);
    }
}
