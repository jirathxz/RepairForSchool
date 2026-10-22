<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * Singleton Database Manager
 * Handles PDO connections, transactions, and queries for MariaDB/MySQL.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Get or create PDO database connection
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::loadEnvironment();

            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_DATABASE') ?: 'repair_system_db';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
            $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
                self::$instance->exec("SET NAMES {$charset} COLLATE {$charset}_unicode_ci");
            } catch (PDOException $e) {
                // If database doesn't exist yet, connect to server without dbname
                if ($e->getCode() === 1049) {
                    $serverDsn = "mysql:host={$host};port={$port};charset={$charset}";
                    self::$instance = new PDO($serverDsn, $username, $password, $options);
                    self::$instance->exec("SET NAMES {$charset} COLLATE {$charset}_unicode_ci");
                } else {
                    throw new RuntimeException("Database connection error: " . $e->getMessage(), (int)$e->getCode(), $e);
                }
            }
        }

        return self::$instance;
    }

    /**
     * Execute a SQL query with parameters and return PDOStatement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all records matching query
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single record matching query
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch single scalar value
     */
    public static function fetchValue(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /**
     * Get last inserted ID
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }

    /**
     * Check database connectivity and health
     */
    public static function checkHealth(): array
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = (int)(getenv('DB_PORT') ?: 3306);
        $dbname = getenv('DB_DATABASE') ?: 'repair_system_db';
        $user = getenv('DB_USERNAME') ?: 'root';

        try {
            $pdo = self::getConnection();
            $info = self::fetchOne("SELECT VERSION() AS db_version, NOW() AS db_now, DATABASE() AS active_db");
            $tables = self::query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            return [
                'status' => 'connected',
                'host' => $host,
                'port' => $port,
                'user' => $user,
                'database' => $info['active_db'] ?? $dbname,
                'version' => $info['db_version'] ?? 'Unknown',
                'server_time' => $info['db_now'] ?? '',
                'tables_count' => count($tables),
                'tables' => $tables,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'disconnected',
                'host' => $host,
                'port' => $port,
                'user' => $user,
                'database' => $dbname,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Load .env file into environment
     */
    private static function loadEnvironment(): void
    {
        $envPath = __DIR__ . '/../../.env';
        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}
