<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use PDO;
use PDOException;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $this->clearCachedConfigForTests();
        $this->ensureTestingDatabaseExists();

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function clearCachedConfigForTests(): void
    {
        if (getenv('APP_ENV') !== 'testing') {
            return;
        }

        $cachedConfig = __DIR__ . '/../bootstrap/cache/config.php';
        if (is_file($cachedConfig)) {
            @unlink($cachedConfig);
        }
    }

    /**
     * Tests run against a dedicated MySQL schema because search migrations and
     * queries rely on MySQL FULLTEXT support.
     */
    protected function ensureTestingDatabaseExists(): void
    {
        if (getenv('APP_ENV') !== 'testing') {
            return;
        }

        $connection = getenv('DB_CONNECTION') ?: 'mysql';
        if (!in_array($connection, ['mysql', 'testing_mysql'], true)) {
            return;
        }

        $database = getenv('DB_TEST_DATABASE') ?: getenv('DB_DATABASE');
        if (!$database) {
            throw new RuntimeException('No MySQL test database name has been configured.');
        }

        $charset = getenv('DB_TEST_CHARSET') ?: 'utf8mb4';
        $collation = getenv('DB_TEST_COLLATION') ?: 'utf8mb4_unicode_ci';
        $username = getenv('DB_TEST_USERNAME') ?: getenv('DB_USERNAME') ?: 'forge';
        $password = getenv('DB_TEST_PASSWORD');
        $password = $password === false ? (getenv('DB_PASSWORD') ?: '') : $password;
        $socket = getenv('DB_TEST_SOCKET') ?: getenv('DB_SOCKET') ?: '';

        if ($socket !== '') {
            $dsn = sprintf('mysql:unix_socket=%s;charset=%s', $socket, $charset);
        } else {
            $host = getenv('DB_TEST_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_TEST_PORT') ?: getenv('DB_PORT') ?: '3306';
            $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $host, $port, $charset);
        }

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $quotedDatabase = str_replace('`', '``', $database);
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                $quotedDatabase,
                $charset,
                $collation
            ));
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf(
                'Unable to prepare the MySQL test database [%s]. Check DB_TEST_* settings and ensure MySQL is running.',
                $database
            ), 0, $e);
        }
    }
}
