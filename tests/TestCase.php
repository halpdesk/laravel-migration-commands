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
use Illuminate\Contracts\Console\Kernel;
use Halpdesk\LaravelMigrationCommands\Console\Kernel as LaravelMigrationCommandsKernel;
use Absolute\DotEnvManipulator\Libs\DotEnv;

class TestCase extends OrchestraTestCase
{
    use CreatesDatabase;

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
        $this->getEnvironmentSetUp($this->app);


        // Set connection
        $migrator = app('migrator');
        $migrator->setConnection('mysql_testing');

        // Create database
        $this->createMysqlTestDatabase($this->app);

        // Load migration files
        $migrator->path(__DIR__.'/database/migrations');


        // $this->loadMigrationsFrom([
        //     '--database' => env('DB_CONNECTION'),
        //     '--path' => static::$dir . '/tests/database/migrations',
        // ]);
        // $this->withFactories(static::$dir . '/tests/database/factories');
        // $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
    }

    protected function tearDown(): void
    {
        $this->dropMysqlTestDatabase($this->app);
        parent::tearDown();
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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $dotenv = new DotEnv(static::$dir, '.env');
        $envs = $dotenv->toArray();
        foreach ($envs as $env => $value) {
            putenv($env.'='.$value);
        }

        $configFiles = glob(static::$dir.'/config/*.php');
        foreach ($configFiles as $configFile) {
            $name   = str_replace(".php", "", basename($configFile));
            $config = require $configFile;
            $existingConfig = Config::get($name, []);
            // Config::set($name, array_replace_recursive($existingConfig, $config));
            $app['config']->set($name, array_replace_recursive($existingConfig, $config));
        }
    }

    public function error($val)
    {
        dd($val);
    }
}
