<?php

namespace Halpdesk\LaravelMigrationCommands\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrateAll extends BaseCommand
{
    protected $signature = 'migrate:all { --s|seed }';
    protected $description = 'Migrates all migration files found in laravel default migration folder its sub folders, as well as regstred migrations from vendors';

    public function go()
    {
        if (!Schema::hasTable('migrations')) {
            $this->call('migrate:install');
            $this->line('');
        }

        $migrations = DB::table('migrations')->select('migration', 'batch')->orderByDesc('batch')->orderByDesc('migration')->get();
        $batches = [];
        foreach ($migrations as $migration) {
            $batches[$migration->batch][] = $migration->migration;
        }
        $paths = $this->getMigrations();

        // Rolling back migrations one step at a time
        $this->info('Rolling back tables...'."\n");
        $this->line(str_pad(str_pad('Files', 65) . 'Batch', 73) .' Num');
        foreach ($batches as $batch => $migrations) {
            $bar = new ProgressBar($this->output, count($migrations));
            // $bar = $this->output->createProgressBar(count($migrations));
            $bar->setFormat('%message% (<comment>%current:2s%/%max:2s%</comment>)');
            foreach ($migrations as $migration) {
                foreach ($paths as $path => $files) {
                    foreach (array_reverse($files) as $file) {
                        if (stripos($file, $migration) > 0) {
                            $this->callSilent('migrate:specific', ['--file' => $file, '--batch' => $batch, '--rollback' => true]);
                            $bar->setMessage(str_pad('<info>Rolled back:</info> '.$migration, 80).'[<comment>'.$batch.'</comment>]');
                            $this->line('');
                            $bar->advance();

                        }
                    }
                }
            }
            $bar->finish();
        }
        $this->line("\n");

        // Migrate
        $this->info('Migrating new tables...'."\n");
        $this->line(str_pad(str_pad('Files', 65) . 'Batch', 73) .' Num');
        $batch = 0;
        foreach ($paths as $i => $path) {
            $batch++;
            $bar = new ProgressBar($this->output, count($migrations));
            // $bar = $this->output->createProgressBar(count($path));
            $bar->setFormat('%message% (<comment>%current:2s%/%max:2s%</comment>)');
            foreach ($path as $file) {

                // Ignore files such as .gitkeep or .DS_store
                if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                    $this->callSilent('migrate:specific', ['--file' => $file, '--batch' => $batch]);
                    $bar->setMessage(str_pad('<info>Migrated:</info> '.substr(last(explode('/', $file)),0,-4), 80) . '[<comment>'.$batch.'</comment>]');
                    $this->line('');
                    $bar->advance();
                }
            }
            $bar->finish();
        }
        $this->line("\n");

        // Seed
        if ($this->option('seed')) {
            $this->info('Seeding database...'."\n");
            $this->call('db:seed');
        }
    }

    /**
     * Get directories for migration files provided by vendors
     *
     * @return Array    An array containing base directory and sub sirectories
     */
    private function getVendorDirectories()
    {
        $migrator = app('migrator');
        $paths = [];
        foreach ($migrator->paths() as $path) {
            $paths[] = realpath($path).'/';
        }
        return $paths;
    }

    /**
     * Get root directory and directories in laravel default path for migration files
     *
     * @return Array    An array containing base directory and sub sirectories
     */
    private function getBaseDirectories()
    {
        $path = base_path('database/migrations').'/'; // Yes, put a trailing slash here
        return array_merge((array)$path, glob($path.'*/', GLOB_ONLYDIR));
    }

    /**
     * Loads database migration files from laravel default path for migrations
     *
     * Also loads migration files from registered vendor packages
     * (i.e. They need to be registered by Laravel providers with loadMigrationsFrom)
     *
     * @return Array    A two-dimensional array containing migration files ordered by batch numbers
     */
    private function getMigrations()
    {
        $dirs = array_merge($this->getVendorDirectories(), $this->getBaseDirectories());
        $paths = [];
        foreach ($dirs as $batch => $dir) {
            $files = array_diff(scandir($dir), ['..', '.']);
            foreach ($files as $file) {
                $path = realpath($dir . $file);
                if (!is_dir($path)) {
                    $paths[$batch][] = $path;
                }
            }
        }
        return $paths;
    }
}
