<?php

namespace Halpdesk\LaravelMigrationCommands\Exceptions;

use Exception;
use Throwable;
use Halpdesk\LaravelMigrationCommands\Exception as LaravelMigrationCommandsException;

class MigrationException extends Exception implements LaravelMigrationCommandsException
{
    private $migration;

    public function __construct($message = 'migration_error', $migration, $code = 404, Trowable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getMigration()
    {
        return $this->migration;
    }
}
