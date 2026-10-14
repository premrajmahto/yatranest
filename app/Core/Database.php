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

                // Self-healing: Repair any mismatched destination imagery automatically
                try {
                    $badDest = self::$instance->query("
                        SELECT id FROM destinations 
                        WHERE (slug = 'assam' OR name LIKE '%Assam%') AND image_url LIKE '%photo-1537996194471%'
                           OR (slug = 'darjeeling' OR name LIKE '%Darjeeling%') AND image_url LIKE '%photo-1602216056096%'
                           OR (slug = 'manipur' OR name LIKE '%Manipur%') AND image_url LIKE '%photo-1595815771614%'
                           OR (slug = 'meghalaya' OR name LIKE '%Meghalaya%') AND image_url LIKE '%photo-1512343879784%'
                           OR (slug = 'mizoram' OR name LIKE '%Mizoram%') AND image_url LIKE '%photo-1477587458883%'
                           OR (slug = 'sikkim' OR name LIKE '%Sikkim%') AND image_url LIKE '%photo-1512453979798%'
                           OR (slug = 'nagaland' OR name LIKE '%Nagaland%') AND image_url LIKE '%photo-1626621341517%'
                           OR (slug = 'tripura' OR name LIKE '%Tripura%') AND image_url LIKE '%photo-1530122037265%'
                        LIMIT 1
                    ")->fetchColumn();

                    if ($badDest) {
                        $suitableImages = [
                            'arunachal-pradesh' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',
                            'assam' => 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80',
                            'meghalaya' => 'https://images.unsplash.com/photo-1609137144813-7d9921338f24?auto=format&fit=crop&w=1200&q=80',
                            'darjeeling' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
                            'manipur' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                            'mizoram' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                            'nagaland' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
                            'tripura' => 'https://images.unsplash.com/photo-1566837945700-30057527ade0?auto=format&fit=crop&w=1200&q=80',
                            'sikkim' => 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80',
                            'bhutan' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1200&q=80',
                        ];
                        $upStmt = self::$instance->prepare("
                            UPDATE destinations SET image_url = :img, is_featured = 1 
                            WHERE slug = :slug OR name LIKE :nameLike
                        ");
                        foreach ($suitableImages as $sSlug => $sImg) {
                            $upStmt->execute([
                                ':img' => $sImg,
                                ':slug' => $sSlug,
                                ':nameLike' => '%' . str_replace('-', ' ', $sSlug) . '%'
                            ]);
                        }
                    }
                } catch (\Throwable $repairEx) {}

                // Self-healing: Ensure updated contact details (phone, whatsapp, emails)
                try {
                    self::$instance->exec("
                        UPDATE site_settings 
                        SET setting_value = '+91 60005 95647' 
                        WHERE setting_key = 'company_phone' AND (setting_value LIKE '%98765%' OR setting_value = '');
                        UPDATE site_settings 
                        SET setting_value = 'yatranest18@gmail.com' 
                        WHERE setting_key = 'company_email' AND (setting_value LIKE '%yatranest.com%' OR setting_value = '');
                        UPDATE site_settings 
                        SET setting_value = 'Guwahati, Assam, India' 
                        WHERE setting_key = 'company_address' AND (setting_value LIKE '%Bengaluru%' OR setting_value = '' OR setting_value = 'Guwahati, India');
                    ");
                    $hasWa = self::$instance->query("SELECT id FROM site_settings WHERE setting_key = 'company_whatsapp' LIMIT 1")->fetchColumn();
                    if (!$hasWa) {
                        self::$instance->exec("
                            INSERT INTO site_settings (setting_key, setting_value, setting_group, description) 
                            VALUES ('company_whatsapp', '+91 60005 95647', 'general', 'Official WhatsApp Number (Direct Contact)')
                        ");
                    } else {
                        self::$instance->exec("
                            UPDATE site_settings 
                            SET setting_value = '+91 60005 95647' 
                            WHERE setting_key = 'company_whatsapp' AND (setting_value LIKE '%98765%' OR setting_value = '')
                        ");
                    }
                } catch (\Throwable $contactEx) {}
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
