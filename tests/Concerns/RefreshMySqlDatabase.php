<?php

namespace Tests\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use RuntimeException;

trait RefreshMySqlDatabase
{
    use CanConfigureMigrationCommands;

    protected ?string $isolatedDatabaseName = null;
    protected ?string $originalDatabaseName = null;

    protected function setUpRefreshMySqlDatabase(): void
    {
        $this->beforeRefreshingMySqlDatabase();

        if ($this->usesMySqlTestConnection()) {
            $this->prepareIsolatedMySqlDatabase();
            $this->artisan('migrate', ['--database' => config('database.default'), '--force' => true]);
            $this->runSeedersIfConfigured();
        } else {
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());
        }

        $this->app[Kernel::class]->setArtisan(null);
        $this->afterRefreshingMySqlDatabase();
    }

    protected function beforeRefreshingMySqlDatabase(): void
    {
        // Override in a test class when pre-refresh hooks are needed.
    }

    protected function afterRefreshingMySqlDatabase(): void
    {
        // Override in a test class when post-refresh hooks are needed.
    }

    protected function usesMySqlTestConnection(): bool
    {
        $connection = config('database.default');

        return config("database.connections.{$connection}.driver") === 'mysql';
    }

    protected function prepareIsolatedMySqlDatabase(): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (!is_array($config)) {
            throw new RuntimeException('Unable to resolve the default database connection configuration for tests.');
        }

        $this->originalDatabaseName = $config['database'] ?? null;
        $this->isolatedDatabaseName = $this->makeIsolatedDatabaseName((string) ($this->originalDatabaseName ?: 'testing'));

        DB::disconnect($connection);
        DB::purge($connection);

        config([
            "database.connections.{$connection}.database" => $this->isolatedDatabaseName,
        ]);

        $this->recreateDatabase($config, $this->isolatedDatabaseName);

        $this->beforeApplicationDestroyed(function () use ($connection, $config) {
            DB::disconnect($connection);
            DB::purge($connection);

            if ($this->isolatedDatabaseName !== null) {
                $this->dropDatabase($config, $this->isolatedDatabaseName);
            }

            config([
                "database.connections.{$connection}.database" => $this->originalDatabaseName,
            ]);

            $this->isolatedDatabaseName = null;
        });
    }

    protected function runSeedersIfConfigured(): void
    {
        if ($seeder = $this->seeder()) {
            $this->artisan('db:seed', ['--class' => $seeder, '--force' => true]);
            return;
        }

        if ($this->shouldSeed()) {
            $this->artisan('db:seed', ['--force' => true]);
        }
    }

    protected function makeIsolatedDatabaseName(string $base): string
    {
        $sanitizedBase = preg_replace('/[^A-Za-z0-9_]/', '_', $base) ?: 'testing';
        $suffix = substr(md5(static::class . '::' . microtime(true) . '::' . spl_object_id($this)), 0, 10);

        return substr($sanitizedBase, 0, 48) . '_' . $suffix;
    }

    protected function recreateDatabase(array $config, string $database): void
    {
        $pdo = $this->serverPdo($config);
        $quotedDatabase = str_replace('`', '``', $database);
        $charset = $config['charset'] ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

        try {
            $pdo->exec("DROP DATABASE IF EXISTS `{$quotedDatabase}`");
            $pdo->exec("CREATE DATABASE `{$quotedDatabase}` CHARACTER SET {$charset} COLLATE {$collation}");
        } catch (PDOException $e) {
            throw new RuntimeException("Unable to create isolated MySQL test database [{$database}].", 0, $e);
        }
    }

    protected function dropDatabase(array $config, string $database): void
    {
        $pdo = $this->serverPdo($config);
        $quotedDatabase = str_replace('`', '``', $database);

        try {
            $pdo->exec("DROP DATABASE IF EXISTS `{$quotedDatabase}`");
        } catch (PDOException $e) {
            throw new RuntimeException("Unable to drop isolated MySQL test database [{$database}].", 0, $e);
        }
    }

    protected function serverPdo(array $config): PDO
    {
        $charset = $config['charset'] ?? 'utf8mb4';
        $username = $config['username'] ?? 'forge';
        $password = $config['password'] ?? '';
        $socket = $config['unix_socket'] ?? '';

        if ($socket !== '') {
            $dsn = sprintf('mysql:unix_socket=%s;charset=%s', $socket, $charset);
        } else {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $host, $port, $charset);
        }

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    }
}
