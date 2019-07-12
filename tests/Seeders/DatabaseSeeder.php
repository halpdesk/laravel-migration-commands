<?php

namespace Halpdesk\LaravelMigrationCommands\Tests\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            TableOneSeeder::class,
            TableTwoSeeder::class,
        ]);
    }

}
