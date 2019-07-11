<?php

/**
 * @author Daniel Leppänen
 */
namespace Halpdesk\LaravelMigrationCommands\Tests;

use Halpdesk\LaravelMigrationCommands\Console\Commands\MigrateAll;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateAllTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $migrator = app('migrator');
        $migrator->path(__DIR__.'/database/migrations');
    }

    public function testMigrateAll()
    {

        // Artisan::call('migrate:all');
        $command = new MigrateAll();
        $command->go();
        $hasTable = Schema::hasTable('users');
        $this->assertTrue($hasTable);
    }
}
