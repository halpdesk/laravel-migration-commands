<?php

/**
 * @author Daniel Leppänen
 */
namespace Halpdesk\LaravelMigrationCommands\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Halpdesk\LaravelMigrationCommands\Tests\Seeders\TableOneSeeder;
use Halpdesk\LaravelMigrationCommands\Tests\Seeders\TableTwoSeeder;
use Halpdesk\LaravelMigrationCommands\Tests\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;

class CommandsTest extends TestCase
{
    protected $sm;

    public function setUp() : void
    {
        parent::setUp();
        $this->sm = DB::connection()->getDoctrineSchemaManager();
    }

    public function testMigrateAll()
    {
        $this->artisan('migrate:all', [])->run();
        $this->artisan('migrate:all', [])->run();

        // Tables
        $tables = $this->sm->listTableNames();
        foreach (['migrations', 'table_one', 'table_two'] as $key => $value) {
            $this->assertArrayHasKey($key, $tables);
            $this->assertSame($value, $tables[$key]);
        }

        // Table one
        $this->assertTrue(Schema::hasColumns('table_one', [
            'id',
            'string_nullable',
            'integer_default',
        ]));
        $tableOne = $this->sm->listTableDetails('table_one');
        $this->assertTrue($tableOne->hasPrimaryKey());
        $this->assertTrue($tableOne->hasIndex('float_index_index'));

        // Table two
        $this->assertTrue(Schema::hasColumns('table_two', [
            'id',
            'table_one_id',
            'string_unique',
        ]));
        $tableTwo = $this->sm->listTableDetails('table_two');
        $this->assertTrue($tableTwo->hasPrimaryKey());
        $this->assertTrue($tableTwo->hasForeignKey('table_two_table_one_id_foreign'));
        $this->assertTrue($tableTwo->hasIndex('string_unique_unique'));
    }

    public function testMigrateDropAll()
    {
        $this->artisan('migrate:all', [])->run();
        $this->artisan('migrate:dropall', ['--force' => true])->run();

        $tables = $this->sm->listTableNames();

        $this->assertEmpty($tables);
    }

    public function testMigrateSpecificTableOne()
    {
        $this->artisan('migrate:specific', [
            '--file' => __DIR__.'/database/migrations/2019_07_11_100100_create_table_one_table.php'
        ])->run();

        $tables = $this->sm->listTableNames();

        foreach (['migrations', 'table_one'] as $key => $value) {
            $this->assertArrayHasKey($key, $tables);
            $this->assertSame($value, $tables[$key]);
        }
        $this->assertCount(2, $tables); // only migrations and table_one
        $this->assertTrue(in_array('table_one', $tables));
        $this->assertFalse(in_array('table_two', $tables));
    }

    public function testMigrateSpecificTableOneAndTableTwo()
    {
        $this->artisan('migrate:specific', [
            '--file' => __DIR__.'/database/migrations/2019_07_11_100100_create_table_one_table.php'
        ])->run();
        $this->artisan('migrate:specific', [
            '--file' => __DIR__.'/database/migrations/2019_07_11_200100_create_table_two_table.php'
        ])->run();

        $tables = $this->sm->listTableNames();
        foreach (['migrations', 'table_one', 'table_two'] as $key => $value) {
            $this->assertArrayHasKey($key, $tables);
            $this->assertSame($value, $tables[$key]);
        }
        $tableTwo = $this->sm->listTableDetails('table_two');
        $this->assertFalse($tableTwo->hasIndex('string_unique_unique'));
    }

    public function testMigrateAllAndRollbackTableTwoUpdate()
    {
        $this->artisan('migrate:all', [])->run();
        $this->artisan('migrate:specific', [
            '--file' => __DIR__.'/database/migrations/2019_07_11_200200_update_table_two_table.php',
            '--rollback' => true
        ])->run();

        $tables = $this->sm->listTableNames();
        foreach (['migrations', 'table_one', 'table_two'] as $key => $value) {
            $this->assertArrayHasKey($key, $tables);
            $this->assertSame($value, $tables[$key]);
        }
        $tableTwo = $this->sm->listTableDetails('table_two');
        $this->assertFalse($tableTwo->hasIndex('string_unique_unique'));
    }

    public function testDbReseed()
    {
        $this->artisan('migrate:all', [])->run();
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])->run();

        $this->assertDatabaseHas('table_one', TableOneSeeder::$data[0]);
        $this->assertDatabaseHas('table_one', TableOneSeeder::$data[1]);
        $this->assertDatabaseHas('table_two', TableTwoSeeder::$data[0]);
        $this->assertDatabaseHas('table_two', TableTwoSeeder::$data[1]);

        // Since table_two have unique constraint we should get duplicate entry from seeders
        try {
            $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])->run();
        } catch (QueryException $e) {
            $this->assertStringContainsString('Duplicate entry', $e->getMessage());
        }

        // This should work
        $this->artisan('db:reseed', ['--class' => DatabaseSeeder::class])->run();
    }
}
