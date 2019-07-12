<?php

namespace Halpdesk\LaravelMigrationCommands\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbReseed extends Command
{
    protected $signature = 'db:reseed
        { --class=DatabaseSeeder : The class name of the root seeder }
        { --connection= : Connection specified in database config }';
    protected $description = 'Disable foreign keychecks, truncate all tables, move primary key and seeds database';

    public function handle()
    {
        $connection = empty($this->option('connection')) ? config('database.default') : $this->option('connection');
        $connectionConfig = config('database.connections')[$connection];

        $this->info("\nTruncating database...");
        // begin transaction
        DB::beginTransaction();
        // disable foreign key checks
        $this->disableForeignKeyCheck($connectionConfig['driver']);

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $bar = $this->output->createProgressBar(count($tables));
        $bar->setFormat('%message% (<comment>%current:2s%/%max:2s%</comment>)');
        foreach ((array)$tables as $table) {
            DB::statement('TRUNCATE '.$table);
            $bar->setMessage(str_pad('<info>Truncated table:</info> ' . $table, 80));
            $this->line("");
            $bar->advance();
        }
        // enable foreign key checks
        $this->enableForeignKeyCheck($connectionConfig['driver']);
        // commit
        DB::commit();

        $this->info("\n\nSeeding database...\n");

        $this->call("db:seed", ['--class' => $this->option('class')]);
    }

    private function disableForeignKeyCheck($driver)
    {
        $sql='';
        switch ($driver) {
            case 'mysql':
                $sql='SET FOREIGN_KEY_CHECKS = 0';
                break;
            case 'sqlite':
                $sql='PRAGMA foreign_keys = OFF';
                break;
        }
        DB::statement($sql);
    }

    private function enableForeignKeyCheck($driver)
    {
        $sql='';
        switch ($driver) {
            case 'mysql':
                $sql='SET FOREIGN_KEY_CHECKS = 1';
                break;
            case 'sqlite':
                $sql='PRAGMA foreign_keys = ON';
                break;
        }
        DB::statement($sql);
    }
}
