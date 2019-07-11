<?php

/**
 *
 * Orchestra Testbench provides extra laravel functionality
 * such as load migrations etc. Read more here:
 * https://github.com/orchestral/testbench
 *
 * @author Daniel Leppänen
 */
namespace Halpdesk\LaravelMigrationCommands\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Support\Facades\Config;
use Halpdesk\LaravelMigrationCommands\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Console\Kernel;
use Halpdesk\LaravelMigrationCommands\Console\Kernel as LaravelMigrationCommandsKernel;

class TestCase extends OrchestraTestCase
{
    /**
     * @param String    The full path to the root of this project
     */
    protected static $dir;

    /**
     * Setup the test environment
     */
    protected function setUp() : void
    {
        parent::setUp();
        $this->initialize();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => static::$dir . '/tests/database/migrations',
        ]);
        $this->withFactories(static::$dir . '/tests/database/factories');
        // $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
    }

    public static function setUpBeforeClass()
    {
        static::$dir = realpath(dirname(realpath(__FILE__)).'/../');
        parent::setUpBeforeClass();
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(Kernel::class, LaravelMigrationCommandsKernel::class);
    }

    /**
     * This is usually loaded/bootstrapped from phpunit.xml otherwise
     *
     * @return void
     */
    public static function composerAutoLoader()
    {
        require_once static::$dir . 'vendor/autoload.php';
    }

    /**
     * Initialize environment
     * Set ini parameters here, for example
     *
     * @return void
     */
    public function initialize()
    {
        date_default_timezone_set('Europe/Stockholm');
    }
}
