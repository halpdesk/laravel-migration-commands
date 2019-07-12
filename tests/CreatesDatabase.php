<?php

/**
 * @author Daniel Leppänen
 */
namespace Halpdesk\LaravelMigrationCommands\Tests;

use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;

trait CreatesDatabase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function createMysqlTestDatabase($app)
    {
        Schema::defaultStringLength(191);

        list($connection,$host,$port,$user,$pass,$schemaName,$charset,$collation) = $this->getConfig($app);

        $app['config']->set('database.connections.'.$connection.'.database', null);

        $query = "CREATE DATABASE IF NOT EXISTS ". $schemaName .
        " CHARACTER SET ".$charset." COLLATE ". $collation;

        try {
            $pdo = $this->getPDOConnection($host, $port, $user, $pass);
            $pdo->exec($query);
        } catch (PDOException $exception) {
            $this->error('Failed to create '.$schemaName.' database: '. $exception->getMessage());
        }

        $app['config']->set('database.connections.'.$connection.'.database', $schemaName);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function dropMysqlTestDatabase($app)
    {
        list($connection,$host,$port,$user,$pass,$schemaName,$charset,$collation) = $this->getConfig($app);

        $query = "DROP DATABASE IF EXISTS ". $schemaName;

        try {
            $pdo = $this->getPDOConnection($host, $port, $user, $pass);
            $pdo->exec($query);

        } catch (PDOException $exception) {
            $this->error('Failed to drop '.$schemaName.' database: '. $exception->getMessage());
        }
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return Array    Application config
     */
    public function getConfig($app)
    {
        $connection = $app['config']->get('database.default');
        return [
            $connection,
            $app['config']->get('database.connections.'.$connection.'.host'),
            $app['config']->get('database.connections.'.$connection.'.port'),
            $app['config']->get('database.connections.'.$connection.'.username'),
            $app['config']->get('database.connections.'.$connection.'.password'),
            $app['config']->get('database.connections.'.$connection.'.database'),
            $app['config']->get('database.connections.'.$connection.'.charset') ?? 'utf8mb4',
            $app['config']->get('database.connections.'.$connection.'.collation') ?? 'utf8mb4_unicode_ci',
        ];
    }

    /**
     * @param  string $host
     * @param  integer $port
     * @param  string $username
     * @param  string $password
     * @return PDO
     */
    private function getPDOConnection($host, $port, $username, $password)
    {
        return new PDO(sprintf('mysql:host=%s;port=%d;', $host, $port), $username, $password);
    }
}
