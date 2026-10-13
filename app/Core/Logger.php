<?php

namespace App\Core;

class Logger {
    private static ?string $logDir = null;

    public static function init(string $storagePath): void {
        self::$logDir = rtrim($storagePath, '/\\') . '/logs';
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }

    public static function log(string $level, string $message, array $context = []): void {
        if (!self::$logDir) {
            self::$logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
        }

        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        $line = "[$date] [" . strtoupper($level) . "] $message$contextStr" . PHP_EOL;

        $file = self::$logDir . '/app-' . date('Y-m-d') . '.log';
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::log('error', $message, $context);
    }

    public static function security(string $message, array $context = []): void {
        self::log('security', $message, $context);
    }
}
