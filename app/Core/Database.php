<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = config('database');

            // SQLite connection
            if (($config['connection'] ?? 'mysql') === 'sqlite') {
                $dbPath = $config['database'];
                if (!str_starts_with($dbPath, '/') && !preg_match('/^[a-zA-Z]:/', $dbPath)) {
                    $dbPath = dirname(__DIR__, 2) . '/' . $dbPath;
                }
                $needSeed = !file_exists($dbPath) || filesize($dbPath) < 1000;
                if (!file_exists($dbPath)) {
                    @mkdir(dirname($dbPath), 0777, true);
                    touch($dbPath);
                }
                self::$instance = new PDO("sqlite:" . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                SqliteSeeder::registerFunctions(self::$instance);
                if ($needSeed) {
                    SqliteSeeder::seed(self::$instance);
                } else {
                    SqliteSeeder::checkAndRepairSchema(self::$instance);
                }
                return self::$instance;
            }

            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 3306,
                $config['database'] ?? 'yatranest_db',
                $config['charset'] ?? 'utf8mb4'
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'] ?? 'root',
                    $config['password'] ?? '',
                    $config['options'] ?? [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                $offsetSecs = (int)date('Z');
                $offsetHours = (int)($offsetSecs / 3600);
                $offsetMins = (int)(abs($offsetSecs) % 3600 / 60);
                $tzString = sprintf('%s%02d:%02d', $offsetHours >= 0 ? '+' : '-', abs($offsetHours), $offsetMins);
                self::$instance->exec("SET time_zone = '$tzString'");
            } catch (PDOException $e) {
                Logger::error("MySQL connection failed: " . $e->getMessage() . " - Activating automatic SQLite fallback.");

                try {
                    $sqlitePath = dirname(__DIR__, 2) . '/storage/database.sqlite';
                    $needSeed = !file_exists($sqlitePath) || filesize($sqlitePath) < 1000;

                    if (!file_exists($sqlitePath)) {
                        @mkdir(dirname($sqlitePath), 0777, true);
                        touch($sqlitePath);
                    }

                    self::$instance = new PDO("sqlite:" . $sqlitePath);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    SqliteSeeder::registerFunctions(self::$instance);

                    if ($needSeed) {
                        SqliteSeeder::seed(self::$instance);
                    } else {
                        SqliteSeeder::checkAndRepairSchema(self::$instance);
                    }

                    return self::$instance;
                } catch (\Throwable $fallbackEx) {
                    Logger::error("SQLite fallback failed: " . $fallbackEx->getMessage());
                    SetupHandler::handle($e, $config);
                }
            }
        }
        return self::$instance;
    }

    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
}
