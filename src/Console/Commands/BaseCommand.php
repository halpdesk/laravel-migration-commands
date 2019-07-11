<?php

namespace Halpdesk\LaravelMigrationCommands\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class BaseCommand extends Command
{
    protected $signature = "do:not:run";
    protected $description = "Base command";
    protected $startTime;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('name');
        $this->maxTime = 0;
        ini_set("max_execution_time", $this->maxTime);
        ini_set("max_input_time", "0");
        ini_set("memory_limit","8G");
        set_time_limit($this->maxTime);

        // If this is empty (which it might be if you wish to run commands from within the application)
        if (empty($this->output)) {
            $this->output = new ConsoleOutput();
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->start();
        $this->go();
        $this->end();
    }

    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->input->setOption($name, $value);
        }
    }

    protected function start()
    {
        $env = config("app.env");
        $this->comment("Application environment is set to " . $env . "\n");
        if ($env == "prod" || $env == "production") {
            if (!$this->confirm("Do you wish to continue?")) {
                $this->comment("Terminated.");
                die();
            }
        }
        $this->startTime = microtime(true);
    }

    protected function end()
    {
        // now we should be done
        $this->comment("\nDone.");
        $this->comment("Run time: " . (microtime(true) - $this->startTime) . "\n");
        // print(PHP_EOL); // don't use this. Causes all tests to write a new line
    }
}
