<?php

namespace Halpdesk\LaravelMigrationCommands\Tests\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableTwoSeeder extends Seeder
{
    public static $data = [
        [
            'table_one_id' => 1,
            'string_unique' => 'foo_asdf',
        ],
        [
            'table_one_id' => 2,
            'string_unique' => 'bar_asdf',
        ]
    ];

    public function run()
    {
        DB::table('table_two')->insert(static::$data[0]);
        DB::table('table_two')->insert(static::$data[1]);
    }
}
