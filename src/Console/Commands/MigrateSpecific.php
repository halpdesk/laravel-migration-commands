<?php

namespace Halpdesk\LaravelMigrationCommands\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Debug\Exception\FatalErrorException;

class MigrateSpecific extends BaseCommand
{
    protected $signature = 'migrate:specific { --file= } { --batch= } { --rollback }';
    protected $description = 'Migrate specific file';

    public function go()
    {
        // Create migrations table if not exists
        if (!Schema::hasTable('migrations')) {
            Artisan::call('migrate:install');
        }

        // Batch number
        $currentBatch = DB::table('migrations')->max('batch');
        $batch = !empty($this->option('batch')) ? $this->option('batch') : (is_null($currentBatch) ? 1 : $currentBatch + 1);

        // Require file
        $file = $this->option('file');
        $migration = substr(last(explode('/', $file)), 0, -4);
        if(!file_exists($file)) {
            $this->error('File does not exist: '.$file.' (try without the file extension).');
            throw new FatalErrorException('File does not exist: '.$file.' (try without the file extension).', 1, 5, __FILE__, (__LINE__)-4);
            die();
        }
        require_once($file);

        // Class instantiation
        $className = Str::studly(last(explode('_', $migration, 5)));

        if(class_exists($className)) {
            $class = new $className();

            // Run the migration
            if ($this->option('rollback')) {

                $class->down();

                DB::table('migrations')->where(['migration' => $migration])->delete();

                $this->line('<info>Rolled back:</info> '.$file);

            } else {

                $class->up();

                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch'     => $batch
                ]);

                $this->line('<info>Migrated:</info> '.$file);
            }

        // Else
        } else {
            $this->error($className . ' is not a class.');
            foreach (file($file) as $key => $line) {
                if (strpos($line, 'class') !== false) {
                    $lineNum =  $key + 1;
                    break;
                }
            }
            throw new FatalErrorException($className . ' is not a class.', 1, 5, $file, $lineNum ?? 0);
            die();
        }
    }
}
