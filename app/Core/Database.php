<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = config('database');
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
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
                Logger::error("Database connection failed: " . $e->getMessage());
                SetupHandler::handle($e, $config);
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
