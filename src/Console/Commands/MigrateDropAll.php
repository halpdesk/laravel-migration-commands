<?php

namespace Halpdesk\LaravelMigrationCommands\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateDropAll extends BaseCommand
{
    protected $signature = 'migrate:dropall { --force }';
    protected $description = 'Drops and re-creates all database or databases from input parameter';

    public function go()
    {
        $database   = env('DB_DATABASE', null);
        $default    = env('DB_CONNECTION', config('database.default'));
        $connection = config('database.connections')[$default];
        $this->comment('Driver is '. $connection['driver']);

        if (!$this->option('force')) {
            if (!$this->confirm('Do you wish to continue?')) {
                $this->comment('Aborted.');
                die();
            }
        }

        $this->info('Preparing to drop tables database (or cancel with CTRL+C)');
        if (!empty($database)) {
            $colname = 'Tables_in_' . env('DB_DATABASE');
            $this->line('> Database '.$database.' selected <');
            if (!$this->option('force')) {
                $this->getOutput()->write('Dropping in 3...' . "\r");
                foreach (range(0, 2) as $i) {
                    $this->getOutput()->write('Dropping in '. (3-$i) . '...' . "\r");
                    sleep(1);
                }
            }
            $this->line('');
            $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
            try {
                // begin transaction
                DB::beginTransaction();

                // disable foreign key checks
                $this->disableForeignKeyCheck($connection['driver']);
                $bar = $this->output->createProgressBar(count($tables));
                $bar->setFormat('%message% (<comment>%current:2s%/%max:2s%</comment>)');
                foreach ((array)$tables as $table) {
                    Schema::drop($table);
                    $bar->setMessage(str_pad('<info>Dropped table:</info> ' . $table, 80));
                    $this->line("");
                    $bar->advance();
                }
                $bar->finish();
                // enable foreign key checks
                $this->enableForeignKeyCheck($connection['driver']);

                // commit
                DB::commit();
            } catch (\Exception $e) {
                // rollback
                DB::rollback();
                throw $e;
            }
        } else {
            $this->info('<error>No database defined</error>');
        }
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
