<?php

namespace Halpdesk\LaravelMigrationCommands;

use Illuminate\Support\ServiceProvider;
// use Halpdesk\LaravelMigrationCommands\Console\Commands\BaseCommand;
use Halpdesk\LaravelMigrationCommands\Console\Commands\DbReseed;
use Halpdesk\LaravelMigrationCommands\Console\Commands\MigrateAll;
use Halpdesk\LaravelMigrationCommands\Console\Commands\MigrateDropAll;
use Halpdesk\LaravelMigrationCommands\Console\Commands\MigrateSpecific;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->commands([
            DbReseed::class,
            MigrateAll::class,
            MigrateDropAll::class,
            MigrateSpecific::class,
        ]);
    }
}
